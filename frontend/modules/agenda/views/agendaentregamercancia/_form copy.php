<?php

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

    /* styles.css */

    /* Cambiar el tamaño de la letra para todo el formulario */
    form {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }
    
    /* Cambiar el tamaño de la letra para etiquetas de campo */
    label {
        font-size: 12px; /* Cambia el tamaño de la letra a 14px */
    }
    
    /* Cambiar el tamaño de la letra para los inputs de texto */
    input[type="text"] {
        font-size: 14px; /* Cambia el tamaño de la letra a 14px */
    }
    
    /* Cambiar el tamaño de la letra para los botones */
    button {
        font-size: 12px; /* Cambia el tamaño de la letra a 16px */
    }

    /* Cambiar el tamaño de la letra para campos de entrada numérica */
    input[type="number"] {
        font-size: 12px; /* Cambia el tamaño de la letra a 12px */
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .title {
        font-weight: bold;
        text-align: center;
    }
    
');

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/dataOrdenCompra.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

$this->registerJsFile(Yii::$app->request->baseUrl.'/js/mainDataModal.js',
['depends' => [\yii\web\JqueryAsset::className()]]
);

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\time\TimePicker;
use yii\helpers\Url;
use yii\bootstrap4\Modal;

use frontend\models\Centrooperacion;
use frontend\models\Tipodocumento;
use frontend\models\Transportadora;
use frontend\models\Agendapresupuestosubcategoria;

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php
    Modal::begin([                
        'title'=>'<h4>Registro datos cita</h4>',
        'id'=>'modaldata',
        'size'=>'modal-lg',
        'options' => [
            'tabindex' => false  // Importante para que funcione el Select
        ]
    ]);
        
    echo "<div id='modalContentData'></div>";
        
    Modal::end(); 
?>

<div class="agendaentregamercancia-form">

    <?php 
        // Mostrar un GridView por cada bodega
    foreach ($dataByCrossDocking as $crossdocking => $data) {
    
    ?>

    <div class="row">
        <h1 class="title"><?php echo $crossdocking ?></h1>
    </div>

    <div class="row">
    <?= GridView::widget([
        'dataProvider' => new yii\data\ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]),

        //'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
        'summary' => '',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],

        'showPageSummary' => true,

        'columns' => [
            //'crossDocking',
            'categoria',
            'subcategoria',

            [
                'attribute' => 'dia1', // Nombre del atributo en el modelo
                'label' => '01', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia2', // Nombre del atributo en el modelo
                'label' => '02', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia3', // Nombre del atributo en el modelo
                'label' => '03', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia4', // Nombre del atributo en el modelo
                'label' => '04', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia5', // Nombre del atributo en el modelo
                'label' => '05', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia6', // Nombre del atributo en el modelo
                'label' => '06', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia7', // Nombre del atributo en el modelo
                'label' => '07', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia8', // Nombre del atributo en el modelo
                'label' => '08', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia9', // Nombre del atributo en el modelo
                'label' => '09', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia10', // Nombre del atributo en el modelo
                'label' => '10', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia11', // Nombre del atributo en el modelo
                'label' => '11', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia12', // Nombre del atributo en el modelo
                'label' => '12', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia13', // Nombre del atributo en el modelo
                'label' => '13', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia14', // Nombre del atributo en el modelo
                'label' => '14', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia15', // Nombre del atributo en el modelo
                'label' => '15', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia16', // Nombre del atributo en el modelo
                'label' => '16', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia17', // Nombre del atributo en el modelo
                'label' => '17', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia18', // Nombre del atributo en el modelo
                'label' => '18', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia19', // Nombre del atributo en el modelo
                'label' => '19', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia20', // Nombre del atributo en el modelo
                'label' => '20', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia21', // Nombre del atributo en el modelo
                'label' => '21', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia22', // Nombre del atributo en el modelo
                'label' => '22', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia23', // Nombre del atributo en el modelo
                'label' => '23', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia24', // Nombre del atributo en el modelo
                'label' => '24', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia25', // Nombre del atributo en el modelo
                'label' => '25', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia26', // Nombre del atributo en el modelo
                'label' => '26', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia27', // Nombre del atributo en el modelo
                'label' => '27', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia28', // Nombre del atributo en el modelo
                'label' => '28', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia29', // Nombre del atributo en el modelo
                'label' => '29', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia30', // Nombre del atributo en el modelo
                'label' => '30', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true,
            ],

            [
                'attribute' => 'dia31', // Nombre del atributo en el modelo
                'label' => '31', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'pageSummary' => true
            ],

            [
                //'attribute' => 'dia1', // Nombre del atributo en el modelo
                'label' => 'Total', // Etiqueta de la columna
                'hAlign' => 'right',
                'vAlign' => 'middle',
                'format' => ['decimal', 0], // Formato decimal con 0 decimales,
                'value' => function ($model) {
                    return  $model['dia1'] + $model['dia2'] + $model['dia3'] + $model['dia4'] + $model['dia5'] +
                            $model['dia6'] + $model['dia7'] + $model['dia8'] + $model['dia9'] + $model['dia10'] +
                            $model['dia11'] + $model['dia12'] + $model['dia13'] + $model['dia14'] + $model['dia15'] +
                            $model['dia16'] + $model['dia17'] + $model['dia18'] + $model['dia19'] + $model['dia20'] +
                            $model['dia21'] + $model['dia22'] + $model['dia23'] + $model['dia24'] + $model['dia25'] +
                            $model['dia26'] + $model['dia27'] + $model['dia28'] + $model['dia29'] + $model['dia30'] +
                            $model['dia31'];
                },
                'pageSummary' => true
            ],

        ],
    ]); ?>

    </div>

    <?php } ?>

    <?php $form = ActiveForm::begin([
                    'id' => 'modal-form-agendaentregamercancia'
                ]); 
    ?>

    <div class="row">
        <!-- Datos Orden de Compra -->
        <div class="col-lg-6 border-right">

            <div class="row">
                <div class="col-lg-3">
                    <?= $form->field($model, 'codigoCentroOperacion')->textInput(['readonly' => true, 'id' => 'codigo-centro-operacion']) ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'codigoTipoDocumento')->textInput(['readonly' => true, 'id' => 'codigo-tipo-documento']) ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'numeroOrdenCompra')->textInput(['readonly' => true, 'id' => 'numero-orden-compra']) ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'fechaOrden')->textInput(['readonly' => true, 'id' => 'fecha-orden']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    <?= $form->field($model, 'nit')->textInput(['readonly' => true, 'id' => 'nit-proveedor']) ?>
                </div>
                <div class="col-lg-9">
                    <?= $form->field($model, 'razonSocial')->textInput(['readonly' => true, 'id' => 'razonSocial']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3">
                    <?= $form->field($model, 'fechaEntrega')->textInput(['readonly' => true, 'id' => 'fecha-entrega']) ?>
                </div>
                <div class="col-lg-3">
                    <?= $form->field($model, 'totalCantidadPedida')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-pedida']) ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'totalCantidadEntrada')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-entrada']) ?>
                </div>

                <div class="col-lg-3">
                    <?= $form->field($model, 'totalCantidadPendiente')->textInput(['readonly' => true, 'type' => 'number', 'id' => 'total-cantidad-pendiente']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">

                    <h5 class="centrar"><?= Html::encode('Orden de Compra - Resumen Unidades x Categoría') ?></h5>

                    <?= GridView::widget([
                        'dataProvider' => $dataProviderSubcategoria,

                        //'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                        'summary' => '',
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        'options' => [
                            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
                        ],
                        'showFooter' => true, // Mostrar el pie de página

                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            //'idOrdenCompra',
                            'categoria',
                            'subcategoria',

                            [
                                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                                'label' => 'Unidades Orden Compra', // Etiqueta de la columna
                                'hAlign' => 'center', // Alineación horizontal al centro
                                'vAlign' => 'middle', // Alineación vertical al centro
                                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                                'footer' => number_format($dataProviderSubcategoria->query->sum('cantidad'), 0, ',', '.'),
                            ],
                        ],
                    ]); ?>

                </div>
            </div>

            <?= Html::tag('hr', '', ['class' => 'horizontal-line']) ?>

            <div class="row">
                <div class="col-lg-12">

                    <h5 class="centrar"><?= Html::encode('Agendamiento Mensual - Resumen Unidades x Categoría') ?></h5>

                    <?= GridView::widget([
                        'dataProvider' => $dataProviderPresupuestoSubcategoria,

                        //'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                        'summary' => '',
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        'options' => [
                            'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
                        ],
                        'showFooter' => true, // Mostrar el pie de página
                        
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            'crossDocking',
                            'categoria',
                            'subcategoria',

                            [
                                'attribute' => 'cantidad', // Nombre del atributo en el modelo
                                'label' => 'Unidades Presupuesto', // Etiqueta de la columna
                                'hAlign' => 'right', // Alineación horizontal al centro
                                'vAlign' => 'middle', // Alineación vertical al centro
                                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                                'footer' => number_format($dataProviderPresupuestoSubcategoria->query->sum('cantidad'), 0, ',', '.'), // Mostrar el total de la columna
                            ],
                            [
                                'attribute' => 'cantidadAgendada', // Nombre del atributo en el modelo
                                'label' => 'Unidades Agenda', // Etiqueta de la columna
                                'hAlign' => 'right', // Alineación horizontal al centro
                                'vAlign' => 'middle', // Alineación vertical al centro
                                'format' => ['decimal', 0], // Formato decimal con 0 decimales
                                'footer' => number_format($dataProviderPresupuestoSubcategoria->query->sum('cantidadAgendada'), 0, ',', '.'),
                            ],
                            [
                                'attribute' => 'cupo', // Nombre del atributo en el modelo
                                'label' => 'Cupo', // Etiqueta de la columna
                                'hAlign' => 'right', // Alineación horizontal al centro
                                'vAlign' => 'middle', // Alineación vertical al centro
                                'format' => ['decimal', 2], // Formato decimal con 0 decimales
                                'value' => function ($model){
                                    return $model->cantidad - $model->cantidadAgendada;
                                },
                                'footer' => number_format($dataProviderPresupuestoSubcategoria->query->sum('cantidad') - $dataProviderPresupuestoSubcategoria->query->sum('cantidadAgendada'), 0, ',', '.'),
                            ],
                        ],
                    ]); ?>

                </div>
            </div>

        </div>

        <div class="col-lg-6 border-right">
            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'fechaAgenda')->widget(Select2::classname(), [
                            'data' => Agendapresupuestosubcategoria::getListaData($model->idAgenda, $categoria),
                            'options' => [
                                'placeholder' => 'Seleccionar Fecha ...', 
                                'multiple' => false,
                                'id' => 'fecha-agenda',
                                'required' => true
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]);    
                    ?>
                </div>

                <div class="col-lg-6">
                    <?= 
                        $form->field($model, 'horaAgenda')->widget(TimePicker::classname(), [
                            'pluginOptions' => [
                                'showSeconds' => false,
                                'showMeridian' => false,
                            ],
                            'options' => [
                                'required' => true
                            ]
                        ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <?= $form->field($model, 'unidades')->textInput(['type' => 'number', 'min' => 1, 'step' => 1, 'id' => 'unidades', 'required' => true, 'readonly' => true]) ?>
                </div>

                <div class="col-lg-6">
                    <?= $form->field($model, 'numeroCajas')->textInput(['type' => 'number', 'min' => 1, 'step' => 1, 'id' => 'numero-cajas', 'required' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <?= $form->field($model, 'idTransportadora')->dropDownList(Transportadora::getListaData(), 
                                                        ['prompt' => ' Seleccionar Transportadora ... ',
                                                        'id' => 'id-transportadora',
                                                        'required' => true
                                                        ])
                    ?>                    
                </div>

                <div class="col-lg-4">
                    <?= $form->field($model, 'numeroGuia')->textInput(['maxlength' => true, 'id' => 'numero-guia']) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <?= 
                        $form->field($model, 'fechaContacto')->widget(DatePicker::className(),[
                            'name' => 'fecha-contacto', 
                            'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                            'language'=>'es',
                            'options' => [  'placeholder' => 'Fecha Contacto ...',
                                            'id' => 'fecha-contacto',
                                            'required' => true
                            ],
                            'pluginOptions' => [
                                'autoclose'=>true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]) 
                    ?>
                </div>

                <div class="col-lg-8">
                    <?= $form->field($model, 'contacto')->textInput(['maxlength' => true, 'id' => 'contacto', 'required' => true]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <?= $form->field($model, 'observacion')->textInput(['maxlength' => true, 'id' => 'observacion']) ?>
                </div>
            </div>

            <div class="col-lg-6 derecha">
                <?php $url = Url::to(['update', 'idagenda' => $model->id]); ?>
                
                <p>
                <?= Html::button('Agendar Cita', 
                            ['value'=>  $url, 'class' => 'btn btn-success btn-lg btn-create', 'id'=>'modalButtonCreate']) 
                ?>
                </p>
            </div>

            <div class="form-group centrar">
                <?= Html::submitButton('Registrar', ['class' => 'btn btn-success btn-lg btn-create']) ?>
            </div>
        </div>
    </div>


    
    <?php ActiveForm::end(); ?>

</div>
