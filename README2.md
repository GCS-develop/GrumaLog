# GRUMALOG

Aplicacion corporativa construida sobre **Yii2 Advanced Template** para operaciones de logistica, ventas, programacion, despacho, contabilidad e integraciones con Siesa.

## Stack tecnologico

- PHP `>=7.4`
- Yii2 `~2.0.45`
- SQL Server (principal) y MySQL (fuente auxiliar POS)
- Apache 2.4 (entorno actual del proyecto)
- Composer
- Integraciones: `yii2-httpclient`, `PhpSpreadsheet`, `mdmsoft/yii2-admin`, AdminLTE

## Arquitectura del repositorio

```text
common/       Configuracion compartida, servicios, componentes, modelos comunes
frontend/     Aplicacion principal (modulos operativos)
backend/      Administracion y catalogos/servicios
console/      Comandos y migraciones
environments/ Plantillas de configuracion por entorno (dev/prod)
vendor/       Dependencias Composer
```

## Modulos principales

### Frontend

- `agenda`
- `auditoriamanual`
- `catalogos`
- `contabilidad`
- `crossdocking`
- `despacho`
- `devolucion`
- `distribucion`
- `grumascanmarcacion`
- `hunter`
- `nomina`
- `ordencompra`
- `productostiquetesprecio`
- `programacion`
- `siesa`
- `transporte`
- `traspaso`
- `ventas`, `ventas1`, `ventas2`

### Backend

- `catalogos`
- `siesa`
- `siesa_conector`

## Flujo funcional destacado: Contabilidad VMI

Controlador: `frontend/modules/contabilidad/controllers/ConciliacionController.php`

Flujo general:

1. `actionIndex`: captura proveedor y rango de fechas.
2. `actionPreview`: consulta movimientos (POS + inventario) desde `dbSiesa`, calcula totales y guarda preview en sesion.
3. `actionConciliacion`: cruza contra existencias consignadas, reasigna bodegas y marca faltantes sin existencia.
4. `actionExport`: genera XLSX (3 hojas: relacion saldos, documentos, cuotas CxP).
5. `actionEnviarSiesaVmi` / `actionReenviar`: construye JSON y envia a API de Siesa, guardando log en `LogFactVmi` y detalle en `LogFactVmiItem`.
6. `actionIndexLog` / `actionViewLog`: consulta historico, edicion y reenvio.

## Requisitos locales

- PHP 7.4+ con extensiones comunes de Yii2.
- Driver SQL Server para PHP (`sqlsrv`, `pdo_sqlsrv`).
- Composer 2.x.
- Apache con virtual host apuntando a:
  - `frontend/web` (sitio principal)
  - `backend/web` (administracion)

## Instalacion

```bash
composer install
php init
```

Durante `php init` seleccione el entorno (normalmente `dev` en local).

## Configuracion

### 1) Archivos locales

Revise/cree los archivos `*-local.php` en:

- `common/config/main-local.php`
- `frontend/config/main-local.php`
- `backend/config/main-local.php`
- `console/config/main-local.php`

### 2) Conexiones de BD

Este proyecto usa multiples conexiones en `common/config/main-local.php`:

- `db`: base principal GRUMALOG (SQL Server)
- `dbSiesa`: base Siesa (SQL Server)
- `dbVentasPOS`: base auxiliar POS (MySQL)
- `dbTest`, `dbDev`: conexiones de apoyo segun entorno

### 3) Correo

Configurar `mailer` (DSN SMTP) y remitente por defecto en `common/config/main.php` o `main-local.php`.

### 4) Endpoints Siesa

Configurar en `common/config/params.php` o preferiblemente en archivo local/no versionado:

- URL de consultas
- URL de conectores
- `conniKey`
- `conniToken`
- `idCompania`

## Ejecucion

Con Apache:

- Frontend: `http://<host>/GRUMALog/frontend/web`
- Backend: `http://<host>/GRUMALog/backend/web`

Con servidor embebido (solo pruebas rapidas):

```bash
php yii serve --docroot=frontend/web --port=8080
```

## Comandos utiles

```bash
# Migraciones
php yii migrate

# Ejecutar una accion de envio por consola (si aplica)
php yii contabilidad/conciliacion/enviar-job <id>
```

## Pruebas

```bash
vendor/bin/codecept run
```

Tambien hay suites separadas por capa (`common`, `frontend`, `backend`).

## Seguridad y buenas practicas

- No versionar credenciales reales (DB, SMTP, tokens API).
- Mover secretos a variables de entorno o archivos locales fuera de control de versiones.
- Rotar cualquier credencial expuesta historicamente en el repositorio.
- Mantener `YII_DEBUG=false` y `YII_ENV=prod` en produccion.

## Notas de mantenimiento

- La aplicacion depende de integraciones externas (Siesa y bases SQL Server), por lo que gran parte de las funcionalidades no se pueden validar solo con mocks locales.
- Para cambios en `contabilidad/conciliacion`, validar siempre:
  - preview de datos
  - conciliacion de existencias
  - JSON enviado a Siesa
  - persistencia en `LogFactVmi` y `LogFactVmiItem`
