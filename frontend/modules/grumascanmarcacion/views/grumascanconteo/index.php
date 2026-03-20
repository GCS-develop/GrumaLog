<?php

use frontend\models\Grumascanconteo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanconteoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Grumascanconteos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascanconteo-index">

    <!-- <p>
        <?= Html::a('Create Grumascanconteo', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php echo $this->render('_search', ['model' => $searchModel]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->bodega ? $model->marcacion->bodega->nombre : 'sin marcacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->ubicacion ?? 'sin ubicacion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'value' => function ($model) {
                    return $model->marcacion->seccion ?? 'sin seccion';
                },
            ],
            [
                'attribute' => 'idmarcacion',
                'label' => 'consecutivo marcacion',
                'value' => function ($model) {
                    return $model->idmarcacion;
                },
            ],
            [
                'attribute' => 'idestado',
                'value' => function ($model) {
                    return $model->estado->nombre;
                },
            ],
            'ultimoean',
            'totalregistros',
            'totalunidades',
            [
                'attribute' => 'created_by',
                'label' => 'Usuario',
                'contentOptions' => ['data-cellvalue' => 'Usuario'],
                'value' => function ($model) {
                    return $model->usuario ? $model->usuario->username : 'Sin nombre de usuario';
                },
            ],
            'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::class,
                'template' => '{anular} {desanular} {snapshot}',
                'buttons' => [
                    'anular' => function ($url, $model) {
                        // Mostrar botón solo si estado = 1 (Terminado)
                        if ((int)$model->idestado !== 1) return '';

                        return Html::a(
                            'Anular',
                            ['anular', 'id' => $model->id],
                            [
                                'class' => 'btn btn-danger btn-xs',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => "¿Seguro que deseas ANULAR el conteo #{$model->id}?\nPasará a estado 2.",
                                ],
                            ]
                        );
                    },
                    'desanular' => function ($url, $model) {
                        // Mostrar botón solo si estado = 2 (Anulado)
                        if ((int)$model->idestado !== 2) return '';

                        return Html::a(
                            'Desanular',
                            ['desanular', 'id' => $model->id],
                            [
                                'class' => 'btn btn-warning btn-xs',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => "¿Seguro que deseas DESANULAR el conteo #{$model->id}?\nVolverá a estado 1.",
                                ],
                            ]
                        );
                    },
                    'snapshot' => function ($url, $model) {
                        $snapUrl = \yii\helpers\Url::to(['regenerar-snapshot', 'id' => $model->id]);
                        return Html::button(
                            '📸 Snapshot',
                            [
                                'class' => 'btn btn-info btn-xs btn-snapshot',
                                'data-url' => $snapUrl,
                                'data-id'  => $model->id,
                                'title'    => 'Capturar inventario Siesa para este conteo (usar antes de ver el consolidado)',
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>

<?php
$js = <<<JS
$(document).on('click', '.btn-snapshot', function () {
    var btn = $(this);
    var url = btn.data('url');
    var id  = btn.data('id');

    if (!confirm('¿Capturar snapshot de inventario Siesa para el conteo #' + id + '?\nEsto sobreescribe el snapshot anterior si existía.')) {
        return;
    }

    btn.prop('disabled', true).text('Procesando...');

    $.getJSON(url)
        .done(function (res) {
            if (res.ok) {
                alert('✅ Snapshot generado: ' + res.rows + ' filas para conteo #' + id);
            } else {
                alert('⚠️ Snapshot con errores:\n' + (res.errors || []).join('\n'));
            }
        })
        .fail(function () {
            alert('❌ Error al conectar con el servidor.');
        })
        .always(function () {
            btn.prop('disabled', false).text('📸 Snapshot');
        });
});
JS;
$this->registerJs($js);
?>
