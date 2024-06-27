// Escuchar el evento de cambio en el campo de número de orden de compra
// $('#id-centro-operacion, $id-tipo-documento, #numero-orden-compra').on('change', function() {

$('#id-empleado').on('keydown', function(event) {
    if (event.keyCode === 9) { // Código de la tecla Tab
        eventoDetectado = 'keydown';
        //alert("PRUEBA");

        var idEmpleado = $(this).val();

        proceso_buscar_empleado (idEmpleado);
    }
});

$('#nid-empleado').on('change', function() {
    eventoDetectado = 'change';

    var idEmpleado = $(this).val();

    proceso_buscar_empleado (idEmpleado);
});

function proceso_buscar_empleado(idEmpleado) {

    // Hacer la solicitud AJAX al controlador para obtener los datos de la orden de compra
    $.ajax({
        url: 'index.php?r=nomina/empleado/obtener-datos-usuario',
        method: 'GET',
        data: {
            idEmpleado: idEmpleado
         },
        success: function(response) {
            // Actualizar los campos de la orden de compra con los datos recibidos

            console.log(response);

            if (response.encontrada) {
                // Habilitar los campos adicionales si se encontró la orden de compra
                
                $('#username').prop('disabled', false);
                $('#email').prop('disabled', false);
                $('#password').prop('disabled', false);
                $('#retypepassword').prop('disabled', false);

                //datetimepicker.enable();
            } else {
                // Deshabilitar los campos adicionales si no se encontró la orden de compra
                $('#username').prop('disabled', true);
                $('#email').prop('disabled', true);
                $('#password').prop('disabled', true);
                $('#retypepassword').prop('disabled', true);
            }

            $('#username').val(response.username);
            $('#email').val(response.email);
            $('#password').val(response.password);
            $('#retypepassword').val(response.retypepassword);
        },
        error: function() {
            // Manejar el error si la solicitud AJAX falla
        }
    });
};
