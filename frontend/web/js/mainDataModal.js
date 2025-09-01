$(function () {
    $('#modalButtonCreate').click(function () {
        $('#modaldata').modal('show')
            .find('#modalContentData')
            .load($(this).attr('value'));
    });
});

$(function () {
    $('.btn_update, .btn_create, .btn_view, .btn_upload').click(function () {
        $('#modaldata').modal('show')
            .find('#modalContentData')
            .load($(this).attr('value'));
    });
});

$(function () {
    $('.btn_user').click(function () {
        $('#modaldatalogistica').modal('show')
            .find('#modalContentDataLogistica')
            .load($(this).attr('value'));
    });
});

$(function () {
    $('body').on('click', '#modalButtonCreateEliminados', function () {
        $('#modaldata2').modal('show')
            .find('#modalContentData2')
            .load($(this).attr('value'));
    });
});

// ESTE ES EL BLOQUE CORRECTO PARA LOS CHECKBOX SELECCIONADOS
/*$(document).ready(function () {
    $('body').on('click', '[data-role="abrir-registro-devoluciones"]', function () {

        var idsSeleccionados = $('.kv-row-checkbox:checked').map(function () {
            return $(this).val();
        }).get();

        if (idsSeleccionados.length === 0) {
            alert('Por favor seleccione al menos un registro.');
            return;
        }

        const baseUrl = $(this).val(); // ej: /devoluciondocumentodetalle/registrar-datos
        const fullUrl = `${baseUrl}?id=${idsSeleccionados.join(',')}`;

        $.ajax({
            url: fullUrl,
            type: 'GET',
            success: function (response) {
                if (response) {
                    $('#modaldata').modal('show').find('#modalContentData').html(response);
                } else {
                    alert('No se ha recibido contenido válido.');
                }
            },
            error: function () {
                alert('Ocurrió un error al cargar el formulario.');
            }
        });
    });
});*/


