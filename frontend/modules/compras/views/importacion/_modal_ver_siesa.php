<?php
use yii\helpers\Html;
use yii\helpers\Url;
/**
 * @var yii\web\View               $this
 * @var frontend\models\Comprasimportacion $model
 * @var array                      $logs   — filas de comprasimportacion_siesa_log
 */
?>

<div class="modal fade" id="modalVerSiesa" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

      <!-- Cabecera -->
      <div class="modal-header" style="background:#343a40;color:#fff;">
        <h5 class="modal-title">
          <i class="fas fa-file-code mr-2"></i>
          Historial de envíos a SIESA — Importación #<?= $model->id ?>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <!-- ===== BANNER CONSECUTIVO ===== -->
        <?php if ($model->estadoSiesa == 1 && $model->tipoDocSiesa && $model->numDocSiesa): ?>
        <div class="alert alert-success d-flex align-items-center py-3 mb-4"
             style="border-left:6px solid #28a745; background:#f0fff4;">
          <i class="fas fa-check-circle fa-3x mr-3 text-success"></i>
          <div>
            <div class="text-muted small mb-1">Documento creado en SIESA</div>
            <div style="font-size:2.2rem;font-weight:800;letter-spacing:3px;color:#155724;line-height:1;">
              <?= Html::encode($model->tipoDocSiesa . ' — ' . $model->numDocSiesa) ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===== RESUMEN 4 COLUMNAS ===== -->
        <div class="row text-center mb-3" style="border:1px solid #dee2e6;border-radius:6px;overflow:hidden;">
          <div class="col-md-3 py-3" style="background:#f8f9fa;border-right:1px solid #dee2e6;">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:1px;">
              Importación
            </div>
            <div class="font-weight-bold" style="font-size:1.6rem;">#<?= $model->id ?></div>
          </div>
          <div class="col-md-3 py-3" style="border-right:1px solid #dee2e6;">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:1px;">
              Tipo Documento
            </div>
            <div class="font-weight-bold" style="font-size:1.4rem;">
              <?= $model->tipoDocSiesa
                    ? Html::encode($model->tipoDocSiesa)
                    : '<span class="text-muted">—</span>' ?>
            </div>
          </div>
          <div class="col-md-3 py-3" style="background:#f8f9fa;border-right:1px solid #dee2e6;">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:1px;">
              Consecutivo
            </div>
            <div class="font-weight-bold" style="font-size:1.4rem;color:#28a745;">
              <?= $model->numDocSiesa
                    ? Html::encode($model->numDocSiesa)
                    : '<span class="text-muted">—</span>' ?>
            </div>
          </div>
          <div class="col-md-3 py-3">
            <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:1px;">
              Estado
            </div>
            <div class="mt-1">
              <?php if ($model->estadoSiesa == 1): ?>
                <span class="badge badge-success" style="font-size:0.9rem;padding:6px 14px;">
                  <i class="fas fa-check-circle mr-1"></i>Exitoso
                </span>
              <?php elseif ($model->estadoSiesa === 0 || $model->estadoSiesa == '0'): ?>
                <span class="badge badge-danger" style="font-size:0.9rem;padding:6px 14px;">
                  <i class="fas fa-times-circle mr-1"></i>Error
                </span>
              <?php else: ?>
                <span class="badge badge-secondary" style="font-size:0.9rem;padding:6px 14px;">
                  Sin enviar
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ===== ASIGNAR CONSECUTIVO (si falta) ===== -->
        <?php if ($model->estadoSiesa == 1 && empty($model->tipoDocSiesa)): ?>
        <div class="alert alert-warning py-2 mt-3 mb-0" id="panel-asignar-consec">
          <div class="d-flex align-items-center justify-content-between">
            <span>
              <i class="fas fa-exclamation-triangle mr-1"></i>
              <strong>Consecutivo SIESA no registrado.</strong>
              Busca el documento 2CA creado en SIESA y asígnalo a esta importación.
            </span>
            <button type="button" class="btn btn-sm btn-warning ml-2" id="btn-buscar-oc-siesa">
              <i class="fas fa-search mr-1"></i>Buscar en SIESA
            </button>
          </div>
          <!-- Tabla de resultados -->
          <div id="oc-siesa-results" class="mt-2 d-none">
            <p class="mb-1 small text-dark"><strong>Últimos documentos 2CA en SIESA — clic para asignar:</strong></p>
            <div id="oc-siesa-list"></div>
            <div id="oc-siesa-msg" class="mt-1 small"></div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ===== HISTORIAL ===== -->
        <h6 class="mb-2 mt-3">
          <i class="fas fa-history mr-1 text-secondary"></i>
          Historial de envíos
          <span class="badge badge-secondary ml-1"><?= count($logs) ?></span>
        </h6>

        <?php if (!empty($logs)): ?>
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
            <thead class="thead-dark">
              <tr>
                <th class="text-center" style="width:36px;">#</th>
                <th>Fecha Envío</th>
                <th class="text-center" style="width:80px;">Estado</th>
                <th class="text-center" style="width:100px;">Doc SIESA</th>
                <th>Mensaje</th>
                <th class="text-center" style="width:90px;">Usuario</th>
                <th class="text-center" style="width:50px;">JSON</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $i => $log): ?>
              <!-- Fila resumen -->
              <tr class="<?= $log['estadoEnvio'] == 1 ? 'table-success' : 'table-danger' ?>" style="opacity:0.92;">
                <td class="text-center font-weight-bold"><?= count($logs) - $i ?></td>
                <td><?= Html::encode(isset($log['fechaEnvio']) ? substr($log['fechaEnvio'], 0, 19) : '') ?></td>
                <td class="text-center">
                  <?php if ($log['estadoEnvio'] == 1): ?>
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>OK</span>
                  <?php else: ?>
                    <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Error</span>
                  <?php endif; ?>
                </td>
                <td class="text-center font-weight-bold">
                  <?= (!empty($log['tipoDoc']) && !empty($log['consecutivo']))
                        ? Html::encode($log['tipoDoc'] . '-' . $log['consecutivo'])
                        : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="text-truncate" style="max-width:260px;" title="<?= Html::encode($log['mensaje'] ?? '') ?>">
                  <?= Html::encode($log['mensaje'] ?? '') ?>
                </td>
                <td class="text-center">
                  <span class="badge badge-light border"><?= Html::encode($log['idUsuario'] ?? '—') ?></span>
                </td>
                <td class="text-center">
                  <button type="button"
                          class="btn btn-sm btn-outline-dark btn-ver-json py-0 px-1"
                          data-idx="<?= $i ?>"
                          title="Ver JSON enviado y respuesta">
                    <i class="fas fa-code"></i>
                  </button>
                </td>
              </tr>
              <!-- Fila expandible con JSON -->
              <tr class="json-detail-row d-none" id="json-row-<?= $i ?>">
                <td colspan="7" class="p-0 bg-white">
                  <ul class="nav nav-tabs px-3 pt-2 border-0" id="tabs-log-<?= $i ?>" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active small py-1" data-toggle="tab"
                         href="#pane-env-<?= $i ?>" role="tab">
                        <i class="fas fa-upload mr-1"></i>JSON Enviado
                      </a>
                    </li>
                    <?php if (!empty($log['jsonRespuesta'])): ?>
                    <li class="nav-item">
                      <a class="nav-link small py-1" data-toggle="tab"
                         href="#pane-resp-<?= $i ?>" role="tab">
                        <i class="fas fa-download mr-1"></i>Respuesta SIESA
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                  <div class="tab-content px-3 pb-3">
                    <div class="tab-pane fade show active" id="pane-env-<?= $i ?>" role="tabpanel">
                      <pre class="json-box mt-1"><?= Html::encode($log['jsonEnviado'] ?? '(sin datos)') ?></pre>
                    </div>
                    <?php if (!empty($log['jsonRespuesta'])): ?>
                    <div class="tab-pane fade" id="pane-resp-<?= $i ?>" role="tabpanel">
                      <pre class="json-box mt-1"><?= Html::encode($log['jsonRespuesta']) ?></pre>
                    </div>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info py-2">
          <i class="fas fa-info-circle mr-1"></i>
          No hay historial de envíos registrado para esta importación.
        </div>
        <?php endif; ?>

      </div><!-- /modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

