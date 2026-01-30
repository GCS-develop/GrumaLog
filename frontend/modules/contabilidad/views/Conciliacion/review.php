<?php
use yii\helpers\Html;
use yii\grid\GridView;

/** @var $dataProvider yii\data\ActiveDataProvider */
/** @var $consultaId int */

$this->title = 'Revisión de consulta VMI (Persistido)';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>
  <?= Html::a('Volver', ['index'], ['class'=>'btn btn-default']) ?>
  <?= Html::beginForm(['cancel','id'=>$consultaId], 'post', ['style'=>'display:inline']); ?>
    <?= Html::submitButton('Cancelar (limpiar)', [
        'class'=>'btn btn-danger',
        'data'=>['confirm'=>'¿Cancelar y limpiar esta consulta persistida?']
    ]) ?>
  <?= Html::endForm(); ?>
</p>

<?= GridView::widget([
  'dataProvider' => $dataProvider,
  'columns' => [
    ['class'=>'yii\grid\SerialColumn'],
    ['attribute'=>'Fecha','format'=>['date','php:Y-m-d']],
    'centro_operacion',
    'tipo_documento',
    ['attribute'=>'consec_documento','format'=>'text'],
    ['attribute'=>'item','format'=>'text'],
    ['attribute'=>'codigo','format'=>'text'],
    ['attribute'=>'talla','format'=>'text'],
    ['attribute'=>'color','format'=>'text'],
    ['attribute'=>'unidad_medida','format'=>'text'],
    ['attribute'=>'bodega','format'=>'text'],
    ['attribute'=>'motivo','format'=>'text'],
    ['attribute'=>'cantidad_base','format'=>['decimal',0]],
    ['attribute'=>'precio_unitario','format'=>['decimal',6]],
  ],
]); ?>
