<?php
// use Yii;
use yii\helpers\Url;

$baseUrl = Url::base(true);

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href=<?= rtrim($baseUrl, '/') . '/index.php' ?> class="brand-link">
        <img src="<?= $assetDir ?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">GRUMALog Total 2.0</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= $assetDir ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">
                    <?php if (!Yii::$app->user->isGuest): ?>
                        <?= Yii::$app->user->identity->username; ?>
                    <?php else: ?>
                        GCS Soluciones
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <!-- <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2 sidebar-no-expand">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([

                'items' => [

                    [
                        'label' => 'Sistema',
                        'icon' => 'cogs',
                        'badge' => '<span class="right badge badge-info">7</span>',
                        'items' => [
                            ['label' => 'Asignar Acceso', 'url' => ['/admin/assignment'], 'iconStyle' => 'far'],
                            ['label' => 'Empleado', 'url' => ['/catalogos/empleado/index'], 'iconStyle' => 'far'],
                            ['label' => 'Usuarios', 'url' => ['/admin/user'], 'iconStyle' => 'far'],
                            ['label' => 'Permisos', 'url' => ['/admin/permission'], 'iconStyle' => 'far'],
                            ['label' => 'Roles', 'url' => ['/admin/role'], 'iconStyle' => 'far'],
                            ['label' => 'Rutas', 'url' => ['/admin/route'], 'iconStyle' => 'far'],
                        ]
                    ],


                    [
                        'label' => 'Catálogos',
                        'icon' => 'list',
                        'badge' => '<span class="right badge badge-info">7</span>',
                        'items' => [
                            ['label' => 'Bodega', 'url' => ['/catalogos/bodegas/index'], 'iconStyle' => 'far'],
                            ['label' => 'Bodega - Tipo Dcto.', 'url' => ['/catalogos/bodegatipodocumento/index'], 'iconStyle' => 'far'],
                            ['label' => 'Cross Docking', 'url' => ['/catalogos/crossdocking/index'], 'iconStyle' => 'far'],
                            ['label' => 'Categoría', 'url' => ['/catalogos/categoria/index'], 'iconStyle' => 'far'],
                            ['label' => 'Condición de Pago', 'url' => ['/catalogos/condicionpago/index'], 'iconStyle' => 'far'],                            
                            ['label' => 'Estados Agenda', 'url' => ['/catalogos/estadoagenda/index'], 'iconStyle' => 'far'],
                            ['label' => 'Impresoras', 'url' => ['/catalogos/impresora'], 'iconStyle' => 'far'],
                            ['label' => 'Subcategoría', 'url' => ['/catalogos/subcategoria/index'], 'iconStyle' => 'far'],
                            ['label' => 'Transportadora', 'url' => ['/catalogos/transportadora/index'], 'iconStyle' => 'far'],
                            //['label' => 'Usuarios Logistica', 'url' => ['/nomina/empleadologistica/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    [
                        'label' => 'SIESA - Consultas',
                        'icon' => 'cogs',
                        'badge' => '<span class="right badge badge-info">7</span>',
                        'items' => [
                            ['label' => 'Bodegas', 'url' => ['/siesa/bodegas-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Tipos Documento', 'url' => ['/siesa/tipos-documento-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Productos', 'url' => ['/siesa/productos-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Proveedores', 'url' => ['/siesa/proveedores-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Inventarios', 'url' => ['/siesa/inventarios-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Ordenes Compra', 'url' => ['/siesa/ordenes-compra-ws/index'], 'iconStyle' => 'far'],
                            ['label' => 'Transferencias', 'url' => ['/siesa/transferencias-ws/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    [
                        'label' => 'SIESA -Conectores',
                        'icon' => 'cogs',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Integración ERP', 'url' => ['/siesa/transferenciaerp/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    //['label' => 'Simple Link', 'icon' => 'th', 'badge' => '<span class="right badge badge-danger">New</span>'],
            
                    ['label' => 'Ingreso', 'header' => true],
                    ['label' => 'Login', 'url' => ['/admin/user/login'], 'icon' => 'user', 'visible' => Yii::$app->user->isGuest],

                    /*['label' => 'Gii',  'icon' => 'file-code', 'url' => ['/gii'], 'target' => '_blank'],
                                   ['label' => 'Debug', 'icon' => 'bug', 'url' => ['/debug'], 'target' => '_blank'],
                                   ['label' => 'MULTI LEVEL EXAMPLE', 'header' => true],
                                   ['label' => 'Level1'],
                                   [
                                       'label' => 'Level1',
                                       'items' => [
                                           ['label' => 'Level2', 'iconStyle' => 'far'],
                                           [
                                               'label' => 'Level2',
                                               'iconStyle' => 'far',
                                               'items' => [
                                                   ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle'],
                                                   ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle'],
                                                   ['label' => 'Level3', 'iconStyle' => 'far', 'icon' => 'dot-circle']
                                               ]
                                           ],
                                           ['label' => 'Level2', 'iconStyle' => 'far']
                                       ]
                                   ],
                                   ['label' => 'Level1'],
                                   ['label' => 'LABELS', 'header' => true],
                                   ['label' => 'Important', 'iconStyle' => 'far', 'iconClassAdded' => 'text-danger'],
                                   ['label' => 'Warning', 'iconClass' => 'nav-icon far fa-circle text-warning'],
                                   ['label' => 'Informational', 'iconStyle' => 'far', 'iconClassAdded' => 'text-info'],*/
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>