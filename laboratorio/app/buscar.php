<?php require 'comun.php'; cabecera('Buscar productos');
$q = $_GET['q'] ?? ''; ?>
<form><input name="q" value="" placeholder="ej: Laptop"> <button>Buscar</button></form>
<?php if ($q !== '') {
    echo "<p>Resultados para: $q</p>";                         // VULNERABLE (XSS): imprime sin escapar
    $sql = "SELECT nombre, precio FROM productos WHERE nombre LIKE '%$q%'";  // VULNERABLE (SQLi)
    echo "<pre>$sql</pre><table><tr><th>Producto</th><th>Precio</th></tr>";
    foreach ($db->query($sql) ?: [] as $r) echo "<tr><td>{$r[0]}</td><td>{$r[1]}</td></tr>";
    echo "</table>";
}
pie();
