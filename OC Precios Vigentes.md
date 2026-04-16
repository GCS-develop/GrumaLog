# Módulo: OC Precios Vigentes
**Ruta en menú:** Compras → Generar OC  
**URL base:** `/compras/oc-precios/index`  
**Empresa SIESA:** CIA = 7 (Grupo Mayorista S.A.)  
**Última actualización:** 2026-04-01

---

## 1. Propósito

Permite consultar las Órdenes de Compra (OC) activas mostrando el **precio vigente actual** de cada ítem (lista de precios `001`), en lugar del precio histórico grabado en la OC al momento de crearla. Esto permite al área de compras generar el documento PDF de la OC con los precios actualizados antes de enviarlo al proveedor.

---

## 2. Archivos del módulo

| Archivo | Rol |
|---|---|
| `frontend/modules/compras/Module.php` | Definición del módulo Yii2, namespace `frontend\modules\compras` |
| `frontend/modules/compras/controllers/OcPreciosController.php` | Controlador principal con 3 acciones |
| `frontend/modules/compras/views/oc-precios/index.php` | Vista principal: filtros + grilla de resultados |

---

## 3. Controlador: `OcPreciosController`

### 3.1 `actionIndex()` — Vista principal

**GET params aceptados:**

| Parámetro | Tipo | Descripción |
|---|---|---|
| `nro_orden` | int | Número consecutivo de la OC |
| `tipo_docto` | string | Tipo de documento (ej. `2CA`, `2CM`) |
| `fecha_ord_ini` | date `YYYY-MM-DD` | Fecha mínima de la OC |
| `fecha_ord_fin` | date `YYYY-MM-DD` | Fecha máxima de la OC |
| `proveedor` | string | Busca por NIT o razón social (LIKE) |

**Comportamiento:**
- Si **ningún filtro** está activo → `$datos = []`, no ejecuta el query.
- Si **al menos un filtro** está activo → ejecuta `ejecutarConsulta()` y pasa los resultados a la vista.
- Siempre pasa al view: `$datos`, `$filtros`, `$hayFiltro`, `$tiposDocto`, `$estados`.

---

### 3.2 `actionExportar()` — Exportar a Excel

Recibe los mismos filtros que `actionIndex()`. Ejecuta la consulta y genera un archivo `.xlsx` con **PhpSpreadsheet**:

- Encabezado con fondo azul oscuro (`#1F4E79`) y texto blanco
- Autofilter en fila 1
- Primera fila congelada
- Código de barras guardado como **texto** (evita notación científica)
- Precio vigente sin decimales
- Filas pares con fondo azul claro (`#D9E1F2`)
- Columnas con ancho automático

**Nombre del archivo descargado:** `OC_Precios_Vigentes_YYYYMMDD_HHmmss.xlsx`

---

### 3.3 `actionPdf()` — Generar PDF de una OC

**GET params requeridos:** `nro_orden`, `tipo_docto`

Genera un PDF en formato **A4 horizontal** usando **mPDF** con la estructura oficial de Orden de Compra.

**Flujo interno:**
1. **Query cabecera** (`t420_cm_oc_docto`): razón social, NIT, dirección, ciudad, teléfono, condición de pago, bodega, moneda, fecha máxima de entrega.
2. **Query detalle** (`t421_cm_oc_movto`): ítems, referencias, colores, tallas, cantidades, precios vigentes y foto del ítem.
3. **PIVOT en PHP:** agrupa por `item_id + color`, crea columnas dinámicas por talla (numéricas primero, luego alfas ordenadas: XXS → XXXL).
4. **Totales:** bruto, descuento, neto.
5. **Salida:** descarga directa del PDF (`Content-Disposition: attachment`).

**Restricción importante:** Solo funciona si la consulta previa retorna exactamente **1 orden** y **1 proveedor**. Si no, el botón PDF muestra advertencia (ver sección 5.3).

---

### 3.4 Métodos privados

