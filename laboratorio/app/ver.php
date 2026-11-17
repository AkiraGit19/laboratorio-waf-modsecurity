<?php require 'comun.php'; cabecera('Políticas de la tienda');
$f = $_GET['archivo'] ?? 'envios.txt'; ?>
<p><a href="?archivo=envios.txt">Envíos</a>  /  <a href="?archivo=devoluciones.txt">Devoluciones</a></p>
<pre><?= @file_get_contents('docs/' . $f) /* VULNERABLE (Path Traversal) */ ?></pre>
<?php pie();