<?php $this->registerJs("
// ---- Expandir JSON ----
$('.btn-ver-json').on('click', function(){
    var idx  = $(this).data('idx');
    var row  = $('#json-row-' + idx);
    var icon = $(this).find('i');
    row.toggleClass('d-none');
    if (row.hasClass('d-none')) {
        icon.removeClass('fa-times').addClass('fa-code');
    } else {
        icon.removeClass('fa-code').addClass('fa-times');
        $('#tabs-log-' + idx + ' a:first').tab('show');
    }
});

// ---- Buscar OC en SIESA ----
var urlBuscarOc  = '" . Url::to(['/compras/importacion/buscar-oc-siesa']) . "';
var urlAsignar   = '" . Url::to(['/compras/importacion/asignar-consecutivo', 'id' => $model->id]) . "';

$('#btn-buscar-oc-siesa').on('click', function(){
    var btn = $(this).prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin mr-1\"></i>Buscando...');
    $.get(urlBuscarOc, function(r){
        btn.prop('disabled', false).html('<i class=\"fas fa-search mr-1\"></i>Buscar en SIESA');
        if (!r.ok) {
            $('#oc-siesa-msg').html('<span class=\"text-danger\">' + r.message + '</span>');
            $('#oc-siesa-results').removeClass('d-none');
            return;
        }
        var html = '';
        if (r.docs.length === 0) {
            html = '<p class=\"text-muted small\">No se encontraron documentos 2CA.</p>';
        } else {
            html += '<div class=\"list-group\">';
            $.each(r.docs, function(i, d){
                html += '<button type=\"button\" class=\"list-group-item list-group-item-action py-1 small btn-asignar-oc\" '
                      + 'data-tipo=\"' + d.tipoDoc + '\" data-num=\"' + d.consecutivo + '\">'
                      + '<strong class=\"text-success\">' + d.tipoDoc + '-' + d.consecutivo + '</strong>'
                      + ' &nbsp;<span class=\"text-muted\">' + (d.fechaCreacion || '') + '</span>'
                      + '</button>';
            });
            html += '</div>';
        }
        $('#oc-siesa-list').html(html);
        $('#oc-siesa-results').removeClass('d-none');
    }).fail(function(){
        btn.prop('disabled', false).html('<i class=\"fas fa-search mr-1\"></i>Buscar en SIESA');
        $('#oc-siesa-msg').html('<span class=\"text-danger\">Error de conexión.</span>');
        $('#oc-siesa-results').removeClass('d-none');
    });
});

// ---- Asignar consecutivo seleccionado ----
$(document).on('click', '.btn-asignar-oc', function(){
    var tipo = $(this).data('tipo');
    var num  = $(this).data('num');
    var self = $(this).prop('disabled', true);
    $('#oc-siesa-msg').html('<i class=\"fas fa-spinner fa-spin mr-1\"></i>Guardando...');

    $.post(urlAsignar, {
        _csrf:   yii.getCsrfToken(),
        tipoDoc: tipo,
        numDoc:  num
    }, function(r){
        if (r.ok) {
            $('#oc-siesa-msg').html('<span class=\"text-success\"><i class=\"fas fa-check-circle mr-1\"></i>Asignado: <strong>' + r.docSiesa + '</strong></span>');
            // Actualizar el banner de consecutivo sin recargar
            setTimeout(function(){ location.reload(); }, 1500);
        } else {
            self.prop('disabled', false);
            $('#oc-siesa-msg').html('<span class=\"text-danger\">' + r.message + '</span>');
        }
    }, 'json').fail(function(){
        self.prop('disabled', false);
        $('#oc-siesa-msg').html('<span class=\"text-danger\">Error al guardar.</span>');
    });
});
"); ?>
