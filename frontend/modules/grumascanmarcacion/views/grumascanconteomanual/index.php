<?php

use app\models\Grumascanconteomanual;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

/** @var yii\web\View $this */
/** @var app\models\search\GrumascanconteomanualSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Grumascanconteomanuals';
$this->params['breadcrumbs'][] = $this->title;


$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

$fecha_actual = date("Y-m-d");
$filename = "Relacion_conteoManual_" . $fecha_actual;


$gridColumns = [
    'id',
    'idgrumascanconteo',
    'idmarcacion',
    'unidades_manual',
    'unidades_sistema',
    'diferencia',
    [
        'attribute' => 'created_by',
        'value' => function ($model) {
            return $model->createdby->username;
        },
    ],
    'created_at',
    'updated_at',
    [
        'attribute' => 'updated_by',
        'value' => function ($model) {
            return $model->updatedby->username;
        },
    ],
];

?>
<div class="grumascanconteomanual-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>
    <div class="col-lg-12 text-center">
        <?php echo ExportMenu::widget(
            [
                'dataProvider' => $dataProvider,
                'columns' => $gridColumns,
                'fontAwesome' => true,
                'filename' => $filename,
                'dropdownOptions' => [
                    'label' => 'Exportar',
                    'class' => 'btn btn-primary btn-lg btn-create',
                ],
                'exportConfig' => [
                    ExportMenu::FORMAT_TEXT => false,
                    ExportMenu::FORMAT_HTML => false,
                    ExportMenu::FORMAT_EXCEL => false,
                    ExportMenu::FORMAT_PDF => false,
                    ExportMenu::FORMAT_CSV => false,
                    ExportMenu::FORMAT_EXCEL_X => [
                        'label' => 'Excel 2007+',
                        'icon' => 'file-excel-o',
                        'iconOptions' => ['class' => 'text-success btn-create'],
                        'linkOptions' => [],
                        'options' => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                        'alertMsg' => 'Se va a generar un archivo en formato EXCEL 2007+ (xlsx).',
                        'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'extension' => 'xlsx',
                        'writer' => ExportMenu::FORMAT_EXCEL_X
                    ],
                ]
            ]
        );
        ?>
    </div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => true,

        'columns' => array_merge(
            [
                ['class' => 'kartik\grid\SerialColumn'],
            ],

            $gridColumns,

        ),
    ]); ?>


</div>