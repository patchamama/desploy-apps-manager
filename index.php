<?php
session_start();
require_once 'config.php';

// Directories for state and logs
define('PID_DIR', __DIR__ . '/.pids');
define('LOG_DIR', __DIR__ . '/.logs');
define('STATE_FILE', __DIR__ . '/.services-state.json');
define('GITHUB_CONFIG_FILE', __DIR__ . '/.github-config.json');

// Create directories if they don't exist
if (!is_dir(PID_DIR)) mkdir(PID_DIR, 0755, true);
if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0755, true);

// Load GitHub configuration
function loadGithubConfig() {
    if (file_exists(GITHUB_CONFIG_FILE)) {
        return json_decode(file_get_contents(GITHUB_CONFIG_FILE), true);
    }
    return null;
}

// Get GitHub repository URL for a project
function getGitRepoUrl($projectPath) {
    if (!is_dir($projectPath . '/.git')) {
        return null;
    }

    $output = shell_exec('cd ' . escapeshellarg($projectPath) . ' && git config --get remote.origin.url 2>/dev/null');
    $url = trim($output);

    if (empty($url)) {
        return null;
    }

    // Convert SSH URL to HTTPS URL if necessary
    if (preg_match('/git@github\.com:(.+?)\.git$/', $url, $matches)) {
        return 'https://github.com/' . $matches[1];
    }

    // Clean HTTPS URL
    if (preg_match('/https:\/\/github\.com\/(.+?)(\.git)?$/', $url, $matches)) {
        return 'https://github.com/' . $matches[1];
    }

    return $url;
}

// Execute git fetch and pull with authentication
function gitPull($projectPath) {
    if (!is_dir($projectPath . '/.git')) {
        return ['success' => false, 'message' => 'Not a git repository'];
    }

    $config = loadGithubConfig();
    $output = [];

    // Get repository URL
    $repoUrl = shell_exec('cd ' . escapeshellarg($projectPath) . ' && git config --get remote.origin.url 2>/dev/null');
    $repoUrl = trim($repoUrl);

    if (empty($repoUrl)) {
        return ['success' => false, 'message' => 'No remote repository configured'];
    }

    // Configure credentials temporarily if we have token
    $credentialHelper = '';
    if ($config && !empty($config['token'])) {
        // Use environment variables for authentication
        $credentialHelper = 'GIT_ASKPASS=echo GIT_USERNAME=' . escapeshellarg($config['username'] ?? '') . ' ';
        // Configure credential helper for this command
        putenv('GIT_TERMINAL_PROMPT=0');
    }

    // Build URL with embedded token if necessary
    $gitEnv = '';
    if ($config && !empty($config['token']) && strpos($repoUrl, 'https://github.com') === 0) {
        // Configure temporary remote URL with token
        $authUrl = str_replace('https://github.com', 'https://' . $config['token'] . '@github.com', $repoUrl);
        shell_exec('cd ' . escapeshellarg($projectPath) . ' && git remote set-url origin ' . escapeshellarg($authUrl) . ' 2>&1');
    }

    // Execute git fetch
    $fetchCmd = 'cd ' . escapeshellarg($projectPath) . ' && git fetch origin 2>&1';
    $fetchOutput = shell_exec($fetchCmd);
    $output[] = "=== Git Fetch ===";
    $output[] = $fetchOutput ?: 'OK';

    // Execute git pull
    $pullCmd = 'cd ' . escapeshellarg($projectPath) . ' && git pull origin $(git branch --show-current) 2>&1';
    $pullOutput = shell_exec($pullCmd);
    $output[] = "\n=== Git Pull ===";
    $output[] = $pullOutput ?: 'OK';

    // Restore original URL (without token) for security
    if ($config && !empty($config['token']) && strpos($repoUrl, 'https://github.com') === 0) {
        shell_exec('cd ' . escapeshellarg($projectPath) . ' && git remote set-url origin ' . escapeshellarg($repoUrl) . ' 2>&1');
    }

    // Check final status
    $statusCmd = 'cd ' . escapeshellarg($projectPath) . ' && git log -1 --format="%h - %s (%cr)" 2>&1';
    $statusOutput = shell_exec($statusCmd);
    $output[] = "\n=== Last commit ===";
    $output[] = $statusOutput;

    return [
        'success' => true,
        'message' => 'Repository updated',
        'output' => implode("\n", $output)
    ];
}

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['authenticated']) &&
           $_SESSION['authenticated'] === true &&
           isset($_SESSION['last_activity']) &&
           (time() - $_SESSION['last_activity']) < SESSION_TIMEOUT;
}

// Load services state
function loadServicesState() {
    if (file_exists(STATE_FILE)) {
        $state = json_decode(file_get_contents(STATE_FILE), true);
        return is_array($state) ? $state : [];
    }
    return [];
}

// Save services state
function saveServicesState($state) {
    file_put_contents(STATE_FILE, json_encode($state, JSON_PRETTY_PRINT));
}

// Check if a process is running by PID
function isProcessRunning($pid) {
    if (empty($pid) || !is_numeric($pid)) return false;
    return file_exists("/proc/$pid");
}

// Check if a port is in use
function isPortInUse($port) {
    if (empty($port)) return false;
    $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 2);
    if ($connection) {
        fclose($connection);
        return true;
    }
    return false;
}

// Check if a service is really running
function isServiceRunning($projectName) {
    $state = loadServicesState();

    // If not in state, it's not running
    if (!isset($state[$projectName])) {
        return false;
    }

    $serviceState = $state[$projectName];
    $isRunning = false;

    // Check by main PID
    if (!empty($serviceState['pid']) && isProcessRunning($serviceState['pid'])) {
        $isRunning = true;
    }

    // Check by multiple PIDs (al menos uno debe estar corriendo)
    if (!empty($serviceState['pids']) && is_array($serviceState['pids'])) {
        foreach ($serviceState['pids'] as $pid) {
            if (isProcessRunning($pid)) {
                $isRunning = true;
                break;
            }
        }
    }

    // Check by single port
    if (!empty($serviceState['port']) && isPortInUse($serviceState['port'])) {
        $isRunning = true;
    }

    // Check by multiple ports (al menos uno debe estar en uso)
    if (!empty($serviceState['portsList']) && is_array($serviceState['portsList'])) {
        foreach ($serviceState['portsList'] as $port) {
            if (isPortInUse($port)) {
                $isRunning = true;
                break;
            }
        }
    }

    // If not running, clean state
    if (!$isRunning) {
        unset($state[$projectName]);
        saveServicesState($state);
    }

    return $isRunning;
}

