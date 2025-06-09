<?php

use frontend\models\Documentosiesa;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\DocumentosiesaSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Documento Contable SIESA';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="documentosiesa-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'tipoDocumento',
            'numeroDocumento',
            'f350_id_cia',
            'f350_rowid',
            //'f350_id_co',
            //'f350_id_tipo_docto',
            //'f350_consec_docto',
            //'idGruma',
            //'origen',
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Documentosiesa $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
