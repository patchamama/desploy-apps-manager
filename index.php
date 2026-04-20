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
        'running_indicator' => 'Running',
        'sudo_password_required' => 'Confirmation required',
        'sudo_password_label' => 'Enter the application password to start this privileged service:',
        'sudo_password_placeholder' => 'Application password',
        'sudo_confirm' => 'Confirm and start',
        'sudo_cancel' => 'Cancel',
        'sudo_wrong_password' => 'Incorrect sudo password',
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
        'running_indicator' => 'En ejecución',
        'sudo_password_required' => 'Confirmación requerida',
        'sudo_password_label' => 'Ingresa la contraseña de la aplicación para iniciar este servicio privilegiado:',
        'sudo_password_placeholder' => 'Contraseña de la aplicación',
        'sudo_confirm' => 'Confirmar e iniciar',
        'sudo_cancel' => 'Cancelar',
        'sudo_wrong_password' => 'Contraseña sudo incorrecta',
    ]
];

// Get current language
function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }
    return 'en'; // Default language is English
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

// Function to count pending tasks (- [ ]) in a project's TODO
function getTodoPendingCount($projectName) {
    $content = loadTodo($projectName);
    if (empty($content)) return 0;
    return substr_count($content, '- [ ]');
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
function isServiceRunning($projectName, $processPattern = null) {
    $state = loadServicesState();

    // If not in state, try to recover from .pid file or process pattern
    if (!isset($state[$projectName])) {
        $recovered = false;

        // Try .pid file first
        $pidFile = PID_DIR . '/' . $projectName . '.pid';
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if ($pid && is_numeric($pid) && file_exists("/proc/$pid")) {
                markServiceRunning($projectName, (int)$pid);
                $state = loadServicesState();
                $recovered = true;
            }
        }

        // Try process pattern (pgrep) if pid file didn't work
        if (!$recovered && !empty($processPattern)) {
            $escapedPattern = escapeshellarg($processPattern);
            $pid = trim(shell_exec("pgrep -f $escapedPattern 2>/dev/null | head -1"));
            if ($pid && is_numeric($pid) && file_exists("/proc/$pid")) {
                markServiceRunning($projectName, (int)$pid);
                // Update pid file too
                file_put_contents(PID_DIR . '/' . $projectName . '.pid', $pid);
                $state = loadServicesState();
            }
        }

        if (!isset($state[$projectName])) {
            return false;
        }
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
        $stopCmd = $info['stopCommand'];
        if (!empty($info['requiresSudo'])) {
            $stopCmd = 'sudo ' . $stopCmd;
        }
        exec('cd ' . escapeshellarg($projectPath) . ' && ' . $stopCmd . ' 2>&1');
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
    $requiresSudo = !empty($info['requiresSudo']);
    $sudoPassword = null;

    if ($requiresSudo) {
        $input = json_decode(file_get_contents('php://input'), true);
        $sudoPassword = $input['sudoPassword'] ?? '';
        if (empty($sudoPassword)) {
            echo json_encode(['success' => false, 'message' => __('sudo_wrong_password'), 'needsSudoPassword' => true]);
            exit;
        }
        if (!password_verify($sudoPassword, PASSWORD_HASH)) {
            echo json_encode(['success' => false, 'message' => __('sudo_wrong_password'), 'needsSudoPassword' => true]);
            exit;
        }
        $sudoPassword = true; // validated — only used as flag now
    }

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
                if ($requiresSudo && $sudoPassword === true) {
                    $command = sprintf(
                        'sudo -n %s %s >> %s 2>&1 & echo $!',
                        escapeshellarg($projectPath . '/' . $scriptFile),
                        $scriptArgs,
                        escapeshellarg($logFile)
                    );
                } else {
                    $command = sprintf(
                        'cd %s && nohup bash %s %s >> %s 2>&1 & echo $!',
                        escapeshellarg($projectPath),
                        escapeshellarg($scriptFile),
                        $scriptArgs,
                        escapeshellarg($logFile)
                    );
                }
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

// API: Run a project extra action (one-shot script, no service tracking)
if (isset($_GET['action']) && $_GET['action'] === 'run-action' && isAuthenticated()) {
    header('Content-Type: application/json');

    $project = $_GET['project'] ?? '';
    $project = basename($project);
    $actionId = (int)($_GET['action-id'] ?? 0);

    $projectPath = __DIR__ . '/' . $project;
    $infoFile = $projectPath . '/.project-info.json';

    if (!is_dir($projectPath) || !file_exists($infoFile)) {
        echo json_encode(['success' => false, 'message' => __('project_not_found')]);
        exit;
    }

    $info = json_decode(file_get_contents($infoFile), true);
    $extraActions = $info['extraActions'] ?? [];

    if (!isset($extraActions[$actionId])) {
        echo json_encode(['success' => false, 'message' => 'Acción no encontrada']);
        exit;
    }

    $actionConfig = $extraActions[$actionId];
    $script = $actionConfig['script'] ?? null;
    $scriptType = $actionConfig['type'] ?? 'bash';
    $label = $actionConfig['label'] ?? 'Action';

    if (!$script) {
        echo json_encode(['success' => false, 'message' => 'Script no configurado']);
        exit;
    }

    if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0755, true);
    $logFile = LOG_DIR . '/' . $project . '-action-' . $actionId . '.log';
    file_put_contents($logFile, "=== $label [" . date('Y-m-d H:i:s') . "] ===\n");

    $scriptParts = explode(' ', $script, 2);
    $scriptFile  = $scriptParts[0];
    $scriptArgs  = isset($scriptParts[1]) ? $scriptParts[1] : '';

    switch ($scriptType) {
        case 'python':
            $command = sprintf(
                'cd %s && nohup python3 %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath), escapeshellarg($scriptFile), $scriptArgs, escapeshellarg($logFile)
            );
            break;
        case 'node':
            $command = sprintf(
                'cd %s && nohup node %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath), escapeshellarg($scriptFile), $scriptArgs, escapeshellarg($logFile)
            );
            break;
        case 'bash':
        default:
            $command = sprintf(
                'cd %s && nohup bash %s %s >> %s 2>&1 & echo $!',
                escapeshellarg($projectPath), escapeshellarg($scriptFile), $scriptArgs, escapeshellarg($logFile)
            );
            break;
    }

    $pid = trim(shell_exec($command));
    $success = !empty($pid) && is_numeric($pid);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? "$label iniciado. Revisa los logs para ver el progreso."
            : "Error al iniciar $label",
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

// API: Server stats
if (isset($_GET['action']) && $_GET['action'] === 'server-stats' && isAuthenticated()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true] + getServerStats());
    exit;
}

