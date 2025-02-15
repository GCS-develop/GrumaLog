<?php
/* @var $destino string */
/* @var $total int */
?>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <colgroup>
        <col style="width: 10%;"> <!-- Columna 1 -->
        <col style="width: 10%;"> <!-- Columna 2 -->
        <col style="width: 10%;"> <!-- Columna 3 -->
        <col style="width: 10%;"> <!-- Columna 4 -->
        <col style="width: 10%;"> <!-- Columna 5 -->
        <col style="width: 10%;"> <!-- Columna 6 -->
        <col style="width: 10%;"> <!-- Columna 7 -->
    </colgroup>
    <tr>
        <td colspan="3" style="text-align: left; width: 39%;"><strong>Total para <?= $destino ?>:</strong></td>
        <td style="text-align: right; width: 10%;"><?= $totalDestino ?></td>
        <td style="text-align: right; width: 20%;"><?= $totalUnidadesEmp ?></td>
        <td style="text-align: right; width: 28%;"></td>
    </tr>
</table>


<!--
<h3>Total para Destino: <?php // $destino ?></h3>
<p><strong>Total Unidades:</strong> <?php // $total ?></p>
<hr>
-->
