<?php
// ADVERTENCIA: aplicación VULNERABLE A PROPÓSITO. Solo para el laboratorio.
session_start();
$db = new PDO('sqlite:/tmp/tienda.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // muestra errores SQL (mala práctica a propósito)
$db->exec("CREATE TABLE IF NOT EXISTS usuarios (id INTEGER PRIMARY KEY, usuario TEXT, clave TEXT, rol TEXT)");
$db->exec("CREATE TABLE IF NOT EXISTS productos (id INTEGER PRIMARY KEY, nombre TEXT, precio REAL)");
if (!$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn()) {
    $db->exec("INSERT INTO usuarios (usuario, clave, rol) VALUES
        ('admin','Adm1n#2026','administrador'), ('maria','maria123','cliente'), ('jose','qwerty','cliente')");
    $db->exec("INSERT INTO productos (nombre, precio) VALUES
        ('Laptop',2999),('Mouse',59),('Teclado',129),('Monitor',799),('Audífonos',199)");
}
function cabecera($titulo) { ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $titulo ?> - TiendaDemo</title>
<style>
 :root{--ink:#1f2633;--muted:#6b7280;--line:#e4e7ec;--bg:#f4f5f7;--accent:#2f5fd0}
 *{box-sizing:border-box}
 body{font-family:-apple-system,system-ui,"Segoe UI",sans-serif;max-width:720px;margin:0 auto;padding:0 20px 48px;background:var(--bg);color:var(--ink);line-height:1.6}
 header{display:flex;align-items:baseline;gap:10px;padding:22px 0 14px;border-bottom:1px solid var(--line)}
 header .logo{font-size:20px;font-weight:700;letter-spacing:-.01em}
 header .tag{font-size:12px;color:var(--muted)}
 nav{display:flex;flex-wrap:wrap;gap:4px;margin:14px 0 0}
 nav a{color:#374151;text-decoration:none;font-size:14px;padding:6px 12px;border-radius:7px}
 nav a:hover{background:#eceef2;color:var(--accent)}
 h2{font-size:19px;margin:0 0 14px}
 .card{background:#fff;border:1px solid var(--line);border-radius:10px;padding:22px 24px;margin-top:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
 form{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 4px}
 input{padding:9px 11px;font-size:14px;border:1px solid #d0d5dd;border-radius:7px}
 input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(47,95,208,.12)}
 button{padding:9px 16px;font-size:14px;font-weight:600;color:#fff;background:var(--accent);border:none;border-radius:7px;cursor:pointer}
 button:hover{background:#2850b8}
 pre{background:#1b2130;color:#d7e0ef;padding:12px 14px;border-radius:8px;overflow:auto;font:13px ui-monospace,Menlo,monospace;margin:14px 0}
 table{border-collapse:collapse;width:100%;margin-top:12px;font-size:14px}
 th,td{border-bottom:1px solid var(--line);padding:9px 8px;text-align:left}
 th{color:var(--muted);font-weight:600;font-size:12.5px;text-transform:uppercase;letter-spacing:.03em}
 a{color:var(--accent)}
</style></head><body>
<header><span class="logo">TiendaDemo</span><span class="tag">tienda de prueba</span></header>
<nav><a href="/">Inicio</a><a href="/login.php">Login</a><a href="/buscar.php">Buscar</a><a href="/ping.php">Herramienta de red (admin)</a><a href="/ver.php?archivo=envios.txt">Políticas</a></nav>
<div class="card"><h2><?= $titulo ?></h2>
<?php }
function pie() { echo "</div></body></html>"; }