// Get services using a specific port
function getServicesUsingPort($port, $excludeProject = null) {
    $state = loadServicesState();
    $services = [];

    foreach ($state as $name => $info) {
        if ($name !== $excludeProject) {
            $usesPort = false;

            // Check main port
            if (!empty($info['port']) && $info['port'] == $port) {
                $usesPort = true;
            }

            // Check ports list
            if (!empty($info['portsList']) && is_array($info['portsList'])) {
                if (in_array($port, $info['portsList'])) {
                    $usesPort = true;
                }
            }

            if ($usesPort && isServiceRunning($name)) {
                $services[] = $name;
            }
        }
    }

    return $services;
}

// Stop a service
function stopService($projectName) {
    $state = loadServicesState();
    $projectPath = __DIR__ . '/' . $projectName;
    $infoFile = $projectPath . '/.project-info.json';

    if (!file_exists($infoFile)) {
        return false;
    }

    $info = json_decode(file_get_contents($infoFile), true);

    // Use stop command if exists
    if (!empty($info['stopCommand'])) {
        exec('cd ' . escapeshellarg($projectPath) . ' && ' . $info['stopCommand'] . ' 2>&1');
        sleep(1);
    }

    // Kill by saved PID (principal)
    if (isset($state[$projectName]['pid'])) {
        $pid = $state[$projectName]['pid'];
        if (isProcessRunning($pid)) {
            exec("kill $pid 2>/dev/null");
            usleep(500000);
            if (isProcessRunning($pid)) {
                exec("kill -9 $pid 2>/dev/null");
            }
        }
    }

    // Kill by multiple PIDs si existen (para proyectos con múltiples scripts)
    if (isset($state[$projectName]['pids']) && is_array($state[$projectName]['pids'])) {
        foreach ($state[$projectName]['pids'] as $pid) {
            if (isProcessRunning($pid)) {
                exec("kill $pid 2>/dev/null");
                usleep(500000);
                if (isProcessRunning($pid)) {
                    exec("kill -9 $pid 2>/dev/null");
                }
            }
        }
    }

    // Kill processes on all configured ports
    $portsToKill = [];
    if (isset($state[$projectName]['port'])) {
        $portsToKill[] = $state[$projectName]['port'];
    }
    if (isset($state[$projectName]['portsList']) && is_array($state[$projectName]['portsList'])) {
        $portsToKill = array_merge($portsToKill, $state[$projectName]['portsList']);
    }
    $portsToKill = array_unique($portsToKill);

    foreach ($portsToKill as $port) {
        if (isPortInUse($port)) {
            $pids = shell_exec("lsof -i :$port -sTCP:LISTEN -t 2>/dev/null");
            if (!empty(trim($pids))) {
                $pidArray = array_filter(explode("\n", trim($pids)));
                foreach ($pidArray as $pid) {
                    $pid = trim($pid);
                    if (is_numeric($pid)) {
                        exec("kill $pid 2>/dev/null");
                        usleep(500000);
                        if (file_exists("/proc/$pid")) {
                            exec("kill -9 $pid 2>/dev/null");
                        }
                    }
                }
            }
        }
    }

    // Clean PID file
    $pidFile = PID_DIR . '/' . $projectName . '.pid';
    if (file_exists($pidFile)) {
        unlink($pidFile);
    }

    // Update state
    unset($state[$projectName]);
    saveServicesState($state);

    return true;
}

// Execute individual script and return its PID
function executeScript($projectPath, $scriptConfig, $logFile) {
    $scriptFile = $scriptConfig['script'];
    $scriptType = $scriptConfig['type'] ?? 'bash';
    $scriptName = $scriptConfig['name'] ?? $scriptFile;

    // Separate file and arguments
    $scriptParts = explode(' ', $scriptFile, 2);
    $scriptFileOnly = $scriptParts[0];
    $scriptArgs = isset($scriptParts[1]) ? $scriptParts[1] : '';

    $pid = null;

    // Add header to log
    file_put_contents($logFile, "\n=== Starting: $scriptName [" . date('Y-m-d H:i:s') . "] ===\n", FILE_APPEND);

    switch ($scriptType) {
        case 'python':
            $command = sprintf(
                'cd %s && nohup python3 %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath),
                escapeshellarg($scriptFileOnly),
                $scriptArgs,
                escapeshellarg($logFile)
            );
            $pid = trim(shell_exec($command));
            break;

        case 'node':
            $command = sprintf(
                'cd %s && nohup node %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath),
                escapeshellarg($scriptFileOnly),
                $scriptArgs,
                escapeshellarg($logFile)
            );
            $pid = trim(shell_exec($command));
            break;

        case 'php':
            $command = sprintf(
                'cd %s && nohup php %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath),
                escapeshellarg($scriptFileOnly),
                $scriptArgs,
                escapeshellarg($logFile)
            );
            $pid = trim(shell_exec($command));
            break;

        case 'bash':
        default:
            $command = sprintf(
                'cd %s && nohup bash %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath),
                escapeshellarg($scriptFileOnly),
                $scriptArgs,
                escapeshellarg($logFile)
            );
            $pid = trim(shell_exec($command));
    }

    return is_numeric($pid) ? (int)$pid : null;
}

// Mark service as running
function markServiceRunning($projectName, $pid, $port = null, $portsList = null, $pids = null) {
    $state = loadServicesState();
    $state[$projectName] = [
        'pid' => $pid,
        'port' => $port,
        'started_at' => date('Y-m-d H:i:s')
    ];

    // Add ports list if available
    if ($portsList && is_array($portsList)) {
        $state[$projectName]['portsList'] = $portsList;
    }

    // Add PIDs list if available (para múltiples scripts)
    if ($pids && is_array($pids)) {
        $state[$projectName]['pids'] = $pids;
    }

    saveServicesState($state);
}

// Get all running services
function getRunningServices() {
    $state = loadServicesState();
    $running = [];

    foreach ($state as $name => $info) {
        if (isServiceRunning($name)) {
            $running[$name] = $info;
        }
    }

    return $running;
}

