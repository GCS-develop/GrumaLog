<?php
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Conciliación de existencias';

// ⚡ Paginación activada
$dp = new ArrayDataProvider([
    'allModels'  => $conciliacion,
    'pagination' => [
        'pageSize' => 100,
    ],
    'sort' => [
        'attributes' => [
            'item','codigo_barras','ext1','ext2',
            'bodega_original','bodega_final',
            'cant_preview','cant_bd','diff_cant',
            'costo_preview','costo_bd','diff_costo','reemplazo'
        ]
    ]
]);
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="mt-3">
    <?= Html::a('Regresar al listado', ['index'], ['class' => 'btn btn-default']) ?>
</div>

<!-- ===================== TABLA CONCILIACIÓN ===================== -->
<?= GridView::widget([
    'dataProvider' => $dp,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'item',
        'codigo_barras',
        ['attribute'=>'ext1','label'=>'Color (Ext1)'],
        ['attribute'=>'ext2','label'=>'Talla (Ext2)'],
        ['attribute'=>'bodega_original','label'=>'Bodega Original'],
        [
            'attribute'=>'bodega_final',
            'label'=>'Bodega Usada',
            'contentOptions'=>function($model){
                $cantBd = $model['cant_bd'] ?? 0;
                return $cantBd > 0
                    ? ['style'=>'background:#d4edda; color:#155724; font-weight:bold;']
                    : ['style'=>'background:#f8d7da; color:#721c24; font-weight:bold;'];
            }
        ],
        ['attribute'=>'cant_preview','label'=>'Cantidad Preview'],
        [
            'attribute'=>'cant_bd',
            'label'=>'Cantidad BD',
            'value'=>fn($model)=>$model['cant_bd'] ?? 0,
            'contentOptions'=>function($model){
                $cantBd = $model['cant_bd'] ?? 0;
                return $cantBd > 0
                    ? ['style'=>'background:#d4edda; color:#155724; font-weight:bold;']
                    : ['style'=>'background:#f8d7da; color:#721c24; font-weight:bold;'];
            }
        ],
        [
            'attribute'=>'diff_cant',
            'label'=>'Dif. Cant',
            'value'=>fn($model)=>$model['diff_cant'] ?? 0,
            'contentOptions'=>fn($model)=>
                ($model['diff_cant'] ?? 0) != 0
                ? ['style'=>'background:#fff3cd; color:#856404; font-weight:bold;']
                : []
        ],
        ['attribute'=>'costo_preview','label'=>'Costo Preview','format'=>['currency']],
        [
            'attribute'=>'costo_bd',
            'label'=>'Costo BD',
            'value'=>fn($model)=>$model['costo_bd'] ?? 0,
            'format'=>['currency'],
            'contentOptions'=>function($model){
                $cantBd = $model['cant_bd'] ?? 0;
                return $cantBd > 0
                    ? ['style'=>'background:#d4edda; color:#155724; font-weight:bold;']
                    : ['style'=>'background:#f8d7da; color:#721c24; font-weight:bold;'];
            }
        ],
        [
            'attribute'=>'diff_costo',
            'label'=>'Dif. Costo',
            'value'=>fn($model)=>$model['diff_costo'] ?? 0,
            'format'=>['currency'],
            'contentOptions'=>fn($model)=>
                abs($model['diff_costo'] ?? 0) > 0
                ? ['style'=>'background:#fff3cd; color:#856404; font-weight:bold;']
                : []
        ],
        [
            'attribute'=>'reemplazo',
            'label'=>'Bodega Reemplazo',
            'value'=>fn($model)=>$model['reemplazo'] ?: '-'
        ],
    ]
]); ?>

<!-- ===================== TABLA ELIMINADOS ===================== -->
<?php if (!empty($eliminados)): ?>
    <h2>Ítems descartados (<?= count($eliminados) ?>)</h2>

    <?php
    $dpEliminados = new ArrayDataProvider([
        'allModels'  => $eliminados,
        'pagination' => false,
    ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dpEliminados,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            ['attribute'=>'Item','label'=>'Item'],
            ['attribute'=>'CodigoBarras','label'=>'Código de Barras'],
            ['attribute'=>'Bodega','label'=>'Bodega'],
            [
                'attribute'=>'Motivo',
                'label'=>'Motivo de descarte',
                'contentOptions'=>['style'=>'color:#721c24; font-weight:bold;']
            ],
        ],
    ]); ?>
<?php endif; ?>

<?php
$enviarUrl   = Url::to(['conciliacion/enviar-siesa-vmi']);
$redirectUrl = Url::to(['index']);

$js = <<<JS
document.getElementById('btn-enviar-siesa')?.addEventListener('click', function() {
    fetch('$enviarUrl', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': yii.getCsrfToken()
        }
    }).then(r => r.json())
      .then(data => console.log('Resultado del envío (async):', data))
      .catch(err => console.error(err));

    window.location.href = '$redirectUrl';
});
JS;
$this->registerJs($js);
?>
