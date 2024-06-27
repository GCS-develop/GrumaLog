<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Programacionentregamercancia $model */

$this->title = 'Create Programacionentregamercancia';
$this->params['breadcrumbs'][] = ['label' => 'Programacionentregamercancias', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$programa = '_form';
if ($oneuser == 1){
    $programa = '_form_oneuser';
}

?>
<div class="programacionentregamercancia-create">

    <?= $this->render($programa, [
        'model' => $model,
    ]) ?>

</div>