// Get ports to projects mapping from .project-info.json files
function getAllProjectPorts() {
    $portMap = [];
    $excludeDirs = ['.', '..', 'assets'];
    $dir = __DIR__;

    foreach (scandir($dir) as $item) {
        if (is_dir($dir . '/' . $item) && !in_array($item, $excludeDirs) && $item[0] !== '.') {
            $projectPath = $dir . '/' . $item;
            $infoFile = $projectPath . '/.project-info.json';

            if (file_exists($infoFile)) {
                $info = json_decode(file_get_contents($infoFile), true);
                if ($info) {
                    $ports = [];

                    // Collect main port
                    if (!empty($info['port'])) {
                        $ports[] = $info['port'];
                    }

                    // Collect ports from list
                    if (!empty($info['portsList']) && is_array($info['portsList'])) {
                        $ports = array_merge($ports, $info['portsList']);
                    }

                    // Collect ports from individual scripts
                    if (!empty($info['scripts']) && is_array($info['scripts'])) {
                        foreach ($info['scripts'] as $script) {
                            if (!empty($script['port'])) {
                                $ports[] = $script['port'];
                            }
                        }
                    }

                    // Remove duplicates and map
                    $ports = array_unique($ports);
                    foreach ($ports as $port) {
                        if (!isset($portMap[$port])) {
                            $portMap[$port] = [];
                        }
                        $portMap[$port][] = [
                            'project' => $item,
                            'title' => $info['title'] ?? ucwords(str_replace(['-', '_'], ' ', $item)),
                            'technology' => $info['technology'] ?? null
                        ];
                    }
                }
            }
        }
    }

    return $portMap;
}

