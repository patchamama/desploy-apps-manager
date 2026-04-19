#!/usr/bin/env bash

# Color codes for better readability
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== MONITOR DE SALUD DEL SERVIDOR (UBC & ARCHIVOS) ===${NC}"
echo -e "Fecha: $(date)"
echo ""

# 1. Intentar leer User Beancounters (requiere sudo)
echo -e "${YELLOW}[1] Verificando límites de Kernel (UBC)...${NC}"
if sudo [ -f /proc/user_beancounters ]; then
    echo -e "Analizando dcachesize y kmemsize (buscando fallos)..."
    # Mostrar cabecera y las líneas de interés
    sudo cat /proc/user_beancounters | grep -E 'resource|dcachesize|kmemsize' | awk '{printf "%-15s %-10s %-10s %-10s %-10s %-10s\n", $1, $2, $3, $4, $5, $6}'
    
    # Verificar si hubo fallos recientemente (columna failcnt, que es la última NF)
    fails=$(sudo cat /proc/user_beancounters | grep -E 'dcachesize|kmemsize' | awk '{sum+=$NF} END {print sum}')
    
    if [[ "$fails" =~ ^[0-9]+$ ]] && [ "$fails" -gt 0 ]; then
        echo -e "${RED}⚠️ ¡ALERTA! Se detectaron $fails fallos en los límites del kernel.${NC}"
        echo -e "Esto significa que el servidor bloqueó acciones por falta de memoria de gestión.${NC}"
    else
        echo -e "${GREEN}✅ No se detectan fallos (failcnt = 0) en el kernel.${NC}"
    fi
else
    echo -e "${RED}❌ No se pudo leer /proc/user_beancounters (incluso con sudo).${NC}"
    echo "Es posible que el entorno de virtualización oculte estos datos por completo."
fi

echo ""

# 2. Censo total de archivos
echo -e "${YELLOW}[2] Censo de archivos en el proyecto actual...${NC}"
total_files=$(find . -type f 2>/dev/null | wc -l)

if [ "$total_files" -gt 50000 ]; then
    echo -e "${RED}⚠️ PELIGRO: Tenés $total_files archivos.${NC}"
    echo "Estás por encima del límite recomendado (50k). El servidor podría volverse inestable."
elif [ "$total_files" -gt 30000 ]; then
    echo -e "${YELLOW}⚠️ ADVERTENCIA: Tenés $total_files archivos.${NC}"
    echo "Estás en la zona amarilla. Considerá limpiar carpetas node_modules o temporales."
else
    echo -e "${GREEN}✅ Tenés $total_files archivos en total. Todo bajo control.${NC}"
fi

echo ""

# 3. Top 10 directorios con más archivos
echo -e "${YELLOW}[3] Top 10 carpetas con más archivos (intensivas en dcachesize):${NC}"
echo "Calculando (esto puede tardar unos segundos)..."
find . -maxdepth 2 -type d -not -path '*/.*' -not -path '.' 2>/dev/null | while read dir; do
    count=$(find "$dir" -type f 2>/dev/null | wc -l)
    echo -e "$count\t$dir"
done | sort -rn | head -n 10 | awk '{printf "%-10s %-s\n", $1, $2}'

echo ""
echo -e "${BLUE}======================================================${NC}"
echo "Consejo: Si ves una carpeta con > 10,000 archivos, revisá si es necesaria."
