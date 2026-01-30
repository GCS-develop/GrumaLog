<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\export\ExportMenu;

$fecha_actual = date('Y-m-d');
$filename = 'Auditados_por_usuario_' . $fecha_actual;

$this->params['breadcrumbs'][] = $this->title ?? '';
$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::className()]]
);

/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \frontend\models\search\TraspasodetalleauditadoSearch $searchModel */
/** @var int|null $idtraspaso */
?>

<link rel="stylesheet" href="css/shared.css">

<?php
// Modal para ver eliminados (por traspaso y opcionalmente por usuario)
yii\bootstrap4\Modal::begin([
    'title' => '<h4>Traspaso detalle auditado ELIMINADOS</h4>',
    'id'    => 'modaldata2',
    'size'  => 'modal-lg',
    'options' => ['tabindex' => false],
]);
echo "<div id='modalContentData2'></div>";
yii\bootstrap4\Modal::end();
?>

<div class="traspasodetalleauditado-agrupado-index">
    <div class="row">
        <h1 class="col-lg-12 centrar"> Traspasos con auditoría (agrupado por usuario) </h1>

        <div class="col-lg-12">
            <?= $this->render('_search_agrupado', ['model' => $searchModel, 'idtraspaso' => $idtraspaso]) ?>
        </div>

        <div class="col-lg-6 derecha">
            <?php
            // Botón general "Ver eliminados" para el traspaso (si viene en contexto)
            $urlEliminados = Url::to([
                '/traspaso/traspasodetalleauditadodelete/index',
                'idtraspaso' => $idtraspaso,
            ]);
            ?>
            <p>
                <?= Html::button('Ver eliminados', [
                    'value' => $urlEliminados,
                    'class' => 'btn btn-success btn-lg btn-create',
                    'id'    => 'modalButtonCreateEliminados'
                ]) ?>
            </p>
        </div>

        <div class="col-lg-6 izquierda">
            <?php
            // Columnas exportables (coinciden con el grid)
            $exportCols = [
                ['attribute' => 'idTraspaso', 'label' => 'Traspaso'],
                ['attribute' => 'consecutivoSiesa', 'label' => 'Consec. SIESA'],
                ['attribute' => 'serie', 'label' => 'Serie'],
                ['attribute' => 'Origen', 'label' => 'Origen'],
                ['attribute' => 'Destino', 'label' => 'Destino'],
                ['attribute' => 'created_by', 'label' => 'Usuario ID'],
                ['attribute' => 'creador', 'label' => 'Usuario'],
                ['attribute' => 'total_registros', 'label' => 'Registros'],
                ['attribute' => 'total_unidades', 'label' => 'Unidades'],
                ['attribute' => 'primera', 'label' => 'Primera'],
                ['attribute' => 'ultima', 'label' => 'Última'],
            ];

            echo ExportMenu::widget([
                'dataProvider'    => $dataProvider,
                'columns'         => $exportCols,
                'fontAwesome'     => true,
                'filename'        => $filename,
                'dropdownOptions' => ['label' => 'Exportar', 'class' => 'btn btn-primary btn-lg btn-create'],
                'exportConfig'    => [
                    ExportMenu::FORMAT_TEXT    => false,
                    ExportMenu::FORMAT_HTML    => false,
                    ExportMenu::FORMAT_EXCEL   => false,
                    ExportMenu::FORMAT_PDF     => false,
                    ExportMenu::FORMAT_CSV     => false,
                    ExportMenu::FORMAT_EXCEL_X => [
                        'label'       => 'Excel 2007+',
                        'icon'        => 'file-excel-o',
                        'iconOptions' => ['class' => 'text-success btn-create'],
                        'options'     => ['title' => 'Microsoft Excel 2007+ (xlsx)'],
                        'alertMsg'    => 'Se va a generar un archivo Excel (xlsx).',
                        'extension'   => 'xlsx',
                        'writer'      => ExportMenu::FORMAT_EXCEL_X
                    ],
                ]
            ]);
            ?>
        </div>

        <div class="col-lg-12">
            <?php Pjax::begin([
                'id' => 'pjax-tda-agrupado',
                'timeout' => 8000,
                'enablePushState' => false,
            ]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'showPageSummary' => true,
                'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
                'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                'options' => ['class' => 'mi-gridview'],
                'hover' => true,
                'condensed' => false,
                'responsive' => true,
                'toolbar' => false,
                'export' => false,
                'panel' => [
                    'type' => 'primary',
                    'heading' => 'Resumen por usuario creador',
                ],
                'rowOptions' => function ($row) {
                    // Colorea opcionalmente por volumen
                    $u = (int)($row['total_unidades'] ?? 0);
                    if ($u > 0 && $u < 10)   return ['class' => 'fila-verde'];
                    if ($u >= 10 && $u < 50) return ['class' => 'fila-amarilla'];
                    if ($u >= 50)            return ['class' => 'fila-roja'];
                    return [];
                },
                'columns' => [
                    ['class' => 'kartik\grid\SerialColumn'],

                    [
                        'attribute' => 'serie',
                        'label' => 'Serie',
                        'contentOptions' => ['style' => 'width:90px;'],
                    ],
                    [
                        'attribute' => 'consecutivoSiesa',
                        'label' => 'Consec. SIESA',
                        'group' => true,
                        'contentOptions' => ['style' => 'width:140px;'],
                    ],
                    [
                        'attribute' => 'Origen',
                        'label' => 'Origen',
                    ],
                    [
                        'attribute' => 'Destino',
                        'label' => 'Destino',
                    ],
                    [
                        'attribute' => 'created_by',
                        'label' => 'ID Usuario',
                        'contentOptions' => ['style' => 'width:110px;'],
                    ],
                    [
                        'attribute' => 'creador',
                        'label' => 'Usuario',
                        'group' => true,
                        'contentOptions' => ['style' => 'min-width:220px'],
                    ],
                    [
                        'attribute' => 'total_registros',
                        'label' => 'Cantidad registros auditados',
                        'value' => fn($r) => (int)$r['total_registros'],
                        'format' => ['decimal', 0],
                        'hAlign' => 'right',
                        'pageSummary' => true,
                        'contentOptions' => ['style' => 'width:150px;'],
                    ],
                    [
                        'attribute' => 'total_unidades',
                        'label' => 'Cantidad unidades auditados',
                        'value' => fn($r) => (int)$r['total_unidades'],
                        'format' => ['decimal', 0],
                        'hAlign' => 'right',
                        'pageSummary' => true,
                        'contentOptions' => ['style' => 'width:150px;'],
                    ],
                    // Fechas separadas (como en tu vista)
                    [
                        'attribute' => 'primera',
                        'label' => 'Fecha Crea',
                        'value' => fn($r) => substr((string)$r['primera'], 0, 10),
                    ],
                    [
                        'attribute' => 'primera',
                        'label' => 'Hora Crea',
                        'value' => fn($r) => substr((string)$r['primera'], 10, 16),
                    ],
                    [
                        'attribute' => 'ultima',
                        'label' => 'Fecha Actualiza',
                        'value' => fn($r) => substr((string)$r['ultima'], 0, 10),
                    ],
                    [
                        'attribute' => 'ultima',
                        'label' => 'Hora Actualiza',
                        'value' => fn($r) => substr((string)$r['ultima'], 10, 16),
                    ],

                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{ver} {verEliminados} {borrarCeros}',
                        'header' => 'Acciones',
                        'buttons' => [
                            // Ver filas (detalle por traspaso, CRUD por fila)
                            'ver' => function ($url, $row) {
                                $url = \yii\helpers\Url::to([
                                    '/traspaso/traspasodetalleauditado/index',
                                    'idtraspaso' => (int)$row['idTraspaso'],
                                    // opcional: para filtrar por el creador en ese index (si lo soportas)
                                    // 'created_by' => (int)$row['created_by'],
                                ]);
                                return \yii\helpers\Html::a('Ver filas', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'data-pjax' => 0, // importante: que no lo intercepte PJAX
                                    'title' => 'Ver líneas auditadas del traspaso',
                                ]);
                            },


                            // Ver eliminados (de este traspaso y usuario) en modal
                            'verEliminados' => function ($url, $row) {
                                $url = Url::to([
                                    '/traspaso/traspasodetalleauditadodelete/index',
                                    'idtraspaso'    => $row['idTraspaso'],
                                    'creadorUserId' => $row['created_by'],
                                ]);
                                return Html::button('Eliminados', [
                                    'value' => $url,
                                    'class' => 'btn btn-sm btn-success',
                                    'title' => 'Ver historial eliminado de este usuario/traspaso',
                                    'id'    => 'modalButtonCreateEliminados', // usa el mismo JS que ya tienes
                                ]);
                            },
                            // Borrar SOLO lo aportado por este usuario (deja copia en *_delete con el usuario que ejecuta)
                            'borrarCeros' => function ($url, $row) {
                                $url = \yii\helpers\Url::to([
                                    '/traspaso/traspasodetalleauditado/delete-zeros-by-user',
                                    'idtraspaso'    => (int)$row['idTraspaso'],
                                    'creadorUserId' => (int)$row['created_by'],
                                ]);
                                return \yii\helpers\Html::a('Borrar ceros', $url, [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'title' => 'Archivar y borrar los registros con cantidad = 0 de este usuario',
                                    'data'  => [
                                        'method'  => 'post',
                                        'confirm' => '¿Seguro? Se archivarán (si faltan) y luego se borrarán los CANTIDAD = 0.',
                                    ],
                                    'data-pjax' => 0,
                                ]);
                            },

                        ],
                        'contentOptions' => ['style' => 'width:320px; white-space:nowrap;'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>