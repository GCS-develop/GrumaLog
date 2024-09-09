<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Usertraspaso;

/** @var yii\web\View $this */
/** @var frontend\models\Traspasouserbodega $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Traspaso user bodegas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="traspasouserbodega-view">

    <p>
        <?= Html::a('Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Borrar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Estas seguro que deseas borrar?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'idUserTraspaso',
                'value' => function ($model) {
                return $model->idUserTraspaso . ' - ' . $model->userTraspaso->empleadoLogistica->empleado->nombreEmpleado;
            }
            ],
            [
                'attribute' => 'idBodega',
                'value' => function ($model) {
                return $model->idBodega . ' - ' . $model->bodega->nombre;
            }
            ],
            'idEstado',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            // [
            //     'attribute' => 'updated_by',
            //     'value' => function ($model) {
            //     return $model->updated_by ;
            // }
            // ],
        ],
    ]) ?>

</div>