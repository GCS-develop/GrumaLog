<?php

$this->registerCss('
.container{
    margin:0;
    font-family: "Curry";
    font-weight: 700;
    font-size:13.5px;
}
th{
    padding-right: 10px;
}
@media (max-width: 650px) {
    table {
        border-collapse: separate;
        // border-spacing: 10px 0px;
        font-size:10px;
        width: 100%;
    }
}
table {
    border-collapse: separate;
    // border-spacing: 10px 0px;
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

');

$this->registerJs("
$(document).ready(function() {
    //capturamos tipodocumento_traspaso desde params (deberia ser desde la bd)
    let tipodocumento = $('#tipodocumento_traspaso').text().trim();
    let consecutivo = $('#consecutivo').text().trim();
    generarCodigoBarras(tipodocumento,'barcodeTipodocumento');
    generarCodigoBarras(consecutivo,'barcodeConsecutivo');

    // Generar el código de barras
    function generarCodigoBarras(id,barcode) {
        // Eliminar el código de barras anterior
        $('#barcode').empty();
        // Generar el código de barras
        // JsBarcode('#'+barcode, id , {format: 'CODE128B'});
        JsBarcode('#'+barcode, id, {
            width: 4, height: 25,
        });
    }
});
");

?>
<div class="d-flex flex-column align-items-baseline">
    <h1>
        <?= Yii::$app->params['tituloTraspaso'] ?? '' ?>
    </h1>
    <h6>
        <?= Yii::$app->params['grupo'] ?? '' ?>
    </h6>
    <div class="d-flex flex-row">
        <h6>NIT: </h6>
        <h6>
            <?= Yii::$app->params['nit'] ?? '' ?>
        </h6>
    </div>
    <div class="d-flex flex-row">
        <h6>
            <?= Yii::$app->params['direccion'] ?? '' ?>
        </h6>
        <h6>&#160TEL:</h6>
        <h6>
            <?= Yii::$app->params['tel'] ?? '' ?>
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
                    $model->bodegaOrigen->tipodocumento->tipodocumento->codigo
                    ?>
            </div>
        </h6>
        <h6 class="d-flex flex-row" style="margin-right:5px;">
            NUMERO:
            <div id="consecutivo">
                <?= $model->consecutivo ?>
            </div>
        </h6>
        <h6 style="margin-left:50px">&#160Caja: PKM</h6>
    </div>
    <h6>
        Fecha:
        <?= Yii::$app->formatter->asDatetime($model->updated_at, 'php:d-m-Y H:i:s') ?>
    </h6>
    <h6>
        Origen:
        <?= $model->bodegaOrigen->nombre; ?>
    </h6>
    <h6>
        Destino:
        <?= $model->bodegaDestino->nombre; ?>
    </h6>
    <h6>
        Usuario:
        <?= Yii::$app->user->isGuest ? ' ' : Yii::$app->user->identity->username ?>
    </h6>
</div>
<hr>
<?PHP
echo '<p></p>';
echo '<h1>ITEMS</h1>';
$items = [];
$nroregistro = 1;
$totalGeneral = 0;
$totalPaquetes = 0;
$unidadempaqueNombre = 0;
$unidadempaqueValor = 0;
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
?>
<svg id="barcodeTipodocumento"></svg>
<h1>Num.Cajas:
    <?= $model->numeroCajas; ?>
</h1>
<h1>Origen:
    <?= $model->bodegaOrigen->nombre; ?>
</h1>
<h1>Destino:
    <?= $model->bodegaDestino->nombre; ?>
</h1>
<h6>Usuario:
    <?= Yii::$app->user->isGuest ? ' ' : Yii::$app->user->identity->username ?>
</h6>
<svg id="barcodeConsecutivo"></svg>

<div class="d-flex justify-content-start">
    <button class="btn btn-lg btn-primary imprimir-solo" onclick="imprimir()">Confirmar!</button>
</div>

<style>
    @media (max-width: 768px) {
        .imprimir-solo {
            display: block !important;
            margin-left: 10px;
            /* Ajusta el margen izquierdo según sea necesario */
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