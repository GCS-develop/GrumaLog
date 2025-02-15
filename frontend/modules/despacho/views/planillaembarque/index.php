<?php
use yii\helpers\ArrayHelper;

// Definir el estilo CSS directamente en la vista
$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }
    
    .centrar {
        text-align: center;
    }

        .izquierda {
        text-align: left;
    }
 
    .derecha {
        text-align: right;
    }    

    
');

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

use frontend\models\Estadodespacho;
use frontend\models\Transportadora;
use frontend\models\Vehiculo;
use Symfony\Component\Mailer\Transport;

use frontend\models\Traspaso;
use frontend\models\Planillaembarque;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

use common\widgets\Alert;
use yii\bootstrap4\Modal;


/** @var yii\web\View $this */
/** @var frontend\models\search\PlanillaembarqueSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Planilla Embarque';
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");
$filename = "Relacion_PlanillaEmbarque_" . $fecha_actual;

$gridColumns = [
    'id',
    'fechaDespacho',
    'horaDespacho',
    [
        'attribute' => 'idTransportadora',
        'filter' => Transportadora::getListaData(),
        'contentOptions' => ['data-cellvalue' => 'serie'],
        'value' => function ($model) {
            return $model->transportadora->nombre;
        },
    ],
    [
        'label' => 'Tiendas destino',
        'value' => function ($model) {
            $bodegas = $model->listabodegasdestino;
            $nombresBodegas = [];
            foreach ($bodegas as $bodega) {
                $nombresBodegas[] = $bodega->bodegaDestino->nombre;  // Accede a cada nombre de bodega
            }
            return implode(', ', $nombresBodegas);  // Devuelve los nombres separados por coma
        },
    ],
    [
        'attribute' => 'idVehiculo',
        'filter' => Vehiculo::getListaData(),
        'contentOptions' => ['data-cellvalue' => 'serie'],
        'value' => function ($model) {
            return $model->vehiculo ? $model->vehiculo->descripcion : ' Sin seleccionar ';
        },
    ],
    'placa',
    'nombreConductor',
    'sello',
    [
        'attribute' => 'idEstado',
        'value' => function ($model) {
            return $model->estado->nombre;
        },
    ],
    [
        'attribute' => 'idEstado',
        'filter' => Estadodespacho::getListaData(),
        'contentOptions' => ['data-cellvalue' => 'serie'],
        'value' => function ($model) {
            return $model->estado->nombre;
        },
    ],
];

?>

<?php
Modal::begin([
    'title' => '<h4>Datos básicos planilla embarque</h4>',
    'id' => 'modaldata',
    'size' => 'modal-lg',
    'options' => [
        'tabindex' => false  // Importante para que funcione el Select
    ]
]);

echo "<div id='modalContentData'></div>";

Modal::end();
?>


<div class="planillaembarque-index">

    <!-- <p>
        <?= Html::a('Registrar planilla de embarque', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= Alert::widget() ?>

    <div class="row">
        <div class="col-lg-6 derecha">

            <?php $url = Url::to(['create']); ?>

            <p>
                <?= Html::button(
                    'Registrar',
                    ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonCreate']
                )
                    ?>
            </p>

        </div>

        <div class="col-lg-6 izquierda">

            <!-- <?= Html::button(
                'Exportar',
                ['value' => $url, 'class' => 'btn btn-success btn-lg btn-create', 'id' => 'modalButtonSubmit']
            )
                ?> -->

            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProvider,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar',
                        'class' => 'btn btn-primary btn-lg btn-create ',
                        // btn disabled',
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
    </div>
</div>


<?= GridView::widget([
    'dataProvider' => $dataProvider,
    // 'filterModel' => $searchModel,
    // 'showPageSummary' => true,
    // 'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'options' => [
        'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
    ],


    'columns' => [
        ['class' => 'kartik\grid\SerialColumn'],

        'fechaDespacho',
        'horaDespacho',
        // 'idTransportadora',
        [
            'attribute' => 'idTransportadora',
            'filter' => Transportadora::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->transportadora->nombre;
},

        ],


        [
            'label' => 'Bodega destino',
            'value' => function ($model) {
                $bodegas = $model->listabodegasdestino;
                $nombresBodegas = [];
                foreach ($bodegas as $bodega) {
                    $nombresBodegas[] = $bodega->bodegaDestino->nombre;  // Accede a cada nombre de bodega
                }
                return implode(', ', $nombresBodegas);  // Devuelve los nombres separados por coma
            },
        ],
        




        // 'idVehiculo',
        [
            'attribute' => 'idVehiculo',
            'filter' => Vehiculo::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->vehiculo ? $model->vehiculo->descripcion : ' Sin seleccionar ';
},
        ],
        'placa',
        // 'idConductor',
        'nombreConductor',
        'sello',
        'id',
        // 'idEstado',
        [
            'attribute' => 'idEstado',
            'filter' => Estadodespacho::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->estado->nombre;
},
        ],

        [
            'class' => ActionColumn::className(),
            'header' => 'Acción',
            'headerOptions' => ['width' => '15%'],
            'template' => '{view} {update} {print} {anular}',

            'buttons' => [

                'update' => function ($url, $model) {
        $t = Url::to([
            'update',
            'id' => $model->id
        ]);

        return Html::button('<i class="fa fa-edit"></i>', [
            'value' => $t,
            'title' => 'Actualizar',
            'class' => 'btn btn-default btn_update',
        ]);
    },

                'view' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-eye"></i>',
            ['/despacho/planillaembarquetraspaso/index', 'id' => $model->id],
            [
                'title' => 'Ver',
                'class' => 'btn btn-default btn-view',
            ]
        );
    },

                'anular' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-ban"></i>',
            ['anular', 'id' => $model->id],
            [
                'class' => 'btn btn-default',
                'title' => 'Anular este registro',
                'data' => [
                    'confirm' => 'Esta seguro de anular este registro? ( Fecha: '
                        . $model->fechaDespacho . ', Placa: '
                        . $model->placa . ', Transportadora: '
                        . $model->transportadora->nombre . ' )',
                    'method' => 'post',
                ]
            ]
        );
    },

                'print' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-print"></i>',
            ['generatepdf', 'id' => $model->id],
            [
                'title' => 'Imprimir Planilla Embarque',
                'target' => '_blank',
                'class' => 'btn btn-default btn-print',
            ]
        );
    },
            ],

            'visibleButtons' => [
                'update' => function ($model, $key, $index) {
        return $model->idEstado == 1; // Condición para mostrar el botón
    },
                'anular' => function ($model, $key, $index) {
        return $model->idEstado != 5; // Condición para mostrar el botón
    },
            ],
        ],
        //'created_at',
        //'created_by',
        //'updated_at',
        //'updated_by',

    ],
]); ?>


</div>