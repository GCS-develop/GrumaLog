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
/** @var common\models\search\OrdenesCompraWsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="ordenescompraws-search mi-gridview">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
	
	<div class="card">
		<div class="card-body">
			<div class="row">
                <div class="col-lg-4">
					<?= $form->field($model, 'CentroOperacion') ?>
				</div>

                <div class="col-lg-4">
					<?= $form->field($model, 'TipoDocumento') ?>
				</div>

				<div class="col-lg-4">
					<?= $form->field($model, 'Consecutivo') ?>
				</div>
			</div>
			
			<div class="form-group" align="center">
				<?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-search']) ?>
			</div>
		</div>
	</div>


    <?php ActiveForm::end(); ?>

</div>
