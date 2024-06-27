<?php

$this->registerCss('

');

$this->registerJs("
$(document).ready(
    function() {

    let tipodocumento = $('#tipodocumento_traspaso').text().trim();
    let consecutivo = $('#consecutivo').text().trim();
    generarCodigoBarras(tipodocumento,'barcodeTipodocumento');
    generarCodigoBarras(consecutivo,'barcodeConsecutivo');

    function generarCodigoBarras(id,barcode) {
        // Eliminar el código de barras anterior
        $('#barcode').empty();
        // Generar el código de barras
        JsBarcode('#'+barcode, id, {
            width: 4, height: 25,
        });
    }

});

");

use frontend\models\Parametroscontrol;

$tituloReporte = 'TRASPASO MERCANCIA';
$modelparametros = ParametrosControl::findOne(['codigo' => '001']);
$nombreEmpresa = $modelparametros->valor;

$modelparametros = ParametrosControl::findOne(['codigo' => '002']);
$nitEmpresa = $modelparametros->valor;

$modelparametros = ParametrosControl::findOne(['codigo' => '003']);
$direccionEmpresa = $modelparametros->valor;

$modelparametros = ParametrosControl::findOne(['codigo' => '004']);
$telefonoEmpresa = $modelparametros->valor;

?>

<div class="d-flex flex-column align-items-baseline">
    
    <h1>
        <?= $tituloReporte ?>
    </h1>
    
    <h6>
        <?= $nombreEmpresa ?>
    </h6>
    
    <div class="d-flex flex-row">
        <h6>NIT: </h6>
        <h6>
            <?= $nitEmpresa ?>
        </h6>
    </div>
    
    <div class="d-flex flex-row">
        <h6>
            <?= $direccionEmpresa ?>
        </h6>
        <h6>&#160TEL:</h6>
        <h6>
            <?= $telefonoEmpresa ?>
        </h6>
    </div>

</div>

<hr>

<div class="d-flex flex-column align-items-baseline">

    <div class="d-flex flex-row">

        <h6 class="d-flex flex-row" style="margin-right:50px;">
            Serie:
            <div id="tipodocumento_traspaso">
                <?=
                    $modelfactura->tipodocumento->codigo;
                ?>
            </div>
        </h6>

        <h6 class="d-flex flex-row" style="margin-right:5px;">
            NUMERO:
            <div id="consecutivo">
                <?= $modelfactura->numeroEntrada ?>
            </div>
        </h6>

        <h6 style="margin-left:50px">&#160Caja: PKM</h6>

    </div>

    <h6>
        ALMACÉN ORIGEN:
        <?= $modelfactura->centroOperacionLegaliza->codigo; ?>
    </h6>
    <h6>
        <?= $modelfactura->centroOperacionLegaliza->nombre; ?>
    </h6>

</div>

<hr>

<?php 

/*
$items = [];
$nroregistro = 1;
$totalGeneral = 0;
$totalPaquetes = 0;
$unidadempaqueNombre = 0;
$unidadempaqueValor = 0;

echo '<p></p>';
echo '<h1>ITEMS</h1>';
echo '<table border="0">';
echo '<tr><th>REFER.</th><th>DESCRIP.</th><th>COLOR</th><th>TALLA</th><th>PAQ</th><th>UM</th><th>CANTIDAD</th><th>TOTAL</th></tr>';

foreach ($modeldetalles as $detalle) {

    echo '<tr><td>' . $detalle->item->referencia . '</td><td>' . $detalle->item->descripcion
        . '</td><td>' . $detalle->item->color->nombre . '</td><td>' . $detalle->item->talla->nombre
        . '</td><td>' . ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->codigo : 0)
        . '</td><td>' . ($detalle->item->unidadOrden ? $detalle->item->unidadOrden : 1)
        . '</td><td>' . $detalle->cantidad . '</td><td>'
        . $detalle->cantidad * ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->equivalencia : 1)
        . '</td></tr>';

    if ($detalle->item->unidadempaque != null) {
        $totalPaquetes += $detalle->cantidad * $detalle->item->unidadempaque->equivalencia; // Acumulamos el valor de la columna "TOTAL" en cada iteración
    }

    $totalGeneral += $detalle->cantidad * ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->equivalencia : 1); // Acumulamos el valor de la columna "TOTAL" en cada iteración

}

echo '<tr><td colspan="4" style="text-align:right">Total Paquetes:</td><td colspan="2">' . $totalPaquetes . '</td>';
echo '<td style="text-align:right;">Total General:</td><td colspan="3" >' . $totalGeneral . '</td></tr>';
echo '</table>';
*/
?>

<svg id="barcodeTipodocumento"></svg>

<h6>
    Usuario:
    <?= Yii::$app->user->isGuest ? ' ' : Yii::$app->user->identity->username ?>
</h6>

<svg id="barcodeConsecutivo"></svg>

<div class="d-flex justify-content-start">
    <button class="btn btn-lg btn-primary imprimir-solo" onclick="imprimir()">Confirmar!</button>
</div>

<style>

.container{
    margin:0;
    font-family: "Curry";
    font-weight: 700;
    font-size:13.5px;
}

th{
    padding-right: 10px;
}

table {
    border-collapse: separate;
    font-size:10px;
    width: 60%;
}

td {
    white-space: normal; /* Permite saltos de línea */
}

h1{
    font-size:25px;
}

h6{
    font-size:13.5px;
}

hr{
    margin:2px;
}

#tipodocumento_traspaso{
    margin-left:2px;
}

#w3-collapse {
    justify-content: flex-end;
  }

@media (max-width: 650px) {
    table {
        border-collapse: separate;
        font-size:10px;
        width: 100%;
    }
}

@media (max-width: 768px) {

.imprimir-solo {
    display: block !important;
    margin-left: 10px;
}

.d-flex.justify-content-start {
    justify-content: center !important;
}

}

@media print {
.imprimir-solo {
    display: none !important;
}
}

</style>

<script>
    function imprimir() {
        // Ocultar el botón de imprimir antes de imprimir
        var botonImprimir = document.querySelector('.imprimir-solo');
        botonImprimir.style.display = 'none';

        // Mandar a imprimir
        window.print();
    }
</script>