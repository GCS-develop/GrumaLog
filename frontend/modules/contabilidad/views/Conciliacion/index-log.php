<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\base\DynamicModel $searchModel */

$this->title = 'Facturas de Consignacion VMI Enviadas';

// Mapa proveedor_id => nombre (solo para los registros de la pagina actual)
$proveedorMap = [];
try {
    $ids = [];
    foreach ($dataProvider->getModels() as $m) {
        $idProv = trim((string)($m['proveedor_id'] ?? ''));
        if ($idProv !== '') {
            $ids[$idProv] = true;
        }
    }

    if (!empty($ids)) {
        $params = [];
        $placeholders = [];
        $i = 0;
        foreach (array_keys($ids) as $idProv) {
            $ph = ':p' . $i++;
            $placeholders[] = $ph;
            $params[$ph] = $idProv;
        }

        $rowsProv = Yii::$app->dbSiesa->createCommand("
            SELECT LTRIM(RTRIM(f106_id)) AS id, LTRIM(RTRIM(f106_descripcion)) AS nombre
            FROM t106_mc_criterios_item_mayores
            WHERE f106_id_plan = '015'
              AND LTRIM(RTRIM(f106_id)) IN (" . implode(',', $placeholders) . ")
        ", $params)->queryAll();

        foreach ($rowsProv as $r) {
            $proveedorMap[(string)$r['id']] = (string)$r['nombre'];
        }
    }
} catch (\Throwable $e) {
    // Si falla la consulta de nombres, la grilla sigue mostrando codigo/nit
}

/**
 * ✅ Formatea el error para mostrarlo "bonito" en tooltip.
 * - Si error_msg es JSON: extrae mensaje/detalle e intenta detectar Item
 * - Si no es JSON: recorta y muestra texto plano
 */
function vmiFormatErrorMsg($raw): string
{
    // ✅ Si llega array u objeto, convertir a JSON string primero
    if (is_array($raw) || is_object($raw)) {
        $raw = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $raw = (string)$raw;
    $rawTrim = trim($raw);

    if ($rawTrim === '') {
        return 'Sin detalle.';
    }

    // Intentar parsear JSON
    $data = json_decode($rawTrim, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        $msg = trim((string)($data['mensaje'] ?? ''));
        $det = $data['detalle'] ?? '';

        // ✅ Si "detalle" también viene como array, convertirlo a texto
        if (is_array($det) || is_object($det)) {
            $det = json_encode($det, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $det = trim((string)$det);

        // Intentar extraer Item del detalle
        $item = '';
        if ($det && preg_match('/Item:\s*([0-9A-Za-z\-]+)/i', $det, $m)) {
            $item = $m[1];
        }

        $out = [];
       // if ($msg !== '')  $out[] = "Mensaje: {$msg}";
        if ($item !== '') $out[] = "Item: {$item}";
        if ($det !== '')  $out[] = "Detalle: {$det}";

        $final = implode("\n", $out);
        return $final !== '' ? $final : 'Error sin mensaje.';
    }

    // No JSON → recortar
    if (mb_strlen($rawTrim) > 350) {
        $rawTrim = mb_substr($rawTrim, 0, 350) . '...';
    }

    return $rawTrim;
}
?>

<p>
    <?= Html::a('➕ Crear nuevo documento', ['index', 'nuevo' => 1], ['class'=>'btn btn-success']) ?>
</p>

<!-- ✅ FILTROS -->
<div class="card" style="padding:12px; margin-bottom:12px;">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['index-log'],
    ]); ?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($searchModel, 'proveedor_id')
                ->textInput(['placeholder'=>'Ej: 0308'])
                ->label('Proveedor ID') ?>
        </div>

        <div class="col-md-3">
            <?= $form->field($searchModel, 'proveedor_nit')
                ->textInput(['placeholder'=>'NIT'])
                ->label('NIT') ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($searchModel, 'estado')
                ->dropDownList([
                    '' => 'Todos',
                    'enviado' => 'enviado',
                    'error' => 'error',
                    'procesando' => 'procesando',
                    'anulado' => 'anulado',
                ])
                ->label('Estado') ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($searchModel, 'desde')->input('date')->label('Desde (Inicio)') ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($searchModel, 'hasta')->input('date')->label('Hasta (Fin)') ?>
        </div>

        <div class="col-md-1" style="margin-top:25px;">
            <?= Html::submitButton('Filtrar', ['class'=>'btn btn-primary btn-block']) ?>
            <?= Html::a('Limpiar', ['index-log'], ['class'=>'btn btn-default btn-block', 'style'=>'margin-top:6px;']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'rowOptions' => function($model){
        return ['id' => 'doc-'.$model['id']];
    },
    'columns' => [
        'id',

        // ✅ Columna unificada Proveedor: NIT + Código
        [
            'label' => 'Proveedor',
            'value' => function($model){
                $nit = trim((string)($model['proveedor_nit'] ?? ''));
                $cod = trim((string)($model['proveedor_id'] ?? ''));
                return $nit ? "$nit (Cod: $cod)" : $cod;
            },
            'contentOptions' => function($model){
                return empty($model['proveedor_nit'])
                    ? ['class' => 'text-danger']
                    : [];
            }
        ],
        [
            'label' => 'Proveedor Nombre',
            'value' => function($model) use ($proveedorMap) {
                $cod = trim((string)($model['proveedor_id'] ?? ''));
                $nom = trim((string)($proveedorMap[$cod] ?? ''));
                return $nom !== '' ? $nom : '(Sin nombre)';
            },
        ],

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

        // ✅ Estado con tooltip bonito
        [
            'attribute' => 'estado',
            'format' => 'raw',
            'value' => function($model) {
                $estado = (string)($model['estado'] ?? '');
                $rawErr = $model['error_msg'] ?? '';

                if ($estado === 'procesando') {
                    return "<span class='badge badge-warning estado-label'>
                                <span class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span>
                                procesando
                            </span>";
                }

                if ($estado === 'error') {
                    $errTxt = vmiFormatErrorMsg($rawErr);

                    // tooltip HTML con saltos de línea
                    $title = Html::encode($errTxt);
                    $title = nl2br($title);

                    return "<span class='badge badge-danger estado-label'
                                data-toggle='tooltip'
                                data-html='true'
                                data-container='body'
                                title='{$title}'>❌ error</span>";
                }

                $color = $estado === 'enviado' ? 'success' : 'secondary';
                $icon  = $estado === 'enviado' ? '✅' : '';
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
            'template' => '{view} {reenviar} {anular}',
            'buttons' => [
                'view' => function($url, $model) {
                    return Html::a('Ver', ['view-log', 'id'=>$model['id']], [
                        'class'=>'btn btn-primary btn-xs',
                        'title'=>'Ver detalle'
                    ]);
                },
                'reenviar' => function($url, $model) {
                    $estado = (string)($model['estado'] ?? '');
                    if ($estado === 'enviado' || $estado === 'anulado') {
                        return '';
                    }
                    return Html::button('Reenviar', [
                        'class'=>'btn btn-warning btn-xs btn-reenviar',
                        'title'=>'Reenviar a Siesa',
                        'data-id'=>$model['id'],
                    ]);
                },
                'anular' => function($url, $model) {
                    if ((string)($model['estado'] ?? '') !== 'error') {
                        return '';
                    }
                    return Html::a('Anular', [
                        '/contabilidad/conciliacion/reenviar',
                        'id' => $model['id'],
                        'anular' => 1,
                    ], [
                        'class' => 'btn btn-danger btn-xs',
                        'title' => 'Anular documento',
                        'data-confirm' => 'Seguro que deseas anular este documento en estado error?',
                        'data-method' => 'post',
                    ]);
                },
            ],
        ],
    ],
]); ?>

<?php
$reenviarUrl = Url::to(['/contabilidad/conciliacion/reenviar']);
$csrf = Yii::$app->request->getCsrfToken();

$js = <<<JS
function parseJsonOrText(resp) {
  return resp.text().then(function (txt) {
    var data = null;
    try { data = JSON.parse(txt); } catch (e) {}
    return { ok: resp.ok, status: resp.status, data: data, text: txt };
  });
}

function initTooltips() {
  if (window.jQuery && $.fn.tooltip) {
    $('[data-toggle="tooltip"]').tooltip('dispose');
    $('[data-toggle="tooltip"]').tooltip({ html: true, container: 'body', boundary: 'window' });
  }
}

document.querySelectorAll('.btn-reenviar').forEach(function (btn) {
  btn.addEventListener('click', function () {
    if (!confirm('Seguro que deseas reenviar este documento?')) return;

    var id = this.getAttribute('data-id');
    var row = document.querySelector('#doc-' + id);
    if (!row) return;

    var estadoCell = row.querySelector('.estado-label');
    var fechaCell = row.querySelector('.fecha-realizado');

    if (estadoCell) {
      estadoCell.className = 'badge badge-warning estado-label';
      estadoCell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> procesando';
      initTooltips();
    }

    fetch('$reenviarUrl?id=' + id, {
      method: 'POST',
      headers: {
        'X-CSRF-Token': '$csrf',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(parseJsonOrText)
    .then(function (res) {
      if (!res.ok || !res.data) {
        throw new Error('HTTP ' + res.status + ' | ' + (res.text || 'Sin respuesta'));
      }

      var data = res.data;
      if (!estadoCell) return;

      if (data.estado === 'enviado') {
        estadoCell.className = 'badge badge-success estado-label';
        estadoCell.innerText = 'enviado';
        estadoCell.removeAttribute('data-toggle');
        estadoCell.removeAttribute('title');
        if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
      } else if (data.estado === 'error') {
        estadoCell.className = 'badge badge-danger estado-label';
        estadoCell.innerText = 'error';
        if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
      } else if (data.estado === 'procesando') {
        estadoCell.className = 'badge badge-warning estado-label';
        estadoCell.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> procesando';
      }

      initTooltips();
      console.log('Resultado de reenvio:', data);
    })
    .catch(function (err) {
      if (estadoCell) {
        estadoCell.className = 'badge badge-danger estado-label';
        estadoCell.innerText = 'error';
        initTooltips();
      }
      alert('Error al reenviar: ' + (err && err.message ? err.message : err));
      console.error(err);
    });
  });
});

setInterval(function () {
  document.querySelectorAll('tr[id^="doc-"]').forEach(function (row) {
    var id = row.id.replace('doc-', '');
    var estadoCell = row.querySelector('.estado-label');
    var fechaCell = row.querySelector('.fecha-realizado');

    if (estadoCell && estadoCell.innerText.indexOf('procesando') !== -1) {
      fetch('$reenviarUrl?id=' + id, {
        method: 'GET',
        headers: {
          'X-CSRF-Token': '$csrf',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.estado === 'enviado') {
          estadoCell.className = 'badge badge-success estado-label';
          estadoCell.innerText = 'enviado';
          estadoCell.removeAttribute('data-toggle');
          estadoCell.removeAttribute('title');
          if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
        } else if (data.estado === 'error') {
          estadoCell.className = 'badge badge-danger estado-label';
          estadoCell.innerText = 'error';
          if (fechaCell) fechaCell.innerText = data.fecha_realizado || '';
        }
        initTooltips();
      });
    }
  });
}, 10000);

initTooltips();
JS;

$this->registerJs($js);
?>


