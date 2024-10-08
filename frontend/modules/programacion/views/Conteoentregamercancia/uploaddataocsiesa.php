<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Agendapresupuesto $model */

?>
<div class="presupuesto-upload">

    <?= $this->render('_form_filedataocsiesa', [
        'model' => $model,
    ]) ?>

</div>
