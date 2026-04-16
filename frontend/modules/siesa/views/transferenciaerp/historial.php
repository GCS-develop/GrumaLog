<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Historial de Transferencias Eliminadas';
$this->params['breadcrumbs'][] = ['label' => 'Integración ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-1"></i>
            Historial de Transferencias Eliminadas
        </h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver', ['index'], ['class' => 'btn btn-sm btn-secondary']) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'tableOptions' => ['class' => 'table table-bordered table-striped table-sm'],
            'columns' => [
                [
                    'attribute' => 'idTransferenciaerpBorrada',
                    'label'     => 'ID Borrada',
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'attribute' => 'idTransferenciaerpNueva',
                    'label'     => 'ID Nueva',
                    'headerOptions' => ['style' => 'width:80px'],
                    'value' => function ($m) {
                        return $m->idTransferenciaerpNueva ?? '—';
                    },
                ],
                [
                    'attribute' => 'accion',
                    'label'     => 'Acción',
                    'headerOptions' => ['style' => 'width:160px'],
                    'value' => function ($m) {
                        $map = [
                            'MANUAL_USUARIO'   => '<span class="badge badge-danger">Manual usuario</span>',
                            'AUTO_REGENERACION' => '<span class="badge badge-warning text-dark">Auto regeneración</span>',
                        ];
                        return $map[$m->accion] ?? '<span class="badge badge-secondary">' . $m->accion . '</span>';
                    },
                    'format' => 'raw',
                    'filter' => [
                        'MANUAL_USUARIO'    => 'Manual usuario',
                        'AUTO_REGENERACION' => 'Auto regeneración',
                    ],
                ],
                [
                    'attribute' => 'descripcion',
                    'label'     => 'Descripción (OC / Factura)',
                ],
                [
                    'attribute' => 'origen',
                    'label'     => 'Origen',
                    'headerOptions' => ['style' => 'width:70px'],
                    'value' => function ($m) {
                        $map = ['C' => 'Conteo', 'T' => 'Tiendas', 'E' => 'Crossdocking', 'R' => 'Traspaso CDSC'];
                        return isset($map[$m->origen]) ? $m->origen . ' – ' . $map[$m->origen] : $m->origen;
                    },
                ],
                [
                    'attribute' => 'numeroRegistros',
                    'label'     => 'Registros',
                    'headerOptions' => ['style' => 'width:80px'],
                ],
                [
                    'attribute' => 'enviadoWS',
                    'label'     => 'Enviado SIESA',
                    'headerOptions' => ['style' => 'width:110px'],
                    'value' => function ($m) {
                        return $m->enviadoWS ? '<span class="badge badge-success">Sí</span>'
                                             : '<span class="badge badge-secondary">No</span>';
                    },
                    'format' => 'raw',
                    'filter' => [1 => 'Sí', 0 => 'No'],
                ],
                [
                    'attribute' => 'created_at',
                    'label'     => 'Fecha borrado',
                    'headerOptions' => ['style' => 'width:140px'],
                    'filter'    => false,
                ],
                [
                    'attribute' => 'created_by',
                    'label'     => 'Borrado por',
                    'headerOptions' => ['style' => 'width:140px'],
                    'value' => function ($m) {
                        return $m->userBorrado ? $m->userBorrado->username : $m->created_by;
                    },
                ],
                [
                    'label'  => 'Log SIESA',
                    'format' => 'raw',
                    'headerOptions' => ['style' => 'width:90px; text-align:center'],
                    'contentOptions' => ['style' => 'text-align:center'],
                    'value' => function ($m) {
                        return Html::a(
                            '<i class="fas fa-search"></i>',
                            ['log-borrada', 'id' => $m->idTransferenciaerpBorrada],
                            ['class' => 'btn btn-xs btn-info', 'title' => 'Ver logs SIESA de la transferencia borrada']
                        );
                    },
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
