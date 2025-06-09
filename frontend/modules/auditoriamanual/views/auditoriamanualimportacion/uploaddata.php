<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Auditoriamanualimportacion $model */

?>
<div class="auditoriamanualimportacion-upload">

    <?= $this->render('_form_filedata', [
        'model' => $model,
    ]) ?>

</div>