// API: Execute project script
if (isset($_GET['action']) && $_GET['action'] === 'run-script' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);

    $projectPath = __DIR__ . '/' . $project;
    $infoFile = $projectPath . '/.project-info.json';

    if (!is_dir($projectPath) || !file_exists($infoFile)) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    $info = json_decode(file_get_contents($infoFile), true);
    $startScript = $info['startScript'] ?? null;
    $scriptType = $info['scriptType'] ?? 'bash';
    $port = $info['port'] ?? null;
    $portsList = $info['portsList'] ?? null;
    $hasMultipleScripts = isset($info['scripts']) && is_array($info['scripts']) && count($info['scripts']) > 0;

    // Validate that at least one script is configured
    if (!$hasMultipleScripts && !$startScript && $scriptType !== 'php-server') {
        echo json_encode(['success' => false, 'message' => 'No startup script configured']);
        exit;
    }

    // Si ya está corriendo este proyecto, detenerlo primero
    if (isServiceRunning($project)) {
        stopService($project);
        sleep(1);
    }

    // Check if another service uses the same port
    $conflictingServices = [];
    $portsToCheck = [];

    // Collect all ports to check
    if ($port) {
        $portsToCheck[] = $port;
    }
    if ($portsList && is_array($portsList)) {
        $portsToCheck = array_merge($portsToCheck, $portsList);
    }
    $portsToCheck = array_unique($portsToCheck);

    // Check each port
    foreach ($portsToCheck as $checkPort) {
        // First check registered services
        $conflicts = getServicesUsingPort($checkPort, $project);
        foreach ($conflicts as $conflictService) {
            if (!in_array($conflictService, $conflictingServices)) {
                $conflictingServices[] = $conflictService;
                stopService($conflictService);
            }
        }

        // Check for orphan processes using the port (no registrados)
        if (isPortInUse($checkPort)) {
            $pids = shell_exec("lsof -i :$checkPort -sTCP:LISTEN -t 2>/dev/null");
            if (!empty(trim($pids))) {
                $pidArray = array_filter(explode("\n", trim($pids)));
                foreach ($pidArray as $pid) {
                    $pid = trim($pid);
                    if (is_numeric($pid)) {
                        exec("kill $pid 2>/dev/null");
                        usleep(500000);
                        if (file_exists("/proc/$pid")) {
                            exec("kill -9 $pid 2>/dev/null");
                        }
                    }
                }
            }
        }
    }

    if (!empty($conflictingServices)) {
        sleep(2);
    }

    // Prepare command
    $logFile = LOG_DIR . '/' . $project . '.log';
    $pidFile = PID_DIR . '/' . $project . '.pid';

    // Clean previous log
    file_put_contents($logFile, "=== Starting service: " . date('Y-m-d H:i:s') . " ===\n");

    $pid = null;
    $pidsArray = [];

    // Verificar si hay múltiples scripts configurados
    if (isset($info['scripts']) && is_array($info['scripts']) && count($info['scripts']) > 0) {
        // Multiple scripts mode
        // Build ports list from scripts
        $portsFromScripts = [];
        foreach ($info['scripts'] as $scriptConfig) {
            if (!empty($scriptConfig['port'])) {
                $portsFromScripts[] = $scriptConfig['port'];
            }
        }

        // Combine with existing portsList
        if (!empty($portsFromScripts)) {
            if ($portsList && is_array($portsList)) {
                $portsList = array_unique(array_merge($portsList, $portsFromScripts));
            } else {
                $portsList = array_unique($portsFromScripts);
            }
        }

        // Use first port as main port if not defined
        if (!$port && !empty($portsFromScripts)) {
            $port = $portsFromScripts[0];
        }

        // Execute each script
        foreach ($info['scripts'] as $scriptConfig) {
            $scriptPid = executeScript($projectPath, $scriptConfig, $logFile);
            if ($scriptPid) {
                $pidsArray[] = $scriptPid;
                file_put_contents($logFile, "PID: $scriptPid - {$scriptConfig['name']}\n", FILE_APPEND);
                // Small pause between scripts
                usleep(500000); // 0.5 segundos
            }
        }

        // Use first PID as main
        $pid = !empty($pidsArray) ? $pidsArray[0] : null;

        // Save all PIDs to file
        if (!empty($pidsArray)) {
            file_put_contents($pidFile, implode("\n", $pidsArray));
        }
    } else {
        // Single script mode (compatibilidad)
        $scriptParts = explode(' ', $startScript, 2);
        $scriptFile = $scriptParts[0];
        $scriptArgs = isset($scriptParts[1]) ? $scriptParts[1] : '';

        switch ($scriptType) {
            case 'python':
                $command = sprintf(
                    'cd %s && nohup python3 %s %s >> %s 2>&1 & echo $!',
                    escapeshellarg($projectPath),
                    escapeshellarg($scriptFile),
                    $scriptArgs,
                    escapeshellarg($logFile)
                );
                $pid = trim(shell_exec($command));
                break;

            case 'node':
                $command = sprintf(
                    'cd %s && nohup node %s %s >> %s 2>&1 & echo $!',
                    escapeshellarg($projectPath),
                    escapeshellarg($scriptFile),
                    $scriptArgs,
                    escapeshellarg($logFile)
                );
                $pid = trim(shell_exec($command));
                break;

            case 'php':
                $command = sprintf(
                    'cd %s && nohup php %s %s >> %s 2>&1 & echo $!',
                    escapeshellarg($projectPath),
                    escapeshellarg($scriptFile),
                    $scriptArgs,
                    escapeshellarg($logFile)
                );
                $pid = trim(shell_exec($command));
                break;

            case 'php-server':
                $serverPort = $port ?? 8000;
                $docRoot = $info['docRoot'] ?? 'public';

                // Verificar si existe router.php en el docRoot
                $routerFile = $projectPath . '/' . $docRoot . '/router.php';
                $routerArg = file_exists($routerFile) ? 'router.php' : '';

                $command = sprintf(
                    'cd %s && nohup php -S 0.0.0.0:%d -t %s %s >> %s 2>&1 & echo $!',
                    escapeshellarg($projectPath),
                    $serverPort,
                    escapeshellarg($docRoot),
                    $routerArg,
                    escapeshellarg($logFile)
                );
                $pid = trim(shell_exec($command));
                break;

            default: // bash
                $command = sprintf(
                    'cd %s && nohup bash %s %s >> %s 2>&1 & echo $!',
                    escapeshellarg($projectPath),
                    escapeshellarg($scriptFile),
                    $scriptArgs,
                    escapeshellarg($logFile)
                );
                $pid = trim(shell_exec($command));
        }

        if ($pid && is_numeric($pid)) {
            $pidsArray[] = (int)$pid;
        }
    }

    // Save initial PID (puede ser del wrapper bash)
    $initialPid = $pid;
    if ($pid && is_numeric($pid)) {
        file_put_contents($pidFile, $pid);
    }

    // Wait for service to start and port to be available
    sleep(3);

    // If we have configured ports, verify they're in use
    $primaryPort = $port;
    $anyPortInUse = false;

    // Check main port
    if ($primaryPort && !isPortInUse($primaryPort)) {
        sleep(2); // Esperar un poco más
    }

    // Verificar si algún puerto está en uso
    if ($primaryPort && isPortInUse($primaryPort)) {
        $anyPortInUse = true;
    }

    // Verificar puertos adicionales
    if ($portsList && is_array($portsList)) {
        foreach ($portsList as $checkPort) {
            if (isPortInUse($checkPort)) {
                $anyPortInUse = true;
                if (!$primaryPort) {
                    $primaryPort = $checkPort; // Usar primer puerto disponible como principal
                }
            }
        }
    }

    // Mark service as running
    if ($anyPortInUse && $primaryPort) {
        // Get real PID of process using main port
        $realPid = trim(shell_exec("lsof -i :$primaryPort -sTCP:LISTEN -t 2>/dev/null | head -n1"));
        if ($realPid && is_numeric($realPid)) {
            $pid = $realPid;
            file_put_contents($pidFile, $pid);
            markServiceRunning($project, $pid, $primaryPort, $portsList, $pidsArray);
        } else {
            // If we can't get PID, mark only with ports
            markServiceRunning($project, null, $primaryPort, $portsList, $pidsArray);
        }
    } else if ($initialPid) {
        // We have PID but ports not in use, usar el PID inicial
        markServiceRunning($project, $initialPid, $primaryPort, $portsList, $pidsArray);
    } else if ($pid && is_numeric($pid)) {
        // Without configured ports, use captured PID
        markServiceRunning($project, $pid, $primaryPort, $portsList, $pidsArray);
    }

    // Read log
    $logOutput = '';
    if (file_exists($logFile)) {
        $logOutput = file_get_contents($logFile);
        $logOutput = substr($logOutput, -3000);
    }

    $isRunning = isServiceRunning($project);

    $message = $isRunning ? 'Service started successfully' : 'Service started (check logs)';
    if (!empty($conflictingServices)) {
        $message .= '. Se detuvo: ' . implode(', ', $conflictingServices);
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'running' => $isRunning,
        'output' => $logOutput ?: 'Servicio iniciándose...',
        'stoppedServices' => $conflictingServices
    ]);
    exit;
}

// API: Stop a specific service
if (isset($_GET['action']) && $_GET['action'] === 'stop-service' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);

    if (stopService($project)) {
        echo json_encode(['success' => true, 'message' => 'Service stopped']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error stopping service']);
    }
    exit;
}

// API: Stop all services
if (isset($_GET['action']) && $_GET['action'] === 'stop-all' && isAuthenticated()) {
    header('Content-Type: application/json');

    $state = loadServicesState();
    $stopped = [];
    $portsKilled = [];

    // First, stop registered services
    foreach ($state as $name => $info) {
        if (stopService($name)) {
            $stopped[] = $name;
        }
    }

    sleep(1);

    // Define common ports to check and close
    $commonPorts = [3000, 3001, 5000, 5001, 8000, 8001, 8005, 8080, 8081, 8888, 9000];

    // Check and kill all open common ports
    foreach ($commonPorts as $port) {
        if (isPortInUse($port)) {
            // Get PIDs of process using the port
            $cmd = "lsof -i :$port -t 2>/dev/null";
            $pids = shell_exec($cmd);

            if (!empty(trim($pids))) {
                $pidArray = array_filter(explode("\n", trim($pids)));

                foreach ($pidArray as $pid) {
                    $pid = trim($pid);
                    if (is_numeric($pid)) {
                        // Try to kill the process
                        exec("kill $pid 2>/dev/null");
                        usleep(500000); // Wait 500ms

                        // If still alive, force
                        if (isProcessRunning($pid)) {
                            exec("kill -9 $pid 2>/dev/null");
                        }

                        if (!in_array($port, $portsKilled)) {
                            $portsKilled[] = $port;
                        }
                    }
                }

                // Clean services state for this port
                foreach ($state as $name => $info) {
                    if (!empty($info['port']) && $info['port'] == $port) {
                        unset($state[$name]);
                    }
                }
            }
        }
    }

    // Save updated state
    if (!empty($state)) {
        saveServicesState($state);
    } else {
        // If no services, clean file
        @unlink(STATE_FILE);
    }

    // Prepare response message
    $message = '';
    if (count($stopped) > 0) {
        $message .= 'Services stopped: ' . implode(', ', $stopped);
    }
    if (count($portsKilled) > 0) {
        if (!empty($message)) $message .= '. ';
        $message .= 'Ports cerrados: ' . implode(', ', $portsKilled);
    }
    if (empty($message)) {
        $message = 'No había servicios ni puertos running';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'stopped' => $stopped,
        'portsKilled' => $portsKilled
    ]);
    exit;
}

