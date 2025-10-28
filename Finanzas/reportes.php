<?php
require_once 'conexion.php';

$vista = '';
$resultado = null;
$mensaje = '';

if (isset($_POST['vista'])) {
    $vista = $_POST['vista'];

    // 🔹 Solo permitir las 4 vistas válidas
    $vistas_permitidas = [
        'vw_BalanceComprobacion',
        'vw_EstadoResultados',
        'vw_BalanceGeneral',
        'vw_MovimientosCC'
    ];

    if (in_array($vista, $vistas_permitidas)) {
        $sql = "SELECT * FROM $vista";
        $resultado = sqlsrv_query($conn, $sql);
        if ($resultado === false) {
            $mensaje = "❌ Error al ejecutar la consulta: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        $mensaje = "⚠️ Vista no válida seleccionada.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Contables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            margin-top: 40px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        table th {
            background: #212529;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">📊 Reportes Contables</h2>

    <!-- Botón para volver -->
    <div class="mb-3 text-start">
        <a href="menuprincipal.html" class="btn-back">⬅️ Volver al Menú Principal</a>
    </div>

    <!-- Selección de vista -->
    <form method="POST" class="mb-4">
        <label for="vista" class="form-label fw-bold">Selecciona el reporte que deseas ver:</label>
        <div class="input-group">
            <select name="vista" id="vista" class="form-select" required>
                <option value="">-- Seleccionar vista --</option>
                <option value="vw_BalanceComprobacion" <?php if($vista=="vw_BalanceComprobacion") echo "selected"; ?>>Balance de Comprobación</option>
                <option value="vw_EstadoResultados" <?php if($vista=="vw_EstadoResultados") echo "selected"; ?>>Estado de Resultados</option>
                <option value="vw_BalanceGeneral" <?php if($vista=="vw_BalanceGeneral") echo "selected"; ?>>Balance General</option>
                <option value="vw_MovimientosCC" <?php if($vista=="vw_MovimientosCC") echo "selected"; ?>>Movimientos de Cuentas Corrientes</option>
            </select>
            <button type="submit" class="btn btn-primary">Mostrar</button>
        </div>
    </form>

    <!-- Mensajes -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- Resultados -->
    <?php if ($resultado && sqlsrv_has_rows($resultado)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <?php
                        $fields = sqlsrv_field_metadata($resultado);
                        foreach ($fields as $field) {
                            echo "<th>" . htmlspecialchars($field['Name']) . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            if ($value instanceof DateTime) $value = $value->format('Y-m-d');
                            echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
                        }
                        echo "</tr>";
                    }
                    sqlsrv_free_stmt($resultado);
                    ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($vista && !$resultado): ?>
        <p class="text-danger fw-bold">❌ No se pudo obtener información de la vista seleccionada.</p>
    <?php elseif ($vista && $resultado && !sqlsrv_has_rows($resultado)): ?>
        <p class="text-muted">No hay datos disponibles para este reporte.</p>
    <?php endif; ?>

</div>
</body>
</html>
