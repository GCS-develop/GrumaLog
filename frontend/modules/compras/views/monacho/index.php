<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->title = 'Compras - Pedido Monacho';
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
    .monacho-card { border-radius: 10px; box-shadow: 0 3px 16px rgba(0,0,0,0.08); border: none; }
    .monacho-header {
        background: linear-gradient(135deg, #1a6b3a 0%, #0d4a28 100%);
        color: white; border-radius: 10px 10px 0 0; padding: 18px 22px;
    }
    .monacho-header h5 { margin: 0; font-weight: 600; font-size: 16px; }
    .monacho-header small { opacity: 0.85; font-size: 12px; }
    .upload-zone {
        border: 2px dashed #1a6b3a; border-radius: 8px;
        padding: 36px 20px; text-align: center;
        background: #f4fbf6; cursor: pointer; transition: all .25s;
    }
    .upload-zone:hover, .upload-zone.drag { background: #e2f5e8; border-color: #0d4a28; }
    .upload-zone i { font-size: 40px; color: #1a6b3a; margin-bottom: 10px; }
    .upload-zone p  { margin: 0; color: #555; font-size: 14px; }
    .upload-zone .fname { font-weight: 600; color: #1a6b3a; margin-top: 6px; font-size: 13px; }
    #file-input { display: none; }
    .counter-tip {
        background: #fffbe6; border: 1px solid #ffe082;
        border-radius: 8px; padding: 14px 16px;
    }
    .counter-tip code { font-size: 11px; background: #fff3cd; padding: 2px 5px; border-radius: 3px; }
    .step-row { display: flex; gap: 10px; align-items: flex-start; padding: 8px 0;
                border-bottom: 1px solid #f0f0f0; }
    .step-row:last-child { border: none; }
    .step-num { min-width: 26px; height: 26px; background: #1a6b3a; color:#fff;
                border-radius: 50%; display:flex; align-items:center; justify-content:center;
                font-weight:700; font-size:12px; }
    .step-text { font-size: 13px; color: #444; line-height: 1.4; }
');
?>

<div class="monacho-index">

    <?= Alert::widget() ?>

    <!-- Acceso rápido a pedidos guardados -->
    <div class="d-flex justify-content-end mb-3" style="gap:8px">
        <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-success">
            <i class="fas fa-list mr-1"></i> Ver Pedidos Guardados
        </a>
        <a href="<?= Url::to(['crear']) ?>" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Nuevo Pedido Manual
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="card monacho-card">
                <div class="monacho-header">
                    <h5><i class="fas fa-boxes mr-2"></i><?= Html::encode($this->title) ?></h5>
                    <small>Cargue el Excel del pedido Monacho para generar EAN13 y el archivo de importación SIESA</small>
                </div>

                <div class="card-body p-4">

                    <?php $form = \yii\bootstrap4\ActiveForm::begin([
                        'action'  => ['procesar'],
                        'method'  => 'post',
                        'options' => ['enctype' => 'multipart/form-data', 'id' => 'monacho-form'],
                    ]); ?>

                    <!-- Zona drag & drop -->
                    <div class="upload-zone mb-4" id="upload-zone"
                         onclick="document.getElementById('file-input').click()">
                        <i class="fas fa-file-excel"></i>
                        <p>Haga clic o arrastre el archivo <strong>Monacho.xlsx</strong></p>
                        <p class="fname" id="file-name" style="display:none"></p>
                    </div>
                    <input type="file" id="file-input" name="monacho_file" accept=".xlsx,.xls" required>

                    <!-- Contador EAN -->
                    <div class="counter-tip mb-4">
                        <label class="font-weight-bold mb-2" style="font-size:14px">
                            <i class="fas fa-sort-numeric-up mr-1"></i> Contador inicial EAN13
                        </label>
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <input type="number" name="contador_inicial" id="contador_inicial"
                                       class="form-control form-control-lg text-center font-weight-bold"
                                       value="1" min="1" max="9999" required>
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">
                                    Próximo contador disponible en el sistema. Consulte con:<br>
                                    <code>SELECT MAX(CAST(SUBSTRING(CodBarras,9,4) AS INT))+1 FROM subarticulos WHERE CodBarras LIKE '99%'</code>
                                </small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Preview EAN (ítem 334887, contador actual):
                                <strong><code id="ean-preview">9933488700011</code></strong>
                            </small>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="text-center">
                        <?= Html::submitButton(
                            '<i class="fas fa-cogs mr-2"></i> Procesar Monacho y Generar EAN',
                            ['class' => 'btn btn-success btn-lg px-5', 'id' => 'btn-procesar']
                        ) ?>
                    </div>

                    <?php \yii\bootstrap4\ActiveForm::end(); ?>
                </div>
            </div>

            <!-- Pasos -->
            <div class="card monacho-card mt-3">
                <div class="card-header" style="background:#f8f9fa; font-weight:600; font-size:13px">
                    <i class="fas fa-info-circle mr-1 text-success"></i> Pasos del proceso
                </div>
                <div class="card-body py-2 px-3">
                    <div class="step-row">
                        <div class="step-num">1</div>
                        <div class="step-text"><strong>Cargar Monacho</strong> — Excel del proveedor con artículos, colores, tallas y cantidades.</div>
                    </div>
                    <div class="step-row">
                        <div class="step-num">2</div>
                        <div class="step-text"><strong>Generar EAN13</strong> — Un código por cada combinación artículo/color/talla. Editables en la vista previa.</div>
                    </div>
                    <div class="step-row">
                        <div class="step-num">3</div>
                        <div class="step-text"><strong>Vista previa</strong> — Revise y ajuste los EAN antes de exportar.</div>
                    </div>
                    <div class="step-row">
                        <div class="step-num">4</div>
                        <div class="step-text"><strong>Exportar SIESA</strong> — Descargue el Excel listo para el conector de importación de artículos de SIESA.</div>
                    </div>
                    <div class="step-row">
                        <div class="step-num">5</div>
                        <div class="step-text"><strong>Crear OC en SIESA</strong> — Una vez cargados los artículos, genere la Orden de Compra.</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php $this->registerJs('
    var zone  = document.getElementById("upload-zone");
    var input = document.getElementById("file-input");
    var fname = document.getElementById("file-name");

    function showFile(file) {
        fname.textContent  = file ? "✓ " + file.name : "";
        fname.style.display = file ? "block" : "none";
        zone.style.borderColor = file ? "#28a745" : "#1a6b3a";
    }

    input.addEventListener("change", function() { showFile(this.files[0] || null); });

    zone.addEventListener("dragover",  function(e) { e.preventDefault(); this.classList.add("drag"); });
    zone.addEventListener("dragleave", function()   { this.classList.remove("drag"); });
    zone.addEventListener("drop", function(e) {
        e.preventDefault(); this.classList.remove("drag");
        var file = e.dataTransfer.files[0];
        if (file) {
            var dt = new DataTransfer(); dt.items.add(file);
            input.files = dt.files; showFile(file);
        }
    });

    // Preview EAN
    function previewEan() {
        var c    = parseInt(document.getElementById("contador_inicial").value) || 1;
        var base = "99334887" + c.toString().padStart(4,"0");
        var sum  = 0;
        for (var i = 0; i < 12; i++) {
            var d = parseInt(base[i]);
            sum += (i%2===0) ? d : d*3;
        }
        document.getElementById("ean-preview").textContent = base + ((10-(sum%10))%10);
    }
    document.getElementById("contador_inicial").addEventListener("input", previewEan);
    previewEan();

    // Loading
    document.getElementById("monacho-form").addEventListener("submit", function() {
        var btn = document.getElementById("btn-procesar");
        btn.disabled = true;
        btn.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-2\"></i> Procesando...";
    });
'); ?>
