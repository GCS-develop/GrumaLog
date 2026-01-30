<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Consulta VMI';

$meta = Yii::$app->session->get('vmi_preview_meta', []);
$selProv = $meta['idProv']  ?? '';   // NIT
$selName = $meta['provNom'] ?? '';  // Razón social / sucursal
$fini    = $meta['fini']    ?? '';
$ffin    = $meta['ffin']    ?? '';
$totU    = (int)($meta['totU'] ?? 0);
$totC    = (float)($meta['totC'] ?? 0);
$items   = (int)($meta['itemsCount'] ?? 0);

/** @var $proveedores array */
/** @var $show bool */

$this->registerCssFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<h1 class="page-title"><?= Html::encode($this->title) ?></h1>

<?php foreach (Yii::$app->session->getAllFlashes() as $type=>$msg): ?>
  <div class="alert alert-<?= $type ?>" role="alert" style="margin-bottom:14px;">
    <?= Html::encode($msg) ?>
  </div>
<?php endforeach; ?>

<style>
  .page-title{ margin: 0 0 10px; }
  .panel { border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
  .panel-heading{ font-weight:600; }
  .summary-strip{ margin: 16px 0 14px; }
  .summary-card{
    background:#f8fafc; border:1px solid #e6eaf0; border-radius:12px;
    padding:12px 14px; box-shadow:0 1px 2px rgba(0,0,0,.04); height:70px;
  }
  .summary-card .title{
    display:block; font-size:11px; color:#6b7280; letter-spacing:.02em;
    text-transform:uppercase; margin-bottom:4px
  }
  .summary-card .value{
    display:block; font-weight:600; font-size:15px; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .btn + .btn { margin-left:8px; }
  .section-gap { margin-top: 18px; }
</style>

<div class="panel panel-default">
  <div class="panel-heading">
    <strong>Parámetros de consulta</strong>
    <small class="text-muted"> Busca el proveedor por NIT o razón social y define el rango de fechas</small>
  </div>
  <div class="panel-body">

    <div class="row">
      <div class="col-sm-12">
        <?= Html::dropDownList('proveedor_id', $selProv, $proveedores, [
          'class'=>'form-control', 'id'=>'prov', 'prompt'=>'-- Seleccione proveedor (NIT) --'
        ]) ?>
      </div>
    </div>

    <div class="row" style="margin-top:10px;">
      <div class="col-sm-6">
        <input type="date" id="fi" class="form-control" value="<?= Html::encode($fini) ?>" placeholder="Fecha Inicio">
      </div>
      <div class="col-sm-6">
        <input type="date" id="ff" class="form-control" value="<?= Html::encode($ffin) ?>" placeholder="Fecha Fin">
      </div>
    </div>

    <div class="row" style="margin-top:12px; margin-bottom:10px;">
      <div class="col-sm-12">
        <?= Html::button('Consultar', ['class'=>'btn btn-primary', 'id'=>'btnPreview']) ?>
        <?= Html::button('Procesar...', ['class'=>'btn btn-default', 'id'=>'btnProcesar']) ?>
      </div>
    </div>

    <?php if ($show && !empty($meta)): ?>
      <div class="summary-strip">
        <div class="row">
          <div class="col-sm-3">
            <div class="summary-card">
              <span class="title">Proveedor</span>
              <span class="value" title="<?= Html::encode($selName) ?>">
                <?= Html::encode($selName ?: '—') ?>
              </span>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="summary-card">
              <span class="title">Rango</span>
              <span class="value"><?= Html::encode($fini) ?> → <?= Html::encode($ffin) ?></span>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="summary-card">
              <span class="title">Unidades</span>
              <span class="value"><?= Yii::$app->formatter->asDecimal($totU, 0) ?></span>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="summary-card">
              <span class="title">Total costo</span>
              <span class="value">$ <?= Yii::$app->formatter->asDecimal($totC, 2) ?></span>
            </div>
          </div>
          <div class="col-sm-2">
            <div class="summary-card">
              <span class="title">Ítems</span>
              <span class="value"><?= Yii::$app->formatter->asDecimal($items, 0) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Datos para documento -->
      <div class="panel panel-default section-gap">
        <div class="panel-heading"><strong>Datos para documento</strong></div>
        <div class="panel-body">
          <?= Html::beginForm(['export'], 'post', ['id'=>'form-conciliacion']); ?>

            <div class="row">
              <div class="col-sm-4">
                <label>Tercero proveedor (NIT) *</label>
                <input class="form-control"
                       name="tercero_proveedor"
                       value="<?= Html::encode($meta['nit'] ?? '') ?>"
                       readonly required>
              </div>
              <div class="col-sm-4">
                <label>Prefijo doc. proveedor</label>
                <input class="form-control" name="prefijo_doc_prov" placeholder="Ej: 001">
              </div>
              <div class="col-sm-4">
                <label>Consecutivo doc. proveedor</label>
                <input class="form-control" name="consec_doc_prov" placeholder="Ej: 11234">
              </div>
            </div>

            <div class="row" style="margin-top:10px;">
              <div class="col-sm-4">
                <label>Sucursal proveedor</label>
                <input class="form-control"
                       name="sucursal_proveedor"
                       value="<?= Html::encode($meta['sucursal'] ?? '001') ?> "
                       readonly>
              </div>
            </div>

            <div class="row" style="margin-top:10px;">
              <div class="col-sm-4">
                <label>Fecha del documento *</label>
                <input type="date"
                       class="form-control"
                       name="fecha_doc"
                       id="fdoc"
                       value="<?= date('Y-m-d') ?>"
                       required>
              </div>
              <div class="col-sm-4">
                <label>Fecha documento proveedor *</label>
                <input type="date"
                       class="form-control"
                       name="fecha_doc_prov"
                       id="fdocprov"
                       value="<?= date('Y-m-d') ?>"
                       required>
              </div>
            </div>

            <div class="row" style="margin-top:10px;">
              <div class="col-sm-4">
                <label>Tipo de proveedor (Fijo)</label>
                <input class="form-control" value="0210" readonly>
              </div>
              <div class="col-sm-4">
                <label>Tipo documento (Fijo)</label>
                <input class="form-control" value="ECG" readonly>
              </div>
              <div class="col-sm-4">
                <label>Centro operación (Fijo)</label>
                <input class="form-control" value="002" readonly>
              </div>
            </div>

            <div class="row" style="margin-top:10px;">
              <div class="col-sm-4">
                <label>Condición de pago (Fijo)</label>
                <input class="form-control" value="015" readonly>
              </div>
              <div class="col-sm-8">
                <label>Cuotas (auto)</label>
                <input class="form-control" id="cuotasAuto" value="Se calculan como FECHA_DOC + 15 días" readonly>
                <!-- campos ocultos -->
                <input type="hidden" name="fecha_vencimiento" id="fvenc">
                <input type="hidden" name="fecha_pronto_pago" id="fpronto">
              </div>
            </div>

            <div class="row" style="margin-top:14px;">
              <div class="col-sm-12">
                <?= Html::submitButton('Exportar (xlsx)', [
                    'class' => 'btn btn-success',
                    'formaction' => Url::to(['export']),
                    'formmethod' => 'post'
                ]) ?>
                <?= Html::button('Enviar a Siesa', [
                    'class' => 'btn btn-primary',
                    'id'    => 'btnEnviarSiesa',
                    'type'  => 'button'
                ]) ?>
                <?= Html::a('Ajustar existencia', ['conciliacion'], [
                    'class' => 'btn btn-warning'
                ]) ?>
              </div>
            </div>

          <?= Html::endForm(); ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php
$previewUrl  = Url::to(['preview']);
$procesarUrl = Url::to(['preview','inline'=>1]);
$sendUrl     = Url::to(['enviar-siesa-vmi']);   // acción de envío
$redirectUrl = Url::to(['index-log']);              // a dónde ir después (nuevo doc)

$js = <<<JS
// Select2
jQuery(function(){
  if (jQuery.fn.select2) {
    jQuery('#prov').select2({ width:'100%', placeholder:'-- Seleccione proveedor (NIT) --', language:'es' });
  }
  var prev = '{$selProv}';
  if (prev) { jQuery('#prov').val(prev).trigger('change'); }
});

// Helpers CSRF
function getCsrfPair(){ 
  var p = (window.yii && yii.getCsrfParam && yii.getCsrfParam()) || jQuery('meta[name="csrf-param"]').attr('content');
  var t = (window.yii && yii.getCsrfToken && yii.getCsrfToken()) || jQuery('meta[name="csrf-token"]').attr('content');
  return {param:p, token:t};
}
function appendCsrf(\$f){
  var pair = getCsrfPair();
  if(pair.param && pair.token){ \$f.append(jQuery('<input/>',{type:'hidden',name:pair.param,value:pair.token})); }
}
function proveedorNombre(){
  var op = jQuery('#prov option:selected');
  return op.length ? op.text() : '';
}

// Botón Consultar
jQuery('#btnPreview').on('click', function(){
  var f = jQuery('<form/>', {method:'post', action:'{$previewUrl}'}); appendCsrf(f);
  f.append(jQuery('<input/>',{type:'hidden',name:'proveedor_id',value:jQuery('#prov').val()})); 
  f.append(jQuery('<input/>',{type:'hidden',name:'proveedor_nombre',value:proveedorNombre()}));
  f.append(jQuery('<input/>',{type:'hidden',name:'fecha_inicio',value:jQuery('#fi').val()}));
  f.append(jQuery('<input/>',{type:'hidden',name:'fecha_fin',value:jQuery('#ff').val()}));
  jQuery('body').append(f); f.trigger('submit');
});

// Botón Procesar
jQuery('#btnProcesar').on('click', function(){
  var f = jQuery('<form/>', {method:'post', action:'{$procesarUrl}'}); appendCsrf(f);
  f.append(jQuery('<input/>',{type:'hidden',name:'proveedor_id',value:jQuery('#prov').val()}));
  f.append(jQuery('<input/>',{type:'hidden',name:'proveedor_nombre',value:proveedorNombre()}));
  f.append(jQuery('<input/>',{type:'hidden',name:'fecha_inicio',value:jQuery('#fi').val()}));
  f.append(jQuery('<input/>',{type:'hidden',name:'fecha_fin',value:jQuery('#ff').val()}));
  jQuery('body').append(f); f.trigger('submit');
});

// --- Helpers fechas ---
function ymdFromDateInput(val){
  if(!val) return '';
  var d = new Date(val);
  if(isNaN(d.getTime())) return '';
  var yy = d.getFullYear().toString(),
      mm = ('0'+(d.getMonth()+1)).slice(-2),
      dd = ('0'+d.getDate()).slice(-2);
  return yy+mm+dd;
}

function add15DaysYMD(val){
  if(!val) return '';
  var d = new Date(val);
  if(isNaN(d.getTime())) return '';
  d.setDate(d.getDate()+15);
  var yy = d.getFullYear().toString(),
      mm = ('0'+(d.getMonth()+1)).slice(-2),
      dd = ('0'+d.getDate()).slice(-2);
  return yy+mm+dd;
}

function refreshCuotas(){
  var baseISO = jQuery('#fdoc').val(); // YYYY-MM-DD
  var plus    = add15DaysYMD(baseISO);
  if(plus){
    jQuery('#cuotasAuto').val('Vencimiento: '+plus+' | Pronto pago: '+plus);
    jQuery('#fvenc').val(plus);
    jQuery('#fpronto').val(plus);
  }else{
    jQuery('#cuotasAuto').val('Se calculan como FECHA_DOC + 15 días');
    jQuery('#fvenc').val('');
    jQuery('#fpronto').val('');
  }
}
jQuery(document).on('change', '#fdoc', refreshCuotas);

// Al cargar, calcular cuotas automáticamente
jQuery(refreshCuotas);

// Botón Enviar a Siesa (background + redirigir)
jQuery('#btnEnviarSiesa').on('click', function(e){
  e.preventDefault();
  var f = jQuery('#form-conciliacion');
  var data = f.serialize();

  jQuery.post('{$sendUrl}', data)
    .done(function(res){
      console.log('Envío terminado en background', res);
      localStorage.setItem('msg_siesa', 'Documento enviado a Siesa.');
    })
    .fail(function(){
      localStorage.setItem('msg_siesa', 'Error al enviar a Siesa.');
    });

  // Redirige inmediatamente
  window.location.href = '{$redirectUrl}';
});
JS;
$this->registerJs($js);
