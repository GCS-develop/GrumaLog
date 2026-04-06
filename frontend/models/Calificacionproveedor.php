<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Modelo para tabla calificacionproveedor.
 *
 * Fórmulas:
 *  - calidad_ponderada  = promedio ponderado de 14 criterios (escala 1-5)
 *  - oportunidad_formula= 5 (mismo día) | 4 (hasta 2 días tarde) | 1 (más tarde)
 *  - cantidad_formula   = 1 (<80%) | 4 (80-99%) | 5 (>=100%)
 *  - puntaje_total      = oportunidad*20% + cantidad*30% + calidad_ponderada*50%
 *
 * Pesos finales configurables como constantes.
 */
class Calificacionproveedor extends \yii\db\ActiveRecord
{
    // ---- Pesos del puntaje final (deben sumar 1.0) ----
    const PESO_OPORTUNIDAD       = 0.10;
    const PESO_CANTIDAD          = 0.30;
    const PESO_CALIDAD           = 0.30;
    const PESO_CALIDAD_PRODUCTO  = 0.30;

    // ---- Pesos de los criterios de calidad (deben sumar 1.0) ----
    const PESOS_CALIDAD = [
        'caja_bulto'          => 0.05,
        'calibre'             => 0.05,
        'rotulo'              => 0.05,
        'contiene_documentos' => 0.05,
        'separa_tallas'       => 0.05,
        'separa_color'        => 0.05,
        'separa_referencia'   => 0.05,
        'etiquetado'          => 0.05,
        'error_tiqueteo'      => 0.20,
        'homologacion'        => 0.05,
        'precio'              => 0.05,
        'novedad'             => 0.15,
        'factura'             => 0.10,
        'orden_compra_doc'    => 0.05,
    ];

