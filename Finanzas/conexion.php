<?php
$server = "4.206.135.183";
$connectionOptions = [
  "Database" => "proyectoDB",
  "Uid" => "orlynk",
  "PWD" => "12345678",
  "Encrypt" => true,                 // cifrado ON
  "TrustServerCertificate" => true   // <- acepta certificado autofirmado
];

$conn = sqlsrv_connect($server, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
