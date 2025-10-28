<?php
require_once 'conexion.php';

// Ejecutar el procedimiento almacenado
$sql = "EXEC dbo.sp_select_transacciones";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo "<h3>Error al ejecutar el procedimiento:</h3>";
    die(print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Transacciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h2 class="mb-4 text-center">Transacciones Registradas</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>Tipo</th>
                        <th>Cuenta</th>
                        <th>Cuenta Contrapartida</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $fecha = $row['fecha'] instanceof DateTime ? $row['fecha']->format('Y-m-d') : $row['fecha'];
                    echo "<tr>
                            <td>".htmlspecialchars($row['id_transaccion'])."</td>
                            <td>".htmlspecialchars($fecha)."</td>
                            <td>".htmlspecialchars($row['descripcion'])."</td>
                            <td>".number_format((float)$row['monto'], 2, '.', ',')."</td>
                            <td>".htmlspecialchars($row['tipo'])."</td>
                            <td>".htmlspecialchars($row['cuenta'])."</td>
                            <td>".htmlspecialchars($row['cuenta_contra'])."</td>
                          </tr>";
                }
                sqlsrv_free_stmt($stmt);
                sqlsrv_close($conn);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
