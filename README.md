# Application Deployment & Management Hub

A comprehensive web-based application deployment and management platform that simplifies the deployment, execution, and monitoring of multiple frontend and backend applications built with different frameworks.

## Project Mission

To create a unified, easy-to-use deployment and management solution that:

- **Simplifies Deployment**: Enable quick and hassle-free deployment of applications without complex server configurations
- **Centralized Management**: Manage multiple applications from a single dashboard
- **Multi-Framework Support**: Support popular frameworks across different ecosystems
- **Development-Friendly**: Facilitate testing, development, and continuous improvement of deployed applications
- **Multi-Port Management**: Handle applications running on multiple ports simultaneously
- **Real-Time Monitoring**: Monitor application status, logs, and port usage in real-time

## Key Features

### 1. **Unified Dashboard**
- Web-based interface to manage all deployed applications
- Real-time status monitoring with visual indicators
- Project cards with metadata (technology stack, ports, repository info)
- Responsive design for desktop and mobile devices

### 2. **Application Management**
- Start, stop, and restart applications with a single click
- Support for multiple scripts per application
- Automatic port conflict detection and resolution
- Process ID (PID) tracking for reliable application management
- Real-time log viewing

### 3. **Multi-Port Support**
- Configure multiple ports per application
- Automatic port conflict detection
- Port status overview and process identification
- Project-to-port relationship mapping
- Bulk port management (start/stop all)

### 4. **Advanced Script Execution**
- Support for multiple scripts per project
- Individual script logging with timestamps
- Script execution with configurable parameters
- Support for bash, Python, Node.js, and PHP scripts

### 5. **Repository Integration**
- GitHub repository status tracking
- Git pull functionality with authentication
- Commit history viewing
- Branch information display

### 6. **Port Analysis & Management**
- Dynamic port scanning from project configurations
- Automatic port detection from `.project-info.json` files
- Project identification for each active port
- Technology stack information display
- Process termination with force kill options

### 7. **Security & Authentication**
- Password-protected dashboard
- Session management with timeout
- Environment variable support for GitHub tokens
- Secure credential handling

### 8. **TODO Management**
- Add and manage TODO notes for projects and the application itself
- Secure storage in `.todos/` directory (excluded from git)
- Real-time note editing through intuitive modal interface
- Organize development tasks and notes per project

### 9. **Git Status Indicators**
- Visual indicators showing pending git changes
- Pulsing yellow dot on GitHub icons when uncommitted changes exist
- Quick identification of projects needing commits
- Helps maintain clean repository status

## Supported Technologies

### Backend Frameworks
- **Python**: Django, FastAPI, Flask, Streamlit
- **PHP**: Laravel, Symfony, PHP Built-in Server
- **Node.js**: Express.js, NestJS
- **Java**: Spring Boot, Apache Tomcat, Jetty
- **Go**: Go HTTP servers
- **Ruby**: Ruby on Rails
- **Bash**: Custom shell scripts

### Frontend Frameworks
- **React**: CRA, Vite, Next.js
- **Vue.js**: Vue 2/3, Nuxt
- **Angular**: Angular CLI
- **Custom**: Any HTTP server on a port

### Web Servers
- **Nginx**
- **Apache** (httpd)
- **PHP Built-in Server**

## System Requirements

- **Server**: Linux/Unix-based system (Ubuntu, CentOS, Debian, etc.)
- **PHP**: >= 7.4
- **Web Server**: Nginx or Apache with PHP support
- **Tools**: Git, Node.js (optional), Python 3 (optional), Java (optional)
- **Permissions**: Proper user permissions for process management

## Quick Start

```bash
# 1. Copy the application to your deployment directory
cp -r deployment-hub /var/www/your-domain/

# 2. Configure permissions
chmod 755 /var/www/your-domain/deployment-hub
chmod 700 /var/www/your-domain/deployment-hub/.todos

# 3. Create config.php with your settings
echo "<?php define('PASSWORD_HASH', password_hash('your_password', PASSWORD_BCRYPT)); ?>" > config.php

# 4. Access the application
# Navigate to http://your-domain.com/deployment-hub/
```

## Detailed Installation & Setup

### 1. **Clone or Deploy the Application**

```bash
cd /path/to/your/deployment/directory
```

### 2. **Configure the Environment**

Create a `config.php` file with your settings:

```php
<?php
define('SITE_NAME', 'Your Application Hub');
define('PASSWORD_HASH', password_hash('your_password', PASSWORD_BCRYPT));
define('SESSION_TIMEOUT', 1800); // 30 minutes
?>
```

