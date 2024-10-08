<?php

$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }
	
	.btn-search {
        width: 300px;
    }
');

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\search\TransferenciasWsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transferencias-search mi-gridview">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
	
	<div class="card">
		<div class="card-body">
			<div class="row">
                <div class="col-lg-4">
					<?= $form->field($model, 'Co') ?>
				</div>

				<div class="col-lg-6">
					<?= $form->field($model, 'Notas') ?>
				</div>
			</div>
			
			<div class="form-group" align="center">
				<?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-search']) ?>
			</div>
		</div>
	</div>


    <?php ActiveForm::end(); ?>

</div>