#### `ejecutarConsulta(array $filtros): array`

Query principal sobre `dbSiesa`. Une:

| Tabla | Alias | Propósito |
|---|---|---|
| `t420_cm_oc_docto` | `oc` | Cabecera de la OC |
| `t421_cm_oc_movto` | `mov` | Líneas/movimientos de la OC |
| `t121_mc_items_extensiones` | `ext` | Extensiones del ítem (color, talla, código de barras) |
| `t120_mc_items` | `item` | Datos maestros del ítem (referencia, descripción) |
| `t202_mm_proveedores` | `prov` | Razón social del proveedor (sucursal `000`) |
| `t200_mm_terceros` | `ter_prov` | NIT del proveedor (para filtro por NIT) |
| `t126_mc_items_precios` | `precio_vigente` | Precio vigente lista `001` (`OUTER APPLY TOP 1 ORDER BY fecha DESC`) |

**Campos retornados:** `nro_orden`, `tipo_docto`, `razon_social_proveedor`, `item`, `referencia`, `color`, `talla`, `codigo_barras`, `desc_item`, `cant_ordenada`, `fecha_entrega`, `precio_vigente`.

**Orden por defecto:** `fecha DESC, nro_orden ASC, rowid_movto ASC`.

#### `getTiposDocto(): array`

Consulta `DISTINCT f420_id_tipo_docto` de la tabla de OC para poblar el dropdown de tipo de documento.

#### `getEstados(): array`

Array estático con los estados de OC (En elaboración, Aprobado, Rechazado, En CDP, Anulado). Actualmente no se usa como filtro en la vista pero está disponible.

---

## 4. Vista: `index.php`

### 4.1 Dependencias PHP

```php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\ArrayDataProvider;
use yii\widgets\LinkPager;
```

No requiere librerías JS externas (ni DataTables). Toda la paginación y ordenamiento son server-side mediante Yii2.

### 4.2 CSS inline registrado

```
.oc-sort-link        → estilo base para links de ordenamiento en encabezados
.oc-sort-link.asc    → muestra ▲ después del label
.oc-sort-link.desc   → muestra ▼ después del label
.oc-sort-link (sin clase) → muestra ⇅ (sin orden activo)
```

---

### 4.3 Sección de Filtros

Formulario GET (`id="form-filtros"`) con 5 campos:

| Campo HTML | Parámetro GET | Descripción |
|---|---|---|
| Text `nro_orden` | `nro_orden` | Número exacto de OC |
| Select `tipo_docto` | `tipo_docto` | Dropdown poblado desde `getTiposDocto()` |
| Date `fecha_ord_ini` | `fecha_ord_ini` | Fecha desde (formato `YYYY-MM-DD`) |
| Date `fecha_ord_fin` | `fecha_ord_fin` | Fecha hasta (formato `YYYY-MM-DD`) |
| Text `proveedor` | `proveedor` | Busca por NIT o nombre (parcial) |

**Botones visibles siempre:** Buscar, Limpiar.  
**Botones visibles solo si hay resultados:** Exportar Excel, Generar OC PDF.

---

### 4.4 Validación del botón "Generar OC PDF"

Antes de mostrar los botones se calculan en PHP:

```php
$nrosUnicos        = array_unique(array_column($datos, 'nro_orden'));
$proveedoresUnicos = array_unique(array_column($datos, 'razon_social_proveedor'));
$pdfValido         = count($nrosUnicos) === 1 && count($proveedoresUnicos) === 1;
```

| Situación | Comportamiento |
|---|---|
| 1 orden, 1 proveedor | Botón rojo activo → abre PDF en nueva pestaña |
| Múltiples órdenes y/o proveedores | Botón rojo deshabilitado visualmente → clic muestra advertencia con SweetAlert2 (o `alert()` nativo si Swal no está disponible) |

El mensaje de error indica cuántas órdenes/proveedores distintos hay y pide filtrar por `Nro. Orden`.

