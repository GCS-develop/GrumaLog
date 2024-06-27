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

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\Signup */

?>
<div class="site-signup">
    
    <?php $form = ActiveForm::begin([
                                    'id' => 'form-signup', 
                                    'enableAjaxValidation' => true
                                ]); 
    ?>

    <div class="row">
        <?php 
        $disable = true;
        if ($model->username == null) { 
                $disable = false;
         }  
         ?>

        <div class="col-lg-6"> 
            <?= $form->field($model, 'username')->textInput([ 'disabled' => $disable])?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'email') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6"> 
            <?= $form->field($model, 'password')->passwordInput() ?>
        </div>

        <div class="col-lg-6">
            <?= $form->field($model, 'retypePassword')->passwordInput() ?>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
