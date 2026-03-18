<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap4\Modal;
use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var array $matriz */

$this->title = 'Tipos Documento Despacho';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .matriz-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        transition: box-shadow .2s;
    }
    .matriz-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,0.15); }
    .matriz-header {
        border-radius: 10px 10px 0 0;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: .5px;
        padding: 14px 18px;
    }
    .doc-chip {
        display: inline-flex;
        align-items: center;
        background: #f0f4ff;
        border: 1px solid #c5d0f0;
        border-radius: 20px;
        padding: 4px 12px;
        margin: 4px;
        font-size: 13px;
        color: #2c3e50;
        white-space: nowrap;
    }
    .doc-chip .chip-remove {
        margin-left: 8px;
        color: #c0392b;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        font-size: 14px;
    }
    .doc-chip .chip-remove:hover { color: #e74c3c; }
    .btn-add-doc {
        border: 2px dashed #aaa;
        border-radius: 20px;
        padding: 4px 14px;
        font-size: 13px;
        color: #666;
        background: transparent;
        cursor: pointer;
        margin: 4px;
        display: inline-block;
    }
    .btn-add-doc:hover { border-color: #3498db; color: #3498db; background: #eaf4fb; }
    .empty-msg { color: #aaa; font-size: 13px; font-style: italic; }
    .origen-title { font-size: 22px; font-weight: 700; margin-bottom: 20px; }
    .cedi-header   { background: #2980b9; color: #fff; }
    .tienda-header { background: #e67e22; color: #fff; }
    .despachar-sub { border-left: 4px solid #27ae60; }
    .recibir-sub   { border-left: 4px solid #2980b9; }
    .accion-label  { font-size: 13px; font-weight: 600; text-transform: uppercase;
                     letter-spacing: 1px; color: #555; margin-bottom: 8px; }
');

$this->registerJs(<<<JS
$(document).on('click', '.btn-add-doc', function(e) {
    e.preventDefault();
    var url = $(this).attr('value');
    $('#modal-title-tdd').text('Agregar tipo de documento');
    $.get(url, function(data) {
        $('#modalContentData').html(data);
        $('#modaldata').modal('show');
    });
});
$(document).on('click', '.btn_update', function(e) {
    e.preventDefault();
    var url = $(this).attr('value');
    $('#modal-title-tdd').text('Editar tipo de documento');
    $.get(url, function(data) {
        $('#modalContentData').html(data);
        $('#modaldata').modal('show');
    });
});
JS);

$colores = [
    'Cedi'   => ['header' => 'cedi-header',   'badge' => 'badge-primary'],
    'Tienda' => ['header' => 'tienda-header', 'badge' => 'badge-warning'],
];
?>

<?php
Modal::begin([
    'title'   => '<h5 id="modal-title-tdd">Agregar tipo de documento</h5>',
    'id'      => 'modaldata',
    'size'    => 'modal-lg',
    'options' => ['tabindex' => false],
]);
echo "<div id='modalContentData'></div>";
Modal::end();
?>

<div class="tipodocumentodespacho-index">

    <?= Alert::widget() ?>

    <p class="text-muted mb-4">
        <i class="fas fa-info-circle"></i>
        Define qué tipos de documento puede usar cada origen cuando despacha o recibe.
    </p>

    <div class="row">

        <?php foreach ($matriz as $origen => $acciones): ?>
        <div class="col-lg-6 mb-4">
            <div class="card matriz-card">

                <div class="card-header matriz-header <?= $colores[$origen]['header'] ?>">
                    <i class="fas <?= $origen === 'Cedi' ? 'fa-warehouse' : 'fa-store' ?>"></i>
                    &nbsp;<?= $origen ?>
                </div>

                <div class="card-body">

                    <?php foreach ($acciones as $accion => $docs): ?>
                    <div class="mb-4 p-3 <?= strtolower($accion) ?>-sub rounded">

                        <div class="accion-label">
                            <i class="fas <?= $accion === 'Despachar' ? 'fa-truck' : 'fa-inbox' ?>"></i>
                            <?= $accion ?>
                        </div>

                        <div class="docs-container">
                            <?php if (empty($docs)): ?>
                                <span class="empty-msg">Sin documentos asignados</span>
                            <?php else: ?>
                                <?php foreach ($docs as $doc): ?>
                                <span class="doc-chip">
                                    <?php if ($doc->tipoDocumento): ?>
                                        <strong><?= Html::encode($doc->tipoDocumento->codigo) ?></strong>
                                        &nbsp;<?= Html::encode($doc->tipoDocumento->nombre) ?>
                                    <?php else: ?>
                                        ID <?= $doc->id ?>
                                    <?php endif; ?>
                                    <?= Html::a(
                                        '×',
                                        ['delete', 'id' => $doc->id],
                                        [
                                            'class' => 'chip-remove',
                                            'title' => 'Quitar',
                                            'data'  => [
                                                'confirm' => '¿Quitar este documento de ' . $origen . ' / ' . $accion . '?',
                                                'method'  => 'post',
                                            ],
                                        ]
                                    ) ?>
                                </span>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?= Html::button(
                                '<i class="fas fa-plus"></i> Agregar',
                                [
                                    'class' => 'btn-add-doc',
                                    'value' => Url::to(['create', 'origen' => $origen, 'accion' => $accion]),
                                    'id'    => null,
                                    'title' => "Agregar documento a $origen / $accion",
                                ]
                            ) ?>
                        </div>

                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

</div>
