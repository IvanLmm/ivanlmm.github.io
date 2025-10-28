<?php
// ----------------------------------------------------------
// CONFIGURACIÓN DE CONEXIÓN A SQL SERVER
// ----------------------------------------------------------

// Nombre del servidor
// Si usas una instancia local de SQL Server Express, usa: localhost\SQLEXPRESS
// Si usas SQL Server normal, usa: localhost
$serverName = "localhost"; 

// Datos de autenticación
$connectionInfo = array(
    "Database" => "proyectoDB",  // nombre exacto de tu base de datos
    "UID" => "sa",               // tu usuario SQL Server
    "PWD" => "Leonardo02329*",   // tu contraseña SQL Server
    "CharacterSet" => "UTF-8"    // para acentos y caracteres especiales
);

// Intentar conectar
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Si hay error, mostrarlo
if (!$conn) {
    echo "<h3 style='color:red'>❌ Error al conectar con SQL Server</h3>";
    echo "<pre>";
    print_r(sqlsrv_errors());
    echo "</pre>";
    exit; // detener ejecución si no conecta
} else {
    // Si quieres verificar que funciona, puedes mostrar:
    // echo "<p style='color:green'>✅ Conexión exitosa</p>";
}
?>
