<?php
session_start();
require_once 'config.php';

// Directories for state and logs
define('PID_DIR', __DIR__ . '/.pids');
define('LOG_DIR', __DIR__ . '/.logs');
define('STATE_FILE', __DIR__ . '/.services-state.json');
define('GITHUB_CONFIG_FILE', __DIR__ . '/.github-config.json');
define('TODO_DIR', __DIR__ . '/.todos');

// Create directories if they don't exist
if (!is_dir(PID_DIR)) mkdir(PID_DIR, 0755, true);
if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0755, true);
if (!is_dir(TODO_DIR)) mkdir(TODO_DIR, 0700, true); // More restrictive permissions for TODOs

// Internationalization (i18n) - Language support
$translations = [
    'en' => [
        'site_name' => 'Application Deployment Hub',
        'password' => 'Password',
        'enter_password' => 'Enter password',
        'login' => 'Login',
        'logout' => 'Logout',
        'incorrect_password' => 'Incorrect password',

        // Header
        'running' => 'running',
        'ports' => 'Ports',
        'stop_all' => 'Stop All',
        'open_ports' => 'Open Ports',
        'view_open_ports' => 'View open ports',
        'stop_all_services' => 'Stop all services',

        // Projects
        'no_projects' => 'No projects available.',
        'projects_available' => 'project(s) available',
        'frontend_project' => 'Frontend Project',
        'backend_project' => 'Backend Project',
        'open' => 'Open',
        'start' => 'Start',
        'stop' => 'Stop',
        'logs' => 'Logs',
        'open_on_github' => 'Open on GitHub',
        'edit_todo' => 'Edit TODO',
        'todo_notes' => 'TODO Notes',
        'save' => 'Save',
        'todo_saved' => 'TODO saved successfully',
        'todo_placeholder' => 'Write your TODO notes here...',
        'pending_changes' => 'Pending changes',
        'unsaved_changes' => 'There are unsaved changes. Do you want to close without saving?',

        // Modal
        'executing' => 'Executing...',
        'close_and_update' => 'Close and update',
        'close' => 'Close',
        'update' => 'Update',

        // Ports Modal
        'port' => 'Port',
        'project' => 'Project',
        'application' => 'Application',
        'type' => 'Type',
        'action' => 'Action',
        'kill' => 'Kill',
        'no_open_ports' => 'No open ports at this time',
        'terminate_process' => 'Terminate process',

        // Messages
        'starting_service' => 'Starting service...',
        'stopping_service' => 'Stopping service...',
        'stopping_all_services' => 'Stopping all services...',
        'loading_logs' => 'Loading logs...',
        'updating_repository' => 'Updating repository...',
        'service_started' => 'Service started successfully',
        'service_starting' => 'Service starting',
        'service_started_check' => 'Service started (check logs)',
        'service_stopped' => 'Service stopped',
        'services_stopped' => 'Services stopped',
        'repository_updated' => 'Repository updated',
        'connection_error' => 'Connection error',
        'error' => 'Error',
        'success' => 'Success',
        'stopped_services' => 'Stopped',
        'ports_closed' => 'Ports closed',
        'no_running_services' => 'There were no running services or ports',
        'error_loading_ports' => 'Error loading ports',
        'stop_all_confirm' => 'Stop all running services?',
        'kill_port_confirm' => 'Kill process on port',
        'process_terminated' => 'Process(es) terminated on port',
        'no_process_on_port' => 'No process using port',
        'no_logs_available' => 'No logs available',
        'project_not_found' => 'Project not found',
        'invalid_port' => 'Invalid port',
        'not_git_repo' => 'Not a git repository',
        'no_remote_configured' => 'No remote repository configured',
        'no_startup_script' => 'No startup script configured',
        'backend_no_desc' => 'Backend project without description.',
        'running_indicator' => 'Running'
    ],
    'es' => [
        'site_name' => 'Centro de Despliegue de Aplicaciones',
        'password' => 'Contraseña',
        'enter_password' => 'Introduce la contraseña',
        'login' => 'Acceder',
        'logout' => 'Cerrar sesión',
        'incorrect_password' => 'Contraseña incorrecta',

        // Header
        'running' => 'en ejecución',
        'ports' => 'Puertos',
        'stop_all' => 'Detener Todo',
        'open_ports' => 'Puertos Abiertos',
        'view_open_ports' => 'Ver puertos abiertos',
        'stop_all_services' => 'Detener todos los servicios',

        // Projects
        'no_projects' => 'No hay proyectos disponibles.',
        'projects_available' => 'proyecto(s) disponible(s)',
        'frontend_project' => 'Proyecto Frontend',
        'backend_project' => 'Proyecto Backend',
        'open' => 'Abrir',
        'start' => 'Iniciar',
        'stop' => 'Detener',
        'logs' => 'Logs',
        'open_on_github' => 'Abrir en GitHub',
        'edit_todo' => 'Editar TODO',
        'todo_notes' => 'Notas TODO',
        'save' => 'Guardar',
        'todo_saved' => 'TODO guardado exitosamente',
        'todo_placeholder' => 'Escribe tus notas TODO aquí...',
        'pending_changes' => 'Cambios pendientes',
        'unsaved_changes' => 'Hay cambios sin guardar. ¿Deseas cerrar sin guardar?',

        // Modal
        'executing' => 'Ejecutando...',
        'close_and_update' => 'Cerrar y actualizar',
        'close' => 'Cerrar',
        'update' => 'Actualizar',

        // Ports Modal
        'port' => 'Puerto',
        'project' => 'Proyecto',
        'application' => 'Aplicación',
        'type' => 'Tipo',
        'action' => 'Acción',
        'kill' => 'Kill',
        'no_open_ports' => 'No hay puertos abiertos en este momento',
        'terminate_process' => 'Terminar proceso',

        // Messages
        'starting_service' => 'Iniciando servicio...',
        'stopping_service' => 'Deteniendo servicio...',
        'stopping_all_services' => 'Deteniendo todos los servicios...',
        'loading_logs' => 'Cargando logs...',
        'updating_repository' => 'Actualizando repositorio...',
        'service_started' => 'Servicio iniciado correctamente',
        'service_starting' => 'Servicio iniciándose',
        'service_started_check' => 'Servicio iniciado (verificar logs)',
        'service_stopped' => 'Servicio detenido',
        'services_stopped' => 'Servicios detenidos',
        'repository_updated' => 'Repositorio actualizado',
        'connection_error' => 'Error de conexión',
        'error' => 'Error',
        'success' => 'Éxito',
        'stopped_services' => 'Detenidos',
        'ports_closed' => 'Puertos cerrados',
        'no_running_services' => 'No había servicios ni puertos en ejecución',
        'error_loading_ports' => 'Error al cargar puertos',
        'stop_all_confirm' => '¿Detener todos los servicios en ejecución?',
        'kill_port_confirm' => '¿Terminar el proceso en el puerto',
        'process_terminated' => 'Proceso(s) terminado(s) en puerto',
        'no_process_on_port' => 'No hay proceso usando el puerto',
        'no_logs_available' => 'No hay logs disponibles',
        'project_not_found' => 'Proyecto no encontrado',
        'invalid_port' => 'Puerto inválido',
        'not_git_repo' => 'No es un repositorio git',
        'no_remote_configured' => 'No hay repositorio remoto configurado',
        'no_startup_script' => 'No hay script de inicio configurado',
        'backend_no_desc' => 'Proyecto backend sin descripción.',
        'running_indicator' => 'En ejecución'
    ]
];

