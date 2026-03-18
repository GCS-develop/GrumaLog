<?php
use yii\helpers\Url;
/** @var frontend\models\Comprasimportacion $model */
?>

<div class="modal fade" id="modalSiesa" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header" style="background:#343a40; color:#fff;">
        <h5 class="modal-title">
          <i class="fas fa-paper-plane mr-2"></i>Envío a SIESA — Importación #<?= $model->id ?>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <div id="siesa-alert" class="d-none mb-3"></div>

        <div class="form-row">

          <div class="form-group col-md-6">
            <label>Tipo de documento <span class="text-danger">*</span></label>
            <input type="text" id="s_tipoDoc" class="form-control form-control-sm"
                   placeholder="Ej: 2CA" maxlength="20">
          </div>

          <div class="form-group col-md-6">
            <label>Fecha del documento <span class="text-danger">*</span></label>
            <input type="text" id="s_fechaDoc" class="form-control form-control-sm"
                   placeholder="YYYYMMDD  Ej: 20260202" maxlength="8">
          </div>

          <div class="form-group col-md-6">
            <label>Tercero comprador <span class="text-danger">*</span></label>
            <input type="text" id="s_comprador" class="form-control form-control-sm"
                   placeholder="NIT comprador  Ej: 24814377">
          </div>

          <div class="form-group col-md-6">
            <label>Tercero proveedor <span class="text-danger">*</span></label>
            <input type="text" id="s_proveedor" class="form-control form-control-sm"
                   placeholder="NIT proveedor  Ej: 800118334">
          </div>

          <div class="form-group col-md-6">
            <label>Sucursal del proveedor <span class="text-danger">*</span></label>
            <input type="text" id="s_sucursal" class="form-control form-control-sm"
                   placeholder="Ej: 0" maxlength="20">
          </div>

          <div class="form-group col-md-6">
            <label>Condición de pago <span class="text-danger">*</span></label>
            <input type="text" id="s_condPago" class="form-control form-control-sm"
                   placeholder="Ej: 60" maxlength="20">
          </div>

          <div class="form-group col-md-6">
            <label>Bodega <span class="text-danger">*</span></label>
            <input type="text" id="s_bodega" class="form-control form-control-sm"
                   placeholder="Ej: 221" maxlength="20">
          </div>

        </div>

        <small class="text-muted">
          <i class="fas fa-info-circle mr-1"></i>
          Centro de operación: <strong>002</strong> &nbsp;|&nbsp;
          Motivo: <strong>01</strong> &nbsp;|&nbsp;
          Los ítems con mismo Código/Color/Talla se consolidan en un solo movimiento.
        </small>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-warning btn-sm" id="btn-siesa-confirmar">
          <i class="fas fa-paper-plane mr-1"></i>Enviar a SIESA
        </button>
      </div>

    </div>
  </div>
</div>

<?php $this->registerJs("
$('#btn-siesa-confirmar').on('click', function () {
    var btn  = $(this).prop('disabled', true).html('<i class=\"fas fa-spinner fa-spin mr-1\"></i>Enviando...');
    var alrt = $('#siesa-alert');

    $.post('" . Url::to(['/compras/importacion/envio-siesa', 'id' => $model->id]) . "', {
        _csrf:             yii.getCsrfToken(),
        tipoDocumento:     $('#s_tipoDoc').val().trim(),
        fechaDocumento:    $('#s_fechaDoc').val().trim(),
        terceroComprador:  $('#s_comprador').val().trim(),
        terceroProveedor:  $('#s_proveedor').val().trim(),
        sucursalProveedor: $('#s_sucursal').val().trim(),
        condicionPago:     $('#s_condPago').val().trim(),
        bodega:            $('#s_bodega').val().trim()
    }, 'json')
    .done(function (r) {
        alrt.removeClass('d-none alert-success alert-danger');
        if (r.success) {
            var docHtml = r.docSiesa
                ? ' &nbsp;<strong><i class=\"fas fa-file-invoice mr-1\"></i>' + r.docSiesa + '</strong>'
                : '';
            alrt.addClass('alert alert-success')
                .html('<i class=\"fas fa-check-circle mr-1\"></i>' + r.message + docHtml);
            // Actualizar badge en la vista principal
            var badgeDoc = r.docSiesa
                ? ' &nbsp;<span class=\"badge badge-light border\"><i class=\"fas fa-file-invoice mr-1 text-success\"></i>' + r.docSiesa + '</span>'
                : '';
            $('#resp-siesa').show()
                .html('<span class=\"badge badge-success\"><i class=\"fas fa-check-circle mr-1\"></i>Exitoso</span>' + badgeDoc);
            $('#btn-envio-siesa').prop('disabled', true)
                .html('<i class=\"fas fa-check mr-1\"></i>Enviado a SIESA');
            // Recargar pagina para mostrar boton Ver envio y datos actualizados
            setTimeout(function () {
                $('#modalSiesa').modal('hide');
                location.reload();
            }, 2500);
        } else {
            alrt.addClass('alert alert-danger')
                .html('<i class=\"fas fa-times-circle mr-1\"></i>' + r.message);
            btn.prop('disabled', false)
               .html('<i class=\"fas fa-paper-plane mr-1\"></i>Enviar a SIESA');
        }
    })
    .fail(function () {
        alrt.removeClass('d-none').addClass('alert alert-danger')
            .text('Error de conexión. Intenta nuevamente.');
        btn.prop('disabled', false)
           .html('<i class=\"fas fa-paper-plane mr-1\"></i>Enviar a SIESA');
    });
});

// Limpiar alerta al abrir el modal
$('#modalSiesa').on('show.bs.modal', function () {
    $('#siesa-alert').addClass('d-none').removeClass('alert-success alert-danger').html('');
});
"); ?>
