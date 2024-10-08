// Escuchar el evento de cambio en el campo de número de orden de compra
// $('#id-centro-operacion, $id-tipo-documento, #numero-orden-compra').on('change', function() {

$('#numero-orden-compra').on('keydown', function(event) {
    if (event.keyCode === 9) { // Código de la tecla Tab
        eventoDetectado = 'keydown';
        //alert("PRUEBA");

        var idCentroOperacion = $('#id-centro-operacion').val();
        var idTipoDocumento = $('#id-tipo-documento').val();
        var numeroOrdenCompra = $(this).val();

        proceso_buscar_orden (idCentroOperacion, idTipoDocumento, numeroOrdenCompra);
    }
});

$('#numero-orden-compra').on('change', function() {
    eventoDetectado = 'change';

    var idCentroOperacion = $('#id-centro-operacion').val();
    var idTipoDocumento = $('#id-tipo-documento').val();
    var numeroOrdenCompra = $(this).val();

    proceso_buscar_orden (idCentroOperacion, idTipoDocumento, numeroOrdenCompra);
});

function proceso_buscar_orden(idCentroOperacion, idTipoDocumento, numeroOrdenCompra) {

// $('#numero-orden-compra').on('change', function() {

    //var idCentroOperacion = $('#id-centro-operacion').val();
    //var idTipoDocumento = $('#id-tipo-documento').val();
    //var numeroOrdenCompra = $(this).val();

    //alert(numeroOrdenCompra);

    /*var idCentroOperacion = $('#id-centro-operacion').val();
    var idTipoDocumento = $('#id-tipo-documento').val();
    var numeroOrdenCompra = $('#numero-orden-compra').val();*/

    // Hacer la solicitud AJAX al controlador para obtener los datos de la orden de compra
    $.ajax({
        url: 'index.php?r=agenda/agendaentregamercancia/obtener-datos-orden',
        method: 'GET',
        data: {
            idCentroOperacion: idCentroOperacion,
            idTipoDocumento: idTipoDocumento, 
            numeroOrdenCompra: numeroOrdenCompra
         },
        success: function(response) {
            // Actualizar los campos de la orden de compra con los datos recibidos

            var datetimepicker = $('#fecha-cita').data('datetimepicker');

            console.log(response);

            if (response.encontrada) {
                // Habilitar los campos adicionales si se encontró la orden de compra
                
                $('#unidades').prop('disabled', false);
                $('#numero-cajas').prop('disabled', false);
                $('#id-transportadora').prop('disabled', false);
                $('#fecha-contacto').prop('disabled', false);
                $('#contacto').prop('disabled', false);
                $('#observacion').prop('disabled', false);
                $('#numero-guia').prop('disabled', false);
                $('#fecha-cita').prop('disabled', false);

                //datetimepicker.enable();
            } else {
                // Deshabilitar los campos adicionales si no se encontró la orden de compra
                $('#unidades').prop('disabled', true);
                $('#numero-cajas').prop('disabled', true);
                $('#id-transportadora').prop('disabled', true);
                $('#fecha-contacto').prop('disabled', true);
                $('#contacto').prop('disabled', true);
                $('#observacion').prop('disabled', true);
                $('#numero-guia').prop('disabled', true);
                $('#fecha-cita').prop('disabled', true);

            }

            $('#id-orden-compra').val(response.id);
            $('#fecha-orden').val(response.fechaOrden);
            $('#total-cantidad-pedida').val(response.totalCantidadPedida);
            $('#total-cantidad-entrada').val(response.totalCantidadEntrada);
            $('#total-cantidad-pendiente').val(response.totalCantidadPendiente);
            $('#data-proveedor').val(response.dataProveedor);

            $('#unidades').val(response.totalCantidadPendiente);
        },
        error: function() {
            // Manejar el error si la solicitud AJAX falla
        }
    });
};
