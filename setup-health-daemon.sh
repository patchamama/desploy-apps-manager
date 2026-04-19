#!/usr/bin/env bash

# Verificar que se ejecute como sudo
if [ "$EUID" -ne 0 ]; then 
  echo -e "\033[0;31mError: Por favor, ejecutá este script con sudo.\033[0m"
  exit 1
fi

# Configuración de rutas
WORKING_DIR=$(pwd)
SCRIPT_PATH="$WORKING_DIR/check-server-health.sh"
SERVICE_NAME="server-health"

# 1. Verificar que el script de monitoreo existe
if [ ! -f "$SCRIPT_PATH" ]; then
    echo -e "\033[0;31mError: No encuentro $SCRIPT_PATH\033[0m"
    echo "Asegurate de estar en la carpeta correcta."
    exit 1
fi

echo -e "\033[0;34m[0/4] Limpiando instalaciones previas...\033[0m"
systemctl stop $SERVICE_NAME.timer 2>/dev/null
systemctl stop $SERVICE_NAME.service 2>/dev/null
systemctl disable $SERVICE_NAME.timer 2>/dev/null
rm -f /etc/systemd/system/$SERVICE_NAME.service /etc/systemd/system/$SERVICE_NAME.timer
systemctl daemon-reload

echo -e "\033[0;34m[1/4] Creando archivo de servicio...\033[0m"
cat <<EOF > /etc/systemd/system/$SERVICE_NAME.service
[Unit]
Description=Monitor de Salud del Servidor (UBC & Archivos)
After=network.target

[Service]
Type=oneshot
User=root
WorkingDirectory=$WORKING_DIR
ExecStart=/usr/bin/bash $SCRIPT_PATH

[Install]
WantedBy=multi-user.target
EOF

echo -e "\033[0;34m[2/4] Creando archivo de timer (cada 10 min)...\033[0m"
cat <<EOF > /etc/systemd/system/$SERVICE_NAME.timer
[Unit]
Description=Ejecutar Monitor de Salud cada 10 minutos

[Timer]
OnBootSec=1min
OnUnitActiveSec=10min
Unit=$SERVICE_NAME.service

[Install]
WantedBy=timers.target
EOF

echo -e "\033[0;34m[3/4] Recargando systemd y activando daemon...\033[0m"
systemctl daemon-reload
systemctl enable $SERVICE_NAME.timer
systemctl start $SERVICE_NAME.timer

echo -e "\033[0;34m[4/4] Verificando estado...\033[0m"
if systemctl is-active --quiet $SERVICE_NAME.timer; then
    echo -e "\033[0;32m✅ ¡Éxito! El daemon de monitoreo está activo y corriendo.\033[0m"
    echo "El reporte se actualizará en $WORKING_DIR/report-systems.txt cada 10 min."
    echo ""
    echo "Comandos útiles:"
    echo " - Ver próxima ejecución: systemctl list-timers $SERVICE_NAME.timer"
    echo " - Ver logs: journalctl -u $SERVICE_NAME.service"
    echo " - Forzar ejecución ahora: systemctl start $SERVICE_NAME.service"
else
    echo -e "\033[0;31m❌ Hubo un problema al activar el timer.\033[0m"
fi
