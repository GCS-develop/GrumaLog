<?php
/* @var $totalGeneral int */
?>

<h3>Total General</h3>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Usuario Planilla</th>
            <th>Total Destinos</th>
            <th>Total Unidades</th>
            <th>Total Empaques</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: right;"><?= $username ?></td>
            <td style="text-align: right;"><?= $numeroBodegasDestino ?></td>
            <td style="text-align: right;"><?= $totalGeneral ?></td>
            <td style="text-align: right;"><?= $totalGeneralUndEmp ?></td>
        </tr>
    </tbody>
</table>