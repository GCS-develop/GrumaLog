<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int|null $idpedido */

$this->title = 'Log de Borrados - Pedidos';
$this->params['breadcrumbs'][] = ['label' => 'Pedidos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .mi-gridview { font-size: 11px; }
    .accion-oc   { background-color: #f8d7da; font-weight: bold; }
    .accion-item { background-color: #fff3cd; }
    .accion-sku  { background-color: #d1ecf1; }
');
?>

<div class="pedido-log-borrados">

    <?= Alert::widget() ?>

    <?php if ($idpedido): ?>
        <div class="mb-3">
            <?= Html::a(
                '<i class="fa fa-arrow-left"></i> Volver a OC del Pedido #' . Html::encode($idpedido),
                ['/distribucion/pedidoordendecompra/index', 'idpedido' => $idpedido],
                ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <?= Html::a(
            '<i class="fa fa-list"></i> Ver todos los borrados',
            ['/distribucion/pedido/log-borrados'],
            ['class' => 'btn btn-outline-secondary btn-sm']
        ) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary'      => 'Mostrando {begin} - {end} de {totalCount} registros',
        'formatter'    => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options'      => ['class' => 'mi-gridview'],
        'rowOptions'   => function ($model) {
            if ($model->accion === 'ELIMINAR_OC')   return ['class' => 'accion-oc'];
            if ($model->accion === 'ELIMINAR_ITEM') return ['class' => 'accion-item'];
            return ['class' => 'accion-sku'];
        },
        'columns' => [
            [
                'attribute' => 'created_at',
                'label'     => 'Fecha',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'format'    => 'datetime',
            ],
            [
                'attribute' => 'created_by',
                'label'     => 'Usuario',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'value'     => function ($model) {
                    return $model->user ? $model->user->username : $model->created_by;
                },
            ],
            [
                'attribute' => 'idPedido',
                'label'     => 'Pedido',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'value'     => function ($model) {
                    return Html::a(
                        '#' . $model->idPedido,
                        ['/distribucion/pedido/log-borrados', 'idpedido' => $model->idPedido]
                    );
                },
                'format'    => 'raw',
            ],
            [
                'attribute' => 'accion',
                'label'     => 'Acción',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'value'     => function ($model) {
                    $map = [
                        'ELIMINAR_OC'   => '<span class="badge badge-danger">Eliminó OC</span>',
                        'ELIMINAR_ITEM' => '<span class="badge badge-warning">Eliminó Item</span>',
                        'ELIMINAR_SKU'  => '<span class="badge badge-info">Eliminó SKU</span>',
                    ];
                    return $map[$model->accion] ?? Html::encode($model->accion);
                },
                'format'    => 'raw',
            ],
            [
                'attribute' => 'codigoCO',
                'label'     => 'C.O.',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'tipoDocumentoOC',
                'label'     => 'Serie',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'consecutivoOC',
                'label'     => 'Consecutivo OC',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'itemCodigo',
                'label'     => 'Item',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'colorCodigo',
                'label'     => 'Color',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'tallaCodigo',
                'label'     => 'Talla',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'bodegaCodigo',
                'label'     => 'Bodega',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
            ],
            [
                'attribute' => 'unidadesAntes',
                'label'     => 'Unidades',
                'hAlign'    => 'right',
                'vAlign'    => 'middle',
                'format'    => ['decimal', 0],
            ],
        ],
    ]) ?>

</div>
