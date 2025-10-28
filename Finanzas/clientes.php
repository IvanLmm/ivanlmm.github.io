<?php
include("conexion.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
$modo_editar = false;
$cliente_actual = null;

// 🔍 Buscar
if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
    $sql = "{CALL sp_buscar_cliente(?)}";
    $params = array($busqueda);
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "{CALL sp_select_clientes}";
    $stmt = sqlsrv_query($conn, $sql);
}
$clientes = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $clientes[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

// ➕ Insertar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    if (!empty($nombre)) {
        $sql = "{CALL sp_insert_cliente(?, ?, ?, ?)}";
        $params = array($nombre, $correo, $telefono, $direccion);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            header("Location: clientes.php?msg=" . urlencode("✅ Cliente agregado correctamente."));
            exit;
        } else {
            $mensaje = "❌ Error al insertar: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        $mensaje = "⚠️ El nombre es obligatorio.";
    }
}

// 🗑️ Eliminar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "{CALL sp_delete_cliente(?)}";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: clientes.php?msg=" . urlencode("🗑️ Cliente eliminado."));
        exit;
    } else {
        $mensaje = "❌ Error al eliminar: " . print_r(sqlsrv_errors(), true);
    }
}

// ✏️ Editar
if (isset($_GET['edit'])) {
    $modo_editar = true;
    $id_edit = (int)$_GET['edit'];
    foreach ($clientes as $c) {
        if ($c['id_cliente'] == $id_edit) {
            $cliente_actual = $c;
            break;
        }
    }
}

// 💾 Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)$_POST['id_cliente'];
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $sql = "{CALL sp_update_cliente(?, ?, ?, ?, ?)}";
    $params = array($id, $nombre, $correo, $telefono, $direccion);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: clientes.php?msg=" . urlencode("✏️ Cliente actualizado correctamente."));
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
    <title>Clientes</title>
    <style>
        body{font-family:Arial;background:#f8f9fa;padding:20px;}
        .container{max-width:1000px;margin:auto;background:#fff;padding:20px;border-radius:8px;}
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
    <h2>👥 Clientes</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="msg <?php echo (strpos($mensaje, 'Error') !== false) ? 'err' : 'ok'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="get">
        <input type="text" name="buscar" placeholder="Buscar cliente..." value="<?php echo isset($busqueda) ? htmlspecialchars($busqueda) : ''; ?>">
        <button type="submit" class="btn">🔍 Buscar</button>
        <a href="clientes.php" class="btn">🔄 Limpiar</a>
    </form>

    <form method="post" style="margin-top:20px;">
        <h3><?php echo $modo_editar ? "✏️ Editar Cliente" : "➕ Agregar Cliente"; ?></h3>
        <?php if ($modo_editar): ?>
            <input type="hidden" name="id_cliente" value="<?php echo $cliente_actual['id_cliente']; ?>">
        <?php endif; ?>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required value="<?php echo $modo_editar ? htmlspecialchars($cliente_actual['nombre']) : ''; ?>"><br><br>
        <label>Correo:</label><br>
        <input type="email" name="correo" value="<?php echo $modo_editar ? htmlspecialchars($cliente_actual['correo']) : ''; ?>"><br><br>
        <label>Teléfono:</label><br>
        <input type="text" name="telefono" value="<?php echo $modo_editar ? htmlspecialchars($cliente_actual['telefono']) : ''; ?>"><br><br>
        <label>Dirección:</label><br>
        <input type="text" name="direccion" value="<?php echo $modo_editar ? htmlspecialchars($cliente_actual['direccion']) : ''; ?>"><br><br>
        <button type="submit" name="<?php echo $modo_editar?'actualizar':'guardar';?>" class="btn">
            <?php echo $modo_editar?'Actualizar':'Guardar';?>
        </button>
        <?php if ($modo_editar): ?><a href="clientes.php" class="btn btn-danger">Cancelar</a><?php endif; ?>
    </form>

    <h3 style="margin-top:30px;">📋 Listado de Clientes</h3>
    <?php if (empty($clientes)): ?>
        <p>No hay clientes registrados.</p>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?php echo $c['id_cliente']; ?></td>
                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($c['correo']); ?></td>
                    <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($c['direccion']); ?></td>
                    <td>
                        <a href="clientes.php?edit=<?php echo $c['id_cliente']; ?>" class="btn">✏️</a>
                        <a href="clientes.php?delete=<?php echo $c['id_cliente']; ?>" class="btn btn-danger"
                           onclick="return confirm('¿Eliminar este cliente?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<script>
  // Si hay un mensaje de éxito, recarga automáticamente después de 1 segundo
  const msg = document.querySelector('.msg');
  if (msg && msg.textContent.includes("✅")) {
      setTimeout(() => {
          window.location.href = window.location.pathname; // Recarga limpia
      }, 1000);
  }
</script>
</body>
</html>