---

### 4.5 Sección de Resultados

Solo se renderiza si `$hayFiltro === true`.

#### Cálculos sobre TODOS los registros (no solo la página actual):

```php
$totalUnidades = array_sum(array_column($datos, 'cant_ordenada'));
$totalNeto     = Σ (precio_vigente × cant_ordenada)   // filas con precio no nulo
```

#### ArrayDataProvider (paginación + orden):

```php
$perPage = 50;   // fijo, sin selector

$provider = new ArrayDataProvider([
    'allModels' => $datos,
    'sort'      => [ 'attributes' => [...12 columnas...], 'defaultOrder' => ['nro_orden' => SORT_ASC] ],
    'pagination'=> [ 'pageSize' => 50 ],
]);
```

- `$provider->getModels()` → solo los 50 registros de la página actual.
- `$provider->sort` → objeto `Sort` usado para generar links en encabezados.
- `$provider->pagination` → objeto `Pagination` usado por `LinkPager`.

#### Header del card de resultados (izquierda → derecha):

```
[Resultados] [N registros] [N Órdenes] [N Proveedores]
                    Cantidad Total: X Uds. | Neto Total: $X
```

#### Columnas de la tabla:

| # | Columna | Campo DB | Ordenable |
|---|---|---|---|
| 1 | Nro Orden | `nro_orden` | Sí |
| 2 | Tipo | `tipo_docto` | Sí |
| 3 | Proveedor | `razon_social_proveedor` | Sí |
| 4 | Ítem | `item` | Sí |
| 5 | Referencia | `referencia` | Sí |
| 6 | Color | `color` | Sí |
| 7 | Talla | `talla` | Sí |
| 8 | Cód. Barras | `codigo_barras` | Sí |
| 9 | Descripción | `desc_item` | Sí |
| 10 | Cant. Ord. | `cant_ordenada` | Sí |
| 11 | Fec. Entrega | `fecha_entrega` | Sí |
| 12 | Precio Vigente | `precio_vigente` | Sí |

**Comportamiento del ordenamiento:** clic en encabezado → ASC → segundo clic → DESC → tercer clic → vuelve al orden default. Los links generados por Yii2's `Sort::link()` preservan todos los GET params del filtro activo.

#### Footer del card de resultados:

```
Mostrando X–Y de Z registros        [« ‹ 1 2 3 ... 8 › »]
```

`LinkPager` configurado con `maxButtonCount = 8`, etiquetas `«‹›»`, clases Bootstrap 4 (`pagination pagination-sm`, `page-item`, `page-link`).

---

## 5. Flujo completo de datos

```
Usuario llena filtros → GET /compras/oc-precios/index
    ↓
OcPreciosController::actionIndex()
    → ejecutarConsulta($filtros)
        → JOIN en dbSiesa (SQL Server)
        → OUTER APPLY para precio vigente lista 001
        → retorna array plano
    → render('index', [...])
        ↓
index.php (vista)
    → Calcula $nrosUnicos, $proveedoresUnicos, $pdfValido
    → Muestra formulario con valores actuales
    → Muestra botones contextuales (Excel / PDF)
    → ArrayDataProvider ordena/pagina $datos en memoria
    → Renderiza tabla con 50 filas de la página actual
    → LinkPager genera navegación de páginas
```

---

## 6. Generación del PDF — Detalle técnico

### Estructura del PDF (A4 landscape, márgenes 8mm)

