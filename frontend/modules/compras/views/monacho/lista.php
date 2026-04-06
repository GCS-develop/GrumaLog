<?php
use yii\helpers\Html;
use yii\helpers\Url;
use common\widgets\Alert;
use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

$this->title = 'Pedidos Monacho';
$this->params['breadcrumbs'][] = ['label' => 'Compras', 'url' => ['/compras/importacion/index']];
$this->params['breadcrumbs'][] = $this->title;

$totalPages = max(1, (int) ceil($count / $pageSize));

$this->registerCss('
    .mon-header { background:linear-gradient(135deg,#1a6b3a 0%,#0d4a28 100%);
                  color:#fff; border-radius:10px 10px 0 0; padding:16px 20px; }
    .mon-header h5 { margin:0; font-weight:600; font-size:16px; }
    .mon-card { border-radius:10px; box-shadow:0 3px 16px rgba(0,0,0,.08); border:none; }
    .tbl-mon th { background:#1a6b3a; color:#fff; font-weight:500; font-size:13px; }
    .tbl-mon td { vertical-align:middle; font-size:13px; }
    .tbl-mon tbody tr:hover { background:#f4fbf6; }
    .badge-borrador  { background:#6c757d; color:#fff; border-radius:20px; padding:3px 10px; font-size:11px; }
    .badge-aprobado  { background:#004085; color:#fff; border-radius:20px; padding:3px 10px; font-size:11px; }
    .badge-enviado   { background:#28a745; color:#fff; border-radius:20px; padding:3px 10px; font-size:11px; }
    .search-bar { background:#f8f9fa; border-radius:8px; padding:14px 16px; margin-bottom:16px; }
    .btn-nuevo { background:#28a745; color:#fff; border:none; border-radius:6px;
                 padding:8px 20px; font-size:14px; font-weight:600; }
    .btn-nuevo:hover { background:#1e7e34; color:#fff; }
    .action-btn { font-size:12px; padding:3px 8px; }
');
?>

<div class="monacho-lista">

    <?= Alert::widget() ?>

    <div class="card mon-card">
        <div class="mon-header d-flex justify-content-between align-items-center">
            <div>
                <h5><i class="fas fa-clipboard-list mr-2"></i><?= Html::encode($this->title) ?></h5>
                <small style="opacity:.85;font-size:12px">Pedidos creados o importados desde Excel Monacho</small>
            </div>
            <div class="d-flex gap-2" style="gap:8px">
                <a href="<?= Url::to(['crear']) ?>" class="btn-nuevo btn">
                    <i class="fas fa-plus mr-1"></i> Nuevo Pedido
                </a>
                <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-light btn-sm" style="font-size:13px">
                    <i class="fas fa-upload mr-1"></i> Importar Excel
                </a>
            </div>
        </div>

        <div class="card-body p-3">

            <!-- Búsqueda -->
            <form method="get" class="search-bar">
                <div class="row align-items-end" style="gap:0">
                    <div class="col-md-5">
                        <label class="mb-1" style="font-size:12px;font-weight:600">Buscar proveedor</label>
                        <input type="text" name="q" class="form-control form-control-sm"
                               value="<?= Html::encode($search) ?>" placeholder="Nombre del proveedor...">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1" style="font-size:12px;font-weight:600">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="BORRADOR" <?= $estado === 'BORRADOR' ? 'selected' : '' ?>>Borrador</option>
                            <option value="ENVIADO"  <?= $estado === 'ENVIADO'  ? 'selected' : '' ?>>Enviado</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <button type="submit" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-search mr-1"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-2 mt-2 mt-md-0">
                        <a href="<?= Url::to(['lista']) ?>" class="btn btn-outline-secondary btn-sm btn-block">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <!-- Tabla -->
            <?php if (empty($pedidos)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                No hay pedidos<?= $search ? ' que coincidan con "' . Html::encode($search) . '"' : '' ?>.
                <br><a href="<?= Url::to(['crear']) ?>" class="text-success mt-2 d-inline-block">
                    <i class="fas fa-plus mr-1"></i> Crear primer pedido
                </a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover tbl-mon mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Proveedor</th>
                            <th>OC SIESA</th>
                            <th>Fecha Despacho</th>
                            <th class="text-center">Artículos</th>
                            <th class="text-center">Unidades</th>
                            <th class="text-center">Estado</th>
                            <th>Creado</th>
                            <th>Creado Por</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td><strong><?= $p->id ?></strong></td>
                            <td><?= Html::encode($p->proveedor) ?></td>
                            <td><?= Html::encode($p->oc_siesa ?: '-') ?></td>
                            <td><?= $p->fecha_despacho ? date('d/m/Y', strtotime($p->fecha_despacho)) : '-' ?></td>
                            <td class="text-center"><?= $p->totalArticulos ?></td>
                            <td class="text-center"><?= number_format($p->totalUnidades) ?></td>
                            <td class="text-center">
                                <span class="badge-<?= strtolower($p->estado) ?>">
                                    <?= $p->estado === 'ENVIADO' ? 'Enviado' : 'Borrador' ?>
                                </span>
                            </td>
                            <td><?= $p->created_at ? date('d/m/Y H:i', strtotime($p->created_at)) : '-' ?></td>
                            <td><?= Html::encode($p->usuariocrea->username ?? '-') ?></td>
                            <td class="text-center" style="white-space:nowrap">
                                <a href="<?= Url::to(['ver', 'id' => $p->id]) ?>"
                                   class="btn btn-sm btn-outline-success action-btn" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($p->estado !== 'APROBADO'): ?>
                                <a href="<?= Url::to(['editar', 'id' => $p->id]) ?>"
                                   class="btn btn-sm btn-outline-warning action-btn" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= Url::to(['pdf', 'id' => $p->id]) ?>" target="_blank"
                                   class="btn btn-sm btn-outline-danger action-btn" title="PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="<?= Url::to(['exportar-excel-pedido', 'id' => $p->id]) ?>"
                                   class="btn btn-sm btn-outline-primary action-btn" title="Excel SIESA">
                                    <i class="fas fa-file-excel"></i>
                                </a>
                                <a href="<?= Url::to(['eliminar', 'id' => $p->id]) ?>"
                                   class="btn btn-sm btn-outline-secondary action-btn" title="Eliminar"
                                   onclick="return confirm('¿Eliminar el pedido #<?= $p->id ?>? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-3">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?q=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>&page=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
                <p class="text-center text-muted mt-1" style="font-size:12px">
                    <?= $count ?> pedido(s) · Página <?= $page ?> de <?= $totalPages ?>
                </p>
            </nav>
            <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
