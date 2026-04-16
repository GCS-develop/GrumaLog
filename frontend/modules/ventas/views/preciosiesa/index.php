<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var frontend\models\forms\PrecioItemForm $model */
/** @var yii\data\ArrayDataProvider $dataProvider */

$this->title = 'Consultar precio Siesa';
$this->params['breadcrumbs'][] = $this->title;

$printUrl = Url::to(['printajax']);
$termValue = trim((string)($model->term ?? ''));
$yaBusco = ($termValue !== '');
?>

<link rel="stylesheet" href="<?= Yii::$app->request->baseUrl ?>/css/swal.css">

<div class="preciosiesa-index">
    <?= Alert::widget() ?>

    <div class="card p-3 mb-3">
        <h5>Buscar ítem</h5>

        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index'],
        ]); ?>

        <div class="row">
            <div class="col-md-10">
                <?= $form->field($model, 'term')->textInput([
                    'placeholder' => 'Digite el item / referencia / descripción...',
                    'autocomplete' => 'off',
                ])->label(false) ?>
            </div>

            <div class="col-md-2">
                <?= Html::submitButton('Consultar', ['class' => 'btn btn-primary btn-block']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <?php if ($yaBusco): ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'showPageSummary' => true,
            'emptyText' => 'No se encontraron resultados con ese criterio.',
            'columns' => [
                ['attribute' => 'item', 'label' => 'Ítem'],
                'referencia',
                'descItem',
                ['attribute' => 'detalleExt1', 'label' => 'Color'],
                ['attribute' => 'detalleExt2', 'label' => 'Talla'],

                [
                    'attribute' => 'precio1',
                    'label' => 'Precio 1',
                    'format' => ['currency'],
                    'contentOptions' => ['style' => 'text-align:right;'],
                ],
                [
                    'attribute' => 'fecha1',
                    'label' => 'Fecha Precio 1',
                    'value' => fn($m) => empty($m['fecha1']) ? '-' : date('Y-m-d', strtotime($m['fecha1']))
                ],

                [
                    'attribute' => 'precio2',
                    'label' => 'Precio 2',
                    'format' => ['currency'],
                    'contentOptions' => ['style' => 'text-align:right;'],
                ],
                [
                    'attribute' => 'fecha2',
                    'label' => 'Fecha Precio 2',
                    'value' => fn($m) => empty($m['fecha2']) ? '-' : date('Y-m-d', strtotime($m['fecha2']))
                ],

                [
                    'header' => 'Acción',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'white-space:nowrap;'],
                    'value' => function ($model) use ($printUrl) {
                        $rowid = (int)($model['rowidItem'] ?? 0);
                        if ($rowid <= 0) {
                            return '-';
                        }

                        $btnPrecio1 = '';
                        if (!empty($model['precio1'])) {
                            $p1 = (float)$model['precio1'];
                            $btnPrecio1 = Html::a(
                                '<i class="fa fa-print"></i> Imprimir precio 1',
                                '#',
                                [
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Imprimir precio 1',
                                    'onclick' => "openPrintModal('{$rowid}', '{$printUrl}', 'precio1', {$p1}); return false;",
                                ]
                            );
                        }

                        $btnPrecio2 = '';
                        if (!empty($model['precio2'])) {
                            $p2 = (float)$model['precio2'];
                            $btnPrecio2 = Html::a(
                                '<i class="fa fa-print"></i> Imprimir precio 2',
                                '#',
                                [
                                    'class' => 'btn btn-sm btn-warning',
                                    'title' => 'Imprimir precio 2',
                                    'onclick' => "openPrintModal('{$rowid}', '{$printUrl}', 'precio2', {$p2}); return false;",
                                ]
                            );
                        }

                        return $btnPrecio1 . '&nbsp;' . $btnPrecio2;
                    }
                ],
            ],
        ]); ?>

    <?php else: ?>
        <div class="alert alert-info">
            Digite un ítem y presione <b>Consultar</b>.
        </div>
    <?php endif; ?>

</div>

<script src="<?= Yii::$app->request->baseUrl ?>/js/sweetalert2@11.js"></script>

<script>
    function openPrintModal(rowidItem, url, tipo, precioValor) {
        let titulo = (tipo === 'precio2') ? 'Imprimir precio 2' : 'Imprimir precio 1';

        Swal.fire({
            title: titulo,
            text: '¿Cuántos stickers desea imprimir?',
            input: 'number',
            inputAttributes: {
                min: 1
            },
            showCancelButton: true,
            buttonsStyling: false,
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Aceptar',
            inputValidator: (value) => {
                if (!value || parseInt(value) <= 0) return '¡Debes ingresar un valor válido!';
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Imprimiendo...',
                text: 'Por favor espere mientras se procesa la solicitud.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            $.post(url, {
                    rowidItem: rowidItem,
                    input: result.value,
                    tipo: tipo,
                    precio: precioValor
                })
                .done(function(response) {
                    if (response.status === 'success') {
                        Swal.fire('¡Éxito!', response.message, 'success');
                    } else {
                        Swal.fire('¡Error!', response.message, 'error');
                    }
                })
                .fail(function() {
                    Swal.fire('¡Error!', 'Hubo un problema con la conexión.', 'error');
                });
        });
    }
</script>