// Get current language
function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    // Default to browser language if available
    $browserLang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : 'en';
    return in_array($browserLang, ['en', 'es']) ? $browserLang : 'en';
}

// Set language
function setLanguage($lang) {
    if (in_array($lang, ['en', 'es'])) {
        $_SESSION['language'] = $lang;
    }
}

// Translation helper function
function __($key) {
    global $translations;
    $lang = getCurrentLanguage();
    return $translations[$lang][$key] ?? $key;
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es'])) {
    setLanguage($_GET['lang']);
    header('Location: index.php');
    exit;
}

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

// Check if a project has pending changes in git
function hasGitChanges($projectPath) {
    if (!is_dir($projectPath . '/.git')) {
        return false;
    }

    $output = shell_exec('cd ' . escapeshellarg($projectPath) . ' && git status --porcelain 2>/dev/null');
    return !empty(trim($output));
}

// Function to sanitize project name for TODO files (security)
function sanitizeTodoProjectName($projectName) {
    // Remove any path traversal attempts and ensure it's just a simple name
    $projectName = basename($projectName);
    $projectName = preg_replace('/[^a-zA-Z0-9_-]/', '', $projectName);
    return $projectName;
}

// Function to get TODO file path for a project
function getTodoFilePath($projectName) {
    $safeName = sanitizeTodoProjectName($projectName);
    if (empty($safeName)) {
        return null;
    }
    return TODO_DIR . '/' . $safeName . '.txt';
}

