$(function (){        
    // get the click of the create button
    $('#modalButtonCreate').click(function (){
        $('#modaldata').modal('show')
                .find('#modalContentData')
                .load($(this).attr('value'))
    });
});

$(function(){
    $('.btn_update').click(function(){
        $('#modaldata').modal('show')
                .find('#modalContentData')
                .load($(this).attr('value'))
    });
});

$(function(){
    $('.btn_create').click(function(){
        $('#modaldata').modal('show')
                .find('#modalContentData')
                .load($(this).attr('value'))
    });
});

$(function(){
    $('.btn_view').click(function(){
        $('#modaldata').modal('show')
                .find('#modalContentData')
                .load($(this).attr('value'))
    });
});

$(function(){
    $('.btn_user').click(function(){
        $('#modaldatalogistica').modal('show')
                .find('#modalContentDataLogistica')
                .load($(this).attr('value'))
    });
});

$(function(){
    $('.btn_upload').click(function(){
        $('#modaldata').modal('show')
                .find('#modalContentData')
                .load($(this).attr('value'))
    });
});