<?php

$this->registerCss('

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

');

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

use frontend\models\Userconteocdsc;

/** @var yii\web\View $this */
/** @var frontend\models\search\ConteocdscdestinoSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestino-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'razonSocial') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'numeroFactura') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'almacen') ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idEstadoFactura')->dropDownList(['1' => 'Conteo', '2' => 'Finalizada', '0' => 'Sin Conteo'], 
                    [   'prompt' => ' Seleccionar Opción ... ', 
                        'id' => 'idestadofactura',
                        'required'=>false]);
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <?= 
                $form->field($model, 'fechaDesde')->widget(DatePicker::className(),[
                    'name' => 'fechadesde', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Inicio Conteo ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-3">
            <?= 
                $form->field($model, 'fechaHasta')->widget(DatePicker::className(),[
                    'name' => 'fechahasta', 
                    'language'=>'es',
                    'options' => ['placeholder' => 'Fecha Fin Conteo ...'],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) 
            ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'idUserConteo')->dropDownList(Userconteocdsc::getListaData(), 
                                                ['prompt' => ' Seleccionar Usuario ... ',
                                                'id' => 'iduserconteo',
                                                'required' => false
                                                ])->label('Usuario')
            ?>                    
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
