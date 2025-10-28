<?php
require_once 'conexion.php';

// === OBTENER LISTA DE CUENTAS PARA LOS SELECT ===
$cuentas = [];
$sqlCuentas = "SELECT id_cuenta, nombre FROM dbo.cuentas ORDER BY nombre ASC";
$stmtCuentas = sqlsrv_query($conn, $sqlCuentas);
if ($stmtCuentas) {
    while ($row = sqlsrv_fetch_array($stmtCuentas, SQLSRV_FETCH_ASSOC)) {
        $cuentas[] = $row;
    }
    sqlsrv_free_stmt($stmtCuentas);
}

// === INSERTAR ===
if (isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
    $sql = "{CALL sp_insert_transaccion(?, ?, ?, ?, ?, ?)}";
    $params = [
        $_POST['fecha'],
        $_POST['descripcion'],
        $_POST['monto'],
        $_POST['tipo'],
        $_POST['id_cuenta'],
        $_POST['id_cuenta_contra']
    ];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) { die(print_r(sqlsrv_errors(), true)); }
    echo "<script>alert('Transacción insertada correctamente');window.location='transacciones.php';</script>";
    exit;
}

// === ACTUALIZAR ===
if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $sql = "{CALL sp_update_transaccion(?, ?, ?, ?, ?, ?, ?)}";
    $params = [
        $_POST['id_transaccion'],
        $_POST['fecha'],
        $_POST['descripcion'],
        $_POST['monto'],
        $_POST['tipo'],
        $_POST['id_cuenta'],
        $_POST['id_cuenta_contra']
    ];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) { die(print_r(sqlsrv_errors(), true)); }
    echo "<script>alert('Transacción actualizada correctamente');window.location='transacciones.php';</script>";
    exit;
}

// === ELIMINAR ===
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $sql = "{CALL sp_delete_transaccion(?)}";
    $params = [$id];
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) { die(print_r(sqlsrv_errors(), true)); }
    echo "<script>alert('Transacción eliminada correctamente');window.location='transacciones.php';</script>";
    exit;
}

// === CONSULTAR TRANSACCIONES ===
$sql = "EXEC sp_select_transacciones";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) { die(print_r(sqlsrv_errors(), true)); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Transacciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="text-center mb-4">Gestión de Transacciones</h2>

    <!-- === FORMULARIO === -->
    <form method="POST" class="card p-3 mb-4">
        <h5>Registrar / Editar Transacción</h5>
        <div class="row g-2">
            <div class="col-md-2">
                <label>ID (solo editar)</label>
                <input type="text" name="id_transaccion" class="form-control" readonly>
            </div>
            <div class="col-md-2">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Monto</label>
                <input type="number" step="0.01" name="monto" class="form-control" required>
            </div>
            <div class="col-md-1">
                <label>Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="DEBITO">Débito</option>
                    <option value="CREDITO">Crédito</option>
                </select>
            </div>

            <div class="col-md-1">
                <label>Cuenta</label>
                <select name="id_cuenta" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($cuentas as $c) {
                        echo "<option value='{$c['id_cuenta']}'>{$c['nombre']}</option>";
                    } ?>
                </select>
            </div>

            <div class="col-md-1">
                <label>Contra</label>
                <select name="id_cuenta_contra" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($cuentas as $c) {
                        echo "<option value='{$c['id_cuenta']}'>{$c['nombre']}</option>";
                    } ?>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" name="accion" value="insertar" class="btn btn-success">Agregar</button>
            <button type="submit" name="accion" value="editar" class="btn btn-primary">Guardar Cambios</button>
            <button type="reset" class="btn btn-secondary">Limpiar</button>
        </div>
    </form>

    <!-- === TABLA DE DATOS === -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Tipo</th>
                    <th>Cuenta</th>
                    <th>Contra</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $fecha = $row['fecha'] instanceof DateTime ? $row['fecha']->format('Y-m-d') : $row['fecha'];
                echo "<tr>
                        <td>{$row['id_transaccion']}</td>
                        <td>{$fecha}</td>
                        <td>{$row['descripcion']}</td>
                        <td>".number_format((float)$row['monto'], 2)."</td>
                        <td>{$row['tipo']}</td>
                        <td>{$row['cuenta']}</td>
                        <td>{$row['cuenta_contra']}</td>
                        <td>
                            <button type='button' class='btn btn-warning btn-sm'
                                onclick='editar(" . json_encode($row) . ")'>Editar</button>
                            <a href='?eliminar={$row['id_transaccion']}' class='btn btn-danger btn-sm'
                               onclick='return confirm(\"¿Eliminar esta transacción?\")'>Eliminar</a>
                        </td>
                      </tr>";
            }
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- === SCRIPT DE EDICIÓN === -->
<script>
function editar(data) {
    document.querySelector('[name=id_transaccion]').value = data.id_transaccion;
    document.querySelector('[name=fecha]').value = data.fecha.date.substring(0, 10);
    document.querySelector('[name=descripcion]').value = data.descripcion;
    document.querySelector('[name=monto]').value = data.monto;
    document.querySelector('[name=tipo]').value = data.tipo;
    document.querySelector('[name=id_cuenta]').value = data.id_cuenta;
    document.querySelector('[name=id_cuenta_contra]').value = data.id_cuenta_contra;
    window.scrollTo({top: 0, behavior: 'smooth'});
}
</script>
</body>
</html>
