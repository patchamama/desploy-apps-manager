// State
let _sudoButton = null;
let currentTodoProject = null;
let originalTodoContent = '';

// ─── Theme ───────────────────────────────────────────────────────────────────

function toggleTheme() {
    const html = document.getElementById('html-root');
    const isLight = html.classList.toggle('light');
    localStorage.setItem('theme', isLight ? 'light' : 'dark');
    updateThemeIcon(isLight);
}

function updateThemeIcon(isLight) {
    const dark = document.getElementById('themeIconDark');
    const light = document.getElementById('themeIconLight');
    if (dark) dark.style.display = isLight ? 'block' : 'none';
    if (light) light.style.display = isLight ? 'none' : 'block';
}

// ─── Services ────────────────────────────────────────────────────────────────

function runScript(button) {
    const project = button.dataset.project;
    const requiresSudo = button.dataset.requiresSudo === 'true';

    if (requiresSudo) {
        _sudoButton = button;
        document.getElementById('sudoPasswordInput').value = '';
        document.getElementById('sudoError').style.display = 'none';
        document.getElementById('sudoModal').style.display = 'flex';
        setTimeout(() => document.getElementById('sudoPasswordInput').focus(), 100);
        return;
    }

    _doRunScript(button, project, null);
}

function confirmSudoStart() {
    const password = document.getElementById('sudoPasswordInput').value;
    if (!password) return;
    closeSudoModal();
    _doRunScript(_sudoButton, _sudoButton.dataset.project, password);
}

function closeSudoModal() {
    document.getElementById('sudoModal').style.display = 'none';
    document.getElementById('sudoPasswordInput').value = '';
}