### 3. **Set Proper Permissions**

```bash
chmod 755 /path/to/your/deployment/directory
chmod 755 /path/to/your/deployment/directory/*
chmod 700 /path/to/your/deployment/directory/.todos
```

### 4. **Create Project Configuration**

For each application, create a `.project-info.json` file in the project root:

```json
{
    "name": "my-app",
    "title": "My Application",
    "description": "Description of my application",
    "image": null,
    "type": "fullstack",
    "technology": "Flask + React",
    "port": 5000,
    "portsList": [5000, 3000],
    "startScript": "services.sh restart",
    "startLabel": "Start Application",
    "scriptType": "bash",
    "stopCommand": "bash services.sh stop",
    "scripts": [
        {
            "name": "Backend API",
            "script": "start-backend.sh",
            "type": "bash",
            "port": 5000
        },
        {
            "name": "Frontend Server",
            "script": "start-frontend.sh",
            "type": "bash",
            "port": 3000
        }
    ]
}
```

### 5. **Configure GitHub Integration (Optional)**

Create `.github-config.json` for git operations:

```json
{
    "username": "your-github-username",
    "token": "your-github-personal-access-token"
}
```

### 6. **Access the Dashboard**

Navigate to: `http://your-domain.com/path/to/application`

Login with your configured password.

## Usage Guide

### Starting an Application

1. Navigate to the dashboard
2. Find your application card
3. Click the "Start" or "Start Flask+RhinoJS" button
4. Wait for the status indicator to turn green
5. Application logs will appear in real-time

### Viewing Logs

1. Click the "Logs" button on the application card
2. View the latest output from the application
3. Use the logs to debug any issues

### Managing Ports

1. Click the "Ports" button in the header
2. View all active ports and their associated applications
3. Click "Kill" to terminate a process running on a specific port
4. Port status updates in real-time

### Stopping Applications

1. Click the "Stop" button on a running application
2. The application will gracefully shut down
3. All associated processes will be terminated

### Git Operations

1. Projects with Git repositories display a GitHub link with a pulsing indicator when changes are pending
2. Click to open the repository in GitHub
3. Use the built-in git pull functionality for updates
4. The yellow indicator shows uncommitted changes in the project

### Managing TODOs

1. Click the **TODO** button in the header to manage application-level notes
2. Click the **TODO** icon on any project card to add notes specific to that project
3. Save your notes securely in the encrypted `.todos/` directory
4. Notes are not tracked in git for privacy and flexibility

## API Endpoints

All endpoints require authentication.

### Application Management

- **Start Application**: `GET /?action=run-script&project=project-name`
- **Stop Application**: `GET /?action=stop-service&project=project-name`
- **Stop All**: `GET /?action=stop-all`
- **Get Status**: `GET /?action=status`
- **View Logs**: `GET /?action=logs&project=project-name`

### Repository Operations

- **Git Pull**: `GET /?action=git-pull&project=project-name`
- **Git Info**: `GET /?action=git-info&project=project-name`

### Port Management

- **List Ports**: `GET /?action=list-ports`
- **Kill Port**: `GET /?action=kill-port&port=5000`

### TODO Management

- **Get TODO**: `GET /?action=get-todo&project=project-name`
- **Save TODO**: `POST /?action=save-todo` (with `project` and `content` parameters)

### Response Format

```json
{
    "success": true,
    "message": "Operation successful",
    "data": {}
}
```

## Advanced Configuration

### Multiple Scripts Per Application

Configure different startup scripts for frontend and backend:

```json
{
    "name": "fullstack-app",
    "scripts": [
        {
            "name": "Python Backend",
            "script": "backend/run.py",
            "type": "python",
            "port": 5000
        },
        {
            "name": "Node.js API",
            "script": "api/server.js",
            "type": "node",
            "port": 8000
        },
        {
            "name": "React Frontend",
            "script": "frontend/start.sh",
            "type": "bash",
            "port": 3000
        }
    ]
}
```

### Custom Environment Variables

Set environment variables before starting applications:

```bash
# In your startup script
export NODE_ENV=production
export DATABASE_URL=your-database-url
```

### Process Management

The system automatically:
- Tracks process IDs (PIDs) for each running application
- Manages multiple processes per project
- Handles graceful shutdowns
- Logs all operations with timestamps
- Recovers from crashed processes

## Monitoring & Logs

### Real-Time Monitoring

- Dashboard updates every 30 seconds
- Live port status indication
- Application status badges
- Running services counter
- Git status indicators showing pending changes
- Visual pulsing indicators for uncommitted work

### Log Files

