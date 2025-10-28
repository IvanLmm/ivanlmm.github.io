<?php
include("conexion.php");

// Llamamos al procedimiento almacenado
$sql = "{CALL sp_select_transacciones}";
$stmt = sqlsrv_query($conn, $sql);

// Verificamos si hubo error al ejecutar
if ($stmt === false) {
    echo "<h3 style='color:red;'>❌ Error al ejecutar el procedimiento:</h3>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
    exit;
}

echo "<h2>✅ Conexión exitosa y procedimiento ejecutado correctamente</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr><th>ID</th><th>Fecha</th><th>Descripción</th><th>Tipo</th><th>Monto</th></tr>";

// Mostrar resultados
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // manejar fecha null
    $fecha = '';
    if (!empty($row['fecha']) && $row['fecha'] instanceof DateTime) {
        $fecha = $row['fecha']->format('Y-m-d');
    }
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id_transaccion']) . "</td>";
    echo "<td>" . $fecha . "</td>";
    echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
    echo "<td>" . htmlspecialchars($row['tipo']) . "</td>";
    echo "<td>" . htmlspecialchars($row['monto']) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Liberar y cerrar conexión
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
