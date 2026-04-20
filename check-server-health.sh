#!/usr/bin/env bash

# Archivo de salida
REPORT_FILE="report-systems.txt"

# Redirigir toda la salida a pantalla y al archivo
# (Usamos un bloque para capturar todo el script)
{
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
        echo -e "Analizando recursos críticos (buscando fallos)..."
        
        # Cabecera de la tabla
        printf "${YELLOW}%-15s %-10s %-10s %-10s %-10s %-8s${NC}\n" "RECURSO" "HELD" "MAXHELD" "LIMIT" "FAIL" "% USO"
        
        # Procesar datos con AWK
        sudo cat /proc/user_beancounters | grep -E 'kmemsize|dcachesize|physpages|privvmpages|numfile|numproc' | awk '
        function hr(val, is_page) {
            if (val == "9223372036854775807" || val == "0") return (val == "0" ? "0" : "Unlim")
            if (is_page) val = val * 4096
            if (val < 1024) return val
            if (val < 1048576) return sprintf("%.1fK", val/1024)
            if (val < 1073741824) return sprintf("%.1fM", val/1048576)
            return sprintf("%.1fG", val/1073741824)
        }
        {
            if ($1 ~ /:$/) { res=$2; hld=$3; max=$4; bar=$5; lim=$6; fail=$7 }
            else { res=$1; hld=$2; max=$3; bar=$4; lim=$5; fail=$6 }
            
            is_p = (res ~ /pages/)
            is_b = (res ~ /size|buf|pages/)
            
            # Divisor para el porcentaje
            div = (lim == "9223372036854775807") ? bar : lim
            pct = 0
            if (div > 0 && div != "9223372036854775807") {
                pct = (hld / div) * 100
            }
            
            # Formatear valores
            f_hld = is_b ? hr(hld, is_p) : hld
            f_max = is_b ? hr(max, is_p) : max
            f_lim = is_b ? hr(lim, is_p) : lim
            
            color="\033[0m"
            if (pct > 90 || (fail != "" && fail > 0)) color="\033[0;31m"
            else if (pct > 70) color="\033[1;33m"
            else if (pct > 0) color="\033[0;32m"

            printf "%s%-15s %-10s %-10s %-10s %-10s %-8s\033[0m\n", color, res, f_hld, f_max, f_lim, fail, (pct > 0 ? sprintf("%.2f%%", pct) : "---")
        }'
        
        fails=$(sudo cat /proc/user_beancounters | awk '{f=($1 ~ /:$/ ? $7 : $6); sum+=f} END {print sum}')
        
        if [[ "$fails" =~ ^[0-9]+$ ]] && [ "$fails" -gt 0 ]; then
            echo -e "${RED}⚠️ ¡ALERTA! Se detectaron $fails fallos en los límites del kernel.${NC}"
        else
            echo -e "${GREEN}✅ No se detectan fallos (failcnt = 0) en el kernel.${NC}"
        fi
    else
        echo -e "${RED}❌ No se pudo leer /proc/user_beancounters.${NC}"
    fi

    echo ""

    # 2. Censo total de archivos
    echo -e "${YELLOW}[2] Censo de archivos en el proyecto actual...${NC}"
    total_files=$(find . -type f 2>/dev/null | wc -l)
    echo -e "Total: $total_files archivos."

    echo ""
    echo -e "${BLUE}======================================================${NC}"
    echo "Reporte generado en: $REPORT_FILE"

} | tee "$REPORT_FILE"

# Instrucciones para el CRON (Solo se muestran si se corre manualmente)
if [ -t 0 ]; then
    echo ""
    echo -e "${YELLOW}>>> CÓMO AGREGAR ESTO AL CRON (Ejecutar cada 10 min):${NC}"
    echo "1. Ejecutá: crontab -e"
    echo "2. Pegá esta línea al final (usando la ruta absoluta):"
    echo "*/10 * * * * cd $(pwd) && bash check-server-health.sh > /dev/null 2>&1"
    echo "------------------------------------------------------"
fi
