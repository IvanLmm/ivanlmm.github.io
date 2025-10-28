<?php
include("conexion.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
$modo_editar = false;
$movimiento_actual = null;

// 🔍 Buscar
if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
    $sql = "{CALL sp_buscar_movimientocc(?)}";
    $params = array($busqueda);
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "{CALL sp_select_movimientoscc}";
    $stmt = sqlsrv_query($conn, $sql);
}
$movimientos = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $movimientos[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

// 🔹 Obtener lista de clientes para el formulario
$sqlClientes = "SELECT id_cliente, nombre FROM clientes ORDER BY nombre";
$resClientes = sqlsrv_query($conn, $sqlClientes);
$clientes = [];
while ($row = sqlsrv_fetch_array($resClientes, SQLSRV_FETCH_ASSOC)) {
    $clientes[] = $row;
}
sqlsrv_free_stmt($resClientes);

// ➕ Insertar movimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $id_cliente = (int)$_POST['id_cliente'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $monto = (float)$_POST['monto'];
    $descripcion = trim($_POST['descripcion']);

    if ($id_cliente > 0 && $monto > 0) {
        $sql = "{CALL sp_insert_movimientocc(?, ?, ?, ?, ?)}";
        $params = array($id_cliente, $fecha, $tipo, $monto, $descripcion);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            header("Location: cuentas_corrientes.php?msg=" . urlencode("✅ Movimiento agregado correctamente."));
            exit;
        } else {
            $mensaje = "❌ Error al insertar: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        $mensaje = "⚠️ Debes seleccionar un cliente y un monto válido.";
    }
}

// 🗑️ Eliminar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "{CALL sp_delete_movimientocc(?)}";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: cuentas_corrientes.php?msg=" . urlencode("🗑️ Movimiento eliminado."));
        exit;
    } else {
        $mensaje = "❌ Error al eliminar: " . print_r(sqlsrv_errors(), true);
    }
}

// ✏️ Editar (mostrar datos en formulario)
if (isset($_GET['edit'])) {
    $modo_editar = true;
    $id_edit = (int)$_GET['edit'];
    foreach ($movimientos as $m) {
        if ($m['id_movimiento'] == $id_edit) {
            $movimiento_actual = $m;
            break;
        }
    }
}

// 💾 Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)$_POST['id_movimiento'];
    $id_cliente = (int)$_POST['id_cliente'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $monto = (float)$_POST['monto'];
    $descripcion = trim($_POST['descripcion']);
    $sql = "{CALL sp_update_movimientocc(?, ?, ?, ?, ?, ?)}";
    $params = array($id, $id_cliente, $fecha, $tipo, $monto, $descripcion);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: cuentas_corrientes.php?msg=" . urlencode("✏️ Movimiento actualizado correctamente."));
        exit;
    } else {
        $mensaje = "❌ Error al actualizar: " . print_r(sqlsrv_errors(), true);
    }
}

if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas Corrientes</title>
    <style>
        body{font-family:Arial;background:#f8f9fa;padding:20px;}
        .container{max-width:1050px;margin:auto;background:#fff;padding:20px;border-radius:8px;}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{border:1px solid #ccc;padding:8px;text-align:center;}
        th{background:#eaeaea;}
        .btn{background:#007bff;color:#fff;padding:5px 10px;border-radius:5px;text-decoration:none;}
        .btn-danger{background:#dc3545;}
        .msg{padding:10px;margin:10px 0;border-radius:5px;}
        .ok{background:#e8f6e8;color:#19692c;}
        .err{background:#fbeaea;color:#962d2d;}
    </style>
</head>
<body>
<div class="container">
    <h2>💰 Cuentas Corrientes</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="msg <?php echo (strpos($mensaje, 'Error') !== false) ? 'err' : 'ok'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <!-- 🔍 Barra de búsqueda -->
    <form method="get">
        <input type="text" name="buscar" placeholder="Buscar por cliente o descripción..." value="<?php echo isset($busqueda) ? htmlspecialchars($busqueda) : ''; ?>">
        <button type="submit" class="btn">🔍 Buscar</button>
        <a href="cuentas_corrientes.php" class="btn">🔄 Limpiar</a>
    </form>

    <!-- Formulario -->
    <form method="post" style="margin-top:20px;">
        <h3><?php echo $modo_editar ? "✏️ Editar Movimiento" : "➕ Agregar Movimiento"; ?></h3>
        <?php if ($modo_editar): ?>
            <input type="hidden" name="id_movimiento" value="<?php echo $movimiento_actual['id_movimiento']; ?>">
        <?php endif; ?>

        <label>Cliente:</label><br>
        <select name="id_cliente" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($clientes as $cl): ?>
                <?php
                $sel = ($modo_editar && $movimiento_actual['cliente'] == $cl['nombre']) ? "selected" : "";
                ?>
                <option value="<?php echo $cl['id_cliente']; ?>" <?php echo $sel; ?>>
                    <?php echo htmlspecialchars($cl['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Fecha:</label><br>
        <input type="date" name="fecha" required value="<?php echo $modo_editar ? htmlspecialchars($movimiento_actual['fecha']->format('Y-m-d')) : ''; ?>"><br><br>

        <label>Tipo:</label><br>
        <select name="tipo" required>
            <option value="Credito" <?php echo ($modo_editar && $movimiento_actual['tipo']=='Credito')?'selected':''; ?>>Crédito</option>
            <option value="Debito" <?php echo ($modo_editar && $movimiento_actual['tipo']=='Debito')?'selected':''; ?>>Débito</option>
        </select><br><br>

        <label>Monto:</label><br>
        <input type="number" step="0.01" name="monto" required value="<?php echo $modo_editar ? htmlspecialchars($movimiento_actual['monto']) : ''; ?>"><br><br>

        <label>Descripción:</label><br>
        <input type="text" name="descripcion" value="<?php echo $modo_editar ? htmlspecialchars($movimiento_actual['descripcion']) : ''; ?>"><br><br>

        <button type="submit" name="<?php echo $modo_editar?'actualizar':'guardar';?>" class="btn">
            <?php echo $modo_editar?'Actualizar':'Guardar';?>
        </button>
        <?php if ($modo_editar): ?><a href="cuentas_corrientes.php" class="btn btn-danger">Cancelar</a><?php endif; ?>
    </form>

    <!-- Tabla -->
    <h3 style="margin-top:30px;">📋 Movimientos Registrados</h3>
    <?php if (empty($movimientos)): ?>
        <p>No hay movimientos registrados.</p>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Cliente</th><th>Fecha</th><th>Tipo</th><th>Monto</th><th>Descripción</th><th>Acciones</th></tr>
            <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?php echo $m['id_movimiento']; ?></td>
                    <td><?php echo htmlspecialchars($m['cliente']); ?></td>
                    <td><?php echo $m['fecha']->format('Y-m-d'); ?></td>
                    <td><?php echo htmlspecialchars($m['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($m['monto']); ?></td>
                    <td><?php echo htmlspecialchars($m['descripcion']); ?></td>
                    <td>
                        <a href="cuentas_corrientes.php?edit=<?php echo $m['id_movimiento']; ?>" class="btn">✏️</a>
                        <a href="cuentas_corrientes.php?delete=<?php echo $m['id_movimiento']; ?>" class="btn btn-danger"
                           onclick="return confirm('¿Eliminar este movimiento?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
