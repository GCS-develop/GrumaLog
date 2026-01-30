<?php

use frontend\models\Ventasimportadas;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var frontend\models\search\VentasimportadasSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $listaProveedores */

$this->title = 'Ventas Importadas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ventasimportadas-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php elseif (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <div class="ventasimportadas-filtro">
        <h4>Consultar Ventas desde Siesa</h4>

        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['index2'],
        ]); ?>

        <div class="row">
            <div class="col-md-3">
                <?= $form->field($searchModel, 'fecha_inicio')->input('date', [
                    'value' => $searchModel->fecha_inicio,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'fecha_fin')->input('date', [
                    'value' => $searchModel->fecha_fin,
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($searchModel, 'proveedor')->widget(Select2::class, [
                    'data' => $listaProveedores,
                    'value' => $searchModel->proveedor,
                    'options' => [
                        'placeholder' => 'Seleccione un proveedor...',
                        'allowClear' => true,
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]) ?>
            </div>
            <div class="col-md-3" style="margin-top: 25px;">
                <?= Html::submitButton('Consultar (Importar desde Siesa)', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

    <br>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'codigoCentroOperacion',
            'nombreCentroOperacion',
            'fecha',
            'rowid_item_ext',
            // Puedes descomentar si deseas mostrar más:
            // 'item',
            // 'codigobarra',
            // 'descripcion',
            // 'color',
            // 'talla',
            // 'nombreproveedor',
            // 'unidades',
            // 'precio_aplicado',
            // 'total',

            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Ventasimportadas $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]); ?>

</div>
