<?php
include("conexion.php"); // Usa tu conexión a SQL Server

// Mostrar errores (solo para pruebas)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";

// 🔹 Insertar cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $descripcion = trim($_POST['descripcion']);

    if (empty($nombre) || empty($tipo)) {
        $mensaje = "⚠️ Debes ingresar un nombre y tipo de cuenta.";
    } else {
        $sql = "{CALL sp_insert_cuenta(?, ?, ?)}";
        $params = array($nombre, $tipo, $descripcion);

        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            $mensaje = "❌ Error al insertar cuenta: " . print_r(sqlsrv_errors(), true);
        } else {
            $mensaje = "✅ Cuenta agregada correctamente.";
            header("Location: cuentas.php?msg=" . urlencode($mensaje));
            exit;
        }
    }
}

// 🔹 Eliminar cuenta
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $sql = "{CALL sp_delete_cuenta(?)}";
        $params = array($id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            $mensaje = "❌ Error al eliminar: " . print_r(sqlsrv_errors(), true);
        } else {
            $mensaje = "🗑️ Cuenta eliminada correctamente.";
            header("Location: cuentas.php?msg=" . urlencode($mensaje));
            exit;
        }
    }
}

// 🔹 Mostrar mensaje de redirección
if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
}

// 🔹 Obtener lista de cuentas
$sql = "{CALL sp_select_cuentas}";
$stmt = sqlsrv_query($conn, $sql);
$cuentas = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $cuentas[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas Contables</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f8; margin: 0; padding: 20px; }
        .container { max-width: 950px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #e8eef3; }
        .msg { margin: 15px 0; padding: 10px; border-radius: 5px; }
        .ok { background: #e7f8e9; color: #256b32; }
        .err { background: #fceaea; color: #a33a3a; }
        .btn { padding: 6px 12px; border: none; border-radius: 4px; text-decoration: none; background: #2a7dfc; color: white; }
        .btn:hover { background: #1b5fc7; }
        form input, form select { padding: 5px; width: 95%; }
    </style>
</head>
<body>
<div class="container">
    <h2>📘 Cuentas Contables</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="msg <?php echo (strpos($mensaje, 'Error') !== false) ? 'err' : 'ok'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de inserción -->
    <form method="POST" style="margin-top: 15px;">
        <h3>Agregar nueva cuenta</h3>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Tipo:</label><br>
        <select name="tipo" required>
            <option value="">-- Seleccione tipo --</option>
            <option value="Activo">Activo</option>
            <option value="Pasivo">Pasivo</option>
            <option value="Ingreso">Ingreso</option>
            <option value="Gasto">Gasto</option>
            <option value="Patrimonio">Patrimonio</option>
        </select><br><br>

        <label>Descripción:</label><br>
        <input type="text" name="descripcion"><br><br>

        <button type="submit" name="guardar" class="btn">Guardar Cuenta</button>
    </form>

    <!-- Tabla de cuentas -->
    <h3 style="margin-top:30px;">Listado de cuentas</h3>
    <?php if (empty($cuentas)): ?>
        <p>No hay cuentas registradas.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($cuentas as $c): ?>
                <tr>
                    <td><?php echo htmlspecialchars($c['id_cuenta']); ?></td>
                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($c['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($c['descripcion']); ?></td>
                    <td>
                        <a href="cuentas.php?delete=<?php echo $c['id_cuenta']; ?>"
                           onclick="return confirm('¿Eliminar la cuenta <?php echo htmlspecialchars($c['nombre']); ?>?')"
                           class="btn" style="background:#dc3545;">🗑️ Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
