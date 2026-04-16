<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = 'Historial Global de Borrados de Conteo';

$th = 'background:#4a5568; color:#fff;';

$etiquetas = [
    'BORRAR_TODOS'      => ['label' => 'Borrar TODOS',        'class' => 'label-danger'],
    'REDUCIR_ITEM'      => ['label' => 'Reducir Item',        'class' => 'label-warning'],
    'REDUCIR_FILA'      => ['label' => 'Reducir Fila',        'class' => 'label-warning'],
    'REDUCIR_TALLA'     => ['label' => 'Reducir Talla',       'class' => 'label-warning'],
    'ELIMINAR_FILA'     => ['label' => 'Eliminar Fila',       'class' => 'label-danger'],
    'REDUCIR_TALLA_PDA' => ['label' => 'Reducir Talla (PDA)', 'class' => 'label-warning'],
    'ELIMINAR_SCAN_PDA' => ['label' => 'Eliminar Scan (PDA)', 'class' => 'label-danger'],
    'ELIMINAR_SCAN'     => ['label' => 'Eliminar Scan',       'class' => 'label-danger'],
];
?>

<div class="container-fluid">

<h3 style="margin-bottom:16px;">
    <i class="glyphicon glyphicon-trash"></i>
    Historial Global de Borrados de Conteo
</h3>

<?php $form = ActiveForm::begin([
    'method'  => 'get',
    'action'  => ['indexborradosglobal'],
    'options' => ['class' => 'form-inline', 'style' => 'margin-bottom:16px; background:#f5f5f5; padding:14px; border-radius:6px; border:1px solid #ddd;'],
]); ?>

<div style="display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end;">

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">OC (consecutivo)</label>
        <?= Html::activeTextInput($searchModel, 'consecutivoOC', [
            'class'       => 'form-control input-sm',
            'placeholder' => 'Ej: 6427',
            'style'       => 'width:100px;',
        ]) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">C.O.</label>
        <?= Html::activeTextInput($searchModel, 'codigoCO', [
            'class'       => 'form-control input-sm',
            'placeholder' => 'Ej: 001',
            'style'       => 'width:80px;',
        ]) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">Acción</label>
        <?= Html::activeDropDownList($searchModel, 'accion', [
            ''                  => '— Todas —',
            'BORRAR_TODOS'      => 'Borrar TODOS',
            'REDUCIR_ITEM'      => 'Reducir Item',
            'REDUCIR_FILA'      => 'Reducir Fila',
            'REDUCIR_TALLA'     => 'Reducir Talla',
            'ELIMINAR_FILA'     => 'Eliminar Fila',
            'REDUCIR_TALLA_PDA' => 'Reducir Talla (PDA)',
            'ELIMINAR_SCAN_PDA' => 'Eliminar Scan (PDA)',
            'ELIMINAR_SCAN'     => 'Eliminar Scan',
        ], ['class' => 'form-control input-sm', 'style' => 'width:170px;']) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">Item</label>
        <?= Html::activeTextInput($searchModel, 'item', [
            'class'       => 'form-control input-sm',
            'placeholder' => 'Código item',
            'style'       => 'width:110px;',
        ]) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">Usuario</label>
        <?= Html::activeTextInput($searchModel, 'username', [
            'class'       => 'form-control input-sm',
            'placeholder' => 'Nombre usuario',
            'style'       => 'width:130px;',
        ]) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">Desde</label>
        <?= Html::activeInput('date', $searchModel, 'fechaDesde', [
            'class' => 'form-control input-sm',
            'style' => 'width:130px;',
        ]) ?>
    </div>

    <div class="form-group">
        <label style="display:block; font-size:12px; color:#555;">Hasta</label>
        <?= Html::activeInput('date', $searchModel, 'fechaHasta', [
            'class' => 'form-control input-sm',
            'style' => 'width:130px;',
        ]) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton('<i class="glyphicon glyphicon-search"></i> Filtrar', [
            'class' => 'btn btn-primary btn-sm',
        ]) ?>
        <?= Html::a('<i class="glyphicon glyphicon-remove"></i> Limpiar', ['indexborradosglobal'], [
            'class' => 'btn btn-default btn-sm',
        ]) ?>
    </div>

