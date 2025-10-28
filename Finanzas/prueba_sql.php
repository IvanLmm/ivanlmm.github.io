<?php
require_once 'conexion.php';
echo "<h2>Diagnóstico completo - DB / Tablas / SP</h2>";

// 1) Base de datos actual a la que está conectado PHP
$res = sqlsrv_query($conn, "SELECT DB_NAME() AS bd, @@SERVERNAME AS servidor");
if ($res === false) {
    echo "<h3>Error al obtener DB_NAME():</h3><pre>"; print_r(sqlsrv_errors()); echo "</pre>"; exit;
}
$r = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
echo "<p><strong>Servidor:</strong> " . htmlspecialchars($r['servidor']) . " &nbsp; | &nbsp; <strong>Base conectada por PHP:</strong> " . htmlspecialchars($r['bd']) . "</p>";

// 2) Listar tablas llamadas 'transacciones' en TODAS las bases accesibles (busca en current DB)
echo "<h3>Buscar tabla 'transacciones' en la base actual</h3>";
$q = "SELECT TABLE_SCHEMA, TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'transacciones'";
$res = sqlsrv_query($conn, $q);
if ($res === false) {
    echo "<pre>Error al consultar INFORMATION_SCHEMA.TABLES:\n"; print_r(sqlsrv_errors()); echo "</pre>";
} else {
    $found = false;
    echo "<pre>";
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $found = true;
        echo htmlspecialchars($row['TABLE_SCHEMA'] . '.' . $row['TABLE_NAME']) . "\n";
    }
    if (!$found) echo "NINGUNA (no existe 'transacciones' en la base conectada)\n";
    echo "</pre>";
}

// 3) Intentar SELECT usando sin prefijo de base (solo dbo.transacciones)
echo "<h3>Intento SELECT directo: SELECT TOP 1 * FROM dbo.transacciones</h3>";
$res = @sqlsrv_query($conn, "SELECT TOP 1 * FROM dbo.transacciones");
if ($res === false) {
    echo "<pre>Error al consultar dbo.transacciones:\n";
    print_r(sqlsrv_errors());
    echo "</pre>";
} else {
    echo "<p>Consulta dbo.transacciones OK. Filas:</p><pre>";
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
}

// 4) Intentar SELECT usando prefijo completo proyectoDB.dbo.transacciones
echo "<h3>Intento SELECT con prefijo completo: SELECT TOP 1 * FROM proyectoDB.dbo.transacciones</h3>";
$res = @sqlsrv_query($conn, "SELECT TOP 1 * FROM proyectoDB.dbo.transacciones");
if ($res === false) {
    echo "<pre>Error al consultar proyectoDB.dbo.transacciones:\n";
    print_r(sqlsrv_errors());
    echo "</pre>";
} else {
    echo "<p>Consulta proyectoDB.dbo.transacciones OK. Filas:</p><pre>";
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        print_r($row);
    }
    echo "</pre>";
}

// 5) Listar procedimientos que coincidan (por si necesitas confirmar SP)
echo "<h3>Procedimientos que contienen 'transaccion' en su nombre</h3>";
$res = sqlsrv_query($conn, "
    SELECT SCHEMA_NAME(p.schema_id) AS esquema, p.name
    FROM sys.procedures p
    WHERE p.name LIKE '%transaccion%'
");
if ($res === false) {
    echo "<pre>Error al consultar sys.procedures:\n"; print_r(sqlsrv_errors()); echo "</pre>";
} else {
    echo "<pre>";
    $found = false;
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $found = true;
        echo htmlspecialchars($row['esquema'] . '.' . $row['name']) . "\n";
    }
    if (!$found) echo "NINGUNO\n";
    echo "</pre>";
}

sqlsrv_close($conn);
?>
