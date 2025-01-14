<?php
use diecoding\barcode\generator\Barcode;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\widgets\Alert;

?>

<?php
// Agregar script de JavaScript para ejecutar la acción de impresión al hacer clic en el botón "Imprimir"
$this->registerJs("
    // Cuando se haga clic en el botón 'Imprimir'
    $('#btn-imprimir').click(function() {
        // Obtener el valor seleccionado de la impresora
        var impresoraSeleccionada = $('#id-impresora').val();
        var idTraspaso = $('#traspaso-id').text(); // Obtener el ID del traspaso desde el contenido del elemento

        // Verificar si se ha seleccionado una impresora
        if (impresoraSeleccionada) {
            // Ejecutar la acción de impresión
            $.ajax({
                url: '" . Yii::$app->urlManager->createUrl(['/traspaso/traspasodetalle/impresion']) . "',
                method: 'POST',
                data: {
                    impresoraSeleccionada: impresoraSeleccionada,
                    idTraspaso: idTraspaso,
                },
                success: function(response) {
                    if (response.success) {
                        alert('Impresión ejecutada correctamente' + response.message); // Mensaje dinámico desde el servidor
                } else {
                        alert('Error: ' + response.message); // Mensaje de error dinámico
                    }
                },
                error: function(xhr, status, error) {

                    // Manejar errores (opcional)

                    console.error('Error al ejecutar la impresión: ' + error);

                    alert(  error + ', ' + 'revisar que esten todos los parametros.');
                }
            });
        } else {
            // Si no se ha seleccionado una impresora, mostrar un mensaje de error (opcional)
            alert('Por favor, selecciona una impresora antes de imprimir.');
        }
    });
");
?>

<?php
$this->registerJsFile('@web/js/jquery.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('@web/js/JsBarcode.all.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>


<style>
    .contenedorTraspasos {
        overflow-y: scroll;
        height: 80vh;
        /* position: absolute; */
        /* background-color: red; */
    }

    .print-border {
        border-width: 1px 0px 1px 0px;
        border-color: black;
    }

    th,
    td {
        padding-right: 8px;
        font-family: "Helvetica";
    }

    th {
        font-size: 17px;
    }

    td {
        font-size: 16px;
    }

    table {
        /* border-collapse: separate; */
        font-size: 16px;
        /* width: 60%; */
    }

    td {
        white-space: normal;
        /* Permite saltos de línea */
    }

    h1 {
        font-family: "Helvetica";
        font-size: 29px;
    }

    h6 {
        font-family: "Helvetica";
        font-size: 15px;
    }

    hr {
        /* margin: 2px; */
    }

    #tipodocumento_traspaso {
        margin-left: 10px;
    }


    .anulado {
        margin-top: 290px;
        margin-left: 100px;
        position: fixed;
        /* Fijo para que no se mueva al hacer scroll */
        /* top: 30vh; */
        /* left: 15vw; */
        transform: translate(-50%, -50%) rotate(-85deg);
        /* Centra y rota en diagonal */
        font-size: 90px;
        /* Ajusta el tamaño según el diseño */
        color: rgba(100, 0, 0, 0.2);
        /* Color semi-transparente */
        z-index: 1;
        /* Asegura que esté detrás del contenido principal */
        white-space: nowrap;
        /* Evita que el texto se divida en varias líneas */
        pointer-events: none;
        /* No interfiere con la interacción del usuario */

    }
</style>




<div>

    <?php $form = ActiveForm::begin(['action' => ['traspasodetalle/impresion',], 'method' => 'post']); ?>
    <div class="contenedorTraspasos">

        <div class="d-flex flex-column ">

            <h1>
                <?= Yii::$app->params['tituloTraspaso'] ?? '' ?>
            </h1>

            <h1 class="anulado">
                <?= $model->idEstado == 2 ? mb_strtoupper($model->estado->nombre) : '' ?>
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
                        <?= $model->tipodocumento->codigo ?>
                    </div>
                </h6>

                <h6 class="d-flex flex-row">
                    NUMERO:
                    <div id="consecutivo">
                        <?= $model->codigoerp ? $model->codigoerp->f350_consec_docto : $model->consecutivo ?>
                    </div>
                </h6>

                <h6 style="margin-left:20px; margin-right:20px; width: max-content;">
                    &#160Caja:
                    <?= $isMobile ? 'PKM' : 'PC'; ?>
                </h6>

                <h6 style="display: flex; display:none">Traspaso:
                    <div id="traspaso-id">
                        <?= $model->id ?>
                    </div>
                </h6>


            </div>

            <h6>
                Fecha:
                <?= Yii::$app->formatter->asDatetime($model->updated_at, 'php:d-m-Y H:i:s') ?>
            </h6>

            <h6>
                Origen:
                <?= $model->bodegaOrigen->codigo ?>
                <?= $model->bodegaOrigen->nombre; ?>
            </h6>

            <h6>
                Destino:
                <?= $model->bodegaDestino->codigo ?>
                <?= $model->bodegaDestino->nombre; ?>
            </h6>

            <h6>
                Usuario:
                <?= $model->usuario ? $model->usuario->username : 'sin usuario' ?>
            </h6>

        </div>

        <hr>


        <?PHP
        $items = [];
        $nroregistro = 1;
        $totalGeneral = 0;
        $totalPaquetes = 0;
        $unidadempaqueNombre = 0;
        $unidadempaqueValor = 0;
        $categoria = '';
        $descipcion = '';

        echo '<table border="0">';
        echo '<tr><th>REFER.</th><th>COLOR</th><th class="text-center">TALLA</th><th class="text-center">TIPO</th><th class="text-center">CANT</th><th class="text-center">TOTAL/UM</th></tr>';

        foreach ($modeldetalles as $detalle) {

            $categoria = explode(' ', $detalle->item->categoria->nombre)[0];
            $descipcion = explode(' ', $detalle->item->descripcion)[0];

            echo '<tr><td>' . $detalle->item->item . '</td>'
                . '<td>' . $detalle->item->color->nombre
                . '</td><td class="text-center">' . $detalle->item->talla->nombre
                . '</td><td class="text-center">' . ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->codigo : $detalle->item->unidadOrden)
                . '</td><td class="text-center">' . $detalle->cantidad
                . '</td><td class="text-center">' . $detalle->cantidad
                * ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->equivalencia : 1)
                . '</td></tr>'
                . ' <tr> <td colspan="12">' . $categoria . ' ' . $descipcion . '</td></tr>';

            $totalPaquetes += $detalle->cantidad;// Acumulamos el valor de la columna "TOTAL" en cada iteración
        
            $totalGeneral += $detalle->cantidad * ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->equivalencia : 1); // Acumulamos el valor de la columna "TOTAL" en cada iteración
        
        }
        echo '
    <tr class="print-border">
        <td colspan="4" style="text-align:left">
            Total unidades:
        </td>
        <td colspan="1" style="text-align:center;"> ' . $totalPaquetes . '</td>' .
            '<td colspan="2" style="text-align:center;">' . $totalGeneral . '</td>
    </tr>';
        echo '</table>';
        ?>

        <?=
            Barcode::widget([
                'value' => $model->tipodocumento->codigo,
                'options' => [
                    'style' => "width: 4cm; height: 1cm;",
                ],
                'pluginOptions' => [
                    'ean128' => true,
                ]
            ]);
        ?>

        <h3>
            Num.Cajas:
            <?= $model->numeroCajas; ?>
        </h3>

        <h3>
            Origen:
            <?= $model->bodegaOrigen->codigo ?>
            <?= $model->bodegaOrigen->nombre; ?>
        </h3>

        <h3>
            Destino:
            <?= $model->bodegaDestino->codigo ?>
            <?= $model->bodegaDestino->nombre; ?>
        </h3>

        <h3>
            Usuario:
            <?= $model->usuario ? $model->usuario->username : 'sin usuario' ?>
        </h3>

        <?=

            Barcode::widget([
                'value' => $model->codigoerp ? $model->codigoerp->f350_consec_docto : $model->consecutivo,
                'options' => [
                    'style' => "width: 4cm; height: 1cm;",
                ],
                'pluginOptions' => [
                    'ean128' => true,
                ]
            ]);

        ?>


        <?php if ($model->idEstado != 2): ?>
            <div class="col-6">
                <?= $form->field($model, 'impresora')->dropDownList(
                    $impresoras,
                    [
                        'prompt' => 'Selecciona una impresora...',
                        'id' => 'id-impresora',
                        'required' => true
                    ]
                ) ?>

                <div class="form-group text-center ">
                    <?= Html::button('impresion', ['class' => 'btn btn-success btn-lg btn-create', 'id' => 'btn-imprimir']) ?>
                </div>
            </div>
        <?php endif; ?>


        <?= Alert::widget() ?>
    </div>
</div>