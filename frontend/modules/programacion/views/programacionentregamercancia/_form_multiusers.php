<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use frontend\models\Userconteo;

/** @var yii\web\View $this */
/** @var int $idfactura */

$this->registerCss('
    .btn-submit-multi { width: 300px; }
    .centrar { text-align: center; }
    .info-distribucion { background:#f0f4ff; border:1px solid #c5d0e6; border-radius:4px; padding:10px 14px; margin-bottom:12px; font-size:13px; }
');
?>

<div class="programacionentregamercancia-form">

    <div class="info-distribucion">
        <strong>¿Cómo funciona?</strong><br>
        Selecciona uno o más operarios. Los ítems <em>sin asignar</em> se repartirán en forma <strong>rotativa</strong>
        entre los usuarios seleccionados (ítem 1 → usuario 1, ítem 2 → usuario 2 … ítem N+1 → usuario 1, etc.).
    </div>

    <?php
    $action = Url::to(['assignmultipleusers', 'idfactura' => $idfactura]);
    echo Html::beginForm($action, 'post', ['id' => 'form-multiusers']);
    ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <label class="control-label">Operarios a asignar</label>
                <?= Select2::widget([
                    'name'    => 'userIds[]',
                    'data'    => Userconteo::getListaDataHabil(),
                    'options' => [
                        'multiple'    => true,
                        'placeholder' => 'Seleccionar uno o más usuarios ...',
                        'id'          => 'multiuserids',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]) ?>
            </div>
        </div>
    </div>

    <div class="form-group centrar">
        <?= Html::submitButton('Asignar y Distribuir', ['class' => 'btn btn-primary btn-lg btn-submit-multi']) ?>
    </div>

    <?= Html::endForm() ?>

</div>
