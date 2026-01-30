<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $model frontend\models\DocumentoGasto */
/** @var $sugeridoConsec int|null */

$this->title = 'Documento Gastos';
$this->params['breadcrumbs'][] = ['label' => 'Documento Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss(<<<CSS
.form-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;box-shadow:0 4px 14px rgba(0,0,0,.05);margin-bottom:12px;}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;}
.grid-3{display:grid;grid-template-columns:1fr;gap:12px;margin-bottom:12px;}
@media (min-width: 768px){ .grid-3{ grid-template-columns:repeat(3,1fr);} }
.form-card .form-group{margin-bottom:10px;}
.form-card label{font-size:12px;color:#374151;}
.form-card .form-control{height:36px;padding:6px 10px;font-size:13px;}
.btn-next{background:#10b981;border:none;color:#fff;padding:9px 14px;border-radius:10px;}
.btn-next:hover{background:#059669;}
CSS);
?>

<div class="form-card">
  <div class="header">
    <h3 style="margin:0"><?= Html::encode($this->title) ?></h3>
    <span class="badge">CIA: 7 • Tipo: 22</span>
  </div>

  <?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
  <?php endif; ?>

  <?php $form = ActiveForm::begin([
      'action' => ['create'],
      'method' => 'post',
      'id'     => 'form-hoja1',
  ]); ?>

    <?= $form->errorSummary($model, ['class'=>'alert alert-danger']) ?>

    <div class="grid-3">
      <?= $form->field($model, 'F350_ID_CO')->textInput(['maxlength'=>50,'placeholder'=>'Ej: 011'])->label('C.O') ?>
      <?= $form->field($model, 'F350_FECHA')->input('date')->label('FECHA') ?>
      <?= $form->field($model, 'F350_ID_TERCERO')->textInput(['maxlength'=>50])->label('TERCERO') ?>
    </div>

    <?= $form->field($model, 'F350_NOTAS')->textInput(['maxlength'=>1000, 'placeholder'=>'Opcional'])->label('NOTAS') ?>

    <?= Html::activeHiddenInput($model, 'F350_ID_TIPO_DOCTO', ['value' => '22']) ?>
    <?= Html::activeHiddenInput($model, 'F350_CONSEC_DOCTO', ['value' => $model->F350_CONSEC_DOCTO ?: $sugeridoConsec]) ?>

    <div style="display:flex;gap:10px;">
      <?= Html::submitButton('Continuar → Part.2', ['class'=>'btn-next']) ?>
      <?= Html::a('Cancelar', ['index'], ['class'=>'btn btn-default']) ?>
    </div>

  <?php ActiveForm::end(); ?>
</div>