// API: Kill process by PID
if (isset($_GET['action']) && $_GET['action'] === 'kill-pid' && isAuthenticated()) {
    header('Content-Type: application/json');
    $pid = $_GET['pid'] ?? '';
    if (!is_numeric($pid) || (int)$pid < 2) { echo json_encode(['success' => false, 'message' => 'Invalid PID']); exit; }
    exec("kill $pid 2>/dev/null");
    usleep(350000);
    if (file_exists("/proc/$pid")) exec("kill -9 $pid 2>/dev/null");
    echo json_encode(['success' => true, 'message' => "Process $pid terminated"]);
    exit;
}

// API: Auto-detect projects without .project-info.json
if (isset($_GET['action']) && $_GET['action'] === 'autodetect-projects' && isAuthenticated()) {
    header('Content-Type: application/json');
    $detected = [];
    $exclude = ['.','..','assets','node_modules','vendor','.git','.claude','.pids','.logs','.todos','.venv','calibre'];
    foreach (scandir(__DIR__) as $item) {
        if (!is_dir(__DIR__."/$item") || in_array($item, $exclude) || $item[0] === '.') continue;
        $projectPath = __DIR__."/$item";
        if (file_exists("$projectPath/.project-info.json")) continue;
        $tech = detectProjectTech($projectPath);
        if (!$tech) continue;
        $info = buildProjectJson($item, $tech);
        file_put_contents("$projectPath/.project-info.json", json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $startPath = "$projectPath/start.sh";
        if (!file_exists($startPath)) { file_put_contents($startPath, buildStartSh($tech)); chmod($startPath, 0755); }
        $detected[] = $item;
    }
    echo json_encode(['success' => true, 'detected' => $detected, 'count' => count($detected)]);
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

// ─── Server Stats helpers ──────────────────────────────────────────────────────

function parseSystemReport() {
    $reportFile = __DIR__ . '/report-systems.txt';
    if (!is_readable($reportFile)) return null;
    $raw = file_get_contents($reportFile);
    if (!$raw) return null;

    // Strip ANSI color codes
    $text = preg_replace('/\x1b\[[0-9;]*m/', '', $raw);

    // Date
    $date = '';
    if (preg_match('/^Fecha:\s*(.+)$/m', $text, $m)) $date = trim($m[1]);

    // UBC resource lines: NAME  HELD  MAXHELD  LIMIT  FAIL  % USO
    $resources = [];
    preg_match_all('/^(\w+)\s+([\d.]+[KMGTP]?)\s+([\d.]+[KMGTP]?)\s+(\S+)\s+(\d+)\s+([\d.]+)%/m', $text, $ms, PREG_SET_ORDER);
    foreach ($ms as $m) {
        $resources[] = [
            'name'    => $m[1],
            'held'    => $m[2],
            'maxheld' => $m[3],
            'limit'   => $m[4],
            'fail'    => (int)$m[5],
            'pct'     => (float)$m[6],
        ];
    }

    // File census
    $fileCount = null;
    if (preg_match('/Total:\s*(\d+)\s*archivos/i', $text, $m)) $fileCount = (int)$m[1];

    $hasFail = !empty(array_filter($resources, fn($r) => $r['fail'] > 0));

    return ['date' => $date, 'resources' => $resources, 'file_count' => $fileCount, 'has_fail' => $hasFail];
}

function getServerStats() {
    // RAM — via `free -b` (open_basedir blocks file('/proc/meminfo'))
    $ramTotal = $ramUsed = 0;
    $freeOut = shell_exec('free -b 2>/dev/null');
    if ($freeOut) {
        $lines = array_values(array_filter(explode("\n", $freeOut)));
        if (isset($lines[1])) {
            $p = preg_split('/\s+/', trim($lines[1]));
            $ramTotal = (int)($p[1] ?? 0);
            $ramUsed  = $ramTotal - (int)($p[6] ?? 0); // total - available
        }
    }

    // Disk — via `df -B1 /` (disk_total_space blocked by open_basedir)
    $diskTotal = $diskUsed = 0;
    $dfOut = shell_exec('df -B1 / 2>/dev/null');
    if ($dfOut) {
        $lines = array_values(array_filter(explode("\n", $dfOut)));
        if (isset($lines[1])) {
            $p = preg_split('/\s+/', trim($lines[1]));
            $diskTotal = (int)($p[1] ?? 0);
            $diskUsed  = (int)($p[2] ?? 0);
        }
    }

    // Inodes
    $inodesTotal = $inodesUsed = 0;
    $dfInode = shell_exec("df -i / 2>/dev/null | awk 'NR==2{print \$2,\$3}'");
    if ($dfInode) { $p = explode(' ', trim($dfInode)); $inodesTotal = (int)($p[0] ?? 0); $inodesUsed = (int)($p[1] ?? 0); }

    // Top processes
    $ps = shell_exec("ps aux --no-headers --sort=-%cpu 2>/dev/null | head -25") ?: '';
    $processes = [];
    foreach (explode("\n", trim($ps)) as $line) {
        if (empty(trim($line))) continue;
        $p = preg_split('/\s+/', trim($line), 11);
        if (count($p) >= 11) $processes[] = ['user' => $p[0], 'pid' => $p[1], 'cpu' => $p[2], 'mem' => $p[3], 'command' => mb_strimwidth($p[10], 0, 80, '…')];
    }

    // Technologies
    $techChecks = [
        'PHP'        => 'php -r "echo PHP_VERSION;" 2>/dev/null',
        'Python'     => 'python3 --version 2>/dev/null',
        'Node.js'    => 'node --version 2>/dev/null',
        'Go'         => 'go version 2>/dev/null',
        'Java'       => 'java -version 2>&1 | head -1',
        'MySQL'      => 'mysql --version 2>/dev/null',
        'PostgreSQL' => 'psql --version 2>/dev/null',
        'Docker'     => 'docker --version 2>/dev/null',
        'Git'        => 'git --version 2>/dev/null',
        'Nginx'      => 'nginx -v 2>&1',
        'Apache'     => 'apache2 -v 2>&1 | head -1',
        'Redis'      => 'redis-server --version 2>/dev/null',
    ];
    $techs = [];
    foreach ($techChecks as $name => $cmd) {
        $out = trim(shell_exec($cmd) ?: '');
        if (!empty($out)) {
            preg_match('/\d+\.\d+\.?\d*/u', $out, $vm);
            $techs[] = ['name' => $name, 'version' => $vm[0] ?? '?'];
        }
    }
    // NVIDIA GPU
    $gpuOut = trim(shell_exec('nvidia-smi --query-gpu=name,memory.total --format=csv,noheader 2>/dev/null') ?: '');
    if (!empty($gpuOut)) $techs[] = ['name' => 'NVIDIA GPU', 'version' => $gpuOut];

    // Docker containers
    $docker = [];
    $dcRaw = trim(shell_exec('docker ps --format "{{.Names}}|{{.Status}}|{{.Image}}" 2>/dev/null') ?: '');
    if (!empty($dcRaw)) {
        foreach (explode("\n", $dcRaw) as $line) {
            $p = explode('|', $line, 3);
            if (count($p) === 3) $docker[] = ['name' => $p[0], 'status' => $p[1], 'image' => $p[2]];
        }
    }

    return ['ram' => ['total' => $ramTotal, 'used' => $ramUsed], 'disk' => ['total' => $diskTotal, 'used' => $diskUsed], 'inodes' => ['total' => $inodesTotal, 'used' => $inodesUsed], 'processes' => $processes, 'techs' => $techs, 'docker' => $docker, 'ubc' => parseSystemReport()];
}

// ─── Auto-detect project technology ───────────────────────────────────────────

function detectProjectTech($path) {
    $files = array_flip(scandir($path));
    if (isset($files['manage.py']))     return ['type'=>'python','fw'=>'Django','entry'=>'manage.py','port'=>8000,'tech'=>'Python + Django'];
    if (isset($files['run.py'])) {
        $c = file_get_contents("$path/run.py");
        $fw = (stripos($c,'fastapi')!==false) ? 'FastAPI' : 'Flask';
        return ['type'=>'python','fw'=>$fw,'entry'=>'run.py','port'=>8000,'tech'=>"Python + $fw"];
    }
    if (isset($files['app.py']))        return ['type'=>'python','fw'=>'Flask','entry'=>'app.py','port'=>8000,'tech'=>'Python + Flask'];
    if (isset($files['main.py']))       return ['type'=>'python','fw'=>'Python','entry'=>'main.py','port'=>8000,'tech'=>'Python'];
    if (isset($files['go.mod']) || isset($files['main.go'])) return ['type'=>'go','fw'=>'Go','entry'=>'main.go','port'=>8080,'tech'=>'Go'];
    if (isset($files['package.json']))  return ['type'=>'node','fw'=>'Node.js','entry'=>'package.json','port'=>3000,'tech'=>'Node.js'];
    if (isset($files['pom.xml']))       return ['type'=>'java','fw'=>'Java/Maven','entry'=>'pom.xml','port'=>8080,'tech'=>'Java + Maven'];
    if (isset($files['build.gradle']))  return ['type'=>'java','fw'=>'Java/Gradle','entry'=>'build.gradle','port'=>8080,'tech'=>'Java + Gradle'];
    if (isset($files['index.php']))     return ['type'=>'php','fw'=>'PHP','entry'=>'index.php','port'=>80,'tech'=>'PHP'];
    return null;
}

function buildProjectJson($name, $tech) {
    return ['name'=>$name,'title'=>ucwords(str_replace(['-','_'],' ',$name)),'description'=>'Auto-detected '.$tech['fw'].' project.','image'=>null,'startScript'=>'start.sh','startLabel'=>'Start '.$tech['fw'],'scriptType'=>'bash','port'=>$tech['port'],'processPattern'=>$tech['entry'],'type'=>'backend','technology'=>$tech['tech'],'webApp'=>true,'requiresSudo'=>false];
}

function buildStartSh($tech) {
    $run = match($tech['type']) {
        'python' => match($tech['entry']) { 'manage.py' => 'python manage.py runserver 0.0.0.0:8000', 'run.py' => 'python run.py', default => 'python '.$tech['entry'] },
        'go'     => 'go run .',
        'node'   => 'npm install && npm start',
        default  => 'echo "Configure this start script."',
    };
    $venvBlock = $tech['type'] === 'python' ? <<<'VENV'

PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
VENV_DIR="$PROJECT_ROOT/.venv"

if [ ! -f "$VENV_DIR/bin/activate" ]; then
    python3 -m venv "$VENV_DIR"
fi
source "$VENV_DIR/bin/activate"

REQS="$SCRIPT_DIR/requirements.txt"
HASH_FILE="$VENV_DIR/.deps_$(basename "$SCRIPT_DIR")"
_hash=$(md5sum "$REQS" 2>/dev/null | cut -d' ' -f1)
if [ "$(cat "$HASH_FILE" 2>/dev/null)" != "$_hash" ]; then
    pip install --quiet --upgrade pip
    pip install --quiet -r "$REQS"
    echo "$_hash" > "$HASH_FILE"
fi

VENV
    : '';
    return "#!/usr/bin/env bash\nset -e\nSCRIPT_DIR=\"\$(cd \"\$(dirname \"\${BASH_SOURCE[0]}\")\" && pwd)\"\n$venvBlock\ncd \"\$SCRIPT_DIR\" && $run\n";
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
                'ports' => null,
                'webApp' => true,
                'processPattern' => null
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
                $projectInfo['isRunning'] = isServiceRunning($item, $projectInfo['processPattern'] ?? null);
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
<html lang="<?php echo $currentLang; ?>" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title><?php echo __('site_name'); ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate icon" href="assets/favicon.svg">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        // Apply theme before render to prevent flash
        (function() {
            var t = localStorage.getItem('theme');
            if (t === 'light') document.documentElement.classList.add('light');
        })();
    </script>
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

                <!-- Language & Theme -->
                <div class="login-footer-row">
                    <a href="?lang=en" class="login-lang-link <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">English</a>
                    <span class="sep">|</span>
                    <a href="?lang=es" class="login-lang-link <?php echo getCurrentLanguage() === 'es' ? 'active' : ''; ?>">Español</a>
                    <span class="sep">|</span>
                    <button class="btn-theme-toggle" onclick="toggleTheme()" title="Toggle theme">
                        <svg id="themeIconDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg id="themeIconLight" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Project Dashboard -->
        <header class="dashboard-header">
            <h1><?php echo __('site_name'); ?></h1>
            <div class="header-actions">
                <!-- Theme Toggle -->
                <button class="btn-theme-toggle" id="themeToggle" onclick="toggleTheme()" title="Toggle light/dark mode">
                    <svg id="themeIconDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                        <circle cx="12" cy="12" r="5"/>
                        <line x1="12" y1="1" x2="12" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/>
                        <line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                    <svg id="themeIconLight" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <!-- Language Selector -->
                <div class="language-selector">
                    <a href="?lang=en" class="<?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>">EN</a>
                    <a href="?lang=es" class="<?php echo getCurrentLanguage() === 'es' ? 'active' : ''; ?>">ES</a>
                </div>
                <!-- TODO Button -->
                <button class="btn-header-todo" onclick="openTodoModal(this)" data-project="backend.patchamama.com" title="<?php echo __('edit_todo'); ?>">
                    TODO<?php $headerTodoPending = getTodoPendingCount('backend.patchamama.com'); if ($headerTodoPending > 0): ?><span class="todo-pending-badge"><?php echo $headerTodoPending; ?></span><?php endif; ?>
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
                <button class="btn-server-action" onclick="autodetectProjects(this)" title="Scan for new projects without config">⊕ Scan</button>
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
                                    <?php $pendingCount = getTodoPendingCount($project['name']); if ($pendingCount > 0): ?><span class="todo-pending-badge"><?php echo $pendingCount; ?></span><?php endif; ?>
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
                        <?php if ($project['webApp'] !== false): ?>
                        <a href="<?php echo htmlspecialchars($project['url']); ?>" class="btn-action btn-open" target="_blank">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                            <?php echo __('open'); ?>
                        </a>
                        <?php endif; ?>
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
                            <?php endif; ?>
                            <?php if ($project['isRunning'] || $project['webApp'] === false): ?>
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
                            <?php endif; ?>
                            <?php if (!$project['isRunning']): ?>
                            <button class="btn-action btn-start"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    data-requires-sudo="<?php echo !empty($project['requiresSudo']) ? 'true' : 'false'; ?>"
                                    onclick="runScript(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"/>
                                </svg>
                                <?php echo __('start'); ?>
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($project['extraActions']) && is_array($project['extraActions'])): ?>
                            <?php foreach ($project['extraActions'] as $idx => $action): ?>
                            <button class="btn-action btn-deploy"
                                    data-project="<?php echo htmlspecialchars($project['name']); ?>"
                                    data-action-id="<?php echo (int)$idx; ?>"
                                    data-action-label="<?php echo htmlspecialchars($action['label'] ?? 'Deploy'); ?>"
                                    onclick="runAction(this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="16 16 12 12 8 16"/>
                                    <line x1="12" y1="12" x2="12" y2="21"/>
                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                                </svg>
                                <?php echo htmlspecialchars($action['label'] ?? 'Deploy'); ?>
                            </button>
                            <?php endforeach; ?>
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
                        <button class="modal-maximize" onclick="toggleMaximizeTodoModal()" title="Maximizar/Restaurar">+</button>
                        <button class="modal-close" onclick="closeTodoModal()">&times;</button>
                    </div>
                </div>
                <div class="modal-body">
                    <textarea id="todoTextarea"
                              placeholder="<?php echo __('todo_placeholder'); ?>"></textarea>
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

        <!-- Sudo Password Modal -->
        <div id="sudoModal" class="modal">
            <div class="modal-content modal-sm">
                <div class="modal-header">
                    <h3><?php echo __('sudo_password_required'); ?></h3>
                    <button class="modal-close" onclick="closeSudoModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="modal-label"><?php echo __('sudo_password_label'); ?></p>
                    <input type="password" id="sudoPasswordInput"
                           placeholder="<?php echo __('sudo_password_placeholder'); ?>"
                           onkeydown="if(event.key==='Enter') confirmSudoStart()" />
                    <p id="sudoError"><?php echo __('sudo_wrong_password'); ?></p>
                </div>
                <div class="modal-footer">
                    <button class="btn-action" onclick="closeSudoModal()"><?php echo __('sudo_cancel'); ?></button>
                    <button class="btn-action btn-primary" onclick="confirmSudoStart()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        <?php echo __('sudo_confirm'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Server Panel -->
        <section class="server-panel" id="serverPanel">
            <div class="server-panel-header">
                <h2>Server</h2>
                <div class="server-panel-actions">
                    <button class="btn-server-action" onclick="refreshServerStats()">↻ Refresh</button>
                </div>
            </div>

            <div class="server-metrics">
                <div class="metric-card">
                    <span class="metric-name">RAM</span>
                    <div class="metric-bar-wrap"><div class="metric-bar-fill" id="ramFill"></div></div>
                    <span class="metric-stat" id="ramStat">—</span>
                </div>
                <div class="metric-card">
                    <span class="metric-name">Disk</span>
                    <div class="metric-bar-wrap"><div class="metric-bar-fill" id="diskFill"></div></div>
                    <span class="metric-stat" id="diskStat">—</span>
                </div>
                <div class="metric-card">
                    <span class="metric-name">Inodes</span>
                    <div class="metric-bar-wrap"><div class="metric-bar-fill" id="inodesFill"></div></div>
                    <span class="metric-stat" id="inodesStat">—</span>
                </div>
            </div>

            <div id="ubcSection" style="display:none; margin-bottom: 20px;">
                <div class="server-proc-header">
                    <h3 id="ubcTitle">Kernel Resources (UBC)</h3>
                    <small id="ubcDateHint">auto-refresh 10m</small>
                </div>
                <div id="ubcTable"></div>
            </div>

            <div class="server-tech-grid" id="serverTechs">
                <div class="server-loading">Loading…</div>
            </div>

            <div class="server-docker" id="serverDocker"></div>

            <div>
                <div class="server-proc-header">
                    <h3>Top Processes</h3>
                    <small id="procRefreshHint">auto-refresh 15s</small>
                </div>
                <div id="processTable"><div class="server-loading">Loading…</div></div>
            </div>
        </section>

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
        'unsaved_changes' => __('unsaved_changes'),
        'sudo_wrong_password' => __('sudo_wrong_password'),
    ], JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="assets/app.js"></script>
</body>
</html>