// API: Get services status
if (isset($_GET['action']) && $_GET['action'] === 'status' && isAuthenticated()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'services' => getRunningServices()
    ]);
    exit;
}

// API: View service logs
if (isset($_GET['action']) && $_GET['action'] === 'logs' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);
    $logFile = LOG_DIR . '/' . $project . '.log';

    if (!file_exists($logFile)) {
        echo json_encode(['success' => false, 'message' => 'No logs available']);
        exit;
    }

    $logs = file_get_contents($logFile);
    $logs = substr($logs, -5000);

    echo json_encode([
        'success' => true,
        'logs' => $logs
    ]);
    exit;
}

// API: Update repository (git fetch + pull)
if (isset($_GET['action']) && $_GET['action'] === 'git-pull' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);
    $projectPath = __DIR__ . '/' . $project;

    if (!is_dir($projectPath)) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    $result = gitPull($projectPath);
    echo json_encode($result);
    exit;
}

// API: Get project GitHub information
if (isset($_GET['action']) && $_GET['action'] === 'git-info' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);
    $projectPath = __DIR__ . '/' . $project;

    if (!is_dir($projectPath)) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    $isGitRepo = is_dir($projectPath . '/.git');
    $repoUrl = $isGitRepo ? getGitRepoUrl($projectPath) : null;

    // Get additional info from repo if git
    $branch = null;
    $lastCommit = null;
    if ($isGitRepo) {
        $branch = trim(shell_exec('cd ' . escapeshellarg($projectPath) . ' && git branch --show-current 2>/dev/null'));
        $lastCommit = trim(shell_exec('cd ' . escapeshellarg($projectPath) . ' && git log -1 --format="%h - %s (%cr)" 2>/dev/null'));
    }

    echo json_encode([
        'success' => true,
        'isGitRepo' => $isGitRepo,
        'repoUrl' => $repoUrl,
        'branch' => $branch,
        'lastCommit' => $lastCommit
    ]);
    exit;
}

// Function to detect application type intelligently
function detectApplicationType($port, $pid, $cmdLine) {
    $appType = 'Unknown';
    $appName = 'Unknown';
    $appDetails = [];

    // Search in registered projects first
    $state = loadServicesState();
    foreach ($state as $name => $info) {
        if (!empty($info['port']) && $info['port'] == $port) {
            $appName = $name;
            break;
        }
    }

    // Process command analysis
    if (!empty($cmdLine)) {
        // Java - detectar Spring Boot, Maven, etc.
        if (stripos($cmdLine, 'java') !== false) {
            if (stripos($cmdLine, 'spring-boot') !== false || stripos($cmdLine, 'SpringApplication') !== false) {
                $appType = 'Spring Boot';
            } elseif (stripos($cmdLine, 'tomcat') !== false) {
                $appType = 'Apache Tomcat';
            } elseif (stripos($cmdLine, 'jetty') !== false) {
                $appType = 'Jetty';
            } else {
                $appType = 'Java';
            }
        }
        // Python - detectar frameworks específicos
        elseif (stripos($cmdLine, 'python') !== false || stripos($cmdLine, 'uvicorn') !== false || stripos($cmdLine, 'gunicorn') !== false) {
            if (stripos($cmdLine, 'fastapi') !== false || stripos($cmdLine, 'uvicorn') !== false) {
                $appType = 'FastAPI';
            } elseif (stripos($cmdLine, 'django') !== false) {
                $appType = 'Django';
            } elseif (stripos($cmdLine, 'flask') !== false) {
                $appType = 'Flask';
            } elseif (stripos($cmdLine, 'streamlit') !== false) {
                $appType = 'Streamlit';
            } else {
                $appType = 'Python';
            }
        }
        // PHP
        elseif (stripos($cmdLine, 'php') !== false) {
            if (stripos($cmdLine, 'artisan') !== false) {
                $appType = 'Laravel';
            } elseif (stripos($cmdLine, 'symfony') !== false) {
                $appType = 'Symfony';
            } else {
                $appType = 'PHP Server';
            }
        }
        // Node.js - detectar frameworks
        elseif (stripos($cmdLine, 'node') !== false || stripos($cmdLine, 'npm') !== false || stripos($cmdLine, 'npx') !== false) {
            if (stripos($cmdLine, 'next') !== false) {
                $appType = 'Next.js';
            } elseif (stripos($cmdLine, 'react') !== false || stripos($cmdLine, 'vite') !== false) {
                $appType = 'React (Vite)';
            } elseif (stripos($cmdLine, 'webpack') !== false || stripos($cmdLine, 'react-scripts') !== false) {
                $appType = 'React (Webpack)';
            } elseif (stripos($cmdLine, 'vue') !== false) {
                $appType = 'Vue.js';
            } elseif (stripos($cmdLine, 'angular') !== false || stripos($cmdLine, 'ng serve') !== false) {
                $appType = 'Angular';
            } elseif (stripos($cmdLine, 'express') !== false) {
                $appType = 'Express.js';
            } elseif (stripos($cmdLine, 'nestjs') !== false || stripos($cmdLine, 'nest start') !== false) {
                $appType = 'NestJS';
            } else {
                $appType = 'Node.js';
            }
        }
        // Nginx, Apache
        elseif (stripos($cmdLine, 'nginx') !== false) {
            $appType = 'Nginx';
        } elseif (stripos($cmdLine, 'apache') !== false || stripos($cmdLine, 'httpd') !== false) {
            $appType = 'Apache';
        }
    }

    // Attempt detection by HTTP headers
    $httpInfo = detectByHttpHeaders($port);
    if ($httpInfo['detected']) {
        if ($appType === 'Unknown') {
            $appType = $httpInfo['type'];
        }
        $appDetails = $httpInfo['details'];
    }

    return [
        'name' => $appName,
        'type' => $appType,
        'details' => $appDetails
    ];
}

