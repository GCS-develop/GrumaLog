<?php
use yii\helpers\Html;
use yii\grid\GridView;

/** @var $dp yii\data\ArrayDataProvider */
/** @var $idProv string */
/** @var $provNom string */
/** @var $fini string */
/** @var $ffin string */
/** @var $totUnidades int */
/** @var $totCosto float */

$this->title = 'Revisión de consulta VMI (Preview)';
?>
<h1><?= Html::encode($this->title) ?></h1>

<div class="row" style="margin-bottom:12px;">
  <div class="col-sm-6">
    <?= Html::a('Volver', ['index'], ['class'=>'btn btn-default']) ?>
  </div>

  <div class="col-sm-6" style="text-align:right;">
    <!-- Si quieres ocultar export aquí, quita este bloque -->
    <?= Html::beginForm(['export'], 'post', ['style'=>'display:inline-block;']) ?>
      <input type="hidden" name="from_preview" value="1">
      <?= Html::submitButton('Exportar (xlsx)', ['class'=>'btn btn-primary']) ?>
    <?= Html::endForm(); ?>
  </div>
</div>

<?= GridView::widget([
  'dataProvider' => $dp,
  'tableOptions' => ['class'=>'table table-striped table-bordered table-hover table-sm'],
  'showFooter'   => true,
  'columns' => [
    ['class'=>'yii\grid\SerialColumn'],
    ['attribute'=>'Fecha','format'=>['date','php:Y-m-d'], 'footer'=>'Totales:'],

    // IDs / descripciones de bodega
    ['attribute'=>'BodegaId','label'=>'bodega_id'],
    ['attribute'=>'Bodega','label'=>'bodega'],

    // Item y código
    ['attribute'=>'Item','label'=>'item'],
    ['attribute'=>'Codigo','label'=>'codigo'],

    // 🔎 NUEVAS columnas para validar Color/Talla
    [
      'attribute'=>'Ext1',
      'label'=>'Extension 1',
      'value'=>function($r){ return $r['Ext1'] ?? ''; },
    ],
    [
      'attribute'=>'Ext2',
      'label'=>'Extension 2',
      'value'=>function($r){ return $r['Ext2'] ?? ''; },
    ],

    ['attribute'=>'unidad_medida','label'=>'unidad_medida'],

    [
      'attribute'=>'cantidad_base','label'=>'cantidad_base','format'=>['decimal',0],
      'footer' => Yii::$app->formatter->asDecimal($totUnidades, 0),
      'contentOptions'=>['style'=>'text-align:right'],
      'footerOptions'=>['style'=>'text-align:right; font-weight:bold']
    ],
    [
      // recuerda: en el controlador redondeamos hacia arriba al exportar
      'attribute'=>'precio_unitario','label'=>'precio_unitario','format'=>['decimal',0],
      'contentOptions'=>['style'=>'text-align:right']
    ],
    [
      'attribute'=>'costo_total','label'=>'costo_total','format'=>['decimal',0],
      'footer' => Yii::$app->formatter->asDecimal($totCosto, 0),
      'contentOptions'=>['style'=>'text-align:right'],
      'footerOptions'=>['style'=>'text-align:right; font-weight:bold']
    ],
  ],
]); ?>

<div class="well" style="margin-top:12px;">
  <strong>Resumen:</strong>
  <?= Html::encode($provNom) ?> |
  Rango: <?= Html::encode($fini) ?> → <?= Html::encode($ffin) ?> |
  <strong>Unidades:</strong> <?= Yii::$app->formatter->asDecimal($totUnidades, 0) ?> |
  <strong>Costo total:</strong> <?= Yii::$app->formatter->asDecimal($totCosto, 0) ?>
</div>
