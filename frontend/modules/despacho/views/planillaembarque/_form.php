<script>

    window.onload = function() {
        inicializaCampos();

        buscarNombreConductor();
        buscarPlaca();
    };

    function inicializaCampos(){
        
        var flotapropia = document.getElementById("flotapropia").value;
        
        if (flotapropia == "1"){
            document.getElementById("id-vehiculo").disabled = false;
            document.getElementById("id-conductor").disabled = false;
            document.getElementById("placa").readOnly = true;   
            document.getElementById("nombre-conductor").readOnly = true; 
        }else{
            document.getElementById("id-vehiculo").disabled = true;
            document.getElementById("id-conductor").disabled = true;
            document.getElementById("placa").readOnly = false;   
            document.getElementById("nombre-conductor").readOnly = false;
        }
    }

    function validarFlotaPropia(){
        
        var flotapropia = document.getElementById("flotapropia").value;
        
        if (flotapropia == "1"){
            document.getElementById("id-vehiculo").disabled = false;
            document.getElementById("id-conductor").disabled = false;
            document.getElementById("placa").readOnly = true;   
            document.getElementById("nombre-conductor").readOnly = true; 

            document.getElementById("id-transportadora").value = '39';
        }else{
            document.getElementById("id-vehiculo").disabled = true;
            document.getElementById("id-conductor").disabled = true;
            document.getElementById("placa").readOnly = false;   
            document.getElementById("nombre-conductor").readOnly = false;

            document.getElementById("id-transportadora").value = ''; 
            document.getElementById("id-vehiculo").value = '';
            document.getElementById("id-conductor").value = '';  
        }

        document.getElementById("placa").value = '';
        document.getElementById("nombre-conductor").value = ''; 
    }

    function buscarNombreConductor(){
        
        var lcBuscar = document.getElementById("id-conductor").value;    
        var select = document.getElementById("id-conductor");
        var lnExiste = 0;
        var lcNombre = '';
        var lcNombreConductor = '';
            
        if (lcBuscar){
            // recorremos todos los valores del select
            for(var i=0;i<select.length;i++)
            {
                lcTexto = select.options[i].text;
                lcTexto = lcTexto.toUpperCase();
        
                if (select.options[i].selected == true){
                    lcNombre = lcTexto.charAt(0) + lcTexto.slice(1); 
                    break;               
                }
            }

            lcNombreConductor = lcNombre.split(' - ')[0];
            document.getElementById("nombre-conductor").value = lcNombreConductor;
        }

    }
    
    function buscarPlaca(){
        
        var lcBuscar = document.getElementById("id-vehiculo").value;    
        var select = document.getElementById("id-vehiculo");
        var lnExiste = 0;
        var lcNombre = '';
        var lcPlaca = '';

        if (lcBuscar){
            // recorremos todos los valores del select
            for(var i=0;i<select.length;i++)
            {
                lcTexto = select.options[i].text;
                lcTexto = lcTexto.toUpperCase();
        
                if (select.options[i].selected == true){
                    lcNombre = lcTexto.charAt(0) + lcTexto.slice(1); 
                    break;               
                }
            }

            lcPlaca = lcNombre.split(' - ')[0];
            document.getElementById("placa").value = lcPlaca;
        }
    }

</script>

<?php 

$this->registerCss('

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }

    /* styles.css */
    
    /* Cambiar el tamaño de la letra para los botones */
    button {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }
    
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\time\TimePicker;

use frontend\models\Transportadora;
use frontend\models\Vehiculo;
use frontend\models\Conductor;

?>

<div class="container">
    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-agendaentregamercancia',
                ]); ?>


    <div class="row">
        <!-- Campo 1 -->
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'fechaDespacho')->textInput(['disabled' => true]) ?>
        </div>

        <div class="col-12 col-md-6">
            <?= $form->field($model, 'horaDespacho')->textInput(['disabled' => true]) ?>
        </div>
    </div>

    <div class="row">
        <!-- Campo 1 
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'sello')->textInput(['maxlength' => true]) ?>
        </div>
        -->

        <!-- Campo 2 -->
        <div class="col-12">
            <?= $form->field($model, 'flotaPropia')->dropDownList(['1' => 'SI', '0' => 'NO'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'flotapropia',
                        'required'=>true,
                        'onChange'=>'validarFlotaPropia()', 
                    ]);
            ?>
        </div>
    </div>

    <div class="row">
        <!-- Campo 5 (tamaño completo) -->
        <div class="col-12">
            <?= $form->field($model, 'idTransportadora')->dropDownList(Transportadora::getListaData(), 
                                            ['prompt' => ' Seleccionar Transportadora ... ',
                                                        'id' => 'id-transportadora',
                                                        'required' => true,
                                            ])
            ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Campo 5 (tamaño completo) -->
        <div class="col-12">
            <?= $form->field($model, 'idVehiculo')->dropDownList(Vehiculo::getListaData(), 
                                            [   'prompt' => ' Seleccionar Vehículo ... ',
                                                'id' => 'id-vehiculo',
                                                'required' => false,
                                                'disabled' => true,
                                                'onChange'=>'buscarPlaca()', 
                                            ])
            ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Campo 5 (tamaño completo) -->
        <div class="col-12">
            <?= $form->field($model, 'idConductor')->dropDownList(Conductor::getListaData(), 
                                            [   'prompt' => ' Seleccionar Conductor ... ',
                                                'id' => 'id-conductor',
                                                'required' => false,
                                                'disabled' => true,
                                                'onChange'=>'buscarNombreConductor()', 
                                            ])
            ?>
        </div>
    </div>
    
    <div class="row">
        <!-- Campo 1 -->
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'placa')->textInput([
                                                        'maxlength' => true,
                                                        'placeholder'=>'Digite Placa Vehículo Transportadora',
                                                        'id' => 'placa',
                                                        'readOnly' => true 
                                                        ]) 
            ?>
        </div>

        <!-- Campo 2 -->
        <div class="col-12 col-md-6">
            <?= $form->field($model, 'nombreConductor')->textInput([
                                                        'maxlength' => true,
                                                        'placeholder'=>'Digite Nombre Conductor Vehículo Transaportadora',
                                                        'id' => 'nombre-conductor',
                                                        'readOnly' => true 
                                                        ]) 
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <div class="col-12">
            <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

