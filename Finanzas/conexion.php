<?php
$server = "4.206.135.183"; 
$database = "Proyecto";
$user = "orlynk";
$password = "12345678";

$conn = sqlsrv_connect($server, [
    "Database" => $database,
    "UID" => $user,
    "PWD" => $password,
    "Encrypt" => true,
    "TrustServerCertificate" => false
]);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
echo "Conexión exitosa a SQL Server.";
?>