```
┌─────────────────────────────────────────────┐
│ GRUPO MAYORISTA S.A.        *ORDEN DE COMPRA*│
│ NIT / Dirección / Tel       Número: TIPO-NRO │
│                             Fecha: YYYY-MM-DD│
├─────────────────────────────────────────────┤
│ Datos Proveedor (izq)   │ Datos adicionales  │
│ Razón social, NIT,      │ Términos, descuento│
│ dirección, forma pago   │ financiero, bodega │
├─────────────────────────────────────────────┤
│ Item │ Ref │ Foto │ Color │ Obs │ [Tallas] │ Cant │ UM │ Precio │ Dcto │ Total │ F.Entrega │
│  ... │ ... │ img  │  ...  │     │  ...     │  ... │ .. │   ...  │ ...  │  ...  │    ...    │
├─────────────────────────────────────────────┤
│ Total Bruto │ Total Descuento │ Valor Total   │
└─────────────────────────────────────────────┘
```

### Lógica de tallas (PIVOT en PHP)

Los detalles se agrupan por `item_id + color`. Las tallas se separan en dos grupos:
- **Numéricas:** ordenadas numéricamente (4, 6, 8, 10, 12...)
- **Alfas:** ordenadas por array predefinido: XXS=0, XS=1, S=2, M=3, L=4, XL=5, XXL=6, XXXL=7

Primero se muestran las numéricas, luego las alfas — como columnas del encabezado de la tabla.

### Fotos

Se toman del campo `f580_foto` (BLOB) de la tabla `t580_ff_fotos`, enlazada al ítem mediante `f120_rowid_foto`. Se convierten a base64 para incrustarlas en el HTML del PDF. Máx 60×70 px.

---

## 7. Restricciones y comportamientos conocidos

| Situación | Comportamiento |
|---|---|
| Sin filtros al entrar | Muestra solo el formulario, sin ejecutar query |
| Filtro con 0 resultados | Muestra alerta amarilla "No se encontraron órdenes" |
| Precio vigente = NULL | Muestra "Sin precio" en gris en la columna |
| PDF con múltiples OC | Botón PDF muestra error con detalle de cuántas órdenes/proveedores hay |
| Excel | Sin restricción, exporta todos los registros sin importar cuántas órdenes haya |
| Paginación | 50 filas por página, fijo |
| Orden default | Nro Orden ASC al cargar por primera vez |
| Cód. Barras en Excel | Guardado como `TYPE_STRING` para evitar notación científica en números largos |

---

## 8. Dependencias externas

| Librería | Uso | Cómo se carga |
|---|---|---|
| **mPDF** | Generación del PDF | `composer` — `new \Mpdf\Mpdf(...)` |
| **PhpSpreadsheet** | Exportación Excel `.xlsx` | `composer` — `PhpOffice\PhpSpreadsheet` |
| **SweetAlert2** | Alerta de error al intentar PDF con múltiples OC | Layout AdminLTE3 (si está disponible); fallback a `alert()` nativo |
| **Yii2 `ArrayDataProvider`** | Paginación y ordenamiento client-side (en memoria) | Framework — sin librerías externas |
| **Yii2 `LinkPager`** | Navegación de páginas | Framework — sin librerías externas |

---

## 9. Base de datos — Tablas involucradas

| Tabla | Descripción |
|---|---|
| `t420_cm_oc_docto` | Cabecera de Órdenes de Compra |
| `t421_cm_oc_movto` | Líneas/movimientos de OC |
| `t120_mc_items` | Maestro de ítems |
| `t121_mc_items_extensiones` | Extensiones del ítem (color, talla, código de barras) |
| `t126_mc_items_precios` | Precios por lista. Lista `001` = precio vigente |
| `t200_mm_terceros` | Maestro de terceros (NIT) |
| `t202_mm_proveedores` | Maestro de proveedores (razón social, sucursal `000`) |
| `t015_mm_contactos` | Contacto del tercero (dirección, teléfono, ciudad) |
| `t013_mm_ciudades` | Tabla de ciudades |
| `t208_mm_condiciones_pago` | Condiciones de pago |
| `t580_ff_fotos` | Fotos de ítems (BLOB) |

**Conexión DB:** `Yii::$app->dbSiesa` (SQL Server, base de datos SIESA ERP)  
**Filtro fijo en todos los queries:** `f_id_cia = 7`
