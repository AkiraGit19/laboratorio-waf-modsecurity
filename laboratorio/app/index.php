<?php require 'comun.php'; cabecera('Inicio'); ?>
<p>Bienvenido<?= isset($_SESSION['usuario']) ? ', <b>'.$_SESSION['usuario'].'</b> ('.$_SESSION['rol'].')' : '' ?>.</p>
<p>Tienda de prueba con 4 vulnerabilidades: SQL Injection, XSS, Inyección de comandos y Path Traversal.</p>
<?php pie();
