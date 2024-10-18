<?php

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

$this->title = 'Planilla embarque';
$this->params['breadcrumbs'][] = $this->title;




$fecha_actual = date("Y-m-d");
$filename = "Relacion_Traspaso_" . $fecha_actual;

// var_dump($planillasEmbarque);
foreach ($planillasEmbarque as $planilla) {

    $gridColumns = [

        [
            'label' => 'Origen',
            'value' => function ($planilla) {
                return $planilla->bodegaOrigen->codigo ?? 'Sin seleccionar';
            },
        ],


    ];

}

// $gridColumns = [
//     [
//         'label' => 'Origen',
// 'value' => function ($model) {
//     return isset($model->planillaembarquetraspaso[0])
//         ? $model->planillaembarquetraspaso[0]->bodegaOrigen->codigo
//         : 'Sin seleccionar';
// },
//     'value' => function ($model) {
//         return implode(', ', array_map(function ($item) {
//             return $item->bodegaOrigen->codigo;
//         }, $model->planillaembarquetraspaso));
//     },
// ],
// [
//     'label' => 'Almacen origen',
//     'value' => function ($model) {
//         return isset($model->planillaembarquetraspaso[0])
//             ? $model->planillaembarquetraspaso[0]->bodegaOrigen->nombre
//             : 'Sin seleccionar';
//     },
// ],
// [
//     'label' => 'Destino',
//     'value' => function ($model) {
//         return isset($model->planillaembarquetraspaso[0])
//             ? $model->planillaembarquetraspaso[0]->bodegaDestino->codigo
//             : 'Sin seleccionar';
//     },
// ],
// [
//     'label' => 'Almacen destino',
//     'value' => function ($model) {
//         return isset($model->planillaembarquetraspaso[0])
//             ? $model->planillaembarquetraspaso[0]->bodegaDestino->nombre
//             : 'Sin seleccionar';
//     },

// ],


// [
//     'label' => 'Serie',
//     'value' => function ($model) {
//         return $model->planillaembarquetraspaso->traspaso->tipodocumento->codigo;
//     },
// ],
// [
//     'label' => 'Numero',
//     'value' => function ($model) {
//         return $model->planillaembarquetraspaso->traspaso->serie;
//     },
// ],
// [
//     'label' => 'Fecha',
//     'value' => function ($model) {
//         return $model->planillaembarquetraspaso->traspaso->created_at;
//     },
// ],
// [
//     'label' => 'Fecha despacho',
//     'value' => function ($model) {
//         return $model->fechaDespacho;
//     },
// ],
// [
//     'label' => 'Hora despacho',
//     'value' => function ($model) {
//         return $model->horaDespacho;
//     },
// ],
// [
//     'label' => 'Unidades',//validar esto, creo que no trae la unidad del traspaso
//     'value' => function ($model) {
//         return $model->totalUnidades;
//     },
// ],
// [
//     'label' => 'Unidades empaque',//validar esto, Gilberto pide que se permita ingresar este valor
//     'value' => function ($model) {
//         // return $model->totalUnidades;
//     },
// ],
// [
//     'label' => 'Estado',
//     'value' => function ($model) {
//         return $model->estado->descripcion;
//     },
// ],
// [
//     'label' => 'Fecha recibo', // ajustar esto que no existe
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],
// [
//     'label' => 'Hora recibo',  //esto no existe
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],
// [ // esto no existe
//     'label' => 'Usuario recibo',
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],
// [
//     'label' => 'Planilla', // no se que es esto
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],
// [
//     'label' => 'Fecha recibido', // ya no existe?
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],
// [
//     'label' => 'Hora recibido',
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],

// [
//     'label' => 'Planilla transito',
//     'value' => function ($model) {
//         // return $model->estado->descripcion;
//     },
// ],

// ];

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
        // 'id',
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
        // 'idEstado',
        [
            'attribute' => 'idEstado',
            'filter' => Estadodespacho::getListaData(),
            'contentOptions' => ['data-cellvalue' => 'serie'],
            'value' => function ($model) {
    return $model->estado->nombre;
},
        ],
        //'created_at',
        //'created_by',
        //'updated_at',
        //'updated_by',
        [
            //             'class' => ActionColumn::className(),
//             'urlCreator' => function ($action, Planillaembarque $model, $key, $index, $column) {
//     return Url::toRoute([$action, 'id' => $model->id]);
// }
            'class' => ActionColumn::className(),
            'header' => 'Acción',
            'headerOptions' => ['width' => '15%'],
            'template' => ' {view} {update} {anular} {print}   ',
            'buttons' => [

                'view' => function ($url, $model) {
        return Html::a(
            '<i class="fa fa-eye"></i>',
            ['view', 'id' => $model->id],
            [
                'title' => 'Ver',
                'class' => 'btn btn-default d-none',
            ]
        );
    },

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
            ['print', 'id' => $model->id],
            [
                'title' => 'Ver pdf',
                'class' => 'btn btn-default btn disabled',
            ]
        );
    },
            ],

            'visibleButtons' => [

                //             'view' => function ($model, $key, $index) {
                //     return $model->idEstado == 0; // Condición para mostrar el botón
                // },
                'update' => function ($model, $key, $index) {
        return $model->idEstado == 1; // Condición para mostrar el botón
    },
                'anular' => function ($model, $key, $index) {
        return $model->idEstado != 5; // Condición para mostrar el botón
    },
            ],
        ],
    ],
]); ?>


</div>