// Function to load TODO for a project
function loadTodo($projectName) {
    $filePath = getTodoFilePath($projectName);
    if (!$filePath || !file_exists($filePath)) {
        return '';
    }
    return file_get_contents($filePath);
}

// Function to save TODO for a project
function saveTodo($projectName, $content) {
    $filePath = getTodoFilePath($projectName);
    if (!$filePath) {
        return false;
    }
    // Sanitize content to prevent code injection
    $content = strip_tags($content);
    return file_put_contents($filePath, $content, LOCK_EX) !== false;
}

// Execute git fetch and pull with authentication
function gitPull($projectPath) {
    if (!is_dir($projectPath . '/.git')) {
        return ['success' => false, 'message' => __('not_git_repo')];
    }

    $config = loadGithubConfig();
    $output = [];

    // Get repository URL
    $repoUrl = shell_exec('cd ' . escapeshellarg($projectPath) . ' && git config --get remote.origin.url 2>/dev/null');
    $repoUrl = trim($repoUrl);

    if (empty($repoUrl)) {
        return ['success' => false, 'message' => __('no_remote_configured')];
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
        'message' => __('repository_updated'),
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
        echo json_encode(['success' => false, 'message' => __('project_not_found')]);
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
        echo json_encode(['success' => false, 'message' => __('no_startup_script')]);
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

    $message = $isRunning ? __('service_started') : __('service_started_check');
    if (!empty($conflictingServices)) {
        $message .= '. ' . __('stopped_services') . ': ' . implode(', ', $conflictingServices);
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
        echo json_encode(['success' => true, 'message' => __('service_stopped')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('error')]);
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
        $message .= __('stopped_services') . ': ' . implode(', ', $stopped);
    }
    if (count($portsKilled) > 0) {
        if (!empty($message)) $message .= '. ';
        $message .= __('ports_closed') . ': ' . implode(', ', $portsKilled);
    }
    if (empty($message)) {
        $message = __('no_running_services');
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
        echo json_encode(['success' => false, 'message' => __('no_logs_available')]);
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
        echo json_encode(['success' => false, 'message' => __('project_not_found')]);
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
        echo json_encode(['success' => false, 'message' => __('project_not_found')]);
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
        echo json_encode(['success' => false, 'message' => __('invalid_port')]);
        exit;
    }

    // Get PID of process using the port
    $cmd = "lsof -i :$port -t 2>/dev/null";
    $pids = shell_exec($cmd);

    if (empty(trim($pids))) {
        echo json_encode(['success' => false, 'message' => __('no_process_on_port') . ' ' . $port]);
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
        'message' => __('process_terminated') . ' ' . $port,
        'killedPids' => $killedPids
    ]);
    exit;
}

// API: Get TODO for a project
if (isset($_GET['action']) && $_GET['action'] === 'get-todo' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);

    $todo = loadTodo($project);

    echo json_encode([
        'success' => true,
        'todo' => $todo
    ]);
    exit;
}

