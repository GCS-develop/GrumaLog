<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DocumentoSearch */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = 'Documentos Repetidos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="documentos-repetidos-index">

    <?php if (!empty($dataProvider->getModels())): ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            // 'filterModel' => $searchModel,  // Asegúrate de usar el modelo de búsqueda para filtros
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'header' => 'No',
                ],
                // Ajusta las columnas para que coincidan con los alias de la consulta SQL
                [
                    'attribute' => 'f350_id_tipo_docto',
                    'label' => 'Tipo Documento',
                ],
                [
                    'attribute' => 'f350_notas',
                    'label' => 'Notas',
                ],
                [
                    'attribute' => 'consecutivosSiesa',
                    'value' => function ($model) {
                                return $model['consecutivosSiesa'];  // Asegúrate de que este campo existe
                            },
                ],
                [
                    'attribute' => 'cantidad',
                    'label' => 'Cantidad',
                    'value' => function ($model) {
                                return $model['cantidad'];  // Este campo también debe existir en el resultado de la consulta
                            },
                ],
            ],
        ]); ?>

    <?php else: ?>
        <h1 class="text-center">No se encontraron documentos repetidos.</h1>
    <?php endif; ?>

</div>