Application logs are stored in:
- `.logs/` - Individual application logs
- `.pids/` - Process ID files
- `.services-state.json` - Current state of all services

### Log Viewing

- Access logs from the dashboard
- Filter logs by project
- Real-time log streaming
- Download logs for analysis

## Security Considerations

1. **Change Default Password**: Always update the default password
2. **Use HTTPS**: Deploy behind a reverse proxy with SSL/TLS
3. **Firewall**: Restrict access to the dashboard to trusted IPs
4. **Git Tokens**: Use GitHub Personal Access Tokens (PATs) with limited scopes
5. **Process Isolation**: Run applications under appropriate user accounts
6. **Regular Updates**: Keep frameworks and dependencies updated
7. **TODO Directory**: The `.todos/` directory has restricted permissions (700) and is excluded from git
8. **Sensitive Data**: Do not store passwords or API keys in TODO notes; use environment variables instead

## Project Structure

```
deployment-hub/
├── index.php                 # Main application
├── config.php               # Configuration file
├── assets/
│   └── style.css           # Dashboard styles
├── .gitignore              # Git ignore rules
├── .logs/                  # Application logs
├── .pids/                  # PID tracking files
├── .todos/                 # TODO notes (not tracked in git)
├── .services-state.json    # Service state
├── .github-config.json     # GitHub integration config
├── project-1/              # Your first project
├── project-2/              # Your second project
└── project-n/              # Additional projects
```

## Project Status Indicators

### Running (Green)
- All configured ports are active
- Application is responding
- Processes are healthy

### Stopped (Gray)
- Application is not running
- No active ports
- Ready to start

### Error (Red)
- Failed to start
- Check logs for details
- May need debugging

## Contributing

Contributions are welcome! Areas for improvement:

1. Support for additional frameworks
2. Enhanced monitoring and analytics
3. Docker integration
4. Kubernetes support
5. Performance optimization
6. UI/UX improvements
7. API enhancements

## Configuration Examples

### Flask Application

```json
{
    "name": "flask-app",
    "title": "Flask Application",
    "technology": "Flask + Python",
    "port": 5000,
    "startScript": "python3 app.py",
    "scriptType": "python"
}
```

### Django Application

```json
{
    "name": "django-app",
    "title": "Django Application",
    "technology": "Django + Python",
    "port": 8000,
    "startScript": "manage.py runserver 0.0.0.0:8000",
    "scriptType": "python"
}
```

### React + Node.js

```json
{
    "name": "mern-app",
    "title": "MERN Stack",
    "technology": "React + Node.js + MongoDB",
    "scripts": [
        {
            "name": "Backend",
            "script": "server.js",
            "type": "node",
            "port": 5000
        },
        {
            "name": "Frontend",
            "script": "npm start",
            "type": "bash",
            "port": 3000
        }
    ]
}
```

### Spring Boot

```json
{
    "name": "springboot-app",
    "title": "Spring Boot Application",
    "technology": "Spring Boot + Java",
    "port": 8080,
    "startScript": "mvn spring-boot:run",
    "scriptType": "bash"
}
```

## Troubleshooting

### Application Won't Start

1. Check `.logs/application-name.log` for error messages
2. Verify port availability: `lsof -i :PORT`
3. Check file permissions
4. Review `.project-info.json` configuration
5. Test startup script manually

### Port Already in Use

1. Click "Ports" button to see active processes
2. Click "Kill" to terminate the conflicting process
3. Try starting the application again

### Git Operations Failing

1. Verify GitHub token in `.github-config.json`
2. Check repository permissions
3. Ensure network connectivity
4. Verify SSH key configuration

### High Memory Usage

1. Check for zombie processes
2. Review application logs for memory leaks
3. Restart affected applications
4. Monitor system resources

## Support

For issues and questions:

1. Check application logs
2. Review this README
3. Consult framework documentation
4. Check system resources (disk, memory, CPU)

## License

This project is provided as-is for deployment and application management purposes.

## Learning Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [Django Documentation](https://docs.djangoproject.com/)
- [Flask Documentation](https://flask.palletsprojects.com/)
- [FastAPI Documentation](https://fastapi.tiangolo.com/)
- [Express.js Documentation](https://expressjs.com/)
- [Spring Boot Documentation](https://spring.io/projects/spring-boot)
- [React Documentation](https://react.dev/)
- [Vue.js Documentation](https://vuejs.org/)

---

**Last Updated**: January 2026

**Version**: 2.1

**Features**: Application Deployment, Project Management, Git Integration, TODO Notes, Real-time Monitoring

**License**: This project is provided as-is for deployment and application management purposes.
