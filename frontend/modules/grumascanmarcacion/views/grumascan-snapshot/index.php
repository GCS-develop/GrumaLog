<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Snapshots de Inventario';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="grumascan-snapshot-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">📦 Snapshots de Inventario Siesa</h4>
            <small class="text-muted">
                Un snapshot es una foto del inventario Siesa en un momento dado.
                Se asigna a los conteos para que el consolidado compare contra ese inventario congelado.
            </small>
        </div>
        <?= Html::a('+ Crear Snapshot', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $msg): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>">
            <?= Html::encode(is_array($msg) ? implode(' ', $msg) : $msg) ?>
        </div>
    <?php endforeach; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => 'Mostrando {begin}-{end} de {totalCount} snapshots',
        'tableOptions' => ['class' => 'table table-bordered table-sm table-hover'],
        'columns' => [
            'id',
            [
                'label' => 'Bodega',
                'value' => function ($m) {
                    $nombre = $m->bodega ? $m->bodega->nombre : '-';
                    return "[{$m->codigoBodega}] {$nombre}";
                },
            ],
            'descripcion',
            [
                'label' => 'Items capturados',
                'attribute' => 'total_items',
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Conteos asignados',
                'value' => function ($m) {
                    return \frontend\models\Grumascanconteo::find()
                        ->where(['idSnapshot' => $m->id])
                        ->count();
                },
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'label' => 'Fecha snapshot',
                'value' => fn($m) => substr($m->fecha_snapshot, 0, 16),
            ],
            [
                'label' => 'Creado por',
                'value' => function ($m) {
                    $user = \common\models\User::findOne($m->created_by);
                    return $user ? $user->username : ($m->created_by ?? '-');
                },
            ],
            [
                'label' => 'Acciones',
                'format' => 'raw',
                'contentOptions' => ['class' => 'text-nowrap'],
                'value' => function ($m) {
                    return Html::a('Ver / Asignar', ['view', 'id' => $m->id], ['class' => 'btn btn-info btn-sm'])
                        . ' '
                        . Html::a('Consolidado', [
                            '/grumascanmarcacion/reporte-conteos/consolidado',
                            'GrumascanReporteSearch[tienda]'    => $m->codigoBodega,
                            'GrumascanReporteSearch[idSnapshot]' => $m->id,
                        ], ['class' => 'btn btn-primary btn-sm']);
                },
            ],
        ],
    ]); ?>

</div>
