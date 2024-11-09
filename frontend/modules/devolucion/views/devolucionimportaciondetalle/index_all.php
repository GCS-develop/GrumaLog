<?php

$this->registerCss('
    .mi-gridview {
        font-size: 11px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }
');

use frontend\models\Devolucionimportaciondetalle;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\DevolucionimportaciondetalleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Devolución Importar', 'url' => ['/devolucion/devolucionimportacion/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="devolucionimportaciondetalle-index">


    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            //'idInterfase',
            'co',
            'fecha',
            'bodegaSalida',
            //'item',
            //'talla',
            //'color',
            'numeroDocumento',
            'notasDocumento',
            'bodegaEntrada',
            'codigoBodegaEntrada',
            'codigoBodegaSalida',
            'referencia',
            'itemResumen',
            'unidadMedida',
            'cantidad',
            'categoria',
            'proveedor',
            'codigoBarras',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Devolucionimportaciondetalle $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
        ],
    ]); ?>


</div>
