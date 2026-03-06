<?php

use yii\helpers\Html;

/** @var string $error */
?>
<div class="alert alert-danger mb-0">
    <strong>Error cargando el detalle:</strong><br>
    <div class="small" style="white-space:pre-wrap"><?= Html::encode($error) ?></div>
</div>