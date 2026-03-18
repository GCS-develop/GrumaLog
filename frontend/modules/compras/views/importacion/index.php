<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use common\widgets\Alert;
use yii\bootstrap4\Modal;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->registerJsFile(
    Yii::$app->request->baseUrl . '/js/mainDataModal.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);

$this->registerCss('
    .mi-gridview { font-size: 12px; }
    .centrar     { text-align: center; }
    .separacion  { display: block; margin-top: 1em; margin-bottom: 1em; }
    .badge-estado-ok  { background-color: #28a745; color:#fff; padding:3px 8px; border-radius:4px; }
    .badge-estado-err { background-color: #dc3545; color:#fff; padding:3px 8px; border-radius:4px; }
    .badge-estado-nd  { background-color: #6c757d; color:#fff; padding:3px 8px; border-radius:4px; }
');

/** @var yii\web\View $this */
/** @var frontend\models\search\ComprasimportacionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Compras - Importar Archivo';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php
Modal::begin([
    'title'   => '<h5><i class="fas fa-file-excel mr-2"></i>Cargar Archivo Excel</h5>',
    'id'      => 'modaldata',
    'size'    => 'modal-lg',
    'options' => ['tabindex' => false],
]);
echo "<div id='modalContentData'></div>";
Modal::end();
?>

<div class="comprasimportacion-index">

    <?= Alert::widget() ?>

    <?php $url = Url::to(['upload']); ?>

    <div class="row mb-3">
        <div class="col-lg-12 centrar">
            <?= Html::button(
                '<i class="fas fa-file-upload mr-2"></i>Importar Archivo Excel',
                ['value' => $url, 'class' => 'btn btn-success btn-lg', 'id' => 'modalButtonCreate']
            ) ?>
            &nbsp;
            <?= Html::a(
                '<i class="fas fa-file-excel mr-2"></i>Descargar Formato',
                Url::to(['/compras/importacion/descargar-formato']),
                [
                    'class' => 'btn btn-outline-success btn-lg',
                    'title' => 'Descarga la plantilla Excel con las columnas requeridas',
                ]
            ) ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary'      => 'Mostrando {begin} - {end} de {totalCount} archivos cargados',
        'formatter'    => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'options'      => ['class' => 'mi-gridview'],
        'columns'      => [

            ['class' => 'kartik\grid\SerialColumn'],

            [
                'attribute'    => 'id',
                'hAlign'       => 'center',
                'vAlign'       => 'middle',
                'headerOptions'=> ['style' => 'width:60px'],
            ],

            [
                'attribute' => 'totalUnidades',
                'label'     => 'Total Unidades',
                'hAlign'    => 'right',
                'vAlign'    => 'middle',
                'format'    => ['decimal', 0],
            ],

            [
                'attribute' => 'numeroRegistros',
                'label'     => 'Registros',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'format'    => ['decimal', 0],
            ],

            [
                'attribute' => 'created_at',
                'label'     => 'Fecha Carga',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'value'     => fn($m) => substr($m->created_at, 0, 16),
            ],

            [
                'attribute' => 'created_by',
                'label'     => 'Usuario',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'value'     => fn($m) => $m->usuariocrea->username ?? '-',
            ],

            [
                'attribute' => 'estadoSiesa',
                'label'     => 'SIESA',
                'hAlign'    => 'center',
                'vAlign'    => 'middle',
                'format'    => 'raw',
                'value'     => function ($m) {
                    if ($m->estadoSiesa === null) return '<span class="badge badge-estado-nd">-</span>';
                    if ($m->estadoSiesa == 1) {
                        $doc = ($m->tipoDocSiesa && $m->numDocSiesa)
                            ? '<br><small class="text-success font-weight-bold">' . $m->tipoDocSiesa . '-' . $m->numDocSiesa . '</small>'
                            : '';
                        return '<span class="badge badge-estado-ok"><i class="fas fa-check mr-1"></i>OK</span>' . $doc;
                    }
                    return '<span class="badge badge-estado-err"><i class="fas fa-times mr-1"></i>Error</span>';
                },
            ],

            [
                'class'         => ActionColumn::class,
                'header'        => 'Acciones',
                'headerOptions' => ['style' => 'width:130px; text-align:center'],
                'template'      => '{view} {delete}',
                'buttons'       => [

                    'view' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-eye"></i> Ver Documento',
                            ['/compras/importacion/view', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-primary', 'title' => 'Ver detalle']
                        );
                    },

                    'delete' => function ($url, $model) {
                        return Html::a(
                            '<i class="fas fa-trash"></i>',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'btn btn-sm btn-danger ml-1',
                                'title' => 'Eliminar',
                                'data'  => [
                                    'confirm' => '¿Eliminar la importación #' . $model->id . '? Se borrarán todos los registros.',
                                    'method'  => 'post',
                                ],
                            ]
                        );
                    },
                ],
            ],
        ],
    ]); ?>

</div>
