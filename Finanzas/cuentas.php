<?php
ob_start(); // Evita errores de encabezado (headers already sent)
include("conexion.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$mensaje = "";
$modo_editar = false;
$cuenta_actual = null;

// 🔍 Buscar
if (isset($_GET['buscar'])) {
    $busqueda = trim($_GET['buscar']);
    $sql = "{CALL sp_buscar_cuenta(?)}";
    $params = array($busqueda);
    $stmt = sqlsrv_query($conn, $sql, $params);
} else {
    $sql = "{CALL sp_select_cuentas}";
    $stmt = sqlsrv_query($conn, $sql);
}
$cuentas = [];
if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $cuentas[] = $row;
    }
    sqlsrv_free_stmt($stmt);
}

// ➕ Insertar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $descripcion = trim($_POST['descripcion']);

    if (!empty($nombre) && !empty($tipo)) {
        $sql = "{CALL sp_insert_cuenta(?, ?, ?)}";
        $params = array($nombre, $tipo, $descripcion);
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            header("Location: cuentas.php?msg=" . urlencode("✅ Cuenta agregada correctamente."));
            exit;
        } else {
            $mensaje = "❌ Error al insertar: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        $mensaje = "⚠️ Nombre y tipo son obligatorios.";
    }
}

// 🗑️ Eliminar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $sql = "{CALL sp_delete_cuenta(?)}";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: cuentas.php?msg=" . urlencode("🗑️ Cuenta eliminada correctamente."));
        exit;
    } else {
        $mensaje = "❌ Error al eliminar: " . print_r(sqlsrv_errors(), true);
    }
}

// ✏️ Editar
if (isset($_GET['edit'])) {
    $modo_editar = true;
    $id_edit = (int)$_GET['edit'];
    foreach ($cuentas as $c) {
        if ($c['id_cuenta'] == $id_edit) {
            $cuenta_actual = $c;
            break;
        }
    }
}

// 💾 Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)$_POST['id_cuenta'];
    $nombre = trim($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $descripcion = trim($_POST['descripcion']);
    $sql = "{CALL sp_update_cuenta(?, ?, ?, ?)}";
    $params = array($id, $nombre, $tipo, $descripcion);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt) {
        header("Location: cuentas.php?msg=" . urlencode("✏️ Cuenta actualizada correctamente."));
        exit;
    } else {
        $mensaje = "❌ Error al actualizar: " . print_r(sqlsrv_errors(), true);
    }
}

if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
}
ob_end_flush(); // Finaliza el buffer de salida
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas Contables</title>
    <style>
        body{font-family:Arial;background:#f8f9fa;padding:20px;}
        .container{max-width:1000px;margin:auto;background:#fff;padding:20px;border-radius:8px;}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{border:1px solid #ccc;padding:8px;text-align:center;}
        th{background:#eaeaea;}
        .btn{background:#007bff;color:#fff;padding:5px 10px;border-radius:5px;text-decoration:none;}
        .btn-danger{background:#dc3545;}
        .msg{padding:10px;margin:10px 0;border-radius:5px;text-align:center;font-weight:bold;}
        .ok{background:#e8f6e8;color:#19692c;}
        .err{background:#fbeaea;color:#962d2d;}
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

    <form method="get">
        <input type="text" name="buscar" placeholder="Buscar cuenta..." value="<?php echo isset($busqueda) ? htmlspecialchars($busqueda) : ''; ?>">
        <button type="submit" class="btn">🔍 Buscar</button>
        <a href="cuentas.php" class="btn">🔄 Limpiar</a>
    </form>

    <form method="post" style="margin-top:20px;">
        <h3><?php echo $modo_editar ? "✏️ Editar Cuenta" : "➕ Agregar Cuenta"; ?></h3>
        <?php if ($modo_editar): ?>
            <input type="hidden" name="id_cuenta" value="<?php echo $cuenta_actual['id_cuenta']; ?>">
        <?php endif; ?>
        <label>Nombre:</label><br>
        <input type="text" name="nombre" required value="<?php echo $modo_editar ? htmlspecialchars($cuenta_actual['nombre']) : ''; ?>"><br><br>
        <label>Tipo:</label><br>
        <select name="tipo" required>
            <?php
            $tipos = ["Activo","Pasivo","Ingreso","Gasto","Patrimonio"];
            foreach($tipos as $t){
                $sel = ($modo_editar && $cuenta_actual['tipo']==$t)?"selected":"";
                echo "<option value='$t' $sel>$t</option>";
            }
            ?>
        </select><br><br>
        <label>Descripción:</label><br>
        <input type="text" name="descripcion" value="<?php echo $modo_editar ? htmlspecialchars($cuenta_actual['descripcion']) : ''; ?>"><br><br>
        <button type="submit" name="<?php echo $modo_editar?'actualizar':'guardar';?>" class="btn">
            <?php echo $modo_editar?'Actualizar':'Guardar';?>
        </button>
        <?php if ($modo_editar): ?><a href="cuentas.php" class="btn btn-danger">Cancelar</a><?php endif; ?>
    </form>

    <h3 style="margin-top:30px;">📋 Listado de Cuentas</h3>
    <?php if (empty($cuentas)): ?>
        <p>No hay cuentas registradas.</p>
    <?php else: ?>
        <table>
            <tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Descripción</th><th>Acciones</th></tr>
            <?php foreach ($cuentas as $c): ?>
                <tr>
                    <td><?php echo $c['id_cuenta']; ?></td>
                    <td><?php echo htmlspecialchars($c['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($c['tipo']); ?></td>
                    <td><?php echo htmlspecialchars($c['descripcion']); ?></td>
                    <td>
                        <a href="cuentas.php?edit=<?php echo $c['id_cuenta']; ?>" class="btn">✏️</a>
                        <a href="cuentas.php?delete=<?php echo $c['id_cuenta']; ?>" class="btn btn-danger"
                           onclick="return confirm('¿Eliminar esta cuenta?')">🗑️</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<script>
const msg = document.querySelector('.msg');
if (msg && msg.textContent.includes("✅")) {
    setTimeout(() => { window.location.href = window.location.pathname; }, 1000);
}
</script>
</body>
</html>