// Function to detect application by HTTP headers
function detectByHttpHeaders($port) {
    $result = [
        'detected' => false,
        'type' => 'Unknown',
        'details' => []
    ];

    $url = "http://127.0.0.1:$port/";

    // Try simple HTTP request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    $response = @curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response && $httpCode > 0) {
        $result['detected'] = true;

        // Analyze headers
        $headers = [];
        $headerLines = explode("\r\n", $response);
        foreach ($headerLines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[strtolower(trim($key))] = trim($value);
            }
        }

        // Detect by specific headers
        if (isset($headers['server'])) {
            $server = strtolower($headers['server']);
            if (strpos($server, 'nginx') !== false) {
                $result['type'] = 'Nginx';
            } elseif (strpos($server, 'apache') !== false) {
                $result['type'] = 'Apache';
            } elseif (strpos($server, 'uvicorn') !== false) {
                $result['type'] = 'FastAPI/Uvicorn';
            } elseif (strpos($server, 'gunicorn') !== false) {
                $result['type'] = 'Python/Gunicorn';
            } elseif (strpos($server, 'werkzeug') !== false) {
                $result['type'] = 'Flask (Dev)';
            }
            $result['details']['server'] = $headers['server'];
        }

        // Detect by other headers
        if (isset($headers['x-powered-by'])) {
            $poweredBy = strtolower($headers['x-powered-by']);
            if (strpos($poweredBy, 'express') !== false) {
                $result['type'] = 'Express.js';
            } elseif (strpos($poweredBy, 'php') !== false) {
                $result['type'] = 'PHP';
            }
            $result['details']['powered-by'] = $headers['x-powered-by'];
        }

        // Specific frameworks headers
        if (isset($headers['x-framework'])) {
            $result['details']['framework'] = $headers['x-framework'];
        }
    }

    return $result;
}

// API: List open ports with applications
if (isset($_GET['action']) && $_GET['action'] === 'list-ports' && isAuthenticated()) {
    header('Content-Type: application/json');

    // Get ports to projects mapping
    $portMap = getAllProjectPorts();

    // Build list of ports to scan (incluye puertos comunes + puertos de proyectos)
    $commonPorts = [3000, 3001, 5000, 5001, 8000, 8001, 8005, 8080, 8081, 8888, 9000];
    $projectPorts = array_keys($portMap);
    $portsToScan = array_unique(array_merge($commonPorts, $projectPorts));
    sort($portsToScan);

    $openPorts = [];

    foreach ($portsToScan as $port) {
        if (isPortInUse($port)) {
            // Obtener información del proceso usando el puerto
            $cmd = "lsof -i :$port -t 2>/dev/null | head -n1";
            $pid = trim(shell_exec($cmd));

            $appInfo = ['name' => 'Unknown', 'type' => 'Unknown', 'details' => []];

            if ($pid) {
                // Obtener el comando del proceso
                $cmdLine = trim(shell_exec("ps -p $pid -o args= 2>/dev/null"));

                // Detectar aplicación de forma inteligente
                $appInfo = detectApplicationType($port, $pid, $cmdLine);
            }

            // Get project info if exists
            $projectInfo = null;
            if (isset($portMap[$port])) {
                // Puede haber múltiples proyectos usando el mismo puerto
                $projectInfo = $portMap[$port];
            }

            $openPorts[] = [
                'port' => $port,
                'pid' => $pid ?: null,
                'appName' => $appInfo['name'],
                'appType' => $appInfo['type'],
                'details' => $appInfo['details'],
                'projects' => $projectInfo
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'ports' => $openPorts,
        'portMap' => $portMap // Additional info of all configured ports
    ]);
    exit;
}

// API: Kill a specific port
if (isset($_GET['action']) && $_GET['action'] === 'kill-port' && isAuthenticated()) {
    header('Content-Type: application/json');

    $port = $_GET['port'] ?? '';

    if (!is_numeric($port)) {
        echo json_encode(['success' => false, 'message' => 'Invalid port']);
        exit;
    }

    // Get PID of process using the port
    $cmd = "lsof -i :$port -t 2>/dev/null";
    $pids = shell_exec($cmd);

    if (empty(trim($pids))) {
        echo json_encode(['success' => false, 'message' => 'No process using port ' . $port]);
        exit;
    }

    $pidArray = array_filter(explode("\n", trim($pids)));
    $killedPids = [];

    foreach ($pidArray as $pid) {
        $pid = trim($pid);
        if (is_numeric($pid)) {
            exec("kill $pid 2>/dev/null");
            usleep(500000);
            if (isProcessRunning($pid)) {
                exec("kill -9 $pid 2>/dev/null");
            }
            $killedPids[] = $pid;
        }
    }

    // Clean services state for this port
    $state = loadServicesState();
    foreach ($state as $name => $info) {
        if (!empty($info['port']) && $info['port'] == $port) {
            unset($state[$name]);
        }
    }
    saveServicesState($state);

    echo json_encode([
        'success' => true,
        'message' => 'Proceso(s) terminado(s) en puerto ' . $port,
        'killedPids' => $killedPids
    ]);
    exit;
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], PASSWORD_HASH)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
    } else {
        $error = 'Contraseña incorrecta';
    }
}

// Process logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Update last activity
if (isAuthenticated()) {
    $_SESSION['last_activity'] = time();
}

