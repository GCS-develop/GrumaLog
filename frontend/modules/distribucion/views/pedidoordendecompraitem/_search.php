<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Search\PedidodetalleSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pedidodetalle-form">

    <?php $form = ActiveForm::begin([
            'action' => ['index', 'idpedido' => $idpedido, 'idordencompra' => $idordencompra],
            'method' => 'get',
            //'options' => ['class' => 'form-inline row g-3'],
        ]); 
    ?>

    <div class="card">
        <div class="card-body">

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($model, 'item_numero')->textInput([
                        'placeholder' => 'Número de Item'
                    ])->label(false) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'item_codigobarras')->textInput([
                        'placeholder' => 'Código de barras'
                    ])->label(false) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'color_codigo')->textInput([
                        'placeholder' => 'Color'
                    ])->label(false) ?>
                </div>

                <div class="col-md-3">
                    <?= $form->field($model, 'talla_codigo')->textInput([
                        'placeholder' => 'Talla'
                    ])->label(false) ?>
                </div>
            </div>

			<div class="form-group" align="center">
				<?= Html::submitButton('Buscar', ['class' => 'btn btn-primary btn-lg btn-create']) ?>
                <?= Html::a('Limpiar', ['index', 'idpedido' => $idpedido, 'idordencompra' => $idordencompra], ['class' => 'btn btn-primary btn-lg btn-create']) ?>
			</div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
