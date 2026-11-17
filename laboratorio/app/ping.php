<?php require 'comun.php'; cabecera('Herramienta de red (admin)');
$ip = $_GET['ip'] ?? ''; ?>
<form><input name="ip" placeholder="ej: 8.8.8.8"> <button>Hacer ping</button></form>
<?php if ($ip !== '') echo '<pre>' . shell_exec("ping -c 1 -W 1 " . $ip . " 2>&1") . '</pre>'; // VULNERABLE (comandos)
pie();
