<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */



use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;



$this->registerCssFile(Yii::$app->request->baseUrl . 'web/css/login.css');
$this->title = 'Traspasos';
$this->params['breadcrumbs'][] = $this->title ;
?>
<link rel="stylesheet" href="css/login.css">
<div class="site-login">
<div class="text-center mb-5">
  <img src="imagenes/Logo_herpo.png" alt="Login image" class="login-image">
</div>
    <h1 class="text-center mb-5"><?= Html::encode($this->title) ?></h1>
          <!--<p>Please fill out the following fields to login:</p>-->
    <div class=" row justify-content-center">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'form-label'],
                    'inputOptions' => ['class' => 'form-control mb-3'],
                    'errorOptions' => ['class' => 'invalid-feedback'],
                ],
            ]); ?>
           
            <?= $form->field($model, 'username')->textInput(['autofocus' => true,'class' => 'form-control mb-3']) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"custom-control custom-checkbox mb-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
            ]) ?>

            <div class="form-group">
                <div class="d-grid gap-2">
                    <?= Html::submitButton('Ingresar', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
          <!-- <div style="color:#999;">
                You may login with <strong>admin/admin</strong> or <strong>demo/demo</strong>.<br>
                To modify the username/password, please check out the code <code>app\models\User::$users</code>.
            </div> -->
        </div>
     </div>
</div>