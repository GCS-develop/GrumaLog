<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Documentos de Gasto';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documento-gasto-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('➕ Nuevo Documento', ['create-all'], ['class' => 'btn btn-success']) ?>
    </p>

    <!-- 🔽 Filtro dinámico por CO -->
    <div class="row mb-3">
        <div class="col-md-4">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['index']),
            ]); ?>

            <?= Html::dropDownList(
                'F350_ID_CO',
                $filtroCO,
                array_combine($cos, $cos),
                [
                    'prompt' => 'Todos los CO',
                    'class' => 'form-control',
                    'onchange' => 'this.form.submit()'
                ]
            ) ?>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <!-- 🔼 Fin filtro -->

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            ['attribute' => 'F350_ID_CO', 'label' => 'Co Tienda'],
            ['attribute' => 'F350_ID_TIPO_DOCTO', 'label' => 'Tipo Documento'],
            ['attribute' => 'F350_CONSEC_DOCTO', 'label' => 'Consecutivo'],
            ['attribute' => 'F350_FECHA', 'label' => 'Fecha'],
            ['attribute' => 'F350_ID_TERCERO', 'label' => 'Tercero'],
            ['attribute' => 'F350_NOTAS', 'label' => 'Notas'],

            // Estado de envío con badges
            [
                'attribute' => 'estado_envio',
                'label'     => 'Estado de Envío',
                'format'    => 'raw',
                'value' => function ($model) {
                    if (isset($model['F350_IND_ESTADO']) && $model['F350_IND_ESTADO'] == 0) {
                        return '<span class="badge bg-dark">Anulado</span>';
                    }
                    switch ($model['estado_envio']) {
                        case 0: return '<span class="badge bg-warning">Pendiente</span>';
                        case 1: return '<span class="badge bg-success">Enviado</span>';
                        case 2: return '<span class="badge bg-danger">Error</span>';
                        case 3: return '<span class="badge bg-dark">Anulado</span>';
                        default: return '<span class="badge bg-secondary">Desconocido</span>';
                    }
                }
            ],

            ['attribute' => 'ID_TRANSACCION', 'label' => 'ID Transacción'],

            // Botones de acción
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {anular} {enviar}',
                'buttons' => [
                    'view' => function ($url, $model) {
                        return Html::a('Ver Detalles', [
                            'view',
                            'id' => $model['ID_TRANSACCION']
                        ], ['class' => 'btn btn-primary btn-sm']);
                    },
                    'anular' => function ($url, $model) {
                        if ($model['estado_envio'] == 3) {
                            return '<span class="badge bg-dark">Anulado</span>';
                        }
                        if ($model['estado_envio'] == 1) {
                            return '';
                        }
                        if (in_array($model['estado_envio'], [0, 2])) {
                            return Html::a('Anular', [
                                'anular',
                                'id' => $model['ID_TRANSACCION']
                            ], [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => '¿Seguro que deseas anular este documento?',
                                    'method' => 'post',
                                ],
                            ]);
                        }
                        return '';
                    },
                    'enviar' => function ($url, $model) {
                        if ($model['estado_envio'] == 0 || $model['estado_envio'] == 2) {
                            return Html::a('Enviar a Siesa', [
                                'enviar-siesa',
                                'id' => $model['ID_TRANSACCION']
                            ], [
                                'class' => 'btn btn-warning btn-sm',
                                'data' => [
                                    'confirm' => '¿Seguro que deseas enviar este documento a Siesa?',
                                    'method' => 'post',
                                ],
                            ]);
                        }
                        return '';
                    }
                ]
            ]
        ]
    ]); ?>
</div>