// Get projects list
function getProjects() {
    $projects = [];
    $excludeDirs = ['.', '..', 'assets'];
    $dir = __DIR__;

    foreach (scandir($dir) as $item) {
        if (is_dir($dir . '/' . $item) && !in_array($item, $excludeDirs) && $item[0] !== '.') {
            $projectPath = $dir . '/' . $item;
            $descFile = $projectPath . '/.project-info.json';

            $projectInfo = [
                'name' => $item,
                'title' => ucwords(str_replace(['-', '_'], ' ', $item)),
                'description' => 'Backend project without description.',
                'image' => null,
                'url' => $item . '/',
                'startScript' => null,
                'startLabel' => 'Start',
                'scriptType' => 'bash',
                'port' => null,
                'isRunning' => false,
                'isGitRepo' => false,
                'repoUrl' => null,
                'type' => 'backend',
                'technology' => null,
                'ports' => null
            ];

            if (file_exists($descFile)) {
                $info = json_decode(file_get_contents($descFile), true);
                if ($info) {
                    $projectInfo = array_merge($projectInfo, $info);
                    $projectInfo['name'] = $item;
                }
            }

            // Check if running
            if (!empty($projectInfo['startScript'])) {
                $projectInfo['isRunning'] = isServiceRunning($item);
            }

            // Check if Git repository
            $projectInfo['isGitRepo'] = is_dir($projectPath . '/.git');
            if ($projectInfo['isGitRepo']) {
                $projectInfo['repoUrl'] = getGitRepoUrl($projectPath);
            }

            // Search for image
            $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
            foreach ($imageExtensions as $ext) {
                if (file_exists($projectPath . '/project-cover.' . $ext)) {
                    $projectInfo['image'] = $item . '/project-cover.' . $ext;
                    break;
                }
            }

            $projects[] = $projectInfo;
        }
    }

    return $projects;
}