// API: Save TODO for a project
if (isset($_GET['action']) && $_GET['action'] === 'save-todo' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_POST['project'] ?? '';
    $project = basename($project);
    $content = $_POST['content'] ?? '';

    // Additional validation
    if (empty($project)) {
        echo json_encode(['success' => false, 'message' => __('project_not_found')]);
        exit;
    }

    $result = saveTodo($project, $content);

    echo json_encode([
        'success' => $result,
        'message' => $result ? __('todo_saved') : __('error')
    ]);
    exit;
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], PASSWORD_HASH)) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
    } else {
        $error = __('incorrect_password');
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
                'description' => __('backend_no_desc'),
                'image' => null,
                'url' => $item . '/',
                'startScript' => null,
                'startLabel' => __('start'),
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
                $projectInfo['hasGitChanges'] = hasGitChanges($projectPath);
            } else {
                $projectInfo['hasGitChanges'] = false;
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
$currentLang = getCurrentLanguage();
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title><?php echo __('site_name'); ?></title>
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
                    <h1><?php echo __('site_name'); ?></h1>
                </div>

                <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password"><?php echo __('password'); ?></label>
                        <input type="password" id="password" name="password" required autofocus
                               placeholder="<?php echo __('enter_password'); ?>">
                    </div>
                    <button type="submit" class="btn-login"><?php echo __('login'); ?></button>
                </form>

                <!-- Language Selector -->
                <div style="text-align: center; margin-top: 20px;">
                    <a href="?lang=en" style="color: #64748b; text-decoration: none; margin: 0 10px; <?php echo getCurrentLanguage() === 'en' ? 'font-weight: bold; color: #3b82f6;' : ''; ?>">English</a>
                    <span style="color: #64748b;">|</span>
                    <a href="?lang=es" style="color: #64748b; text-decoration: none; margin: 0 10px; <?php echo getCurrentLanguage() === 'es' ? 'font-weight: bold; color: #3b82f6;' : ''; ?>">Español</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Project Dashboard -->
        <header class="dashboard-header">
            <h1><?php echo __('site_name'); ?></h1>
            <div class="header-actions">
                <!-- Language Selector -->
                <div class="language-selector">
                    <a href="?lang=en" class="<?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=es" class="<?php echo getCurrentLanguage() === 'es' ? 'active' : ''; ?>">ES</a>
                </div>
                <!-- TODO Button -->
                <button class="btn-header-todo" onclick="openTodoModal(this)" data-project="backend.patchamama.com" title="<?php echo __('edit_todo'); ?>">
                    TODO
                </button>
                <!-- GitHub Button -->
                <a href="https://github.com/patchamama/desploy-apps-manager"
                   target="_blank"
                   class="btn-header-github"
                   title="<?php echo __('open_on_github'); ?>">
                    GitHub
                </a>
                <span id="runningCount" class="running-badge <?php echo $runningCount > 0 ? 'active' : ''; ?>">
                    <span class="pulse"></span>
                    <span class="count"><?php echo $runningCount; ?></span> <?php echo __('running'); ?>
                </span>
                <button class="btn-ports" onclick="showOpenPorts()" title="<?php echo __('view_open_ports'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 1v6m0 6v6M1 12h6m6 0h6"/>
                    </svg>
                    <?php echo __('ports'); ?>
                </button>
                <button class="btn-stop-all" onclick="stopAllServices()" title="<?php echo __('stop_all_services'); ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                    </svg>
                    <?php echo __('stop_all'); ?>
                </button>
                <a href="?logout" class="btn-logout"><?php echo __('logout'); ?></a>
            </div>
        </header>

        <main class="projects-grid">
            <?php if (empty($projects)): ?>
            <div class="no-projects">
                <p><?php echo __('no_projects'); ?></p>
            </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                <div class="project-card <?php echo $project['isRunning'] ? 'running' : ''; ?>" data-project="<?php echo htmlspecialchars($project['name']); ?>">
                    <a href="<?php echo htmlspecialchars($project['url']); ?>" class="project-link" target="_blank">
                        <div class="project-image">
                            <?php if ($project['isRunning']): ?>
                            <div class="running-indicator">
                                <span class="pulse"></span> <?php echo __('running_indicator'); ?>
                            </div>
                            <?php endif; ?>
                            <div class="git-actions">
                                <button class="btn-git btn-git-todo"
                                        data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                        onclick="event.preventDefault(); event.stopPropagation(); openTodoModal(this);"
                                        title="<?php echo __('edit_todo'); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </button>
                                <?php if ($project['isGitRepo'] && $project['repoUrl']): ?>
                                <a href="<?php echo htmlspecialchars($project['repoUrl']); ?>"
                                   target="_blank"
                                   class="btn-git btn-git-repo <?php echo $project['hasGitChanges'] ? 'has-changes' : ''; ?>"
                                   onclick="event.stopPropagation();"
                                   title="<?php echo __('open_on_github'); ?><?php echo $project['hasGitChanges'] ? ' - ' . __('pending_changes') : ''; ?>">
                                    <?php if ($project['hasGitChanges']): ?>
                                    <span class="git-changes-indicator"></span>
                                    <?php endif; ?>
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                </a>
                                <?php endif; ?>
                            </div>
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
                                <span><?php echo __('frontend_project'); ?></span>
                                <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span><?php echo __('backend_project'); ?></span>
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
                            <?php echo __('open'); ?>
                        </a>
                        <?php if ($project['startScript']): ?>
                            <?php if ($project['isRunning']): ?>
                            <button class="btn-action btn-stop"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    onclick="stopService(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="6" y="6" width="12" height="12"/>
                                </svg>
                                <?php echo __('stop'); ?>
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
                                <?php echo __('logs'); ?>
                            </button>
                            <?php else: ?>
                            <button class="btn-action btn-start"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    onclick="runScript(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                <?php echo __('start'); ?>
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
                    <h3 id="modalTitle"><?php echo __('executing'); ?></h3>
                    <button class="modal-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="modalLoader" class="loader"></div>
                    <div id="modalResult" class="result-output"></div>
                </div>
                <div class="modal-footer" id="modalFooter" style="display: none;">
                    <button class="btn-action btn-primary" onclick="closeModalAndReload()"><?php echo __('close_and_update'); ?></button>
                </div>
            </div>
        </div>

        <!-- Open Ports Modal -->
        <div id="portsModal" class="modal">
            <div class="modal-content ports-modal-content">
                <div class="modal-header">
                    <h3><?php echo __('open_ports'); ?></h3>
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
                        <?php echo __('update'); ?>
                    </button>
                    <button class="btn-action btn-primary" onclick="closePortsModal()"><?php echo __('close'); ?></button>
                </div>
            </div>
        </div>

        <!-- TODO Modal -->
        <div id="todoModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><?php echo __('todo_notes'); ?>: <span id="todoProjectName"></span></h3>
                    <div class="modal-header-actions">
                        <button class="modal-maximize" onclick="toggleMaximizeTodoModal()" title="Maximizar/Restaurar">
                            <svg id="maximizeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/>
                            </svg>
                        </button>
                        <button class="modal-close" onclick="closeTodoModal()">&times;</button>
                    </div>
                </div>
                <div class="modal-body">
                    <textarea id="todoTextarea"
                              placeholder="<?php echo __('todo_placeholder'); ?>"
                              style="width: 100%; min-height: 300px; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: monospace; font-size: 14px; resize: vertical;"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn-action" onclick="closeTodoModal()"><?php echo __('close'); ?></button>
                    <button class="btn-action btn-primary" onclick="saveTodo()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <?php echo __('save'); ?>
                    </button>
                </div>
            </div>
        </div>

        <footer class="dashboard-footer">
            <p><?php echo count($projects) . ' ' . __('projects_available'); ?></p>
        </footer>
        <?php endif; ?>
    </div>

    <script>
    // Translations for JavaScript
    const i18n = <?php echo json_encode([
        'starting_service' => __('starting_service'),
        'stopping_service' => __('stopping_service'),
        'stopping_all_services' => __('stopping_all_services'),
        'loading_logs' => __('loading_logs'),
        'updating_repository' => __('updating_repository'),
        'service_started' => __('service_started'),
        'service_starting' => __('service_starting'),
        'service_stopped' => __('service_stopped'),
        'services_stopped' => __('services_stopped'),
        'repository_updated' => __('repository_updated'),
        'connection_error' => __('connection_error'),
        'error' => __('error'),
        'no_open_ports' => __('no_open_ports'),
        'port' => __('port'),
        'project' => __('project'),
        'application' => __('application'),
        'type' => __('type'),
        'action' => __('action'),
        'kill' => __('kill'),
        'terminate_process' => __('terminate_process'),
        'error_loading_ports' => __('error_loading_ports'),
        'stop_all_confirm' => __('stop_all_confirm'),
        'kill_port_confirm' => __('kill_port_confirm'),
        'process_terminated' => __('process_terminated'),
        'logs' => __('logs'),
        'todo_saved' => __('todo_saved'),
        'edit_todo' => __('edit_todo'),
        'todo_placeholder' => __('todo_placeholder'),
        'unsaved_changes' => __('unsaved_changes')
    ], JSON_UNESCAPED_UNICODE); ?>;

    function runScript(button) {
        const project = button.dataset.project;
        showModal(i18n.starting_service, true);

        button.disabled = true;
        button.classList.add('loading');

        fetch(`?action=run-script&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? (data.running ? i18n.service_started : i18n.service_starting) : i18n.error,
                    data.output || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal(i18n.error, i18n.connection_error + ': ' + error.message, false);
            })
            .finally(() => {
                button.disabled = false;
                button.classList.remove('loading');
            });
    }

    function stopService(button) {
        const project = button.dataset.project;
        showModal(i18n.stopping_service, true);

        button.disabled = true;

        fetch(`?action=stop-service&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? i18n.service_stopped : i18n.error,
                    data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal(i18n.error, i18n.connection_error + ': ' + error.message, false);
            });
    }

    function stopAllServices() {
        if (!confirm(i18n.stop_all_confirm)) return;

        showModal(i18n.stopping_all_services, true);

        fetch('?action=stop-all')
            .then(response => response.json())
            .then(data => {
                updateModal(
                    i18n.services_stopped,
                    data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal(i18n.error, i18n.connection_error + ': ' + error.message, false);
            });
    }

    function viewLogs(button) {
        const project = button.dataset.project;
        showModal(i18n.loading_logs, true);

        fetch(`?action=logs&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    i18n.logs + ': ' + project,
                    data.logs || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal(i18n.error, i18n.connection_error + ': ' + error.message, false);
            });
    }

    function gitPull(button) {
        const project = button.dataset.project;
        showModal(i18n.updating_repository, true);

        button.disabled = true;
        button.classList.add('loading');

        fetch(`?action=git-pull&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                updateModal(
                    data.success ? i18n.repository_updated : i18n.error,
                    data.output || data.message,
                    data.success
                );
            })
            .catch(error => {
                updateModal(i18n.error, i18n.connection_error + ': ' + error.message, false);
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
                    portsList.innerHTML = '<div class="no-ports">' + i18n.no_open_ports + '</div>';
                    return;
                }

                let html = '<table class="ports-table"><thead><tr><th>' + i18n.port + '</th><th>' + i18n.project + '</th><th>' + i18n.application + '</th><th>' + i18n.type + '</th><th>PID</th><th>' + i18n.action + '</th></tr></thead><tbody>';

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
                                <button class="btn-kill" onclick="killPort(${port.port})" title="${i18n.terminate_process}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    ${i18n.kill}
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
                portsList.innerHTML = '<div class="error-message">' + i18n.error_loading_ports + ': ' + error.message + '</div>';
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
        if (!confirm(i18n.kill_port_confirm + ' ' + port + '?')) {
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

    // TODO Modal functions
    let currentTodoProject = null;
    let originalTodoContent = '';
    let currentTodoContent = '';

    function openTodoModal(button) {
        const project = button.dataset.project;
        currentTodoProject = project;

        const modal = document.getElementById('todoModal');
        const projectNameSpan = document.getElementById('todoProjectName');
        const textarea = document.getElementById('todoTextarea');

        projectNameSpan.textContent = project;
        textarea.value = '';
        textarea.disabled = true;
        originalTodoContent = '';
        currentTodoContent = '';

        modal.classList.add('active');

        // Load TODO content
        fetch(`?action=get-todo&project=${encodeURIComponent(project)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    textarea.value = data.todo || '';
                    originalTodoContent = data.todo || '';
                    currentTodoContent = data.todo || '';
                }
                textarea.disabled = false;
                textarea.focus();
            })
            .catch(error => {
                console.error('Error loading TODO:', error);
                textarea.disabled = false;
            });
    }

    function hasUnsavedChanges() {
        const textarea = document.getElementById('todoTextarea');
        currentTodoContent = textarea.value;
        return currentTodoContent !== originalTodoContent;
    }

    function closeTodoModal() {
        if (hasUnsavedChanges()) {
            if (!confirm(i18n.unsaved_changes)) {
                return;
            }
        }

        const modal = document.getElementById('todoModal');
        modal.classList.remove('active');
        modal.classList.remove('maximized');
        currentTodoProject = null;
        originalTodoContent = '';
        currentTodoContent = '';
    }

    function toggleMaximizeTodoModal() {
        const modal = document.getElementById('todoModal');
        modal.classList.toggle('maximized');
    }

    function saveTodo() {
        if (!currentTodoProject) return;

        const textarea = document.getElementById('todoTextarea');
        const content = textarea.value;

        const formData = new FormData();
        formData.append('project', currentTodoProject);
        formData.append('content', content);

        fetch('?action=save-todo', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(i18n.todo_saved, 'success');
                    // Update original content after successful save
                    originalTodoContent = content;
                    currentTodoContent = content;
                } else {
                    showNotification(data.message || i18n.error, 'error');
                }
            })
            .catch(error => {
                showNotification(i18n.error + ': ' + error.message, 'error');
            });
    }

    // Close TODO modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('todoModal').classList.contains('active')) {
            closeTodoModal();
        }
    });
    </script>
</body>
</html>
