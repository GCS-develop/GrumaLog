<div style="font-family: Arial, sans-serif; font-size: 16px; margin-bottom: 10px; line-height: 1.2;">
    <span style="font-size: 20px; font-weight: bold;">CONTEO CDSC No:</span> <?= $conteoid ?><br>
    <span style="font-size: 20px; font-weight: bold;">CAJAS:</span> <?= $numeroCaja ?> DE <?= $totalCajas ?><br>
    <span style="font-size: 20px; font-weight: bold;">PROVEEDOR:</span><br>
    <?= $nitproveedor . ' - ' . $proveedor ?><br>
    <span style="font-size: 20px; font-weight: bold;">FACTURA:</span> <?= $numerofactura ?><br>
    <span style="font-size: 20px; font-weight: bold;">ALM. DESTINO:</span><br>
    <?= $codigoalmacen . ' - ' . $almacen ?><br>
    <span style="font-size: 20px; font-weight: bold;">UND. EMPAQUE:</span> <?= round($totalunidades,0) ?><br>
    <span style="font-size: 20px; font-weight: bold;">TOTAL UNDS:</span> <?= $total ?><br>
    <span style="font-size: 20px; font-weight: bold;">USUARIO:</span><br>
    <?= $usuarioconteo ?><br>
</div>