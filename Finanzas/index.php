<?php
session_start();
require_once 'conexion.php';

// Si ya hay sesión, redirige directo al menú principal
if (isset($_SESSION['usuario'])) {
    header("Location: menuprincipal.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['user']);
    $password = trim($_POST['pass']);

    // 🔹 Valida desde la base de datos (puedes cambiar esto por tu propia lógica si deseas)
    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";
    $params = [$usuario, $password];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $_SESSION['usuario'] = $usuario;
        header("Location: menuprincipal.html");
        exit;
    } else {
        $error = "❌ Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

    <div class="padre">
        <div class="borroso"></div>

        <div class="hijo">
            <h3 class="titulo">Ingresa tus datos</h3>

            <!-- 🔹 Cambié form a método POST y con PHP funcional -->
            <form class="form" autocomplete="off" method="POST">
                <input type="text" placeholder="Usuario" class="user" name="user" required><br>
                <input type="password" placeholder="Contraseña" class="pass" name="pass" required><br>
                <input type="submit" value="Iniciar" class="btn" id="btn">
            </form>

            <?php if (!empty($error)): ?>
                <p style="color:red; text-align:center; font-weight:bold;"><?= $error ?></p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
