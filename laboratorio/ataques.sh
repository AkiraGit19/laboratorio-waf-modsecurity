#!/usr/bin/env bash
# Lanza los mismos ataques contra el sitio SIN WAF (:8081) y CON WAF (:8080).
# Uso (desde Kali):  ./ataques.sh <IP_DEL_SERVIDOR>
IP=${1:-127.0.0.1}
G='\033[32m'; R='\033[31m'; N='\033[0m'

prueba() {  # nombre, metodo, ruta, parametro, carga, texto que prueba que el ataque funcionó
  local nombre=$1 metodo=$2 ruta=$3 param=$4 carga=$5 exito=$6
  local ok="ATAQUE EXITOSO" c=$R; [[ $nombre == Tráfico* ]] && ok="OK, responde normal" c=$G
  echo -e "\n== $nombre ==   carga: $carga"
  for puerto in 8081 8080; do
    [ $puerto = 8081 ] && etiqueta="SIN WAF" || etiqueta="CON WAF"
    if [ "$metodo" = POST ]; then
      resp=$(curl -s -w '\n%{http_code}' --data-urlencode "$param=$carga" --data-urlencode "clave=x" "http://$IP:$puerto$ruta")
    else
      resp=$(curl -s -w '\n%{http_code}' -G --data-urlencode "$param=$carga" "http://$IP:$puerto$ruta")
    fi
    codigo=$(tail -1 <<<"$resp")
    if [ "$codigo" = 403 ]; then echo -e "  [$etiqueta] ${G}403 BLOQUEADO por el WAF${N}"
    elif grep -q -- "$exito" <<<"$resp"; then echo -e "  [$etiqueta] ${c}$codigo $ok${N} -> $(grep -m1 -o -- "$exito.\{0,40\}" <<<"$resp")"
    else echo "  [$etiqueta] $codigo (sin efecto)"; fi
  done
}

prueba "Tráfico normal (búsqueda)"  GET  /buscar.php q       "Laptop"                                        "2999"
prueba "SQLi: saltar el login"      POST /login.php  usuario "' OR 1=1 -- "                                 "Bienvenido <b>admin"
prueba "SQLi: evasión (solo PL2)"   POST /login.php  usuario "admin' -- "                                   "Bienvenido <b>admin"
prueba "SQLi: robar contraseñas"    GET  /buscar.php q       "x' UNION SELECT usuario,clave FROM usuarios-- " "Adm1n#2026"
prueba "XSS reflejado"              GET  /buscar.php q       "<script>alert('XSS')</script>"                  "<script>alert"
prueba "Inyección de comandos"      GET  /ping.php   ip      "127.0.0.1; cat /etc/passwd"                    "root:x:0"
prueba "Path Traversal / LFI"       GET  /ver.php    archivo "../../../../etc/passwd"                        "root:x:0"
