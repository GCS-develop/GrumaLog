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
use kartik\select2\Select2;

use frontend\models\Bodegatipodocumento;
use frontend\models\Centrooperacion;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscdestinofactura $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="conteocdscdestinofactura-form">

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-conteocdscdestinofactura',
                    'enableAjaxValidation' => true,
                ]); 
    ?>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'idCentroOperacionMovimiento')->widget(Select2::classname(), [
                    'data' => Centrooperacion::getListaData(),
                    'options' => [
                        'placeholder' => 'Centro Operación ...', 
                        'multiple' => false,
                        'id' => 'id-centro-operacion',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'idBodegaMovimiento')->widget(Select2::classname(), [
                    'data' => Bodegatipodocumento::getListaData(),
                    'options' => [
                        'placeholder' => 'Seleccionar Bodega ...', 
                        'multiple' => false,
                        'id' => 'id-bodega',
                        'required' => true
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);    
            ?>
        </div>

    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
