<?php require 'comun.php'; cabecera('Iniciar sesión');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // VULNERABLE (SQLi): concatena lo que escribe el usuario dentro del SQL
    $sql = "SELECT * FROM usuarios WHERE usuario = '{$_POST['usuario']}' AND clave = '{$_POST['clave']}'";
    echo "<pre>$sql</pre>";
    $u = $db->query($sql)->fetch();
    if ($u) { $_SESSION['usuario'] = $u['usuario']; $_SESSION['rol'] = $u['rol'];
              echo "<p style='color:green'>Bienvenido <b>{$u['usuario']}</b> (rol: {$u['rol']})</p>"; }
    else      echo "<p style='color:red'>Usuario o clave incorrectos</p>";
} ?>
<form method="post"><input name="usuario" placeholder="usuario"> <input name="clave" type="password" placeholder="clave"> <button>Entrar</button></form>
<?php pie();