    public static function tableName()
    {
        return 'calificacionproveedor';
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => new Expression('GETDATE()'),
            ],
            [
                'class'              => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
        ];
    }

    public function rules()
    {
        return [
            // Requeridos
            [['numero_oc'], 'required'],

            // Enteros
            [['id_ordendecompra', 'id_proveedor', 'unidades_ordenadas', 'unidades_entregadas',
              'created_by', 'updated_by'], 'integer'],

            // Criterios de calidad: entero 1-5
            [['caja_bulto','calibre','rotulo','contiene_documentos','separa_tallas',
              'separa_color','separa_referencia','etiquetado','error_tiqueteo',
              'homologacion','precio','novedad','factura','orden_compra_doc',
              'calidad_producto'],
              'integer', 'min' => 1, 'max' => 5],

            // Criterios informativos (sin peso): entero 1-5, opcionales
            [['gancho','tallero'], 'integer', 'min' => 1, 'max' => 5],
            [['gancho','tallero'], 'default', 'value' => null],

            // Strings
            [['numero_oc','proveedor','categoria','subcategoria','tipo_mercancia',
              'producto','transportadora','revisado_por'], 'string', 'max' => 200],
            [['observacion'], 'string'],

            // Fechas
            [['fecha_entrega_cita','fecha_entrega_oc','created_at','updated_at'], 'safe'],

            // Decimales calculados
            [['calidad_ponderada','oportunidad_formula','cantidad_formula','puntaje_total'], 'number'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'id_ordendecompra'      => 'Orden de Compra',
            'numero_oc'             => 'N° OC',
            'id_proveedor'          => 'Proveedor',
            'proveedor'             => 'Proveedor',
            'categoria'             => 'Categoría',
            'subcategoria'          => 'Subcategoría',
            'tipo_mercancia'        => 'Tipo Mercancía',
            'producto'              => 'Producto',
            'transportadora'        => 'Transportadora',
            'unidades_ordenadas'    => 'Uds. Ordenadas',
            'unidades_entregadas'   => 'Uds. Entregadas',
            'fecha_entrega_cita'    => 'Fecha Cita',
            'fecha_entrega_oc'      => 'Fecha Entrega OC',
            'revisado_por'          => 'Revisado Por',
            'caja_bulto'            => 'Caja / Bulto',
            'calibre'               => 'Calibre',
            'rotulo'                => 'Rótulo',
            'contiene_documentos'   => 'Contiene Documentos',
            'separa_tallas'         => 'Separa Tallas',
            'separa_color'          => 'Separa Color',
            'separa_referencia'     => 'Separa Referencia',
            'etiquetado'            => 'Etiquetado',
            'error_tiqueteo'        => 'Error Tiqueteo',
            'homologacion'          => 'Homologación',
            'precio'                => 'Precio',
            'novedad'               => 'Novedad',
            'factura'               => 'Factura',
            'orden_compra_doc'      => 'Orden de Compra (doc)',
            'calidad_producto'      => 'Calidad del Producto',
            'gancho'                => 'Gancho',
            'tallero'               => 'Tallero',
            'calidad_ponderada'     => 'Calidad (criterios)',
            'oportunidad_formula'   => 'Oportunidad',
            'cantidad_formula'      => 'Cantidad',
            'puntaje_total'         => 'Puntaje Total',
            'observacion'           => 'Observación',
            'created_at'            => 'Fecha Calificación',
            'created_by'            => 'Calificado Por',
        ];
    }

    // ----------------------------------------------------------------
    //  Relaciones
    // ----------------------------------------------------------------

    public function getOrdendecompra()
    {
        return $this->hasOne(Ordendecompra::class, ['id' => 'id_ordendecompra']);
    }

    public function getProveedor()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'id_proveedor']);
    }

    public function getCreatedByUser()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }

    // ----------------------------------------------------------------
    //  Cálculos
    // ----------------------------------------------------------------

    /**
     * Calcula y almacena los puntajes derivados antes de guardar.
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->calcularPuntajes();
        return true;
    }

    public function calcularPuntajes()
    {
        $this->calidad_ponderada   = $this->calcularCalidad();
        $this->oportunidad_formula = $this->calcularOportunidad();
        $this->cantidad_formula    = $this->calcularCantidad();
        $this->puntaje_total       = $this->calcularPuntajeTotal();
    }

    /**
     * Alias para usar en vistas: devuelve calidad_producto como float o null.
     */
    public function getCalidadProductoValor()
    {
        return $this->calidad_producto ? (float)$this->calidad_producto : null;
    }

    /**
     * Promedio ponderado de los 14 criterios.
     * Resultado en escala 1-5.
     */
    public function calcularCalidad()
    {
        $suma = 0;
        foreach (self::PESOS_CALIDAD as $campo => $peso) {
            $suma += ((float)($this->$campo ?? 0)) * $peso;
        }
        return round($suma, 2);
    }

    /**
     * Oportunidad: 5=mismo día, 4=hasta 2 días tarde, 1=más tarde.
     */
    public function calcularOportunidad()
    {
        if (!$this->fecha_entrega_cita || !$this->fecha_entrega_oc) {
            return null;
        }
        $cita     = new \DateTime($this->fecha_entrega_cita);
        $entrega  = new \DateTime($this->fecha_entrega_oc);
        $diasDiff = (int)$cita->diff($entrega)->format('%r%a'); // negativo=adelantado, positivo=tarde

        if ($diasDiff <= 0) {
            return 5;
        } elseif ($diasDiff <= 2) {
            return 4;
        } else {
            return 1;
        }
    }

    /**
     * Cantidad: 5=>=100%, 4=80-99%, 1=<80%.
     */
    public function calcularCantidad()
    {
        if (!$this->unidades_ordenadas || $this->unidades_ordenadas == 0) {
            return null;
        }
        $ratio = $this->unidades_entregadas / $this->unidades_ordenadas;
        if ($ratio >= 1.0) {
            return 5;
        } elseif ($ratio >= 0.80) {
            return 4;
        } else {
            return 1;
        }
    }

    /**
     * Puntaje final ponderado.
     * oportunidad*10% + cantidad*30% + calidad_criterios*30% + calidad_producto*30%
     */
    public function calcularPuntajeTotal()
    {
        $oportunidad      = $this->oportunidad_formula ?? $this->calcularOportunidad();
        $cantidad         = $this->cantidad_formula    ?? $this->calcularCantidad();
        $calidad          = $this->calidad_ponderada   ?? $this->calcularCalidad();
        $calidadProducto  = $this->calidad_producto    ? (float)$this->calidad_producto : null;

        if ($oportunidad === null || $cantidad === null || $calidadProducto === null) {
            return null;
        }

        return round(
            $oportunidad     * self::PESO_OPORTUNIDAD      +
            $cantidad        * self::PESO_CANTIDAD         +
            $calidad         * self::PESO_CALIDAD          +
            $calidadProducto * self::PESO_CALIDAD_PRODUCTO,
            2
        );
    }

    // ----------------------------------------------------------------
    //  Helpers de presentación
    // ----------------------------------------------------------------

    /**
     * Convierte puntuación 1-5 a letra: A / B / C.
     * Fórmula: score/5 < 80% → C, <= 90% → B, > 90% → A
     */
    public static function puntajeALetra($score)
    {
        if ($score === null || $score == 0) {
            return '-';
        }
        $pct = $score / 5;
        if ($pct < 0.80) {
            return 'C';
        } elseif ($pct <= 0.90) {
            return 'B';
        } else {
            return 'A';
        }
    }

    /**
     * Clase Bootstrap para badge según letra.
     */
    public static function letraClase($letra)
    {
        switch ($letra) {
            case 'A': return 'success';
            case 'B': return 'warning';
            default:  return 'danger';
        }
    }

    /**
     * Badge HTML listo para usar en vistas.
     */
    public function badgePuntaje($score = null)
    {
        $s      = $score ?? $this->puntaje_total;
        $letra  = self::puntajeALetra($s);
        $clase  = self::letraClase($letra);
        $num    = $s !== null ? number_format((float)$s, 2) : '-';
        return "<span class=\"badge badge-{$clase}\">{$letra} ({$num})</span>";
    }

    /**
     * Lista de OCs (últimas 10) con su estado de calificación.
     * Soporta filtros: q_oc, q_proveedor, q_co (búsqueda parcial).
     */
    public static function listaOcsPendientes($soloSinCalificar = true, $filtros = [])
    {
        $db = Yii::$app->db;

        // puntaje_ponderado = promedio simple de puntaje_total por subcategoría (todas pesan igual)
        $calificadasSubQuery = "
            SELECT id_ordendecompra, COUNT(*) AS total_calif,
                   AVG(puntaje_total) AS puntaje_ponderado,
                   MAX(created_at)   AS ultima_fecha
            FROM calificacionproveedor
            GROUP BY id_ordendecompra
        ";

        $where  = [];
        $params = [];

        if ($soloSinCalificar) {
            $where[] = "cal.id_ordendecompra IS NULL";
        }

        if (!empty($filtros['q_oc']) && is_numeric($filtros['q_oc'])) {
            $where[]        = "oc.consecutivo = :q_oc";
            $params[':q_oc'] = (int)$filtros['q_oc'];
        }
        if (!empty($filtros['q_proveedor'])) {
            $where[]               = "p.razonSocial LIKE :q_proveedor";
            $params[':q_proveedor'] = '%' . $filtros['q_proveedor'] . '%';
        }
        if (!empty($filtros['q_tipo_doc'])) {
            $where[]                = "oc.idTipoDocumento = :q_tipo_doc";
            $params[':q_tipo_doc']  = (int)$filtros['q_tipo_doc'];
        }

        $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
            SELECT TOP 10
                oc.id,
                ISNULL(td.nombre,'') + '-' + FORMAT(oc.consecutivo,'00000000') AS numero_oc,
                ISNULL(p.razonSocial,'') AS proveedor,
                oc.fecha,
                oc.idCO,
                ISNULL(co.nombre,'') AS centro_operacion,
                ISNULL(cal.total_calif, 0)  AS total_calificaciones,
                cal.puntaje_ponderado,
                cal.ultima_fecha
            FROM ordendecompra oc
            LEFT JOIN proveedor      p   ON p.id  = oc.idProveedor
            LEFT JOIN tipodocumento  td  ON td.id = oc.idTipoDocumento
            LEFT JOIN centrooperacion co ON co.id = oc.idCO
            LEFT JOIN ({$calificadasSubQuery}) cal ON cal.id_ordendecompra = oc.id
            {$whereClause}
            ORDER BY oc.fecha DESC, oc.id DESC
        ";

        return $db->createCommand($sql, $params)->queryAll();
    }

    /**
     * Subcategorías distintas de una OC con unidades por subcategoría.
     */
    public static function subcategoriasDeOc($idOc)
    {
        $sql = "
            SELECT
                sub.nombre                      AS subcategoria,
                cat.nombre                      AS categoria,
                SUM(d.cantidadPedida)           AS unidades_ordenadas,
                SUM(d.cantidadEntrada)          AS unidades_entregadas
            FROM ordendecompradetalle d
            INNER JOIN subcategoria sub ON sub.id = d.idSubcategoria
            INNER JOIN categoria    cat ON cat.id = d.idCategoria
            WHERE d.idOrdenCompra = :id
            GROUP BY sub.nombre, cat.nombre
            ORDER BY sub.nombre
        ";
        return Yii::$app->db->createCommand($sql, [':id' => $idOc])->queryAll();
    }

    /**
     * Calificaciones existentes por subcategoría para una OC.
     * Devuelve [subcategoria => row] para saber cuáles ya están calificadas.
     */
    public static function calificadasPorSubcategoria($idOc)
    {
        $rows = self::find()
            ->where(['id_ordendecompra' => $idOc])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $map = [];
        foreach ($rows as $r) {
            // Si hay varias para la misma subcategoría, queda la más reciente (DESC)
            if (!isset($map[$r->subcategoria])) {
                $map[$r->subcategoria] = $r;
            }
        }
        return $map;
    }

    /**
     * Puntaje ponderado de una OC (promedio simple de todas sus subcategorías calificadas).
     */
    public static function puntajePonderadoOc($idOc)
    {
        $sql = "
            SELECT AVG(puntaje_total) AS ponderado, COUNT(*) AS total
            FROM calificacionproveedor
            WHERE id_ordendecompra = :id AND puntaje_total IS NOT NULL
        ";
        return Yii::$app->db->createCommand($sql, [':id' => $idOc])->queryOne();
    }

    /**
     * Ranking por proveedor usando el ponderado por OC.
     * Paso 1: ponderado de cada OC = AVG(puntaje_total) de sus subcategorías.
     * Paso 2: promedio de esos ponderados por proveedor.
     */
    public static function ranking()
    {
        $sql = "
            SELECT
                oc_pond.id_proveedor,
                oc_pond.proveedor,
                COUNT(*)                        AS total_ocs_calificadas,
                SUM(oc_pond.total_subcats)      AS total_calificaciones,
                AVG(oc_pond.ponderado_oc)       AS promedio_total,
                AVG(oc_pond.prom_calidad)       AS promedio_calidad,
                AVG(oc_pond.prom_calidad_prod)  AS promedio_calidad_producto,
                AVG(oc_pond.prom_oportunidad)   AS promedio_oportunidad,
                AVG(oc_pond.prom_cantidad)      AS promedio_cantidad,
                MAX(oc_pond.ultima_calif)       AS ultima_calificacion
            FROM (
                SELECT
                    cp.id_proveedor,
                    ISNULL(p.razonSocial, cp.proveedor) AS proveedor,
                    cp.id_ordendecompra,
                    AVG(cp.puntaje_total)        AS ponderado_oc,
                    COUNT(*)                     AS total_subcats,
                    AVG(cp.calidad_ponderada)    AS prom_calidad,
                    AVG(CAST(cp.calidad_producto AS FLOAT)) AS prom_calidad_prod,
                    AVG(cp.oportunidad_formula)  AS prom_oportunidad,
                    AVG(cp.cantidad_formula)     AS prom_cantidad,
                    MAX(cp.created_at)           AS ultima_calif
                FROM calificacionproveedor cp
                LEFT JOIN proveedor p ON p.id = cp.id_proveedor
                WHERE cp.puntaje_total IS NOT NULL
                GROUP BY cp.id_proveedor, p.razonSocial, cp.proveedor, cp.id_ordendecompra
            ) oc_pond
            GROUP BY oc_pond.id_proveedor, oc_pond.proveedor
            ORDER BY promedio_total DESC
        ";

        return Yii::$app->db->createCommand($sql)->queryAll();
    }

    /**
     * Historial de calificaciones de un proveedor.
     */
    public static function historialProveedor($idProveedor)
    {
        return self::find()
            ->where(['id_proveedor' => $idProveedor])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();
    }
}
