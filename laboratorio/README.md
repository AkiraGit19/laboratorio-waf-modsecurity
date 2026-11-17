# Laboratorio WAF — ModSecurity + OWASP CRS

## Arquitectura

```
        ATACANTE                              SERVIDOR (Mac con Docker)
  ┌──────────────────┐               ┌──────────────────────────────────────────┐
  │  Kali Linux (VM) │   :8080       │  ┌───────────────┐      ┌──────────────┐ │
  │  172.16.140.132  │ ─────────────▶│  │ WAF           │─────▶│ TiendaDemo   │ │
  │                  │   CON WAF     │  │ ModSecurity + │      │ PHP + SQLite │ │
  │  Firefox, curl,  │               │  │ OWASP CRS     │      │ (vulnerable) │ │
  │  sqlmap, nikto   │   :8081       │  └──────┬────────┘      └──────▲───────┘ │
  │                  │ ──────────────┼─────────┼──── SIN WAF ─────────┘         │
  └──────────────────┘               │         │ audit.log                       │
                                     │  ┌──────▼────────┐                        │
                                     │  │ Panel :9090   │  monitoreo en vivo     │
                                     │  └───────────────┘                        │
                                     └──────────────────────────────────────────┘
```

- **:8081** — la tienda directa, sin protección (solo para comparar el "antes").
- **:8080** — la misma tienda detrás del WAF (el "después").
- **:9090** — panel que lee el log del WAF y muestra cada ataque, regla y acción.

La tienda tiene 4 vulnerabilidades reales a propósito:

| Página | Vulnerabilidad | Carga de ejemplo |
|---|---|---|
| `/login.php` | SQL Injection (saltar login) | usuario: `' OR 1=1 -- ` |
| `/buscar.php?q=` | SQL Injection (robar claves) | `x' UNION SELECT usuario,clave FROM usuarios-- ` |
| `/buscar.php?q=` | XSS reflejado | `<script>alert('XSS')</script>` |
| `/ping.php?ip=` | Inyección de comandos | `127.0.0.1; cat /etc/passwd` |
| `/ver.php?archivo=` | Path Traversal / LFI | `../../../../etc/passwd` |

## Encender / apagar (en el Mac)

```bash
cd ~/Desktop/"CIBERSEGURIDAD PROYECTO"/laboratorio
docker compose up -d --build      # encender
: > logs/audit.log                # limpiar el panel antes de la demo
docker compose down               # apagar
```

Cambiar el WAF (se recrea en 5 s):

```bash
WAF_MODE=DetectionOnly docker compose up -d waf   # solo detecta y registra, no bloquea
PARANOIA=2 docker compose up -d waf               # reglas más estrictas
docker compose up -d waf                          # volver a On + paranoia 1
```

## Guion de demostración (~8 min)

Desde Kali, el servidor es `172.16.140.1` (IP del Mac en la red de VMware).
Abrir el panel `http://172.16.140.1:9090` en una pestaña al costado.

1. **Tráfico normal.** En Firefox: `http://172.16.140.1:8080/buscar.php?q=Laptop` → funciona igual con WAF.
2. **Ataque sin WAF.** Login en `:8081` con usuario `' OR 1=1 -- ` → entra como admin.
   Se ve la consulta SQL alterada en pantalla.
3. **Mismo ataque con WAF.** Login en `:8080` → `403 Forbidden`. En el panel aparece
   "SQL Injection · BLOQUEADO 403" con las reglas 942xxx y el puntaje de anomalía.
4. **XSS.** `:8081/buscar.php?q=<script>alert('XSS')</script>` → sale la alerta. En `:8080` → 403.
5. **Todos los ataques de golpe.** `./ataques.sh 172.16.140.1` → tabla SIN WAF / CON WAF.
6. **Ataque automatizado con sqlmap.**
   ```bash
   sqlmap -u "http://172.16.140.1:8081/buscar.php?q=Laptop" --batch --dbms=SQLite -T usuarios --dump
   ```
   Sin WAF descarga la tabla de usuarios con contraseñas. Con `:8080` recibe cientos de 403
   y no logra nada; el panel muestra "Escáner automatizado" y "SQL Injection".
7. **El WAF no es infalible (ajuste).** Login en `:8080` con `admin' -- ` → pasa con paranoia 1.
   Correr `PARANOIA=2 docker compose up -d waf` y repetir → 403. La búsqueda normal sigue en 200.
   Mensaje: el WAF es una capa extra; la solución de fondo es corregir el código (consultas preparadas).

## Conceptos para explicar

- **ModSecurity** = el motor (lee cada petición HTTP). **OWASP CRS** = las reglas (qué es un ataque).
- **Puntaje de anomalía:** cada regla que coincide suma puntos (crítica = 5). Si el total ≥ 5, se bloquea (regla 949110).
- **Nivel de paranoia:** 1 = pocas reglas, casi sin falsos positivos; 2–4 = más reglas, más bloqueos, más falsos positivos.
- **Modos:** `On` bloquea · `DetectionOnly` solo registra · `Off` apagado.
- Familias de reglas: 942 SQLi · 941 XSS · 932 comandos · 930 traversal/LFI · 913 escáneres.