$projects = isAuthenticated() ? getProjects() : [];
$runningCount = count(array_filter($projects, fn($p) => $p['isRunning']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <?php if (!isAuthenticated()): ?>
        <!-- Login Form -->
        <div class="login-container">
            <div class="login-box">
                <div class="login-header">
                    <svg class="lock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <h1><?php echo SITE_NAME; ?></h1>
                </div>

                <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required autofocus
                               placeholder="Introduce la contraseña">
                    </div>
                    <button type="submit" class="btn-login">Acceder</button>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- Project Dashboard -->
        <header class="dashboard-header">
            <h1><?php echo SITE_NAME; ?></h1>
            <div class="header-actions">
                <span id="runningCount" class="running-badge <?php echo $runningCount > 0 ? 'active' : ''; ?>">
                    <span class="pulse"></span>
                    <span class="count"><?php echo $runningCount; ?></span> running
                </span>
                <button class="btn-ports" onclick="showOpenPorts()" title="Ver puertos abiertos">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6M1 12h6m6 0h6"/>
                    </svg>
                    Ports
                </button>
                <button class="btn-stop-all" onclick="stopAllServices()" title="Stop todos los servicios">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                    Stop All
                </button>
                <a href="?logout" class="btn-logout">Logout</a>
            </div>
        </header>

        <main class="projects-grid">
            <?php if (empty($projects)): ?>
            <div class="no-projects">
                <p>No projects available.</p>
            </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                <div class="project-card <?php echo $project['isRunning'] ? 'running' : ''; ?>" data-project="<?php echo htmlspecialchars($project['name']); ?>">
                    <a href="<?php echo htmlspecialchars($project['url']); ?>" class="project-link" target="_blank">
                        <div class="project-image">
                            <?php if ($project['isRunning']): ?>
                            <div class="running-indicator">
                                <span class="pulse"></span> En ejecución
                            </div>
                            <?php endif; ?>
                            <?php if ($project['isGitRepo'] && $project['repoUrl']): ?>
                            <div class="git-actions">
                                <a href="<?php echo htmlspecialchars($project['repoUrl']); ?>"
                                   target="_blank"
                                   class="btn-git btn-git-repo"
                                   onclick="event.stopPropagation();"
                                   title="Open on GitHub">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                </a>
                            </div>
                            <?php endif; ?>
                            <?php if ($project['image']): ?>
                            <img src="<?php echo htmlspecialchars($project['image']); ?>"
                                 alt="<?php echo htmlspecialchars($project['title']); ?>">
                            <?php else: ?>
                            <div class="default-image <?php echo $project['type'] === 'frontend' ? 'frontend' : 'backend'; ?>">
                                <?php if ($project['type'] === 'frontend'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                                    <line x1="8" y1="21" x2="16" y2="21"/>
                                    <line x1="12" y1="17" x2="12" y2="21"/>
                                </svg>
                                <span>Frontend Project</span>
                                <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>Backend Project</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="project-info">
                            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                            <p><?php echo htmlspecialchars($project['description']); ?></p>
                            <?php if ($project['technology'] || $project['ports']): ?>
                            <div class="project-meta">
                                <?php if ($project['technology']): ?>
                                <div class="tech-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="16 18 22 12 16 6"/>
                                        <polyline points="8 6 2 12 8 18"/>
                                    </svg>
                                    <span><?php echo htmlspecialchars($project['technology']); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($project['ports']): ?>
                                <div class="port-badge">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M12 1v6m0 6v6M1 12h6m6 0h6"/>
                                    </svg>
                                    <span><?php echo htmlspecialchars($project['ports']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="project-actions">
                        <a href="<?php echo htmlspecialchars($project['url']); ?>" class="btn-action btn-open" target="_blank">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            Open
                        </a>
                        <?php if ($project['startScript']): ?>
                            <?php if ($project['isRunning']): ?>
                            <button class="btn-action btn-stop"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    onclick="stopService(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="6" y="6" width="12" height="12"/>
                                </svg>
                                Stop
                            </button>
                            <button class="btn-action btn-logs"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    onclick="viewLogs(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                                Logs
                            </button>
                            <?php else: ?>
                            <button class="btn-action btn-start"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    onclick="runScript(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                <?php echo htmlspecialchars($project['startLabel']); ?>
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <!-- Result Modal -->
        <div id="resultModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="modalTitle">Ejecutando...</h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="modalLoader" class="loader"></div>
                    <div id="modalResult" class="result-output"></div>
                </div>
                <div class="modal-footer" id="modalFooter" style="display: none;">
                    <button class="btn-action btn-primary" onclick="closeModalAndReload()">Cerrar y actualizar</button>
                </div>
            </div>
        </div>

        <!-- Open Ports Modal -->
        <div id="portsModal" class="modal">
            <div class="modal-content ports-modal-content">
                <div class="modal-header">
                    <h3>Ports Abiertos</h3>
                    <button class="modal-close" onclick="closePortsModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="portsLoader" class="loader"></div>
                    <div id="portsList" class="ports-list"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn-action" onclick="refreshPorts()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 4v6h6"/>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                        </svg>
                        Actualizar
                    </button>
                    <button class="btn-action btn-primary" onclick="closePortsModal()">Cerrar</button>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p><?php echo count($projects); ?> proyecto(s) disponible(s)</p>
        </footer>
        <?php endif; ?>
    </div>

    <script>
    function runScript(button) {
        const project = button.dataset.project;
        showModal('Starting service...', true);

        button.disabled = true;
        button.classList.add('loading');

        fetch(`?action=run-script&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? (data.running ? 'Servicio iniciado' : 'Service starting') : 'Error',
                    data.output || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal('Error', 'Connection error: ' + error.message, false);
            })
            .finally(() => {
                button.disabled = false;
                button.classList.remove('loading');
            });
    }

    function stopService(button) {
        const project = button.dataset.project;
        showModal('Stopping service...', true);

        button.disabled = true;

        fetch(`?action=stop-service&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? 'Service stopped' : 'Error',
                    data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal('Error', 'Connection error: ' + error.message, false);
            });
    }

    function stopAllServices() {
        if (!confirm('Stop all running services?')) return;

        showModal('Stopping all services...', true);

        fetch('?action=stop-all')
            .then(response => response.json())
            .then(data => {
                updateModal(
                    'Services stopped',
                    data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal('Error', 'Connection error: ' + error.message, false);
            });
    }

    function viewLogs(button) {
        const project = button.dataset.project;
        showModal('Loading logs...', true);

        fetch(`?action=logs&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    'Logs: ' + project,
                    data.logs || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal('Error', 'Connection error: ' + error.message, false);
            });
    }

    function gitPull(button) {
        const project = button.dataset.project;
        showModal('Updating repository...', true);

        button.disabled = true;
        button.classList.add('loading');

        fetch(`?action=git-pull&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? 'Repository updated' : 'Error',
                    data.output || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal('Error', 'Connection error: ' + error.message, false);
            })
            .finally(() => {
                button.disabled = false;
                button.classList.remove('loading');
            });
    }

    function showModal(title, showLoader) {
        const modal = document.getElementById('resultModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalLoader = document.getElementById('modalLoader');
        const modalResult = document.getElementById('modalResult');
        const modalFooter = document.getElementById('modalFooter');

        modal.classList.add('active');
        modalTitle.textContent = title;
        modalLoader.style.display = showLoader ? 'block' : 'none';
        modalResult.textContent = '';
        modalResult.className = 'result-output';
        modalFooter.style.display = 'none';
    }

    function updateModal(title, content, success) {
        const modalTitle = document.getElementById('modalTitle');
        const modalLoader = document.getElementById('modalLoader');
        const modalResult = document.getElementById('modalResult');
        const modalFooter = document.getElementById('modalFooter');

        modalLoader.style.display = 'none';
        modalTitle.textContent = title;
        modalResult.textContent = content;
        modalResult.classList.add(success ? 'success' : 'error');
        modalFooter.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('resultModal').classList.remove('active');
    }

    function closeModalAndReload() {
        closeModal();
        location.reload();
    }

    document.getElementById('resultModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            // No cerrar al hacer clic fuera, solo con el botón
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // No cerrar con Escape automáticamente
        }
    });

    // Funciones para gestión de puertos
    function showOpenPorts() {
        const modal = document.getElementById('portsModal');
        const loader = document.getElementById('portsLoader');
        const portsList = document.getElementById('portsList');

        modal.classList.add('active');
        loader.style.display = 'block';
        portsList.innerHTML = '';

        loadPorts();
    }

    function loadPorts() {
        const loader = document.getElementById('portsLoader');
        const portsList = document.getElementById('portsList');

        fetch('?action=list-ports')
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';

                if (!data.success || data.ports.length === 0) {
                    portsList.innerHTML = '<div class="no-ports">No hay puertos abiertos en este momento</div>';
                    return;
                }

                let html = '<table class="ports-table"><thead><tr><th>Port</th><th>Project</th><th>Application</th><th>Type</th><th>PID</th><th>Action</th></tr></thead><tbody>';

                data.ports.forEach(port => {
                    // Generar información del proyecto
                    let projectInfo = '-';
                    if (port.projects && port.projects.length > 0) {
                        projectInfo = port.projects.map(p => {
                            const tech = p.technology ? `<br><small style="color: #94a3b8;">${p.technology}</small>` : '';
                            return `<strong>${p.title}</strong>${tech}`;
                        }).join('<br>');
                    }

                    html += `
                        <tr>
                            <td><strong>${port.port}</strong></td>
                            <td>${projectInfo}</td>
                            <td>${port.appName}</td>
                            <td><span class="app-type-badge">${port.appType}</span></td>
                            <td>${port.pid || 'N/A'}</td>
                            <td>
                                <button class="btn-kill" onclick="killPort(${port.port})" title="Terminar proceso">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    Kill
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table>';
                portsList.innerHTML = html;
            })
            .catch(error => {
                loader.style.display = 'none';
                portsList.innerHTML = '<div class="error-message">Error al cargar puertos: ' + error.message + '</div>';
            });
    }

    function refreshPorts() {
        const portsList = document.getElementById('portsList');
        const loader = document.getElementById('portsLoader');

        portsList.innerHTML = '';
        loader.style.display = 'block';
        loadPorts();
    }

    function killPort(port) {
        if (!confirm(`¿Terminar el proceso en el puerto ${port}?`)) {
            return;
        }

        const loader = document.getElementById('portsLoader');
        loader.style.display = 'block';

        fetch(`?action=kill-port&port=${port}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        refreshPorts();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                    loader.style.display = 'none';
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
                loader.style.display = 'none';
            });
    }

    function closePortsModal() {
        document.getElementById('portsModal').classList.remove('active');
    }

    function showNotification(message, type) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Update state cada 30 segundos
    setInterval(() => {
        fetch('?action=status')
            .then(r => r.json())
            .then(data => {
                const count = Object.keys(data.services).length;
                const badge = document.getElementById('runningCount');
                if (badge) {
                    badge.querySelector('.count').textContent = count;
                    badge.classList.toggle('active', count > 0);
                }
            });
    }, 30000);
    </script>
</body>
</html>
