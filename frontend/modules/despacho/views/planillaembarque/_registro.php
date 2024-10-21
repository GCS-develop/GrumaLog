<?php
/* @var $registro array */
?>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Traspaso</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Unidades</th>
            <th>Und. Emp.</th>
            <th>Sello Destino</th>
            <th>Destino</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= $registro['tipoDocumento'] .'-' . $registro['consecutivoDocumento'] ?></td>
            <td><?= $registro['codAlmacenOrigen']. ' ' . $registro['almacenOrigen'] ?></td>
            <td><?= $registro['codAlmacenDestino']. ' ' . $registro['almacenDestino'] ?></td>
            <td><?= $registro['unidades'] ?></td>
        </tr>
    </tbody>
</table>
<hr>
