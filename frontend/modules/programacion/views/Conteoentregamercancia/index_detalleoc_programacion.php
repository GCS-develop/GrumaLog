<?php

$this->registerCss('
    .mi-gridview {
        font-size: 12px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

    .btn-create {
        width: 300px;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc; /* Color y grosor de la línea */
        margin: 10px 0; /* Espacio alrededor de la línea */
    }

    .centrar {
        text-align: center;
    }

    .mi-titulo-red {
        font-weight: bold;
        font-size: 18px;
        color: red;
    }

    .mi-titulo-black {
        font-weight: bold;
        font-size: 18px;
        color: black;
    }
');

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use kartik\grid\SerialColumn;

use frontend\models\Programacionentregamercancia;
use frontend\models\Conteoentregamercancia;

$this->title = 'Conteo - Curva de Tallas y Colores';
$this->params['breadcrumbs'][] = ['label' => 'Programación', 'url' => ['/programacion/facturaentregamercancia/indexconteoprogramacion', 'idfactura' => $modelprogramacion->idFacturaEntregaMercancia]];
$this->params['breadcrumbs'][] = $this->title;

$fecha_actual = date("Y-m-d");

$consecutivo = $modelprogramacion->agendaEntregaMercancia->ordenCompra->consecutivo;
$tipodocumento = $modelprogramacion->agendaEntregaMercancia->ordenCompra->tipoDocumento->codigo;
$co = $modelprogramacion->agendaEntregaMercancia->ordenCompra->cO->codigo;
$numeroorden = $co . '-' . $tipodocumento . '-' . $consecutivo;

$filename = "Relacion_Conteo_Matriz_CurvaTallasColores_" . $numeroorden . "_" . $fecha_actual;
$filenameBD = "Relacion_Conteo_BD_CurvaTallasColores_" . $numeroorden . "_" . $fecha_actual;

?>

<?php

// Obtener todas las tallas únicas
$tallasUnicas = [];
foreach ($dataProvider as $fila) {
    foreach (array_keys($fila) as $columna) {
        if ($columna !== 'numeroOrden' && $columna !== 'item' && $columna !== 'color' && $columna !== 'descripcion' && $columna !== 'Item' && $columna !== 'Color' && $columna !== 'totalUnidadesAsignadas' && $columna !== 'totalUnidadesConteo') {
            if (!in_array($columna, $tallasUnicas)) {
                $tallasUnicas[] = $columna;
            }
        }
    }
}

//					

$gridColumns = [
    [
        'attribute' => 'codigoCentroOperacion',
        'label' => 'f470_id_co',
    ],
    [
        'attribute' => 'codigoTipoDocumento',
        'label' => 'f470_id_tipo_docto',
    ],
    [
        'attribute' => 'consecutivo',
        'label' => 'f470_consec_docto',
    ],
    [
        'attribute' => 'numeroRegistro',
        'label' => 'f470_nro_registro',
    ],
    [
        'attribute' => 'idBodega',
        'label' => 'f470_id_bodega',
        'value' => function () {
            return '210';
        }
    ],
    [
        'attribute' => 'unidadEmpaque',
        'label' => 'f470_id_unidad_medida',
    ],
    [
        'attribute' => 'fechaEntrega',
        'label' => 'f421_fecha_entrega',
        'format' => ['date', 'php:Ymd'],
    ],
    [
        'attribute' => 'unidadesConteo',
        'label' => 'f470_cant_base',
    ],
    [
        'attribute' => 'notas',
        'label' => 'f470_notas',
        'value' => function ($model) {
            return 'Recepción Mercancía OC: ' . $model->tipoDocumento . '-' . $model->consecutivo . ' PRV: ' . $model->razonSocial;
        }
    ],
    [
        'attribute' => 'item',
        'label' => 'f470_id_item',
    ],
    [
        'attribute' => 'talla',
        'label' => 'f470_id_ext1_detalle',
    ],
    [
        'attribute' => 'color',
        'label' => 'f470_id_ext2_detalle',
    ],
    [
        'attribute' => 'numeroFila',
        'label' => 'f470_rowid',
    ],

];

// Definir las columnas para el GridView
$columns = [
    [
        'attribute' => 'Numero con',
        'label' => 'Items',
        'headerOptions' => ['style' => 'background-color:#4CAF50; color:white;'], // Estilo del encabezado
        'contentOptions' => ['style' => 'text-align:center;'], // Estilo del contenido
    ],
    [
        'attribute' => 'Almacen',
        'label' => 'Almacen',
        'headerOptions' => ['style' => 'background-color:#f2f2f2;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Orden',
        'label' => 'Orden',
        'headerOptions' => ['style' => 'background-color:#4CAF50; color:white;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Proovedor',
        'label' => 'Proovedor',
        'headerOptions' => ['style' => 'background-color:#f2f2f2;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Numero fact',
        'label' => 'Numero fact',
        'headerOptions' => ['style' => 'background-color:#4CAF50; color:white;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Tipo proovedor',
        'label' => 'Tipo proovedor',
        'headerOptions' => ['style' => 'background-color:#f2f2f2;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Fecha',
        'label' => 'Fecha',
        'headerOptions' => ['style' => 'background-color:#4CAF50; color:white;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Fecha inicio',
        'label' => 'Fecha inicio',
        'headerOptions' => ['style' => 'background-color:#f2f2f2;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],
    [
        'attribute' => 'Fecha fin',
        'label' => 'Fecha fin',
        'headerOptions' => ['style' => 'background-color:#4CAF50; color:white;'],
        'contentOptions' => ['style' => 'text-align:center;'],
    ],

    [
        'attribute' => 'item', // Nombre del atributo en el modelo
        'label' => 'Item', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'color', // Nombre del atributo en el modelo
        'label' => 'color', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    [
        'attribute' => 'descripcion', // Nombre del atributo en el modelo
        'label' => 'Descripción', // Etiqueta de la columna
        'hAlign' => 'left', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ],

    /*[
        'attribute' => 'totalUnidadesAsignadas', // Nombre del atributo en el modelo
        'label' => 'Total UND OC', // Etiqueta de la columna
        'hAlign' => 'right', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],*/

    [
        'attribute' => 'totalUnidadesConteo', // Nombre del atributo en el modelo
        'label' => 'Total UND Conteo', // Etiqueta de la columna
        'hAlign' => 'right', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
        'format' => ['decimal', 0], // Formato decimal con 0 decimales,
        'pageSummary' => true,

    ],
];

// Agregar las columnas por talla dinámicamente
foreach ($tallasUnicas as $talla) {
    $columns[] = [
        'label' => 'Conteo Talla: ' . $talla,
        'format' => 'raw',
        'value' => function ($model) use ($talla, $modelprogramacion) {
            $unidades = 0;
            if (isset($model[$talla]['unidadesConteo'])) {
                $unidades = (int)$model[$talla]['unidadesConteo'];
            }
            if ($unidades <= 0) return '0';
            $item  = \yii\helpers\Html::encode($model['item']);
            $color = \yii\helpers\Html::encode($model['color']);
            $tallaEnc = \yii\helpers\Html::encode($talla);
            return $unidades . "&nbsp;<button type='button' class='btn btn-danger btn-xs' title='Borrar talla {$tallaEnc} (máx: {$unidades})'
                        onclick=\"borrarConteosTalla({$modelprogramacion->id}, '{$item}', '{$color}', '{$tallaEnc}', {$unidades})\">
                        <i class='fa fa-trash'></i>
                    </button>";
        },
        'pageSummary' => false,
        'hAlign' => 'right',
        'vAlign' => 'middle',
    ];

    /*$columns[] = [
        'attribute' => $talla,
        'value' => function ($model) use ($talla) {
            //return isset($model[$talla]) ? $model[$talla]['unidadesAsignadas'] : null;
            $unidades = 0;

            if (isset($model[$talla])) {
                if (isset($model[$talla]['unidadesAsignadas'])) {
                    // La clave 'unidadesConteo' está definida en la fila actual
                    $unidades = $model[$talla]['unidadesAsignadas'];
                }
            } 

            return $unidades;
        },
        'label' => 'Emp Talla: ' . $talla,
        'pageSummary' => true,
        'hAlign' => 'right', // Alineación horizontal al centro
        'vAlign' => 'middle', // Alineación vertical al centro
    ];*/
}

// Columna de acción por fila (reducir conteo de color específico)
$columns[] = [
    'label' => 'Reducir',
    'format' => 'raw',
    'value' => function ($model) use ($modelprogramacion) {
        if ((int)$model['totalUnidadesConteo'] <= 0) return '';
        $item  = \yii\helpers\Html::encode($model['item']);
        $color = \yii\helpers\Html::encode($model['color']);
        $total = (int)$model['totalUnidadesConteo'];
        return "<button type='button' class='btn btn-danger btn-xs' title='Borrar unidades de {$color} (máx: {$total})'
                    onclick=\"borrarConteosFila({$modelprogramacion->id}, '{$item}', '{$color}', {$total})\">
                    <i class='fa fa-trash'></i>
                </button>";
    },
    'hAlign' => 'center',
    'vAlign' => 'middle',
];

// Agrupar los resultados por item
$itemsAgrupados = [];
foreach ($dataProvider as $fila) {
    $item = $fila['item'];
    if (!isset($itemsAgrupados[$item])) {
        $itemsAgrupados[$item] = [];
    }
    $itemsAgrupados[$item][] = $fila;
}

?>

<div class="ordendecompradetalle-index">

    <!--
    <div class="row">
        <div class="col-lg-12 centrar">
            <?= Html::a(
                'Generar Excel',
                [
                    'generarexcelconteocurvas',
                    'idprogramacion' => $modelprogramacion->id
                ],
                [
                    'class' => 'btn btn-success btn-lg btn-create',
                ]
            ) ?>
        </div>
    </div>
    -->

    <?php
    $csrfParam = Yii::$app->request->csrfParam;
    $csrfToken = Yii::$app->request->getCsrfToken();
    $this->registerJs("
        function submitPostForm(action, fields) {
            var params = [];
            for (var n in fields) {
                params.push(encodeURIComponent(n) + '=' + encodeURIComponent(fields[n]));
            }
            var fullAction = action + '&' + params.join('&');
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = fullAction;
            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = " . json_encode($csrfParam) . ";
            csrf.value = " . json_encode($csrfToken) . ";
            form.appendChild(csrf);
            document.body.appendChild(form);
            form.submit();
        }

        function borrarConteosFila(idprogramacion, item, color, maxUnidades) {
            var cantidad = prompt('Eliminar unidades de Item: ' + item + ' | Color: ' + color + ' - Máximo: ' + maxUnidades, '');
            if (cantidad === null) return;
            cantidad = parseInt(cantidad);
            if (isNaN(cantidad) || cantidad <= 0) { alert('Ingrese un número válido mayor a 0.'); return; }
            if (cantidad > maxUnidades) { alert('No puede eliminar más de ' + maxUnidades + ' unidades.'); return; }
            submitPostForm('" . \yii\helpers\Url::to(['reducirconteosfila']) . "', {
                'idprogramacion': idprogramacion, 'item': item, 'color': color, 'cantidad': cantidad
            });
        }

        function borrarConteosTalla(idprogramacion, item, color, talla, maxUnidades) {
            if (maxUnidades <= 0) return;
            var cantidad = prompt('Eliminar unidades - Item: ' + item + ' | Color: ' + color + ' | Talla: ' + talla + ' - Máximo: ' + maxUnidades, '');
            if (cantidad === null) return;
            cantidad = parseInt(cantidad);
            if (isNaN(cantidad) || cantidad <= 0) { alert('Ingrese un número válido mayor a 0.'); return; }
            if (cantidad > maxUnidades) { alert('No puede eliminar más de ' + maxUnidades + ' unidades.'); return; }
            submitPostForm('" . \yii\helpers\Url::to(['reducirconteostalla']) . "', {
                'idprogramacion': idprogramacion, 'item': item, 'color': color, 'talla': talla, 'cantidad': cantidad
            });
        }

        function borrarConteosItem(idprogramacion, item, maxUnidades) {
            var cantidad = prompt('Cuantas unidades desea eliminar del item ' + item + '? (maximo: ' + maxUnidades + ')', '');
            if (cantidad === null) return;
            cantidad = parseInt(cantidad);
            if (isNaN(cantidad) || cantidad <= 0) { alert('Ingrese un número válido mayor a 0.'); return; }
            if (cantidad > maxUnidades) { alert('No puede eliminar más de ' + maxUnidades + ' unidades.'); return; }
            submitPostForm('" . \yii\helpers\Url::to(['reducirconteositem']) . "', {
                'idprogramacion': idprogramacion, 'item': item, 'cantidad': cantidad
            });
        }
    ", \yii\web\View::POS_END);
    ?>

    <div class="row">

        <div class="col-lg-3 centrar">
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => new \yii\data\ArrayDataProvider([
                        'allModels' => $dataProvider,
                        'pagination' => false, // Opcional: desactiva la paginación si no la necesitas
                    ]),
                    'columns' => $columns,
                    'fontAwesome' => true,
                    'filename' => $filename,
                    'dropdownOptions' => [
                        'label' => 'Exportar Matriz',
                        'class' => 'btn btn-success btn-lg btn-create',
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
                            'iconOptions' => ['class' => 'text-success'],
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

        <div class="col-lg-3 centrar">
            <?= Html::a(
                'Borrar Conteos',
                [
                    'deleteconteos',
                    'idprogramacion' => $modelprogramacion->id,
                ],
                [
                    'class' => 'btn btn-danger btn-lg btn-create',
                    'data' => [
                        'confirm' => '¿Está seguro de borrar TODOS los conteos de esta programación? ( OC:' . $modelprogramacion->agendaEntregaMercancia->ordenCompra->tipoDocumento->codigo . '-' .
                            $modelprogramacion->agendaEntregaMercancia->ordenCompra->cO->codigo . '-' .
                            $modelprogramacion->agendaEntregaMercancia->ordenCompra->consecutivo . ' ) El valor máximo quedará en 0.',
                        'method' => 'post',
                    ]
                ]
            ) ?>
        </div>

        <div class="col-lg-3 centrar">
            <?= Html::a(
                'Finalizar Conteo',
                [
                    'finalizarconteo',
                    'id' => $modelprogramacion->id,
                    'origen' => 'programacion',
                    'idfactura' => $modelprogramacion->idFacturaEntregaMercancia
                ],
                [
                    'class' => 'btn btn-success btn-lg btn-create',
                    'data' => [
                        'confirm' => 'Esta Seguro de FinalizarEste Conteo Este Registro? ( OC:' . $modelprogramacion->agendaEntregaMercancia->ordenCompra->tipoDocumento->codigo . '-' .
                            $modelprogramacion->agendaEntregaMercancia->ordenCompra->cO->codigo . '-' .
                            $modelprogramacion->agendaEntregaMercancia->ordenCompra->consecutivo . ' )',
                        'method' => 'post',
                    ]
                ]
            ) ?>
        </div>

        <div class="col-lg-3 centrar">
            <?php echo ExportMenu::widget(
                [
                    'dataProvider' => $dataProviderBD,
                    'columns' => $gridColumns,
                    'fontAwesome' => true,
                    'filename' => $filenameBD,
                    'dropdownOptions' => [
                        'label' => 'Exportar BD',
                        'class' => 'btn btn-success btn-lg btn-create',
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
                            'iconOptions' => ['class' => 'text-success'],
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

    <!-- Espacio -->
    <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px;"></div>
    </div>

    <?php
    echo '<div class="mi-titulo-black">OC:' . $modelprogramacion->agendaEntregaMercancia->ordenCompra->tipoDocumento->codigo . '-' .
        $modelprogramacion->agendaEntregaMercancia->ordenCompra->cO->codigo . '-' .
        $modelprogramacion->agendaEntregaMercancia->ordenCompra->consecutivo . '</div>';
    ?>

    <div class="row">
        <div class="col-md-12" style="margin-bottom: 20px;"></div>
    </div>

    <?php

    foreach ($dataByItem as $item => $data) {

        $totalUnidadesAsignadas = $modelprogramacion->unidadesAsignadas;
        $totalUnidadesConteo = Conteoentregamercancia::totalCantidadConteo(
            $modelprogramacion->id,
            $item,
            $modelprogramacion->userConteo->user->id
        );

        if ($totalUnidadesAsignadas != $totalUnidadesConteo) {
            echo "<p class='mi-titulo-red'>Item: $item" . ' - ' . 'Total UND Asignadas: ' . $totalUnidadesAsignadas . "</p>";
        } else {
            echo "<p class='mi-titulo-black'>Item: $item" . ' - ' . 'Total UND Asignadas: ' . $totalUnidadesAsignadas . "</p>";
        }
        ?>

        <?= GridView::widget([
            'dataProvider' => new \yii\data\ArrayDataProvider([
                //'allModels' => $dataProvider,
                'allModels' => $data,
                'pagination' => false, // Opcional: desactiva la paginación si no la necesitas
            ]),

            'summary' => '',
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'options' => [
                'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
            ],
            'showPageSummary' => true,

            'columns' => $columns,

            /*'rowOptions' => function ($model, $key, $index, $grid) {
                $options = [];
                $diferencia = $model['totalUnidadesAsignadas'] - $model['totalUnidadesConteo'];

                if ($diferencia != 0){
                    $options['style'] = 'background-color: #ff9999;'; // Puedes cambiar el color aquí
                }

                return $options;
            },*/

        ]); ?>

        <?php if ($totalUnidadesConteo > 0): ?>
        <div style="margin: 8px 0 20px 0;">
            <button type="button" class="btn btn-warning btn-sm"
                onclick="borrarConteosItem(<?= $modelprogramacion->id ?>, '<?= \yii\helpers\Html::encode($item) ?>', <?= (int)$totalUnidadesConteo ?>)">
                Borrar conteos de Item <?= \yii\helpers\Html::encode($item) ?> (contado: <?= (int)$totalUnidadesConteo ?>)
            </button>
        </div>
        <?php endif; ?>

    <?php } ?>


</div>