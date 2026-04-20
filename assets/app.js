// State
let _sudoButton = null;
let currentTodoProject = null;
let originalTodoContent = '';
let _serverTechs = [];

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

// ─── Server Stats ─────────────────────────────────────────────────────────────

function _fmtBytes(b) {
    if (!b) return '0 B';
    const u = ['B','KB','MB','GB','TB'], i = Math.min(Math.floor(Math.log(b) / Math.log(1024)), 4);
    return (b / Math.pow(1024, i)).toFixed(1) + ' ' + u[i];
}

function _fmtCount(n) {
    if (n >= 1e9) return (n/1e9).toFixed(1)+'B';
    if (n >= 1e6) return (n/1e6).toFixed(1)+'M';
    if (n >= 1e3) return (n/1e3).toFixed(1)+'K';
    return n;
}

function _setBar(fillId, statText, used, total) {
    const pct = total > 0 ? Math.round((used / total) * 100) : 0;
    const fill = document.getElementById(fillId);
    if (fill) {
        fill.style.width = pct + '%';
        fill.className = 'metric-bar-fill' + (pct > 90 ? ' danger' : pct > 70 ? ' warn' : '');
    }
    return pct;
}

function loadServerStats() {
    fetch('?action=server-stats')
        .then(r => r.json())
        .then(d => {
            if (!d.success) return;

            const el = id => document.getElementById(id);
            const _applyStatColor = (id, pct) => {
                const element = el(id);
                if (!element) return;
                element.style.color = pct > 90 ? 'var(--error-color)' : (pct > 70 ? '#f59e0b' : 'var(--success-color)');
                element.style.fontWeight = 'bold';
            };

            // Metrics
            const ramPct = _setBar('ramFill', '', d.ram.used, d.ram.total);
            if (el('ramStat')) {
                el('ramStat').textContent = _fmtBytes(d.ram.used) + ' / ' + _fmtBytes(d.ram.total) + ' (' + ramPct + '%)';
                _applyStatColor('ramStat', ramPct);
            }

            const diskPct = _setBar('diskFill', '', d.disk.used, d.disk.total);
            if (el('diskStat')) {
                el('diskStat').textContent = _fmtBytes(d.disk.used) + ' / ' + _fmtBytes(d.disk.total) + ' (' + diskPct + '%)';
                _applyStatColor('diskStat', diskPct);
            }

            const inPct = _setBar('inodesFill', '', d.inodes.used, d.inodes.total);
            if (el('inodesStat')) {
                el('inodesStat').textContent = _fmtCount(d.inodes.used) + ' / ' + _fmtCount(d.inodes.total) + ' (' + inPct + '%)';
                _applyStatColor('inodesStat', inPct);
            }

            // UBC Section
            const ubcSection = el('ubcSection');
            const ubcTable = el('ubcTable');
            if (ubcSection && ubcTable && d.ubc && d.ubc.resources && d.ubc.resources.length > 0) {
                ubcSection.style.display = 'block';
                const dateHint = el('ubcDateHint');
                if (dateHint) dateHint.textContent = d.ubc.date || 'auto-refresh 10m';
                
                const ubcTitle = el('ubcTitle');
                if (ubcTitle) {
                    ubcTitle.style.color = d.ubc.has_fail ? 'var(--error-color)' : 'var(--text-secondary)';
                    if (d.ubc.has_fail) ubcTitle.innerHTML = 'Kernel Resources (UBC) <span style="font-size:0.7em; background:var(--error-color); color:white; padding:2px 6px; border-radius:4px; margin-left:8px;">FAILURES DETECTED</span>';
                    else ubcTitle.textContent = 'Kernel Resources (UBC)';
                }

                let html = '<table class="process-table"><thead><tr><th>Resource</th><th>Held</th><th>Max</th><th>Limit</th><th>Fail</th><th>%</th></tr></thead><tbody>';
                d.ubc.resources.forEach(r => {
                    const failClass = r.fail > 0 ? 'color: var(--error-color); font-weight: bold;' : '';
                    // Semaphore colors: Red (>90), Yellow (>70), Green (Good)
                    const pctColor = r.pct > 90 ? 'var(--error-color)' : (r.pct > 70 ? '#f59e0b' : 'var(--success-color)');
                    const pctStyle = `color: ${pctColor}; font-weight: bold;`;
                    
                    html += `<tr>
                        <td style="${failClass}">${r.name}</td>
                        <td>${r.held}</td>
                        <td>${r.maxheld}</td>
                        <td>${r.limit}</td>
                        <td style="${failClass}">${r.fail}</td>
                        <td style="${pctStyle}">${r.pct}%</td>
                    </tr>`;
                });
                html += '</tbody></table>';
                ubcTable.innerHTML = html;
            } else if (ubcSection) {
                ubcSection.style.display = 'none';
            }

            // Tech chips
            _serverTechs = d.techs || [];
            const techsEl = el('serverTechs');
            if (techsEl) {
                techsEl.innerHTML = _serverTechs.length
                    ? _serverTechs.map(t => _renderTechChip(t, null)).join('')
                    : '<span class="server-loading">—</span>';
                _fetchLatestVersions();
            }

            // Docker
            const dockerEl = el('serverDocker');
            if (dockerEl) {
                dockerEl.innerHTML = d.docker && d.docker.length
                    ? '<div class="server-docker-title">Docker Containers</div>' +
                      d.docker.map(c => `<div class="docker-container"><span class="docker-dot"></span><span class="docker-name">${c.name}</span><span class="docker-image">${c.image}</span><span class="docker-status">${c.status}</span></div>`).join('')
                    : '';
            }

            // Processes
            const procEl = el('processTable');
            if (procEl) {
                procEl.innerHTML = d.processes && d.processes.length
                    ? `<table class="process-table"><thead><tr><th>PID</th><th>User</th><th>%CPU</th><th>%MEM</th><th>Command</th><th></th></tr></thead><tbody>` +
                      d.processes.map(p => `<tr><td>${p.pid}</td><td>${p.user}</td><td>${p.cpu}</td><td>${p.mem}</td><td class="process-cmd">${p.command}</td><td><button class="btn-kill-pid" onclick="killProcess(${p.pid})">kill</button></td></tr>`).join('') +
                      '</tbody></table>'
                    : '<div class="server-loading">No processes</div>';
            }
        })
        .catch(() => {});
}

