<?php

use frontend\models\Productostiquetesprecio;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;
use yii\bootstrap4\Modal;


/** @var yii\web\View $this */
/** @var frontend\models\search\ProductostiquetesprecioSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Imprimir solo precios';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);


?>

<link rel="stylesheet" href="css/shared.css">

<?php
Modal::begin([
    'title' => '<h4>Datos de productos - items - precio</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>
<div class="productostiquetesprecio-index">

    <!-- <div class="col-lg-12 centrar">

        <?php $url = Url::to(['create']); ?>

        <p>
            <?= Html::button(
                'Registrar',
                ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
            )
                ?>
        </p>

    </div> -->

    <?php $url = Url::to(['upload']); ?>

    <!-- <div class="row">
        <div class="col-lg-12 centrar">
            <?= Html::button(
                'Importar Datos',
                ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
            )
                ?>
        </div>
    </div> -->

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Alert::widget() ?>

    <!-- <?= $dataProvider->pagination->pageSize = 100; ?> -->

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
    
        'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options' => [
            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
        ],
        'showPageSummary' => true,
        // 'pager' => [
        //     'pageSizeLimit' => [10, 100], // Opcional: Establecer límites para selección de tamaño
        // ],
        'columns' => [
            'id',
            'descBodega',
            'codigoBarra',
            'item',
            'descItem',
            'detalleExt1',
            'detalleExt2',
            'existencia',
            'proveedor',
            'marca',
            'referencia',
            'categoria',
            'subcategoria',
            // 'precio',
            [
                'attribute' => 'precio',
                'format' => ['currency'], // Formato de moneda
                'contentOptions' => ['style' => 'text-align: right;'], // Opcional: alinea a la derecha
            ],
            //'created_at',
            //'created_by',
            //'updated_at',
            //'updated_by',
    
            [
                'class' => ActionColumn::className(),
                'header' => 'Acción',
                'headerOptions' => ['width' => '15%'],
                // 'template' => '{view}  {print} ',
                'template' => '  {print} ',


                'buttons' => [

                    'print' => function ($url, $model) {
            return Html::a(
                '<i class="fa fa-print"></i>',
                ['productostiquetesprecio/print', 'id' => $model->id],
                [
                    'title' => 'Imprimir',
                    'class' => 'btn btn-default',
                ]
            );
        },


                    //             'view' => function ($url, $model) {
                    //     return Html::a(
                    //         '<i class="fa fa-eye"></i>',
                    //         ['/despacho/planillaembarquetraspaso/index', 'id' => $model->id],
                    //         [
                    //             'title' => 'Ver',
                    //             'class' => 'btn btn-default btn-view',
                    //         ]
                    //     );
                    // },
    
                ],


            ],


        ],
    ]); ?>



</div>