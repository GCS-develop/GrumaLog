<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Pedido $model */

?>
<div class="pedido-upload">

    <?= $this->render('_form_filetransferencia', [
        'model' => $model,
    ]) ?>

</div>
