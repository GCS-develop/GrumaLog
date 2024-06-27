<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Conteocdscusuario $model */

$this->title = 'Create Conteocdscusuario';
$this->params['breadcrumbs'][] = ['label' => 'Conteocdscusuarios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="conteocdscusuario-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