function _doRunScript(button, project, sudoPassword) {
    showModal(i18n.starting_service, true);

    button.disabled = true;
    button.classList.add('loading');

    const fetchOptions = sudoPassword
        ? { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ sudoPassword }) }
        : {};

    fetch(`?action=run-script&project=${encodeURIComponent(project)}`, fetchOptions)
        .then(response => response.json())
        .then(data => {
            if (data.needsSudoPassword) {
                closeModal();
                _sudoButton = button;
                document.getElementById('sudoError').style.display = 'block';
                document.getElementById('sudoModal').style.display = 'flex';
                setTimeout(() => document.getElementById('sudoPasswordInput').focus(), 100);
                return;
            }
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

function runAction(button) {
    const project = button.dataset.project;
    const actionId = button.dataset.actionId;
    const label = button.dataset.actionLabel || 'Ejecutando...';

    showModal(label + '...', true);
    button.disabled = true;
    button.classList.add('loading');

    fetch(`?action=run-action&project=${encodeURIComponent(project)}&action-id=${encodeURIComponent(actionId)}`)
        .then(response => response.json())
        .then(data => {
            updateModal(
                data.success ? label : i18n.error,
                data.message,
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
            updateModal(i18n.services_stopped, data.message, data.success);
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

// ─── Result Modal ─────────────────────────────────────────────────────────────

function showModal(title, showLoader) {
    document.getElementById('resultModal').classList.add('active');
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalLoader').style.display = showLoader ? 'block' : 'none';
    document.getElementById('modalResult').textContent = '';
    document.getElementById('modalResult').className = 'result-output';
    document.getElementById('modalFooter').style.display = 'none';
}

function updateModal(title, content, success) {
    document.getElementById('modalLoader').style.display = 'none';
    document.getElementById('modalTitle').textContent = title;
    const result = document.getElementById('modalResult');
    result.textContent = content;
    result.classList.add(success ? 'success' : 'error');
    document.getElementById('modalFooter').style.display = 'flex';
}

function closeModal() {
    document.getElementById('resultModal').classList.remove('active');
}

function closeModalAndReload() {
    closeModal();
    location.reload();
}

// ─── Ports Modal ──────────────────────────────────────────────────────────────

function showOpenPorts() {
    document.getElementById('portsModal').classList.add('active');
    document.getElementById('portsLoader').style.display = 'block';
    document.getElementById('portsList').innerHTML = '';
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

            let html = '<table class="ports-table"><thead><tr>'
                + '<th>' + i18n.port + '</th>'
                + '<th>' + i18n.project + '</th>'
                + '<th>' + i18n.application + '</th>'
                + '<th>' + i18n.type + '</th>'
                + '<th>PID</th>'
                + '<th>' + i18n.action + '</th>'
                + '</tr></thead><tbody>';

            data.ports.forEach(port => {
                let projectInfo = '-';
                if (port.projects && port.projects.length > 0) {
                    projectInfo = port.projects.map(p => {
                        const tech = p.technology ? `<br><small class="text-muted">${p.technology}</small>` : '';
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
    document.getElementById('portsList').innerHTML = '';
    document.getElementById('portsLoader').style.display = 'block';
    loadPorts();
}

function killPort(port) {
    if (!confirm(i18n.kill_port_confirm + ' ' + port + '?')) return;

    document.getElementById('portsLoader').style.display = 'block';

    fetch(`?action=kill-port&port=${port}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(refreshPorts, 1000);
            } else {
                showNotification(data.message, 'error');
                document.getElementById('portsLoader').style.display = 'none';
            }
        })
        .catch(error => {
            showNotification('Error: ' + error.message, 'error');
            document.getElementById('portsLoader').style.display = 'none';
        });
}

function closePortsModal() {
    document.getElementById('portsModal').classList.remove('active');
}

// ─── Notifications ────────────────────────────────────────────────────────────

function showNotification(message, type) {
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
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ─── TODO Modal ───────────────────────────────────────────────────────────────

function openTodoModal(button) {
    const project = button.dataset.project;
    currentTodoProject = project;

    const modal = document.getElementById('todoModal');
    const textarea = document.getElementById('todoTextarea');

    document.getElementById('todoProjectName').textContent = project;
    textarea.value = '';
    textarea.disabled = true;
    originalTodoContent = '';

    modal.classList.add('active');

    fetch(`?action=get-todo&project=${encodeURIComponent(project)}`)
        .then(response => response.json())
        .then(data => {
            const content = (data && data.success && data.todo) ? data.todo : '';
            textarea.value = content;
            originalTodoContent = content;
            textarea.disabled = false;
            textarea.focus();
        })
        .catch(() => {
            textarea.value = '';
            originalTodoContent = '';
            textarea.disabled = false;
        });
}

function hasUnsavedChanges() {
    const textarea = document.getElementById('todoTextarea');
    if (!textarea) return false;
    return String(textarea.value) !== String(originalTodoContent);
}

function closeTodoModal() {
    if (hasUnsavedChanges() && !confirm(i18n.unsaved_changes)) return;

    const modal = document.getElementById('todoModal');
    const maximizeBtn = document.querySelector('.modal-maximize');
    modal.classList.remove('active', 'maximized');
    if (maximizeBtn) maximizeBtn.textContent = '+';
    currentTodoProject = null;
    originalTodoContent = '';
}

function toggleMaximizeTodoModal() {
    const modal = document.getElementById('todoModal');
    const maximizeBtn = document.querySelector('.modal-maximize');
    if (!modal) return;
    modal.classList.toggle('maximized');
    if (maximizeBtn) maximizeBtn.textContent = modal.classList.contains('maximized') ? '−' : '+';
}

function updateTodoBadge(project, content) {
    const count = (content.match(/- \[ \]/g) || []).length;
    document.querySelectorAll(`[data-project="${CSS.escape(project)}"]`).forEach(btn => {
        let badge = btn.querySelector('.todo-pending-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'todo-pending-badge';
                btn.appendChild(badge);
            }
            badge.textContent = count;
        } else {
            if (badge) badge.remove();
        }
    });
}

function saveTodo() {
    if (!currentTodoProject) return;

    const textarea = document.getElementById('todoTextarea');
    const content = textarea.value;

    const formData = new FormData();
    formData.append('project', currentTodoProject);
    formData.append('content', content);

    fetch('?action=save-todo', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(i18n.todo_saved, 'success');
                originalTodoContent = content;
                updateTodoBadge(currentTodoProject, content);
            } else {
                showNotification(data.message || i18n.error, 'error');
            }
        })
        .catch(error => {
            showNotification(i18n.error + ': ' + error.message, 'error');
        });
}

// ─── Initialization ───────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    // Init theme toggle icon state
    const html = document.getElementById('html-root');
    if (html) updateThemeIcon(html.classList.contains('light'));

    // Poll running services count every 30 seconds
    const badge = document.getElementById('runningCount');
    if (badge) {
        setInterval(() => {
            fetch('?action=status')
                .then(r => r.json())
                .then(data => {
                    const count = Object.keys(data.services).length;
                    badge.querySelector('.count').textContent = count;
                    badge.classList.toggle('active', count > 0);
                })
                .catch(() => {});
        }, 30000);
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const todoModal = document.getElementById('todoModal');
            if (todoModal && todoModal.classList.contains('active')) {
                closeTodoModal();
            }
        }
    });
});
