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
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\number\NumberControl;

use common\models\ProcedimientosGenerales;
use frontend\models\Categoria;
use frontend\models\Crossdocking;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuestosubcategoria $model */
/** @var yii\widgets\ActiveForm $form */

$yearsList = ProcedimientosGenerales::listaAnios ();

$fechaactual = date('Y-m-d');
$fechadesde = date('Y-m-d', strtotime($modelAgenda->desde));

if ($fechadesde <= $fechaactual){
    $fechadesde = $fechaactual;
}

$disabled = true;
if ($model->isNewRecord){
    $disabled = false;
}

?>

<div class="agendapresupuestosubcategoria-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-agendapresupuestosubcategoria',
                    'enableAjaxValidation' => true,
                ]); ?>

    <div class="row">
        <div class="col-lg-3">
            <?= $form->field($model, 'periodoAnio')->widget(Select2::classname(), [
                'data' => $yearsList,
                'options' => ['placeholder' => 'Selecciona un año...', 'disabled' => true],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ]); ?>
        </div>

        <div class="col-lg-3">
            <?= $form->field($model, 'periodoMes')->dropDownList(ProcedimientosGenerales::meses(), 
                                                    [   'prompt' => 'Selecciona un mes...',
                                                        'disabled' => true
                                                    ]) ?>
        </div>

        <div class="col-lg-6">
            <?= 
                $form->field($model, 'fechaLlegada')->widget(DatePicker::className(),[
                    'name' => 'fecha-llegada', 
                    'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                    'language'=>'es',
                    'options' => [  'placeholder' => 'Fecha Llegada ...',
                                    'id' => 'fecha-llegada',
                                    'required' => true,
                                    'disabled' => $disabled
                    ],
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true,
                        'startDate' => $fechadesde,
                        'endDate' => date('Y-m-d', strtotime($modelAgenda->hasta)),
                    ]
                ]) 
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <?= $form->field($model, 'crossdocking_id')->dropDownList(Crossdocking::getListaData(), ['prompt' => ' Seleccionar CrossDocking ... ', 'disabled' => $disabled]) ?>                    
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'categoria_id')->widget(Select2::classname(), [
                    'data' => Categoria::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar Categoría', 
                        'multiple' => false,
                        'id' => 'categoria_id',
                        'disabled' => $disabled
                    ],
                    'pluginOptions' => [
                        //'allowClear' => true
                    ],
                    ]);    
            ?>
        </div>

        <div class="col-lg-4">
            <?= $form->field($model, 'subcategoria_id')->widget(DepDrop::classname(), [
                    'options'=>['id'=>'subcategoria_id', 'disabled' => $disabled],
                    'pluginOptions' => [
                        'depends' => ['categoria_id'],
                        'placeholder' => 'Seleccionar Subcategoría', 
                        'initialize' => $model->isNewRecord ? false : true,
                        'url'=>Url::to(['/catalogos/subcategoria/getlistaxcategoria', 'selected_id' => $model->subcategoria_id]),
                        //'params' => ['selected' => $model->subcategoria_id],
                    ],
                ]);    
            ?>
        </div>

    </div>


    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'cantidad')->widget(NumberControl::classname(), [
                    'maskedInputOptions' => [
                        //'prefix' => '$ ',
                        //'suffix' => ' ¢',
                        'min' => 0,
                        'digits' => 0,
                        'allowMinus' => false
                    ],
                    //'options' => $saveOptions,
                    //'displayOptions' => $dispOptions,
                    //'saveInputContainer' => $saveCont
                ]);
            ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
