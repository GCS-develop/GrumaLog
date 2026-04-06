<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->title = isset($pedidoEditar) ? 'Editar Pedido Monacho #'.$pedidoEditar->id : 'Nuevo Pedido Monacho';
$this->params['breadcrumbs'][] = ['label' => 'Compras',        'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = ['label' => 'Pedido Monacho', 'url' => ['lista']];
$this->params['breadcrumbs'][] = $this->title;

// Build edit data for JS
$editarJson = 'null';
if (isset($pedidoEditar)) {
    $artData = [];
    foreach ($articulosEditar as $art) {
        $tallas = [];
        $coloresTmp = [];
        foreach ($art->detalles as $d) {
            if (!in_array($d->talla, $tallas)) $tallas[] = $d->talla;
            $coloresTmp[$d->color][$d->talla] = $d->cantidad;
        }
        $coloresArr = [];
        foreach ($coloresTmp as $colorNombre => $cantidades) {
            $coloresArr[] = ['nombre' => $colorNombre, 'cantidades' => $cantidades];
        }
        $artData[] = [
            'codigo'       => $art->codigo,
            'descripcion'  => $art->descripcion,
            'referencia'   => $art->referencia,
            'estilo1'      => $art->estilo1,
            'estilo2'      => $art->estilo2,
            'estilo3'      => $art->estilo3,
            'estilo4'      => $art->estilo4,
            'estilo5'      => $art->estilo5,
            'concepto'     => $art->concepto,
            'consumidor'   => $art->consumidor,
            'universo'     => $art->universo,
            'prenda'       => $art->prenda,
            'tendencia'    => $art->tendencia,
            'costo'        => $art->costo,
            'precio_venta' => $art->precio_venta,
            'margen'       => $art->margen,
            'rango'        => $art->rango,
            'pvp_mayorista'=> $art->pvp_mayorista,
            'tallas'       => $tallas ?: ['S','M','L','XL'],
            'colores'      => $coloresArr,
        ];
    }
    $editarJson = json_encode([
        'pedido_id'      => $pedidoEditar->id,
        'proveedor'      => $pedidoEditar->proveedor,
        'oc_siesa'       => $pedidoEditar->oc_siesa,
        'oc_icg'         => $pedidoEditar->oc_icg,
        'fecha_despacho' => $pedidoEditar->fecha_despacho,
        'contador_ean'   => $pedidoEditar->contador_ean_inicio,
        'notas'          => $pedidoEditar->notas,
        'articulos'      => $artData,
    ]);
}

$this->registerCss('
/* ── Layout general ─────────────────────────────────── */
.mon-wrap       { background:#f4f4f4; padding:16px; border-radius:8px; }
.mon-top-bar    { background:#fff; border:1px solid #ccc; border-radius:6px;
                  padding:12px 16px; margin-bottom:16px; }
.mon-top-bar h5 { margin:0 0 10px; font-weight:700; font-size:15px; color:#1a6b3a; }

/* ── Grid de artículos (2 por fila) ─────────────────── */
.arts-grid      { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:900px){ .arts-grid { grid-template-columns:1fr; } }

/* ── Tarjeta artículo ───────────────────────────────── */
.art-card       { background:#fff; border:1px solid #aaa; border-radius:4px; overflow:hidden; }
.art-card-photo { background:#e8e8e8; height:160px; display:flex; align-items:center;
                  justify-content:center; cursor:pointer; position:relative; overflow:hidden; }
.art-card-photo img { width:100%; height:100%; object-fit:contain; }
.art-card-photo .ph-hint { color:#888; font-size:12px; text-align:center; }
.ref-badge      { position:absolute; top:6px; left:6px; background:rgba(0,0,0,.55);
                  color:#fff; font-size:10px; font-weight:700; padding:2px 7px; border-radius:3px; }
.photo-input    { display:none; }

/* ── Tabla de campos (estilo Monacho) ───────────────── */
.mon-tbl        { width:100%; border-collapse:collapse; font-size:11.5px; }
.mon-tbl td     { border:1px solid #ccc; padding:3px 6px; vertical-align:middle; }
.mon-tbl .lbl   { background:#f0f0f0; font-weight:700; color:#333; width:50%; white-space:nowrap; }
.mon-tbl .val   { background:#fff; }
.mon-tbl .full  { background:#f0f0f0; font-weight:700; color:#333; text-align:center;
                  padding:4px 6px; font-size:11px; }
.mon-tbl input, .mon-tbl select {
    border:none; outline:none; width:100%; background:transparent;
    font-size:11.5px; padding:0; font-family:inherit;
}
.mon-tbl input:focus { background:#fffde7; }

/* ── Sección colores ────────────────────────────────── */
.colors-section { padding:4px 6px 8px; }
.colors-header  { display:flex; background:#1a6b3a; color:#fff; font-size:10.5px;
                  font-weight:700; border-radius:3px 3px 0 0; }
.colors-header .ch-color { flex:0 0 100px; padding:3px 5px; border-right:1px solid rgba(255,255,255,.3); }
.colors-header .ch-talla { flex:1; text-align:center; padding:3px 2px; border-right:1px solid rgba(255,255,255,.3); }
.colors-header .ch-total { flex:0 0 50px; text-align:center; padding:3px 2px; }
.colors-header .ch-rm    { flex:0 0 24px; }

.color-row      { display:flex; align-items:center; border-bottom:1px solid #e0e0e0; }
.color-row:nth-child(even) { background:#f9f9f9; }
.color-row .cr-color { flex:0 0 100px; padding:2px 4px; border-right:1px solid #ddd; }
.color-row .cr-qty   { flex:1; text-align:center; padding:2px; border-right:1px solid #ddd; }
.color-row .cr-total { flex:0 0 50px; text-align:center; font-weight:700; font-size:11px;
                       padding:2px; color:#1a6b3a; }
.color-row .cr-rm    { flex:0 0 24px; text-align:center; }
.color-row input.color-name { border:none; outline:none; width:100%; font-size:11px;
                               text-transform:uppercase; background:transparent; }
.color-row input.qty-inp    { border:none; outline:none; width:100%; font-size:11px;
                               text-align:center; background:transparent; }
.color-row input.qty-inp:focus { background:#fffde7; }

.total-row-sum  { display:flex; background:#edf5ee; border-top:1px solid #aaa; font-size:11px; font-weight:700; }
.total-row-sum .tr-lbl   { flex:0 0 100px; padding:3px 6px; }
.total-row-sum .tr-total { flex:1; text-align:center; padding:3px 2px; }
.total-row-sum .tr-grand { flex:0 0 50px; text-align:center; padding:3px 2px; color:#1a6b3a; }

.btn-add-color  { background:#edf5ee; border:1px dashed #1a6b3a; color:#1a6b3a;
                  font-size:11px; padding:3px 10px; border-radius:3px; cursor:pointer; margin-top:4px; }
.btn-add-color:hover { background:#d4edda; }
.btn-rm-color   { background:none; border:none; color:#dc3545; cursor:pointer; font-size:12px;
                  padding:0; line-height:1; }

/* ── Tallas configuración ───────────────────────────── */
.tallas-cfg     { display:flex; align-items:center; flex-wrap:wrap; gap:4px; margin-bottom:4px; }
.talla-tag      { background:#1a6b3a; color:#fff; border-radius:3px;
                  padding:2px 6px; font-size:10px; font-weight:700;
                  display:inline-flex; align-items:center; gap:3px; }
.talla-tag .rm  { cursor:pointer; opacity:.7; font-size:10px; }
.talla-tag .rm:hover { opacity:1; }
.inp-talla-add  { border:1px solid #aaa; border-radius:3px; font-size:11px; padding:2px 6px;
                  width:55px; text-transform:uppercase; }

/* ── Botones de acción de tarjeta ───────────────────── */
.art-card-footer { background:#f8f8f8; border-top:1px solid #ccc; padding:6px 10px;
                   display:flex; justify-content:space-between; align-items:center; }
.btn-del-art    { background:#dc3545; color:#fff; border:none; border-radius:3px;
                  font-size:11px; padding:3px 10px; cursor:pointer; }

/* ── Botones principales ────────────────────────────── */
.btn-add-art    { background:#1a6b3a; color:#fff; border:none; border-radius:6px;
                  padding:9px 22px; font-size:13px; font-weight:700; cursor:pointer; }
.btn-add-art:hover { background:#0d4a28; }
.btn-save-main  { background:#28a745; color:#fff; border:none; border-radius:6px;
                  padding:10px 28px; font-size:14px; font-weight:700; cursor:pointer; }
.btn-save-main:hover { background:#1e7e34; }

/* ── Campos top ─────────────────────────────────────── */
.top-field label { font-size:11px; font-weight:700; color:#444; margin-bottom:2px; display:block; }
.top-field input, .top-field select { font-size:12px; }
');
?>

<div class="monacho-crear">

    <!-- ── BARRA SUPERIOR ──────────────────────────────────────────── -->
    <div class="mon-top-bar">
        <h5><i class="fas fa-plus-circle mr-2"></i><?= Html::encode($this->title) ?></h5>
        <div class="row">
            <div class="col-md-3 top-field">
                <label>MARCA / PROVEEDOR <span style="color:red">*</span></label>
                <input type="text" id="proveedor" class="form-control form-control-sm"
                       placeholder="CONFECCIONES PAMURE" required>
            </div>
            <div class="col-md-2 top-field">
                <label>ORDEN DE COMPRA SIESA</label>
                <input type="text" id="oc_siesa" class="form-control form-control-sm" placeholder="1257">
            </div>
            <div class="col-md-2 top-field">
                <label>ORDEN DE COMPRA ICG</label>
                <input type="text" id="oc_icg" class="form-control form-control-sm" placeholder="6655">
            </div>
            <div class="col-md-2 top-field">
                <label>FECHA DESPACHO</label>
                <input type="date" id="fecha_despacho" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 top-field">
                <label>CONTADOR EAN INICIAL <span style="color:red">*</span></label>
                <input type="number" id="contador_ean" class="form-control form-control-sm text-center"
                       value="1" min="1" max="9999" required>
            </div>
            <div class="col-md-1 top-field">
                <label>&nbsp;</label>
                <input type="text" id="notas" class="form-control form-control-sm" placeholder="Notas...">
            </div>
        </div>
        <div class="row mt-2 align-items-center">
            <div class="col">
                <button type="button" class="btn-add-art" onclick="agregarArticulo()">
                    <i class="fas fa-plus mr-1"></i> Agregar Artículo
                </button>
            </div>
            <div class="col-auto">
                <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-secondary btn-sm mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="button" class="btn-save-main" onclick="guardarPedido()">
                    <i class="fas fa-save mr-2"></i> Guardar Pedido
                </button>
            </div>
        </div>
    </div>

    <!-- ── GRID DE ARTÍCULOS ────────────────────────────────────────── -->
    <div class="arts-grid" id="arts-grid"></div>

    <!-- Aviso vacío -->
    <div id="empty-hint" class="text-center py-5 text-muted"
         style="border:2px dashed #bbb;border-radius:8px;background:#fff;">
        <i class="fas fa-box-open fa-3x mb-3 d-block" style="opacity:.25"></i>
        Haga clic en <strong>Agregar Artículo</strong> para comenzar
    </div>

    <!-- Botón guardar al final -->
    <div class="text-right mt-3" id="bottom-save" style="display:none">
        <button type="button" class="btn-save-main" onclick="guardarPedido()">
            <i class="fas fa-save mr-2"></i> Guardar Pedido
        </button>
    </div>

</div>

<?php $this->registerJs('
var articulosData = {};
var artIdx = 0;
var totalArts = 0;

var saveUrl      = "' . Url::to(['guardar']) . '";
var actualizarUrl= "' . Url::to(['actualizar']) . '";
var csrf         = "' . Yii::$app->request->csrfToken . '";
var listaUrl     = "' . Url::to(['lista']) . '";
var verUrl       = "' . Url::to(['ver', 'id' => '__ID__']) . '";

/* ── Agregar artículo ──────────────────────────────── */
function agregarArticulo() {
    var idx = ++artIdx;
    totalArts++;
    articulosData[idx] = { tallas: ["S","M","L","XL"], colorCount: 0 };

    document.getElementById("empty-hint").style.display = "none";
    document.getElementById("bottom-save").style.display = "";

    var card = document.createElement("div");
    card.className = "art-card";
    card.id = "art-" + idx;
    card.innerHTML = buildCardHtml(idx);
    document.getElementById("arts-grid").appendChild(card);

    // Inicializar chips de tallas y cabecera
    initTallas(idx);
    // Agregar primer color por defecto
    agregarColor(idx);
}

/* ── HTML de la tarjeta ─────────────────────────────── */
function buildCardHtml(idx) {
    return `
    <label class="art-card-photo" for="photo-file-${idx}" id="photo-zone-${idx}" tabindex="0" style="margin:0;display:flex;align-items:center;justify-content:center;cursor:pointer;" onclick="setFocusedZone(${idx})">
        <span class="ref-badge" id="ref-badge-${idx}">REF ______</span>
        <div class="ph-hint" id="ph-hint-${idx}">
            <i class="fas fa-image fa-2x mb-1" style="opacity:.3"></i><br>
            <small style="font-size:10px;opacity:.6;">Ctrl+V para pegar</small><br>
            Clic para añadir foto
        </div>
        <img id="photo-img-${idx}" src="" style="display:none;width:100%;height:100%;object-fit:contain;">
        <input type="file" class="photo-input" id="photo-file-${idx}"
               accept="image/*" style="display:none" onchange="showPhoto(${idx}, this)">
    </label>
    <table class="mon-tbl">
        <tr><td colspan="2" class="full">DESCRIPCION:</td></tr>
        <tr><td colspan="2" class="val" style="padding:0">
            <input type="text" class="art-descripcion" placeholder="CHAQUETA MUJER COMBINADA"
                   style="padding:3px 6px;font-size:12px;font-weight:600;"
                   oninput="updateRefBadge(${idx}, this)">
        </td></tr>

        <tr>
            <td class="lbl">ESTILO 1</td>
            <td class="lbl">ESTILO 2</td>
        </tr>
        <tr>
            <td class="val"><input type="text" class="art-estilo1" list="dl-e1-${idx}" placeholder="MANGA LARGA" autocomplete="off">
                <datalist id="dl-e1-${idx}"></datalist></td>
            <td class="val"><input type="text" class="art-estilo2" list="dl-e2-${idx}" placeholder="CHOMPA CON CIERRE" autocomplete="off">
                <datalist id="dl-e2-${idx}"></datalist></td>
        </tr>

        <tr>
            <td class="lbl">ESTILO 3</td>
            <td class="lbl">ESTILO 4</td>
        </tr>
        <tr>
            <td class="val"><input type="text" class="art-estilo3" list="dl-e3-${idx}" placeholder="REGULAR" autocomplete="off">
                <datalist id="dl-e3-${idx}"></datalist></td>
            <td class="val"><input type="text" class="art-estilo4" list="dl-e4-${idx}" placeholder="MEDIO" autocomplete="off">
                <datalist id="dl-e4-${idx}"></datalist></td>
        </tr>

        <tr>
            <td class="lbl">ESTILO 5</td>
            <td class="lbl">CONCEPTO</td>
        </tr>
        <tr>
            <td class="val"><input type="text" class="art-estilo5" list="dl-e5-${idx}" placeholder="TEJIDO PUNTO" autocomplete="off">
                <datalist id="dl-e5-${idx}"></datalist></td>
            <td class="val"><input type="text" class="art-concepto" placeholder=""></td>
        </tr>

        <tr>
            <td class="lbl">CONSUMIDOR</td>
            <td class="lbl">UNIVERSO</td>
        </tr>
        <tr>
            <td class="val"><input type="text" class="art-consumidor" placeholder="INICIO LABORAL"></td>
            <td class="val"><input type="text" class="art-universo" placeholder="ACTIVO"></td>
        </tr>

        <tr>
            <td class="lbl">PRENDA</td>
            <td class="lbl">TENDENCIA</td>
        </tr>
        <tr>
            <td class="val"><input type="text" class="art-prenda" list="dl-prenda-${idx}" placeholder="CHAQUETA" autocomplete="off" oninput="updateEstiloSuggestions(${idx})">
                <datalist id="dl-prenda-${idx}">
                    <option value="CAMISETA"><option value="SHORT"><option value="PANTALON">
                    <option value="FALDA"><option value="CONJUNTOS"><option value="ENTERIZO">
                    <option value="CHAQUETA"><option value="BLUSA"><option value="LEGGINS">
                    <option value="CAMISA"><option value="VESTIDO"><option value="CHALECO">
                    <option value="SUETER"><option value="CALZADO"><option value="BOLSOS">
                    <option value="JEAN"><option value="CAMISETA TIPO POLO"><option value="TOP">
                    <option value="TRAJE DE BANO"><option value="SALIDA"><option value="SOMBREROS">
                    <option value="BATOLA"><option value="SACO">
                </datalist></td>
            <td class="val"><input type="text" class="art-tendencia" placeholder="MODA"></td>
        </tr>

        <tr>
            <td class="lbl">CODIGO:</td>
            <td class="lbl">REFERENCIA:</td>
        </tr>
        <tr>
            <td class="val" style="width:50%">
                <input type="number" class="art-codigo" placeholder="334887"
                       oninput="updateRefBadge(${idx}, this)" style="font-weight:700">
            </td>
            <td class="val">
                <input type="text" class="art-referencia" placeholder="004705"
                       oninput="updateRefBadgeRef(${idx}, this)" style="font-weight:700">
            </td>
        </tr>

        <tr>
            <td class="lbl">COSTO:</td>
            <td class="lbl">PRECIO V:</td>
        </tr>
        <tr>
            <td class="val"><input type="number" step="100" class="art-costo" placeholder="36900"></td>
            <td class="val"><input type="number" step="100" class="art-pventa" placeholder="69900"></td>
        </tr>

        <tr>
            <td class="lbl">MARGEN:</td>
            <td class="lbl">RANGO:</td>
        </tr>
        <tr>
            <td class="val"><input type="number" step="0.1" class="art-margen" placeholder="37.2"></td>
            <td class="val"><input type="number" class="art-rango" placeholder="4"></td>
        </tr>

        <tr>
            <td class="lbl">PVP MAYORISTA</td>
            <td class="lbl">PVP REBAJA</td>
        </tr>
        <tr>
            <td class="val"><input type="number" step="100" class="art-pvp" placeholder="47500"></td>
            <td class="val"><input type="number" step="100" class="art-pvp-rebaja" placeholder=""></td>
        </tr>

        <tr><td colspan="2" class="full">COLORES:</td></tr>
    </table>

    <!-- Sección colores/tallas -->
    <div class="colors-section">
        <!-- Configurador de tallas -->
        <div style="background:#f5f5f5;border:1px solid #ddd;border-radius:4px;padding:5px 8px;margin-bottom:6px;">
            <span style="font-size:10px;font-weight:700;color:#555">TALLAS / CURVA: </span>
            <span class="tallas-cfg" id="tallas-cfg-${idx}"></span>
            <div style="display:flex;align-items:center;gap:5px;margin-top:3px">
                <input type="text" class="inp-talla-add" id="inp-talla-${idx}"
                       placeholder="+ Talla" onkeydown="if(event.key===\'Enter\'){event.preventDefault();agregarTalla(${idx});}">
                <button type="button" onclick="agregarTalla(${idx})"
                        style="border:1px solid #1a6b3a;background:#edf5ee;color:#1a6b3a;border-radius:3px;padding:2px 8px;font-size:11px;cursor:pointer">
                    + Agregar
                </button>
            </div>
        </div>

        <!-- Cabecera de colores (dinámica) -->
        <div class="colors-header" id="colors-header-${idx}"></div>

        <!-- Filas de colores -->
        <div id="colores-${idx}"></div>

        <!-- Fila total -->
        <div class="total-row-sum" id="total-row-${idx}">
            <div class="tr-lbl">TOTAL</div>
        </div>

        <button type="button" class="btn-add-color" onclick="agregarColor(${idx})">
            <i class="fas fa-plus mr-1"></i> Agregar Color
        </button>
    </div>

    <div class="art-card-footer">
        <small class="text-muted" style="font-size:10px">Artículo ${totalArts}</small>
        <button class="btn-del-art" onclick="eliminarArticulo(${idx})">
            <i class="fas fa-trash mr-1"></i> Quitar artículo
        </button>
    </div>
    `;
}

/* ── Foto ───────────────────────────────────────────── */
var lastFocusedPhotoIdx = null;

function setFocusedZone(idx) {
    lastFocusedPhotoIdx = idx;
}

function applyPhotoDataUrl(idx, dataUrl) {
    var img  = document.getElementById("photo-img-"  + idx);
    var hint = document.getElementById("ph-hint-" + idx);
    if (!img) return;
    img.src = dataUrl;
    img.style.display = "";
    hint.style.display = "none";
}

function showPhoto(idx, input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) { applyPhotoDataUrl(idx, e.target.result); };
    reader.readAsDataURL(input.files[0]);
}

/* Pegar imagen desde portapapeles (Ctrl+V) */
document.addEventListener("paste", function(e) {
    var items = (e.clipboardData || (e.originalEvent && e.originalEvent.clipboardData) || {}).items;
    if (!items) return;
    for (var i = 0; i < items.length; i++) {
        if (items[i].type.indexOf("image") !== -1) {
            var blob = items[i].getAsFile();
            if (!blob) continue;
            var targetIdx = lastFocusedPhotoIdx;
            // Si no hay zona focalizada pero solo hay 1 artículo, usarla
            if (targetIdx === null) {
                var cards = document.querySelectorAll(".art-card");
                if (cards.length === 1) targetIdx = parseInt(cards[0].id.replace("art-",""));
            }
            if (targetIdx === null) {
                alert("Haga clic primero en la zona de foto del artículo antes de pegar.");
                return;
            }
            (function(artIdx, b) {
                var reader = new FileReader();
                reader.onload = function(ev) { applyPhotoDataUrl(artIdx, ev.target.result); };
                reader.readAsDataURL(b);
            })(targetIdx, blob);
            e.preventDefault();
            break;
        }
    }
});

/* ── Badge REF ──────────────────────────────────────── */
function updateRefBadge(idx) {
    var ref = document.querySelector("#art-" + idx + " .art-referencia");
    var cod = document.querySelector("#art-" + idx + " .art-codigo");
    var badge = document.getElementById("ref-badge-" + idx);
    var v = (ref && ref.value.trim()) ? ref.value.trim() : (cod && cod.value ? cod.value : "______");
    badge.textContent = "REF " + v;
}
function updateRefBadgeRef(idx) { updateRefBadge(idx); }

/* ── Tallas ─────────────────────────────────────────── */
function renderTallasHeader(idx) {
    var data   = articulosData[idx];
    var header = document.getElementById("colors-header-" + idx);
    var tRow   = document.getElementById("total-row-" + idx);

    header.innerHTML =
        `<div class="ch-color">COLOR</div>` +
        data.tallas.map(function(t) {
            return `<div class="ch-talla">${t}</div>`;
        }).join("") +
        `<div class="ch-total">TOTAL</div>
         <div class="ch-rm"></div>`;

    tRow.innerHTML =
        `<div class="tr-lbl">TOTAL</div>` +
        data.tallas.map(function(t) {
            return `<div class="tr-total" id="tcol-${idx}-${t}">0</div>`;
        }).join("") +
        `<div class="tr-grand" id="tgrand-${idx}">0</div>`;

    // Actualizar filas existentes de color
    var coloresDiv = document.getElementById("colores-" + idx);
    coloresDiv.querySelectorAll(".color-row").forEach(function(row) {
        var rowIdx = row.dataset.rowidx;
        var colorName = row.querySelector(".color-name") ? row.querySelector(".color-name").value : "";

        var newRow = buildColorRow(idx, rowIdx, colorName, data.tallas);
        row.parentNode.replaceChild(newRow, row);
    });
    recalcTotals(idx);
}

function addTallaTag(idx, talla) {
    talla = talla.trim().toUpperCase();
    if (!talla) return;
    var data = articulosData[idx];
    if (data.tallas.includes(talla)) return;
    data.tallas.push(talla);

    var cfg = document.getElementById("tallas-cfg-" + idx);
    var tag = document.createElement("span");
    tag.className = "talla-tag";
    tag.id = "tag-" + idx + "-" + talla;
    tag.innerHTML = talla + ` <span class="rm" onclick="quitarTalla(${idx},\'${talla}\')">✕</span>`;
    cfg.appendChild(tag);

    renderTallasHeader(idx);
}

function quitarTalla(idx, talla) {
    var data = articulosData[idx];
    var i = data.tallas.indexOf(talla);
    if (i > -1) data.tallas.splice(i, 1);
    var tag = document.getElementById("tag-" + idx + "-" + talla);
    if (tag) tag.remove();
    renderTallasHeader(idx);
}

function agregarTalla(idx) {
    var inp = document.getElementById("inp-talla-" + idx);
    addTallaTag(idx, inp.value);
    inp.value = "";
    inp.focus();
}

/* ── Inicializar tallas por defecto ─────────────────── */
function initTallas(idx) {
    var data = articulosData[idx];
    var cfg  = document.getElementById("tallas-cfg-" + idx);
    data.tallas.forEach(function(t) {
        var tag = document.createElement("span");
        tag.className = "talla-tag";
        tag.id = "tag-" + idx + "-" + t;
        tag.innerHTML = t + ` <span class="rm" onclick="quitarTalla(${idx},\'${t}\')">✕</span>`;
        cfg.appendChild(tag);
    });
    renderTallasHeader(idx);
}

/* ── Color rows ─────────────────────────────────────── */
function buildColorRow(idx, rowIdx, colorName, tallas) {
    var row = document.createElement("div");
    row.className = "color-row";
    row.dataset.rowidx = rowIdx;

    var colorDiv = document.createElement("div");
    colorDiv.className = "cr-color";
    var colorInp = document.createElement("input");
    colorInp.type = "text";
    colorInp.className = "color-name";
    colorInp.placeholder = "NEGRO";
    colorInp.value = colorName || "";
    colorInp.style.textTransform = "uppercase";
    colorDiv.appendChild(colorInp);
    row.appendChild(colorDiv);

    tallas.forEach(function(t) {
        var cell = document.createElement("div");
        cell.className = "cr-qty";
        var inp = document.createElement("input");
        inp.type = "number";
        inp.min = "0";
        inp.value = "0";
        inp.className = "qty-inp qty-" + t.replace(/[^a-zA-Z0-9]/g, "_");
        inp.title = t;
        inp.dataset.talla = t;
        inp.oninput = function() { recalcRow(row, idx); };
        cell.appendChild(inp);
        row.appendChild(cell);
    });

    // Total fila
    var totalCell = document.createElement("div");
    totalCell.className = "cr-total row-total";
    totalCell.textContent = "0";
    row.appendChild(totalCell);

    // Botón eliminar
    var rmCell = document.createElement("div");
    rmCell.className = "cr-rm";
    var rmBtn = document.createElement("button");
    rmBtn.type = "button";
    rmBtn.className = "btn-rm-color";
    rmBtn.title = "Quitar color";
    rmBtn.innerHTML = "✕";
    rmBtn.onclick = function() { row.remove(); recalcTotals(idx); };
    rmCell.appendChild(rmBtn);
    row.appendChild(rmCell);

    return row;
}

function agregarColor(idx) {
    var data = articulosData[idx];
    data.colorCount++;
    var row = buildColorRow(idx, data.colorCount, "", data.tallas);
    document.getElementById("colores-" + idx).appendChild(row);
}

/* ── Recálculo de totales ───────────────────────────── */
function recalcRow(row, idx) {
    var data = articulosData[idx];
    var sum = 0;
    data.tallas.forEach(function(t) {
        var cls = "qty-" + t.replace(/[^a-zA-Z0-9]/g, "_");
        var inp = row.querySelector("." + cls);
        if (inp) sum += parseInt(inp.value) || 0;
    });
    var tc = row.querySelector(".row-total");
    if (tc) tc.textContent = sum;
    recalcTotals(idx);
}

function recalcTotals(idx) {
    var data     = articulosData[idx];
    var colDiv   = document.getElementById("colores-" + idx);
    var grandTotal = 0;

    data.tallas.forEach(function(t) {
        var cls  = "qty-" + t.replace(/[^a-zA-Z0-9]/g, "_");
        var sum  = 0;
        colDiv.querySelectorAll("." + cls).forEach(function(inp) { sum += parseInt(inp.value) || 0; });
        var cell = document.getElementById("tcol-" + idx + "-" + t);
        if (cell) cell.textContent = sum;
        grandTotal += sum;
    });
    var gc = document.getElementById("tgrand-" + idx);
    if (gc) gc.textContent = grandTotal;
}

/* ── Eliminar artículo ──────────────────────────────── */
function eliminarArticulo(idx) {
    var el = document.getElementById("art-" + idx);
    if (el) el.remove();
    delete articulosData[idx];
    totalArts = Math.max(0, totalArts - 1);
    if (Object.keys(articulosData).length === 0) {
        document.getElementById("empty-hint").style.display = "";
        document.getElementById("bottom-save").style.display = "none";
    }
}

/* ── Guardar ────────────────────────────────────────── */
function guardarPedido() {
    var proveedor = document.getElementById("proveedor").value.trim();
    if (!proveedor) { alert("Ingrese el nombre del proveedor."); return; }

    var articulos = [];

    document.querySelectorAll(".art-card").forEach(function(card) {
        var idx  = card.id.replace("art-", "");
        var data = articulosData[idx];
        if (!data) return;

        var codigo = (card.querySelector(".art-codigo").value || "").trim();
        var desc   = (card.querySelector(".art-descripcion").value || "").trim();
        if (!desc) return;

        var colores = [];
        card.querySelectorAll(".color-row").forEach(function(row) {
            var nombre = (row.querySelector(".color-name").value || "").trim().toUpperCase();
            if (!nombre) return;
            var cantidades = {};
            data.tallas.forEach(function(t) {
                var cls = "qty-" + t.replace(/[^a-zA-Z0-9]/g, "_");
                var inp = row.querySelector("." + cls);
                cantidades[t] = inp ? (parseInt(inp.value) || 0) : 0;
            });
            colores.push({ nombre: nombre, cantidades: cantidades });
        });

        if (colores.length === 0) return;

        articulos.push({
            codigo:        parseInt(codigo),
            descripcion:   desc,
            referencia:    (card.querySelector(".art-referencia").value || "").trim(),
            estilo1:       (card.querySelector(".art-estilo1").value    || "").trim(),
            estilo2:       (card.querySelector(".art-estilo2").value    || "").trim(),
            estilo3:       (card.querySelector(".art-estilo3").value    || "").trim(),
            estilo4:       (card.querySelector(".art-estilo4").value    || "").trim(),
            estilo5:       (card.querySelector(".art-estilo5").value    || "").trim(),
            concepto:      (card.querySelector(".art-concepto").value   || "").trim(),
            consumidor:    (card.querySelector(".art-consumidor").value || "").trim(),
            universo:      (card.querySelector(".art-universo").value   || "").trim(),
            prenda:        (card.querySelector(".art-prenda").value     || "").trim(),
            tendencia:     (card.querySelector(".art-tendencia").value  || "").trim(),
            costo:         parseFloat(card.querySelector(".art-costo").value)   || 0,
            precio_venta:  parseFloat(card.querySelector(".art-pventa").value)  || 0,
            pvp_mayorista: parseFloat(card.querySelector(".art-pvp").value)     || 0,
            margen:        parseFloat(card.querySelector(".art-margen").value)  || 0,
            rango:         (card.querySelector(".art-rango").value      || "").trim(),
            colores:       colores
        });
    });

    if (articulos.length === 0) {
        alert("Agregue al menos un artículo con código, descripción y colores.");
        return;
    }

    var btns = document.querySelectorAll(".btn-save-main");
    btns.forEach(function(b) {
        b.disabled = true;
        b.innerHTML = "<i class=\"fas fa-spinner fa-spin mr-2\"></i> Guardando...";
    });
    var btn = btns[0];

    var targetUrl = (_editarData && _editarData.pedido_id) ? actualizarUrl : saveUrl;
    fetch(targetUrl, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Csrf-Token": csrf },
        body: JSON.stringify({
            pedido_id:      (_editarData && _editarData.pedido_id) ? _editarData.pedido_id : null,
            proveedor:      proveedor,
            oc_siesa:       document.getElementById("oc_siesa").value.trim(),
            oc_icg:         document.getElementById("oc_icg").value.trim(),
            fecha_despacho: document.getElementById("fecha_despacho").value,
            contador_ean:   parseInt(document.getElementById("contador_ean").value) || 1,
            notas:          document.getElementById("notas").value.trim(),
            articulos:      articulos
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.href = verUrl.replace("__ID__", data.id);
        } else {
            alert("Error: " + data.message);
            btns.forEach(function(b) { b.disabled = false; b.innerHTML = "<i class=\"fas fa-save mr-2\"></i> Guardar Pedido"; });
        }
    })
    .catch(function(e) {
        alert("Error de red: " + e);
        btns.forEach(function(b) { b.disabled = false; b.innerHTML = "<i class=\"fas fa-save mr-2\"></i> Guardar Pedido"; });
    });
}

/* ── Catálogo de estilos por prenda ─────────────────── */
var estilosCatalogo = {
"CAMISETA":          {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO NERU"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO","BODY"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"SHORT":             {e1:["TIRO BAJO","TIRO MEDIO","TIRO ALTO"],e2:[],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"PANTALON":          {e1:["TIRO BAJO","TIRO MEDIO","TIRO ALTO"],e2:["BOTA RECTA","BOTA CAMPANA","BOTA AJUSTADA","BOTA PALAZZO","BOTA ENRESORTADA"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM","SKINNY"],e4:["CAPRI","TOBILLERO","LARGO"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"FALDA":             {e1:["CINTURA BAJA","CINTURA MEDIA","CINTURA ALTA"],e2:[],e3:["LINEA A","LAPIZ","SIRENA","CAPAS","GLOBO","DEPORTIVA","BOLERO","ASIMETRICA","ENVOLVENTE"],e4:["CORTO","MEDIO","LARGO","MIDI"],e5:["ENRESORTADA","LISA"]},
"CONJUNTOS":         {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4","UN SOLO HOMBRO"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO NERU","STRAPLE","CUELLO HALTER","CUELLO BEBE","CUELLO CAMISERO"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO","CAPRI","TOBILLERO","MIDI"],e5:["SHORT","FALDA","PANTALON","LEGGINS","JOGGER","JEAN"]},
"ENTERIZO":          {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4","UN SOLO HOMBRO"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO NERU","STRAPLE","CUELLO HALTER","CUELLO BEBE","CUELLO CAMISERO"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO","CAPRI","TOBILLERO"],e5:["SHORT","PANTALON","LEGGINS","JOGGER"]},
"CHAQUETA":          {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA"],e2:["BLAZER","BOMBER JACKET","BIKER","TORERO","PEPLUM","GABARDINA","CHOMPA CON CIERRE","CHOMPA SIN CIERRE","PULL OVER","VAQUERA"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"BLUSA":             {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4","UN SOLO HOMBRO"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO NERU","STRAPLE","CUELLO HALTER","CUELLO BEBE","CUELLO CAMISERO"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO","BODY"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"LEGGINS":           {e1:["TIRO BAJO","TIRO MEDIO","TIRO ALTO"],e2:[],e3:[],e4:["CORTO","CAPRI","TOBILLERO","LARGO"],e5:[]},
"CAMISA":            {e1:["MANGA CORTA","MANGA LARGA","MANGA 3/4","MANGA SISA"],e2:["CUELLO CAMISERO"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"VESTIDO":           {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4","UN SOLO HOMBRO"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO TIPO POLO","CUELLO NERU","STRAPLE","CUELLO HALTER","CUELLO BEBE","CUELLO CAMISERO"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM","SIRENA","CAPAS","GLOBO","LINEA A","LAPIZ"],e4:["CORTO","MEDIO","LARGO","MIDI"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"CHALECO":           {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","MANGA 3/4","PONCHO"],e2:[],e3:[],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"SUETER":            {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","MANGA 3/4"],e2:[],e3:[],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PUNTO"]},
"CALZADO":           {e1:[],e2:[],e3:[],e4:["BALETA","TENIS","SANDALIA","BOTA","MOCASIN","BOTIN","SANDALIA PLANA","SANDALIA TACON","CHANCLAS","TACON","PANTUFLA"],e5:["TELA","SINTETICO","CAUCHO","CUERO"]},
"BOLSOS":            {e1:["MORRAL","CANGURO","CUERDA","BAGUETTE","SOBRE","BANDOLERA","BAUL","TOTE","PLAYERO","MOCHILA"],e2:[],e3:[],e4:[],e5:["TELA","SINTETICO","CAUCHO","CUERO"]},
"JEAN":              {e1:["TIRO BAJO","TIRO MEDIO","TIRO ALTO"],e2:["BOTA RECTA","BOTA CAMPANA","BOTA AJUSTADA","BOTA PALAZZO","BOTA ENRESORTADA"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM","SKINNY"],e4:["LARGO","CAPRI","TOBILLERO"],e5:["ENRESORTADA","LISA"]},
"CAMISETA TIPO POLO":{e1:["MANGA CORTA","MANGA LARGA","MANGA 3/4"],e2:[],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:[],e5:[]},
"TOP":               {e1:["MANGA SISA","TIRAS"],e2:[],e3:[],e4:[],e5:[]},
"TRAJE DE BANO":     {e1:["TANKINI 2 PIEZAS","BIKINI 2 PIEZAS","MONOKINI 1 PIEZA","OLIMPICO","ENTERO","CONJUNTO 3 PIEZAS"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","STRAPLE","CUELLO HALTER"],e3:["COPA TRIANGULAR","COPA BALCONETTE"],e4:[],e5:["BRASILERA","TANGA","CACHETERO","CLASICO"]},
"SALIDA":            {e1:[],e2:[],e3:[],e4:["CORTO","LARGO","CAPRI","TOBILLERO","MIDI","BATA"],e5:["SHORT","FALDA","PANTALON"]},
"SOMBREROS":         {e1:["PAVA","GORRA"],e2:[],e3:[],e4:[],e5:[]},
"BATOLA":            {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","TIRAS","MANGA 3/4","UN SOLO HOMBRO"],e2:["CUELLO EN V","CUELLO REDONDO","CUELLO BANDEJA","CUELLO TORTUGA","CUELLO HALTER","CUELLO BEBE"],e3:["REGULAR","AMPLIA","AJUSTADA - SLIM"],e4:["CORTO","LARGO","MIDI"],e5:["TEJIDO PLANO","TEJIDO PUNTO"]},
"SACO":              {e1:["MANGA CORTA","MANGA LARGA","MANGA SISA","MANGA 3/4"],e2:[],e3:["CHOMPA SIN CIERRE","CHOMPA CON CIERRE"],e4:["CORTO","MEDIO","LARGO"],e5:["TEJIDO PUNTO"]}
};

function updateEstiloSuggestions(idx) {
    var card   = document.getElementById("art-" + idx);
    var prenda = (card.querySelector(".art-prenda").value || "").toUpperCase().trim();
    var data   = estilosCatalogo[prenda] || {};
    for (var i = 1; i <= 5; i++) {
        var dl = document.getElementById("dl-e" + i + "-" + idx);
        if (!dl) continue;
        dl.innerHTML = "";
        var opts = data["e" + i] || [];
        opts.forEach(function(opt) {
            var o = document.createElement("option");
            o.value = opt;
            dl.appendChild(o);
        });
    }
}
window.updateEstiloSuggestions = updateEstiloSuggestions;

/* ── Modo edición ───────────────────────────────────── */
var _editarData = ' . $editarJson . ';

function cargarEdicion() {
    if (!_editarData) return;
    // Llenar encabezado
    document.getElementById("proveedor").value      = _editarData.proveedor || "";
    document.getElementById("oc_siesa").value       = _editarData.oc_siesa || "";
    document.getElementById("oc_icg").value         = _editarData.oc_icg || "";
    document.getElementById("fecha_despacho").value = _editarData.fecha_despacho || "";
    document.getElementById("contador_ean").value   = _editarData.contador_ean || 1;
    var notasEl = document.getElementById("notas");
    if (notasEl) notasEl.value = _editarData.notas || "";

    // Agregar cada artículo
    (_editarData.articulos || []).forEach(function(a) {
        var idx = ++artIdx;
        totalArts++;
        articulosData[idx] = { tallas: a.tallas || ["S","M","L","XL"], colorCount: 0 };

        document.getElementById("empty-hint").style.display = "none";
        document.getElementById("bottom-save").style.display = "";

        var card = document.createElement("div");
        card.className = "art-card";
        card.id = "art-" + idx;
        card.innerHTML = buildCardHtml(idx);
        document.getElementById("arts-grid").appendChild(card);

        // Llenar campos del artículo
        var q = function(sel) { return card.querySelector(sel); };
        if (q(".art-descripcion"))  q(".art-descripcion").value  = a.descripcion || "";
        if (q(".art-referencia"))   q(".art-referencia").value   = a.referencia  || "";
        if (q(".art-estilo1"))      q(".art-estilo1").value      = a.estilo1     || "";
        if (q(".art-estilo2"))      q(".art-estilo2").value      = a.estilo2     || "";
        if (q(".art-estilo3"))      q(".art-estilo3").value      = a.estilo3     || "";
        if (q(".art-estilo4"))      q(".art-estilo4").value      = a.estilo4     || "";
        if (q(".art-estilo5"))      q(".art-estilo5").value      = a.estilo5     || "";
        if (q(".art-concepto"))     q(".art-concepto").value     = a.concepto    || "";
        if (q(".art-consumidor"))   q(".art-consumidor").value   = a.consumidor  || "";
        if (q(".art-universo"))     q(".art-universo").value     = a.universo    || "";
        if (q(".art-prenda"))       { q(".art-prenda").value     = a.prenda      || ""; updateEstiloSuggestions(idx); }
        if (q(".art-tendencia"))    q(".art-tendencia").value    = a.tendencia   || "";
        if (q(".art-codigo"))       q(".art-codigo").value       = a.codigo      || "";
        if (q(".art-costo"))        q(".art-costo").value        = a.costo       || "";
        if (q(".art-pventa"))       q(".art-pventa").value       = a.precio_venta|| "";
        if (q(".art-margen"))       q(".art-margen").value       = a.margen      || "";
        if (q(".art-rango"))        q(".art-rango").value        = a.rango       || "";
        if (q(".art-pvp"))          q(".art-pvp").value          = a.pvp_mayorista || "";

        updateRefBadge(idx);

        // Tallas: limpiar las default y cargar las del pedido
        articulosData[idx].tallas = a.tallas || ["S","M","L","XL"];
        var cfg = document.getElementById("tallas-cfg-" + idx);
        if (cfg) {
            cfg.innerHTML = "";
            articulosData[idx].tallas.forEach(function(t) {
                var tag = document.createElement("span");
                tag.className = "talla-tag";
                tag.id = "tag-" + idx + "-" + t;
                tag.innerHTML = t + " <span class=\"rm\" onclick=\"quitarTalla(" + idx + ",\u0027" + t + "\u0027)\">\u2715</span>";
                cfg.appendChild(tag);
            });
        }
        renderTallasHeader(idx);

        // Colores: limpiar fila vacía por defecto y cargar las del pedido
        var coloresDiv = document.getElementById("colores-" + idx);
        if (coloresDiv) coloresDiv.innerHTML = "";

        (a.colores || []).forEach(function(c) {
            if (!c.nombre) return;
            var rowIdx = ++articulosData[idx].colorCount;
            var row = buildColorRow(idx, rowIdx, c.nombre.toUpperCase(), articulosData[idx].tallas);
            // Llenar cantidades
            articulosData[idx].tallas.forEach(function(t) {
                var clsKey = "qty-" + t.replace(/[^a-zA-Z0-9]/g, "_");
                var inp = row.querySelector("." + clsKey);
                if (inp && c.cantidades && c.cantidades[t]) inp.value = c.cantidades[t];
            });
            if (coloresDiv) coloresDiv.appendChild(row);
            recalcTotals(idx);
        });

        // Si no hay colores cargados, agregar uno vacío
        if ((a.colores || []).length === 0) {
            agregarColor(idx);
        }
    });
}

// Ejecutar carga al inicio si hay datos de edición
document.addEventListener("DOMContentLoaded", function() {
    if (_editarData) {
        cargarEdicion();
    }
});

// Exponer funciones al scope global para que los onclick del HTML las encuentren
window.agregarArticulo  = agregarArticulo;
window.eliminarArticulo = eliminarArticulo;
window.guardarPedido    = guardarPedido;
window.showPhoto        = showPhoto;
window.applyPhotoDataUrl= applyPhotoDataUrl;
window.setFocusedZone   = setFocusedZone;
window.agregarTalla     = agregarTalla;
window.quitarTalla      = quitarTalla;
window.agregarColor     = agregarColor;
window.updateRefBadge   = updateRefBadge;
window.updateRefBadgeRef= updateRefBadgeRef;
window.recalcRow        = recalcRow;
'); ?>
