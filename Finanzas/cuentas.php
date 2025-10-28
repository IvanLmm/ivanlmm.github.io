<?php include("conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cuentas Contables</title>
  <style>
    body{font-family: Arial; margin:20px;}
    table{border-collapse:collapse; width:100%;}
    th,td{border:1px solid #ccc; padding:8px; text-align:center;}
    th{background:#f0f0f0;}
    .btn{padding:6px 10px; background:#0078ff; color:white; border:none; border-radius:4px;}
  </style>
</head>
<body>
  <h2>Listado de Cuentas Contables</h2>
  <a href="#formAgregar" class="btn">➕ Agregar Cuenta</a>

  <?php
  $sql = "{CALL sp_select_cuentas}";
  $stmt = sqlsrv_query($conn, $sql);
  ?>
  <table>
    <tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Descripción</th><th>Acciones</th></tr>
    <?php while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
      <tr>
        <td><?= htmlspecialchars($row['id_cuenta']) ?></td>
        <td><?= htmlspecialchars($row['nombre']) ?></td>
        <td><?= htmlspecialchars($row['tipo']) ?></td>
        <td><?= htmlspecialchars($row['descripcion']) ?></td>
        <td>
          <a href="editar_cuenta.php?id=<?= $row['id_cuenta'] ?>">✏️</a> |
          <a href="eliminar_cuenta.php?id=<?= $row['id_cuenta'] ?>" onclick="return confirm('¿Eliminar esta cuenta?')">🗑️</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

  <h3 id="formAgregar">Agregar Nueva Cuenta</h3>
  <form method="post" action="insertar_cuenta.php">
    <label>Nombre:</label> <input type="text" name="nombre" required><br>
    <label>Tipo:</label>
    <select name="tipo">
      <option value="Activo">Activo</option>
      <option value="Pasivo">Pasivo</option>
      <option value="Ingreso">Ingreso</option>
      <option value="Gasto">Gasto</option>
      <option value="Patrimonio">Patrimonio</option>
    </select><br>
    <label>Descripción:</label> <input type="text" name="descripcion"><br>
    <button type="submit" class="btn">Guardar</button>
  </form>
</body>
</html>
