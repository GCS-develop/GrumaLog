<?php

use frontend\models\Talla;
use yii\helpers\Html;
use kartik\grid\GridView;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Difference;
use yii\widgets\DetailView;
use yii\bootstrap4\Alert;

/** @var yii\web\View $this */
/** @var app\models\Transferdevdocumentos $documento */
/** @var yii\data\ActiveDataProvider $detalles */

$this->title = 'Detalles del Documento #' . $documento->id_transferencia;
$this->params['breadcrumbs'][] = ['label' => 'Transferencias ERP', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .mi-gridview {
        font-size: 11px;
    }

    .btn-create {
        width: 300px;
    }

    .centrar {
        text-align: center;
    }

    .horizontal-line {
        border: none;
        border-top: 1px solid #ccc;
        margin: 20px 0;
    }

    .table th, .table td {
        vertical-align: middle !important;
    }

    .card-header h5 {
        margin-bottom: 0;
    }
    .verde-texto {
    color: green !important;
}

');


?>

<div class="transferencia-view">

    <!-- ✅ Mostrar alertas de éxito o error -->
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <?= Alert::widget([
            'options' => ['class' => 'alert alert-' . $type],
            'body' => $message,
        ]) ?>
    <?php endforeach; ?>

    <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Encabezado del Documento</h5>

        <?php if ($documento->estado_envio != 1): ?>
            <?= Html::a('<i class="fas fa-edit"></i> Editar Encabezado', ['update', 'id_transferencia' => $documento->id_transferencia], [
                'class' => 'btn btn-success btn-sm',
            ]) ?>
        <?php endif; ?>

    </div>


        <!-- Detalle del documento principal -->
        <?= DetailView::widget([
            'model' => $documento,
            'attributes' => [
                'id_transferencia',
                'centro_operacion',
                'tipo_documento',
                'consecutivo_documento',
                'fecha_documento',
                'tercero_proveedor',
                'sucursal_proveedor',
                'comprador',
                'consignacion',
                'notas:ntext',
                [
                    'attribute' => 'respuesta_siesa',
                    'format' => 'raw',
                    'value' => function ($model) {
                        if (empty($model->respuesta_siesa)) {
                            return Html::tag('span', 'Sin respuesta', ['class' => 'badge badge-secondary']);
                        }

                        $json = json_decode($model->respuesta_siesa, true);

                        // Si es JSON válido
                        if (is_array($json)) {
                            // Si fue exitoso
                            if (isset($json['codigo']) && $json['codigo'] == 0) {
                                return Html::tag('span', 'Enviado correctamente', ['class' => 'badge badge-success']);
                            }

                            // Si hay error
                            $mensaje = $json['mensaje'] ?? 'Error desconocido';
                            $detalle = '';

                            if (isset($json['detalle']) && is_array($json['detalle'])) {
                                foreach ($json['detalle'] as $error) {
                                    $detalle .= isset($error['f_detalle']) ? $error['f_detalle'] . '<br>' : '';
                                }
                            }

                            $contenido = Html::tag('div', 'Error: ' . Html::encode($mensaje), ['class' => 'badge badge-danger']);
                            if (!empty($detalle)) {
                                $contenido .= Html::tag('div', "<strong>Detalle:</strong><br>$detalle", ['class' => 'mt-2 text-danger']);
                            }

                            return $contenido;
                        }

                        // Si no es JSON válido, lo mostramos como texto plano
                        return Html::tag('span', Html::encode($model->respuesta_siesa), ['class' => 'badge badge-danger']);
                    },
                    'label' => 'Respuesta Siesa',
                ],

            ],
        ]) ?>
        

        <hr class="horizontal-line">

        <div class="centrar">

    <div class="centrar">
    <?php if ($documento->estado_envio != 1): ?>
        <?= Html::a('Ejecutar Transferencia', ['ejecutar-transferencia', 'id' => $documento->id_transferencia], [
            'class' => 'btn btn-success',
            'data-confirm' => '¿Estás seguro de enviar esta transferencia a Siesa?',
            'data-method' => 'post',
        ]) ?>
    <?php else: ?>
        <div class="alert alert-info">
            <strong>Nota:</strong> Esta transferencia ya fue enviada a Siesa.
        </div>
    <?php endif; ?>

    <?= Html::a('Salir', Yii::$app->request->referrer, [
        'class' => 'btn btn-success',
    ]) ?>
</div>

        <hr class="horizontal-line">

        <!-- Tabla de detalles -->
        <h4>Detalles del Documento</h4>

        <?= GridView::widget([
            'dataProvider' => $detalles,
            'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
            'options' => ['class' => 'mi-gridview'],
            'showPageSummary' => true,
            'columns' => [
                //'id', 
                [

                    'label' => 'CENTRO DE OPERACION',
                    'value' => function () use ($documento) {
                        return $documento->centro_operacion;
                    },
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                ],
                [
                    'label' => 'TIPO DE DOCUMENTO',
                    'value' => function () use ($documento) {
                        return $documento->tipo_documento;
                    },
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                ],
                [
                    'attribute' => 'bodegadestino',
                    'label' => 'BODEGA',
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                    'value' => function () {
                        return '214';
                    }
                ],
                [
                    'attribute' => 'motivo',
                    'label' => 'MOTIVO',
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                    'value' => function () use ($documento) {
                        return $documento->motivo;
                    }
                ],

                [
                    'attribute' => 'unidadMedida', // Nombre del atributo en el modelo
                    'label' => 'UNIDAD DE MEDIDA',  // Nombre personalizado para el encabezado
                    'hAlign' => 'left', // Alineación horizontal al centro
                    'vAlign' => 'middle', // Alineación vertical al centro
                    'value' => function ($model) {
                        return 'UND'; // Siempre devuelve "UND", sin importar el valor real
                    },
                ],
                [
                    'attribute' => 'cantidadRegistrada', // Nombre del atributo en el modelo
                    'hAlign' => 'center', // Alineación horizontal al centro
                    'vAlign' => 'middle', // Alineación vertical al centro
                    'format' => ['decimal', 0], // Formato decimal con 0 decimales
                    'label' => 'CANTIDAD BASE',
                    'filter' => '',
                    'value' => function ($model) {
                        $equivalencia = 1;

                        if ($model->unidadempaque) {
                            $equivalencia = $model->unidadempaque->equivalencia;
                        };
                        return $model->cantidadRegistrada * $equivalencia;
                    },
                    'pageSummary' => true,
                ],
                
                
                /*[
                    'label' => 'VALOR BRUTO',
                    'hAlign' => 'right',
                    'vAlign' => 'middle',
                    'format' => 'raw',  // Importante: evita que se apliquen formatos numéricos
                    'value' => function ($model) {
                        $equivalencia = 1;
                        if ($model->unidadempaque) {
                            $equivalencia = $model->unidadempaque->equivalencia;
                        }

                        $cantidadBase = $model->cantidadRegistrada * $equivalencia;
                        $costo = $model->getCosto();

                        // Calcular el valor bruto
                        $valorBruto = $cantidadBase * $costo;

                        // Quitar comas/puntos con number_format sin separadores
                        return number_format($valorBruto, 0, '', '');  // ← esto da por ejemplo: "1000000"
                    },
                    'pageSummary' => true,
                ],*/
[
    'label' => 'EAN',
    'value' => fn($m) => $m->codigoBarras ? trim($m->codigoBarras) : '(sin EAN)',
],

                [
    'label' => 'VALOR BRUTO',
    'hAlign' => 'right',
    'vAlign' => 'middle',
    'format' => 'raw',
    'value' => function ($model) {
        $equivalencia = 1;
        if ($model->unidadempaque) {
            $equivalencia = $model->unidadempaque->equivalencia;
        }

        $cantidadBase = $model->cantidadRegistrada * $equivalencia;
        $costo = $model->getCosto();

        // VALOR BRUTO exacto sin redondeo ni formato oculto
        $valorBruto = $cantidadBase * $costo;

        // Mostrar con los mismos decimales exactos que se usan para Siesa
        return number_format($valorBruto, 2, ',', '.');

    },
    'pageSummary' => true,
],



                [
                    'attribute' => 'item',
                    'label' => 'ITEM',
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                ],



                [
                    'attribute' => 'color',
                    'label' => 'COLOR',
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                    'value' => function ($model) {
                        return $model->color; // Asegúrate de que esto es lo que debe mostrarse
                    },
                ],


                [
                    'label' => 'TALLA',
                    'hAlign' => 'left',
                    'vAlign' => 'middle',
                    'value' => function ($model) {
                        return $model->talla ?? 'NA';
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'buttons' => [
                        'delete' => function ($url, $model) {
                            return Html::a(
                                '<i class="fas fa-trash-alt"></i>',
                                ['transferdevdocumentodetalle/delete', 'id' => $model->id], // Asegúrate de que 'id' sea la clave primaria
                                [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Eliminar',
                                    'data-confirm' => '¿Estás seguro de eliminar este detalle?',
                                    'data-method' => 'post',
                                ]
                            );
                        },
                    ],
                ],

            ],
        ]); ?>

    </div>
    <?php
    $script = <<< JS
        $('#btn-ejecutar-transferencia').on('click', function () {
            let id = $(this).data('id');

            $.ajax({
                url: '/devolucion/transferdevdocumentos/enviar-a-api', // Debes crear esta acción
                type: 'POST',
                data: { id: id },
                success: function (response) {
                    alert('Transferencia enviada a Siesa correctamente.');
                    console.log(response);
                },
                error: function () {
                    alert('Error al enviar la transferencia.');
                }
            });
        });
        JS;

    $this->registerJs($script);
    ?>