<?php

use frontend\models\SiesaConectorDocumento;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\SiesaconectordocumentoSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Siesa Conector Documentos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="siesa-conector-documento-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Siesa Conector Documento', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'conector_id',
                'value' => function ($model) {
                        return $model->conector->nombre;
                    },
            ],
            'nombre',
            'descripcion:ntext',
            'id_traspaso',
            'created_by',
            //'created_at',
            //'updated_by',
            //'updated_at',
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '20%'],
                'template' => ' {view} {viewdetalle} {update} {delete}',
                'buttons' => [

                    // Botón ver
                    'view' => function ($url, $model) {
                            return Html::a(
                                '<i class="fa fa-eye"></i>',
                                ['siesaconectordocumento/view', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-info',
                                    'title' => 'Ver'
                                ]
                            );
                        },

                    // Botón ver detalles dinámicos
                    'viewdetalle' => function ($url, $model) {
                            return Html::a(
                                '<i class="fa fa-list"></i>',
                                ['siesaconectordocumento/viewdetalle', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-primary',
                                    'title' => 'Ver Detalles Dinámicos'
                                ]
                            );
                        },

                    // Botón actualizar
                    'update' => function ($url, $model) {
                            return Html::button('<i class="fa fa-edit"></i>', [
                                'value' => Url::to(['update', 'id' => $model->id]),
                                'title' => 'Actualizar',
                                'class' => 'btn btn-sm btn-warning btn_update',
                            ]);
                        },

                    // Botón eliminar
                    'delete' => function ($url, $model) {
                            return Html::a(
                                '<i class="fa fa-trash"></i>',
                                ['delete', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Eliminar',
                                    'data' => [
                                        'confirm' => '¿Está seguro de eliminar el conector? (ID: ' . $model->id . ')',
                                        'method' => 'post',
                                    ]
                                ]
                            );
                        },

                ],
            ],


        ],
    ]); ?>


</div>