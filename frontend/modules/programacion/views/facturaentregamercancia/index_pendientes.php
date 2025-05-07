<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\SqlDataProvider */
/* @var $username string */

$this->title = 'Conteos Pendientes';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="orden-compra-pendientes">

    <div class="form-group text-center d-flex justify-content-center align-items-center w-100">

        <div class="search-form">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['indexpendientes'],
                'options' => ['class' => 'form-inline'],
            ]); ?>


            <label class="form-label">Nombre de Usuario:</label>
            <?= Html::textInput('username', $username, [
                'class' => 'form-control',
                'placeholder' => 'Buscar por usuario',
                'style' => 'max-width: 300px; margin: 0 auto;'
            ]) ?>



            <div class="form-group">
                <?= Html::submitButton('<i class="fa fa-search"></i> Buscar', ['class' => 'btn btn-primary ml-2']) ?>
                <?= Html::a('<i class="fa fa-refresh "></i> Limpiar', ['indexpendientes'], ['class' => 'btn btn-default ml-2']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'ConsecutivoOC',
            'username',
            'Estado_Agendaentregamercancia',
            'Estado_programacionentregamercancia',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view}',
                'buttons' => [
                    'view' => function ($url, $model) {
                return Html::a(
                    '<span class="glyphicon glyphicon-eye-open"></span>',
                    ['/programacion/facturaentregamercancia/view-orden', 'consecutivo' => $model['ConsecutivoOC']],
                    ['title' => 'Ver detalles', 'data-pjax' => '0']
                );
            },
                ],
            ],
        ],
        'tableOptions' => [
            'class' => 'table table-striped table-bordered table-hover'
        ],
        'summary' => 'Mostrando {begin}-{end} de {totalCount} órdenes',
        'emptyText' => 'No se encontraron órdenes de compra',
    ]); ?>
    <?php Pjax::end(); ?>
</div>