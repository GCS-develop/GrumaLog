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
                    /*[
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
                    ],*/

                    [
                        'label' => 'SIESA -Conectores',
                        'icon' => 'cogs',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Integración ERP', 'url' => ['/siesa/transferenciaerp/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    [
                        'label' => 'Logística',
                        'icon' => 'calendar',
                        'badge' => '<span class="right badge badge-info">5</span>',
                        'items' => [
                            [
                                'label' => 'Ordenes de Compra',
                                'icon' => 'book',
                                'badge' => '<span class="right badge badge-info">1</span>',
                                'items' => [
                                    ['label' => 'Registrar', 'url' => ['/ordencompra/ordendecompratemporal/index'], 'iconStyle' => 'far'],
                                ],
                            ],
                            [
                                'label' => 'Datos de Control',
                                'icon' => 'book',
                                'badge' => '<span class="right badge badge-info">3</span>',
                                'items' => [
                                    ['label' => 'Usuarios', 'url' => ['/nomina/userconteo/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Período recibo mercancia', 'url' => ['/agenda/agendapresupuesto/indexperiodo'], 'iconStyle' => 'far'],
                                    ['label' => 'Fechas recibo mercancia', 'url' => ['/agenda/agendapresupuesto/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Recibo mercancia categoria', 'url' => ['/agenda/agendapresupuestosubcategoria/indexperiodo'], 'iconStyle' => 'far'],
                                ],
                            ],
                            [
                                'label' => 'Agendamiento',
                                'icon' => 'calendar-check',
                                'badge' => '<span class="right badge badge-info">3</span>',
                                'items' => [
                                    ['label' => 'Agenda Recepción Mercancia', 'url' => ['/agenda/agendaentregamercancia/indexagendaperiodo'], 'iconStyle' => 'far'],
                                    ['label' => 'Recepción Mercancia', 'url' => ['/programacion/programacionentregamercancia/indexrecepcion', 'menu' => 'recepcion'], 'iconStyle' => 'far'],
                                ],
                            ],
                            [
                                'label' => 'Programación',
                                'icon' => 'list',
                                'badge' => '<span class="right badge badge-info">3</span>',
                                'items' => [
                                    ['label' => 'Programación Recibo Mercancia', 'url' => ['/programacion/programacionentregamercancia/indexprogramacion', 'menu' => 'programacion'], 'iconStyle' => 'far'],
                                    ['label' => 'Conteo Recibo Mercancia', 'url' => ['/programacion/programacionentregamercancia/indexconteoagenda'], 'iconStyle' => 'far'],
                                    ['label' => 'Legalización Conteo', 'url' => ['/programacion/conteoentregamercancia/indexlegalizacion'], 'iconStyle' => 'far'],
                                    //['label' => 'Gestionar Conteo', 'url' => ['/programacion/conteoentregamercancia/indexall'], 'iconStyle' => 'far'],
                                ],
                            ],

                        ],
                        //'labelTemplate' => '<span style="font-size: 10px;">{label}</span>',
                    ],

                    [
                        'label' => 'Crossdocking',
                        'icon' => 'store',
                        'badge' => '<span class="right badge badge-info">2</span>',
                        'items' => [
                            ['label' => 'Usuarios', 'url' => ['/nomina/userconteocdsc/index'], 'iconStyle' => 'far'],
                            ['label' => 'Factura', 'url' => ['/crossdocking/conteocdscdestinofactura/index'], 'iconStyle' => 'far'],
                            ['label' => 'Legalizar Factura', 'url' => ['/crossdocking/conteocdscdestinofactura/indexlegaliza'], 'iconStyle' => 'far'],
                            ['label' => 'Entrada Factura', 'url' => ['/crossdocking/conteocdscdestinofactura/indexentrada'], 'iconStyle' => 'far'],
                            ['label' => 'Generar Traspaso', 'url' => ['/crossdocking/conteocdscdestinofactura/indextraspaso'], 'iconStyle' => 'far'],

                            ['label' => 'Exportar - Destino', 'url' => ['/crossdocking/conteocdscdestino/index'], 'iconStyle' => 'far'],


                            /*[
                                'label' => 'Destino',
                                'icon' => 'edit',
                                'items' => [
                                    ['label' => 'Legalización', 'url' => ['/crossdocking/conteocdscdestinofactura/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Exportar', 'url' => ['/crossdocking/conteocdscdestino/index'], 'iconStyle' => 'far'],
                                ]
                            ],

                            ['label' => 'Factura', 'url' => ['/crossdocking/conteocdscdestinofactura/index'], 'iconStyle' => 'far'],*/
                        ],
                    ],

                    [
                        'label' => 'Traspaso',
                        'icon' => 'window-restore',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Usuarios', 'url' => ['/nomina/usertraspaso/index'], 'iconStyle' => 'far'],
                            ['label' => 'Traspasos', 'url' => ['/traspaso/traspaso/index'], 'iconStyle' => 'far'],
                            ['label' => 'Detalle traspasos', 'url' => ['/traspaso/traspasodetalle/index'], 'iconStyle' => 'far'],
                            ['label' => 'Bodegas / Usuario', 'url' => ['/traspaso/traspasouserbodega/index'], 'iconStyle' => 'far'],

                        ]
                    ],

                    [
                        'label' => 'Despachos',
                        'icon' => 'car',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [

                            ['label' => 'Planilla Embarque', 'url' => ['/despacho/planillaembarque/index'], 'iconStyle' => 'far'],
                            ['label' => 'Planilla - Detalles', 'url' => ['/despacho/planillaembarquetraspaso/index'], 'iconStyle' => 'far'],

                            // [
                            //     // 'label' => 'planillaembarque',
                            //     // 'icon' => 'book',
                            //     // 'badge' => '<span class="right badge badge-info">1</span>',
                            //     // 'items' => [
                            //     //     ['label' => 'planillaembarque', 'url' => ['/despacho/planillaembarque/index'], 'iconStyle' => 'far'],
                            //     // ],
                            // ],
            
                            [
                                // 'label' => 'Catálogos',
                                'label' => 'Config',
                                'icon' => 'cog',
                                'items' => [
                                    //['label' => 'Bodega', 'url' => ['/catalogos/bodegas/indexcedi', 'cedi' => 1], 'iconStyle' => 'far'],
                                    //['label' => 'Usuarios - Planillas', 'url' => ['/despacho/userbodega/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Conductores', 'url' => ['/despacho/conductor/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Vehículos', 'url' => ['/despacho/vehiculo/index'], 'iconStyle' => 'far'],
                                    ['label' => 'Usuarios', 'url' => ['/nomina/userdespacho/index'], 'iconStyle' => 'far'],
                                ]
                            ],
                        ]
                    ],

                    [
                        'label' => 'Devoluciones',
                        'icon' => 'reply',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Importar', 'url' => ['/devolucion/devolucionimportacion/index'], 'iconStyle' => 'far'],
                            ['label' => 'Registrar Documento', 'url' => ['/devolucion/devoluciondocumento/register'], 'iconStyle' => 'far'],
                            ['label' => 'Consultar', 'url' => ['/devolucion/devoluciondocumentodetalle/indexall'], 'iconStyle' => 'far'],
                        ]
                    ],

                    /*[
                        'label' => 'Devoluciones',
                        'icon' => 'car',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            [
                                'label' => 'Config',
                                'icon' => 'edit',
                                'items' => [
                                    ['label' => 'Importar', 'url' => ['/devolucion/vehiculo/index'], 'iconStyle' => 'far'],
                                ],
                            ],
                        ]
                    ],*/

                    [
                        'label' => 'Ventas',
                        'icon' => 'dollar-sign',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Config. Impresoras paxar', 'url' => ['/productostiquetesprecio/impresoraspaxarbodega/index'], 'iconStyle' => 'far'],
                            ['label' => 'Impresion de precios', 'url' => ['/productostiquetesprecio/productostiquetesprecio/index'], 'iconStyle' => 'far'],
                            ['label' => 'Consulta', 'url' => ['/ventas/factura/index'], 'iconStyle' => 'far'],
                            ['label' => 'Cotización Precio', 'url' => ['/ventas/cotizacionprecio/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    /*[
                        'label' => 'Nomina',
                        'icon' => 'users',
                        'badge' => '<span class="right badge badge-info">1</span>',
                        'items' => [
                            ['label' => 'Horas Extras', 'url' => ['/nomina/horasextras/index'], 'iconStyle' => 'far'],
                        ]
                    ],*/

                    [
                        'label' => 'Configuración',
                        'icon' => 'tachometer-alt',
                        'badge' => '<span class="right badge badge-info">7</span>',
                        'items' => [
                            ['label' => 'Bodega', 'url' => ['/catalogos/bodegas/index'], 'iconStyle' => 'far'],
                            ['label' => 'Bodega - Tipo Dcto.', 'url' => ['/catalogos/bodegatipodocumento/index'], 'iconStyle' => 'far'],
                            ['label' => 'Cross Docking', 'url' => ['/catalogos/crossdocking/index'], 'iconStyle' => 'far'],
                            ['label' => 'Categoría', 'url' => ['/catalogos/categoria/index'], 'iconStyle' => 'far'],
                            ['label' => 'Estados Agenda', 'url' => ['/catalogos/estadoagenda/index'], 'iconStyle' => 'far'],
                            ['label' => 'Subcategoría', 'url' => ['/catalogos/subcategoria/index'], 'iconStyle' => 'far'],
                            ['label' => 'Transportadora', 'url' => ['/catalogos/transportadora/index'], 'iconStyle' => 'far'],
                            ['label' => 'Usuarios Logistica', 'url' => ['/nomina/empleadologistica/index'], 'iconStyle' => 'far'],
                        ]
                    ],

                    //['label' => 'Simple Link', 'icon' => 'th', 'badge' => '<span class="right badge badge-danger">New</span>'],
            
                    ['label' => 'Ingreso Sistema', 'header' => true],
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