function refreshServerStats() {
    ['serverTechs','processTable'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = '<div class="server-loading">Loading…</div>';
    });
    loadServerStats();
}

function _renderTechChip(t, latestMap) {
    const latest = latestMap ? (latestMap[t.name] || null) : null;
    const isOutdated = latest && t.version && t.version !== '?' &&
        (function() {
            try { return latest.localeCompare(t.version, undefined, {numeric:true, sensitivity:'base'}) > 0; }
            catch(e) { return latest !== t.version; }
        })();
    const nameEsc = (t.name || '').replace(/'/g, "\\'");
    const verEsc  = (t.version || '').replace(/'/g, "\\'");
    const latEsc  = (latest || '').replace(/'/g, "\\'");
    const cmdEsc  = (t.upgrade_cmd || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    const badge = isOutdated
        ? `<button class="tech-outdated-btn" onclick="openTechUpgradeModal('${nameEsc}','${verEsc}','${latEsc}','${cmdEsc}')" title="Actualizar a ${latest}">✱</button>`
        : '';
    return `<div class="tech-chip${isOutdated ? ' tech-outdated' : ''}"><strong>${t.name}</strong>&nbsp;<span>${t.version}</span>${badge}</div>`;
}

function _fetchLatestVersions() {
    fetch('?action=latest-versions')
        .then(r => r.json())
        .then(d => {
            if (!d.success || !d.versions) return;
            const techsEl = document.getElementById('serverTechs');
            if (!techsEl || !_serverTechs.length) return;
            techsEl.innerHTML = _serverTechs.map(t => _renderTechChip(t, d.versions)).join('');
        })
        .catch(() => {});
}

function openTechUpgradeModal(name, installed, latest, upgradeCmd) {
    document.getElementById('techUpgradeName').textContent = name;
    document.getElementById('techCurrentVersion').textContent = installed;
    document.getElementById('techLatestVersion').textContent = latest;
    document.getElementById('techUpgradeCommand').textContent = upgradeCmd;
    document.getElementById('techUpgradeModal').style.display = 'flex';
}

function closeTechUpgradeModal() {
    document.getElementById('techUpgradeModal').style.display = 'none';
}

function copyUpgradeCommand() {
    const cmd = document.getElementById('techUpgradeCommand').textContent;
    navigator.clipboard.writeText(cmd).then(() => showNotification('Comando copiado', 'success')).catch(() => {});
}

function killProcess(pid) {
    if (!confirm('Kill process ' + pid + '?')) return;
    fetch('?action=kill-pid&pid=' + pid)
        .then(r => r.json())
        .then(d => {
            showNotification(d.message || 'Done', d.success ? 'success' : 'error');
            if (d.success) setTimeout(refreshServerStats, 1000);
        })
        .catch(() => showNotification('Error', 'error'));
}

function autodetectProjects(btn) {
    if (btn) { btn.disabled = true; btn.textContent = 'Scanning…'; }
    fetch('?action=autodetect-projects')
        .then(r => r.json())
        .then(d => {
            if (d.detected && d.detected.length > 0) {
                showNotification('Detected ' + d.count + ' project(s): ' + d.detected.join(', '), 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showNotification('No new projects found', 'success');
            }
        })
        .catch(() => showNotification('Scan error', 'error'))
        .finally(() => { if (btn) { btn.disabled = false; btn.textContent = '⊕ Scan'; } });
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

    // Server panel auto-load & refresh
    if (document.getElementById('serverPanel')) {
        loadServerStats();
        setInterval(loadServerStats, 15000);
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
