<?php
/* @var $registro array */
?>

<tr>
    <td><?= $registro['tipoDocumento'] .'-' . $registro['consecutivoDocumento'] ?></td>
    <td><?= $registro['codAlmacenOrigen']. ' ' . $registro['almacenOrigen'] ?></td>
    <td><?= $registro['codAlmacenDestino']. ' ' . $registro['almacenDestino'] ?></td>
    <td style="text-align: right;"><?= $registro['unidades'] ?></td>
    <td style="text-align: right;"><?= $registro['unidadesEmp'] ?></td>
    <td><?= '' ?></td>
    <td><?= '' ?></td>
</tr>
