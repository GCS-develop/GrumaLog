# GRUMALOG

Sistema de gestión logística para **Grupo Mayorista S.A** (NIT: 900.091.175), desarrollado sobre el framework **Yii 2 Advanced Template**. Integra procesos de traspasos, ventas, despachos, devoluciones, contabilidad, nómina, inventarios y comunicación con el ERP **SIESA Cloud**.

---

## Tabla de contenido

- [Tecnologías](#tecnologías)
- [Requisitos](#requisitos)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Módulos del sistema](#módulos-del-sistema)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Dependencias principales](#dependencias-principales)
- [Integración con SIESA](#integración-con-siesa)
- [Permisos y roles (RBAC)](#permisos-y-roles-rbac)

---

## Tecnologías

| Tecnología | Versión |
|---|---|
| PHP | >= 7.4 |
| Yii 2 Advanced | ~2.0.45 |
| Bootstrap | 4.x / 5.x |
| AdminLTE | 3.x |
| Apache | 2.4 |
| MySQL / MariaDB | - |
| Zona horaria | America/Bogota |
| Moneda | COP (Peso colombiano) |

---

## Requisitos

- PHP >= 7.4 con extensiones: `pdo_mysql`, `mbstring`, `intl`, `json`, `gd`
- Apache 2.4 con `mod_rewrite` habilitado
- Composer
- MySQL / MariaDB
- Acceso a la red interna del servidor (`192.168.2.20`)

---

## Estructura del proyecto

```
GRUMALog/
├── backend/                  Aplicación de administración
│   ├── controllers/
│   ├── views/
│   └── web/                  Entry point backend
├── common/
│   ├── components/           Servicios compartidos (MailService, SiesaPrecioService)
│   ├── config/               Configuración global
│   └── models/               Modelos compartidos (User, etc.)
├── console/
│   ├── controllers/          Comandos de consola (tareas programadas)
│   └── migrations/           Migraciones de base de datos
├── frontend/
│   ├── config/               Configuración de la aplicación web
│   ├── controllers/          Controladores base (SiteController)
│   ├── models/               Modelos propios del frontend
│   ├── modules/              Módulos funcionales (ver sección Módulos)
│   ├── views/                Vistas y layouts
│   └── web/                  Entry point frontend
├── environments/             Overrides por entorno (dev / prod)
├── vendor/                   Dependencias de terceros
├── composer.json
└── yii                       CLI de Yii
```

---

## Módulos del sistema

| Módulo | Ruta | Descripción |
|---|---|---|
| **agenda** | `frontend/modules/agenda` | Gestión de agenda y presupuestos por categoría |
| **siesa** | `frontend/modules/siesa` | Integración con ERP SIESA Cloud (web services de bodegas, inventarios, órdenes de compra, transferencias, documentos) |
| **catalogos** | `frontend/modules/catalogos` | Catálogos maestros: bodegas, tipos de documento, colores, tallas, categorías, subcategorías, transportadoras |
| **nomina** | `frontend/modules/nomina` | Gestión de empleados logísticos y de tienda, horas extras, asignación de usuarios a conteos y despachos |
| **traspaso** | `frontend/modules/traspaso` | Traslado de mercancía entre bodegas (auditoría, detalle auditado, dashboard de traspasos) |
| **programacion** | `frontend/modules/programacion` | Programación de entregas de mercancía (conteo por lectura, factura de entrega) |
| **crossdocking** | `frontend/modules/crossdocking` | Operaciones de cross-docking (CDSC: conteo por destino, usuarios, tránsito) |
| **despacho** | `frontend/modules/despacho` | Gestión de despachos (conductores, vehículos, planillas de embarque, bodegas de usuario) |
| **ventas** | `frontend/modules/ventas` | Módulo de ventas (facturas, cotizaciones de precio, análisis de ventas, POS, proveedores, transferencias) |
| **transporte** | `frontend/modules/transporte` | Administración de vehículos de transporte |
| **ordencompra** | `frontend/modules/ordencompra` | Órdenes de compra y detalle temporal para aprobación |
| **devolucion** | `frontend/modules/devolucion` | Devoluciones de mercancía (documentos, detalle, importación) |
| **productostiquetesprecio** | `frontend/modules/productostiquetesprecio` | Generación e impresión de tiquetes de precio para productos |
| **auditoriamanual** | `frontend/modules/auditoriamanual` | Auditoría manual de documentos e importaciones |
| **contabilidad** | `frontend/modules/contabilidad` | Conciliación, gastos de tienda, importación de ventas, reporte de créditos a empleados, devolución de mercancía contable |
| **distribucion** | `frontend/modules/distribucion` | Distribución y entrega de pedidos a destinos |
| **hunter** | `frontend/modules/hunter` | API Hunter para consultas externas |
| **grumascanmarcacion** | `frontend/modules/grumascanmarcacion` | GRUMAScan: conteo físico por lectura de código, marcación, ranking de operarios, reportes de conteos |

---

## Instalación

### 1. Clonar o descomprimir el proyecto

```bash
# En el directorio raíz de Apache
cd C:/Apache24/htdocs/
# Copiar/clonar el proyecto como GRUMALog
```

### 2. Instalar dependencias

```bash
cd GRUMALog
composer install
```

### 3. Inicializar el entorno

```bash
php init
# Seleccionar: 0 (Development) o 1 (Production)
```

### 4. Configurar base de datos

Editar `common/config/main-local.php`:

```php
return [
    'components' => [
        'db' => [
            'class'    => 'yii\db\Connection',
            'dsn'      => 'mysql:host=localhost;dbname=grumalog',
            'username' => 'tu_usuario',
            'password' => 'tu_password',
            'charset'  => 'utf8',
        ],
    ],
];
```

### 5. Ejecutar migraciones

```bash
php yii migrate
```

### 6. Configurar Apache

```apache
<VirtualHost *:80>
    DocumentRoot "C:/Apache24/htdocs/GRUMALog/frontend/web"
    ServerName grumalog.local

    <Directory "C:/Apache24/htdocs/GRUMALog/frontend/web">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Configuración

### Variables de entorno principales

| Parámetro | Descripción |
|---|---|
| `adminEmail` | Correo del administrador del sistema |
| `grupo` | Nombre de la empresa (`Grupo mayorista S.A`) |
| `nit` | NIT de la empresa (`900.091.175`) |
| `timeZone` | Zona horaria (`America/Bogota`) |
| `tipodocumento_traspaso` | Código de tipo de documento para traspasos (`2TB`) |
| `tipodocumento_crossdocking` | Código para cross-docking (`2TA`) |

### Correo (SMTP)

Configurado con Gmail SMTP en `common/config/main.php` mediante `symfony/mailer`.
Actualizar credenciales en el DSN: `smtp://usuario@gmail.com:app_password@smtp.gmail.com:587`

---

## Dependencias principales

| Paquete | Propósito |
|---|---|
| `mdmsoft/yii2-admin` | Gestión de roles y permisos RBAC |
| `hail812/yii2-adminlte3` | Tema visual AdminLTE 3 |
| `kartik-v/yii2-grid` | GridView avanzado con búsqueda y exportación |
| `kartik-v/yii2-export` | Exportación a Excel, PDF, CSV |
| `kartik-v/yii2-widget-select2` | Selectores con búsqueda |
| `kartik-v/yii2-widget-datepicker` | Selector de fecha |
| `kartik-v/yii2-widget-fileinput` | Carga de archivos |
| `kartik-v/yii2-detail-view` | Vista de detalle mejorada |
| `kartik-v/yii2-money` | Campo de entrada de valores monetarios |
| `kartik-v/yii2-number` | Campo de entrada numérica |
| `phpoffice/phpspreadsheet` | Lectura/escritura de archivos Excel |
| `mpdf/mpdf` | Generación de documentos PDF |
| `mike42/escpos-php` | Impresión térmica ESC/POS |
| `diecoding/yii2-barcode-generator` | Generación de códigos de barras |
| `philippfrenzel/yii2fullcalendar` | Calendario interactivo (agenda) |
| `symfony/mailer` | Envío de correos electrónicos |
| `xstreamka/yii2-mobile-detect` | Detección de dispositivos móviles |

---

## Integración con SIESA

GRUMALOG se comunica con el ERP **SIESA Cloud** mediante la API REST de **Connekta**:

- **Endpoint producción:** `https://serviciosconnekta.siesacloud.com/api/v3/ejecutarconsulta`
- **Endpoint conectores:** `https://serviciosconnekta.siesacloud.com/api/v3/conectoresimportar`
- **ID Compañía:** `8203`

Los servicios disponibles (en `frontend/modules/siesa/controllers/`) incluyen:

| Servicio | Descripción |
|---|---|
| `ProductosWsController` | Consulta de productos |
| `InventariosWsController` | Consulta de inventarios |
| `BodegasWsController` | Consulta de bodegas |
| `ProveedoresWsController` | Consulta de proveedores |
| `OrdenesCompraWsController` | Consulta/envío de órdenes de compra |
| `TransferenciasWsController` | Envío de transferencias al ERP |
| `TiposDocumentoWsController` | Consulta de tipos de documento |
| `DocumentosiesaController` | Gestión de documentos SIESA |
| `TransferenciaerpController` | Transferencias al ERP con log de errores |

---

## Permisos y roles (RBAC)

La autenticación y autorización usa **`yii\rbac\DbManager`** con el módulo `mdmsoft/yii2-admin`.

- Login disponible en: `site/login`
- Auto-login habilitado (cookie `_identity-frontend`)
- Acciones públicas (sin autenticación): `site/*`, `hunter/hunter-api/*`
- Gestión de roles en: `/admin`

### Logs

Los logs de la aplicación se almacenan en:

- `frontend/runtime/logs/app.log` — errores y warnings generales
- `frontend/runtime/logs/inventario-YYYY-MM-DD.log` — operaciones de inventario/SIESA