</div>

<?php ActiveForm::end(); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'tableOptions' => ['class' => 'table table-bordered table-striped table-condensed', 'style' => 'font-size:13px;'],
    'layout'       => "{summary}\n{items}\n{pager}",
    'columns' => [

        [
            'attribute'      => 'created_at',
            'label'          => 'Fecha / Hora',
            'format'         => 'raw',
            'value'          => function ($model) {
                return Html::encode($model->created_at);
            },
            'headerOptions'  => ['style' => $th . 'white-space:nowrap;'],
            'contentOptions' => ['style' => 'white-space:nowrap; font-size:12px;'],
        ],

        [
            'label'         => 'OC',
            'format'        => 'raw',
            'value'         => function ($model) {
                $oc = trim(
                    ($model->tipoDocumentoOC ?? '') . '-' .
                    ($model->codigoCO        ?? '') . '-' .
                    ($model->consecutivoOC   ?? ''),
                    '-'
                );
                return '<strong>' . Html::encode($oc) . '</strong>';
            },
            'headerOptions' => ['style' => $th],
        ],

        [
            'attribute'     => 'accion',
            'label'         => 'Acción',
            'format'        => 'raw',
            'value'         => function ($model) use ($etiquetas) {
                $e = $etiquetas[$model->accion] ?? ['label' => $model->accion, 'class' => 'label-default'];
                return '<span class="label ' . $e['class'] . '">' . Html::encode($e['label']) . '</span>';
            },
            'headerOptions' => ['style' => $th],
        ],

        [
            'label'         => 'Usuario',
            'format'        => 'raw',
            'value'         => function ($model) {
                return Html::encode($model->user ? $model->user->username : 'ID:' . $model->created_by);
            },
            'headerOptions' => ['style' => $th],
        ],

        [
            'attribute'     => 'item',
            'label'         => 'Item',
            'value'         => function ($model) { return $model->getAttribute('item') ?? '—'; },
            'headerOptions' => ['style' => $th],
        ],

        [
            'attribute'     => 'color',
            'label'         => 'Color',
            'value'         => function ($model) { return $model->getAttribute('color') ?? '—'; },
            'headerOptions' => ['style' => $th],
        ],

        [
            'attribute'     => 'talla',
            'label'         => 'Talla',
            'value'         => function ($model) { return $model->getAttribute('talla') ?? '—'; },
            'headerOptions' => ['style' => $th],
        ],

        [
            'attribute'      => 'unidadesAntes',
            'label'          => 'Antes',
            'contentOptions' => ['style' => 'text-align:right;'],
            'headerOptions'  => ['style' => $th . 'text-align:right;'],
        ],

        [
            'attribute'      => 'unidadesBorradas',
            'label'          => 'Borradas',
            'format'         => 'raw',
            'value'          => function ($model) {
                return '<span style="color:#e53e3e; font-weight:bold;">' . (int)$model->unidadesBorradas . '</span>';
            },
            'contentOptions' => ['style' => 'text-align:right;'],
            'headerOptions'  => ['style' => $th . 'text-align:right;'],
        ],

        [
            'label'          => 'Detalle',
            'format'         => 'raw',
            'value'          => function ($model) {
                return Html::a(
                    '<i class="glyphicon glyphicon-list-alt"></i> Ver',
                    ['/programacion/conteoentregamercancia/historialborrados',
                     'idprogramacion' => $model->idProgramacionEntregaMercancia],
                    ['class' => 'btn btn-xs btn-info', 'title' => 'Ver historial de borrados de esta programación']
                );
            },
            'headerOptions'  => ['style' => $th . 'width:70px; text-align:center;'],
            'contentOptions' => ['style' => 'text-align:center;'],
        ],

    ],
]); ?>

</div>
