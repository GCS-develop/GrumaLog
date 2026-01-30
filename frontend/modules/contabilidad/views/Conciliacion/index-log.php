<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Facturas de Consignacion VMI Enviadas';
?>


<p>
    <?= Html::a('➕ Crear nuevo documento', ['index', 'nuevo' => 1], ['class'=>'btn btn-success']) ?>
</p>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'rowOptions' => function($model){
        return ['id' => 'doc-'.$model['id']]; // ID de fila único
    },
    'columns' => [
        'id',
        'proveedor_id',
        [
            'attribute' => 'fecha_inicio',
            'format' => ['date','php:Y-m-d']
        ],
        [
            'attribute' => 'fecha_fin',
            'format' => ['date','php:Y-m-d']
        ],
        [
            'attribute' => 'total_items_unicos',
            'label' => 'Items',
        ],
        [
            'attribute' => 'total_unidades',
            'label' => 'Unidades',
        ],
        [
            'attribute' => 'total_costo',
            'label' => 'Costo total',
            'format' => ['currency'],
        ],
        [
            'attribute' => 'estado',
            'format' => 'raw',
            'value' => function($model) {
                $estado = $model['estado'];
                if ($estado === 'procesando') {
                    return "<span class='badge badge-warning estado-label'>
                                <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>
                                procesando
                            </span>";
                }

                $color = $estado === 'enviado' ? 'success' : ($estado === 'error' ? 'danger' : 'secondary');
                $icon  = $estado === 'enviado' ? '✅' : ($estado === 'error' ? '❌' : '');
                return "<span class='badge badge-{$color} estado-label'>{$icon} ".Html::encode($estado)."</span>";
            }
        ],
        [
            'attribute' => 'fecha_realizado',
            'format' => ['datetime','php:Y-m-d H:i'],
            'contentOptions' => ['class'=>'fecha-realizado']
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view} {reenviar}',
            'buttons' => [
                'view' => function($url, $model) {
                    return Html::a('Ver', ['view-log', 'id'=>$model['id']], [
                        'class'=>'btn btn-primary btn-xs',
                        'title'=>'Ver detalle'
                    ]);
                },
                'reenviar' => function($url, $model) {
                    return Html::button('Reenviar', [
                        'class'=>'btn btn-warning btn-xs btn-reenviar',
                        'title'=>'Reenviar a Siesa',
                        'data-id'=>$model['id'],
                    ]);
                },
            ],
        ],
    ],
]); ?>

<?php
$reenviarUrl = Url::to(['reenviar']);
$csrf = Yii::$app->request->getCsrfToken();
$js = <<<JS
document.querySelectorAll('.btn-reenviar').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('¿Seguro que deseas reenviar este documento?')) {
            return;
        }

        let id = this.getAttribute('data-id');
        let row = document.querySelector('#doc-' + id);
        let estadoCell = row.querySelector('.estado-label');
        let fechaCell  = row.querySelector('.fecha-realizado');

        // Mostrar estado provisional con spinner
        estadoCell.className = 'badge badge-warning estado-label';
        estadoCell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> procesando';

        fetch('$reenviarUrl?id=' + id, {
            method: 'POST',
            headers: { 'X-CSRF-Token': '$csrf' }
        }).then(r => r.json())
          .then(data => {
              if (data.estado === 'enviado') {
                  estadoCell.className = 'badge badge-success estado-label';
                  estadoCell.innerText = '✅ enviado';
                  if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
              } else if (data.estado === 'error') {
                  estadoCell.className = 'badge badge-danger estado-label';
                  estadoCell.innerText = '❌ error';
                  if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
              } else if (data.estado === 'procesando') {
                  estadoCell.className = 'badge badge-warning estado-label';
                  estadoCell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> procesando';
              }
              console.log('Resultado del reenvío:', data);
          })
          .catch(err => {
              estadoCell.className = 'badge badge-danger estado-label';
              estadoCell.innerText = '❌ error';
              console.error(err);
          });
    });
});

// 🔄 Auto-refresh de filas en estado "procesando"
setInterval(() => {
    document.querySelectorAll('tr[id^="doc-"]').forEach(row => {
        let id = row.id.replace('doc-', '');
        let estadoCell = row.querySelector('.estado-label');
        let fechaCell  = row.querySelector('.fecha-realizado');
        if (estadoCell && estadoCell.innerText.includes('procesando')) {
            fetch('$reenviarUrl?id=' + id, {
                method: 'GET',
                headers: { 'X-CSRF-Token': '$csrf' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.estado === 'enviado') {
                    estadoCell.className = 'badge badge-success estado-label';
                    estadoCell.innerText = '✅ enviado';
                    if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
                }
                if (data.estado === 'error') {
                    estadoCell.className = 'badge badge-danger estado-label';
                    estadoCell.innerText = '❌ error';
                    if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
                }
            });
        }
    });
}, 10000); // cada 10 segundos
JS;
$this->registerJs($js);
?>
