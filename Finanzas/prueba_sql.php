<?php
require_once 'conexion.php';

echo "<h2>Prueba de conexión y consulta directa</h2>";

// Consulta directa a una tabla (sin usar procedimientos)
$sql = "
SELECT TOP 10 
    t.id_transaccion, 
    t.fecha, 
    t.descripcion,
    t.monto, 
    t.tipo, 
    c.nombre AS cuenta
FROM proyectoDB.dbo.transacciones t
LEFT JOIN proyectoDB.dbo.cuentas c ON t.id_cuenta = c.id_cuenta
ORDER BY t.fecha DESC
";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo "<h3>Error al ejecutar la consulta:</h3>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
    exit;
}

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr style='background:#222;color:#fff;'>
        <th>ID</th>
        <th>Fecha</th>
        <th>Descripción</th>
        <th>Monto</th>
        <th>Tipo</th>
        <th>Cuenta</th>
      </tr>";

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $fecha = $row['fecha'] instanceof DateTime ? $row['fecha']->format('Y-m-d') : $row['fecha'];
    echo "<tr>
            <td>{$row['id_transaccion']}</td>
            <td>{$fecha}</td>
            <td>{$row['descripcion']}</td>
            <td>{$row['monto']}</td>
            <td>{$row['tipo']}</td>
            <td>{$row['cuenta']}</td>
          </tr>";
}

echo "</table>";

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
