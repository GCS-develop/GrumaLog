<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Devolucionimportacion $model */

?>
<div class="devolucionimportacion-upload">

    <?= $this->render('_form_filedata', [
        'model' => $model,
    ]) ?>

</div>
