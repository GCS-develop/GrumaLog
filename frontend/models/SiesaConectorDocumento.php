<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use frontend\models\search\GrumascanEnvioFisicoPreviewSearch;

/**
 * This is the model class for table "siesa_conector_documento".
 *
 * @property int $id
 * @property int $conector_id
 * @property string $nombre
 * @property string|null $descripcion
 * @property int|null $id_traspaso
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConector $conector
 * @property SiesaEnvioDocumentoValor[] $siesaEnvioDocumentoValors
 * @property SiesaEnvioMovimiento[] $siesaEnvioMovimientos
 * @property Traspaso $traspaso
 */
class SiesaConectorDocumento extends \yii\db\ActiveRecord
{
    public $consecutivoSiesa;
    public $usuarioTransferencia;
    public $consecutivosiesatraspaso;
    public $bodegaentrada;
    public $tieneAen;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_conector_documento';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['conector_id', 'nombre'], 'required'],
            [['conector_id', 'id_traspaso', 'created_by', 'updated_by'], 'integer'],
            [['descripcion'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre'], 'string', 'max' => 255],
            [['conector_id'], 'exist', 'skipOnError' => true, 'targetClass' => SiesaConector::class, 'targetAttribute' => ['conector_id' => 'id']],
            [['id_traspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['id_traspaso' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'conector_id' => 'Conector ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'id_traspaso' => 'Id Traspaso',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Conector]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConector()
    {
        return $this->hasOne(SiesaConector::class, ['id' => 'conector_id']);
    }

    /**
     * Gets query for [[SiesaEnvioDocumentoValors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioDocumentoValors()
    {
        return $this->hasMany(SiesaEnvioDocumentoValor::class, ['documento_id' => 'id']);
    }

    /**
     * Gets query for [[SiesaEnvioMovimientos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaEnvioMovimientos()
    {
        return $this->hasMany(SiesaEnvioMovimiento::class, ['documento_id' => 'id']);
    }

    /**
     * Gets query for [[Traspaso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'id_traspaso']);
    }

    public function getValoresDocumento()
    {
        return $this->hasMany(SiesaEnvioDocumentoValor::class, ['documento_id' => 'id']);
    }

    public function getMovimientos()
    {
        return $this->hasMany(SiesaEnvioMovimiento::class, ['documento_id' => 'id']);
    }
    public function getCamposDocumento()
    {
        return $this->hasMany(SiesaConectorDocumentoCampo::class, ['conector_id' => 'conector_id']);
    }
    public function getCamposMovimiento()
    {
        return $this->hasMany(\frontend\models\SiesaConectorMovimientoCampo::class, ['conector_id' => 'conector_id']);
    }

    /**
     * Busca un AEN candidato en Siesa para este conector.
     * Devuelve un array con los campos del AEN o null si no encuentra nada.
     */
    public function buscarAenAutomatico(): ?array
    {
        // 1) Datos base desde tu BD normal (a partir de id_traspaso)
        $traspaso = Traspaso::findOne($this->id_traspaso);
        if (!$traspaso) {
            return null;
        }

        // Documento base 3TB / 2TL / 3TA asociado al traspaso
        $docBase = Documentosiesa::find()
            ->where(['idGruma' => $traspaso->id])
            ->andWhere(['<>', 'f350_id_tipo_docto', 'AEN']) // evitar usar el AEN como origen
            ->orderBy(['id' => SORT_ASC])                   // el primero que llegó de Siesa
            ->one();


        if (!$docBase) {
            return null;
        }

        $centroOp = Centrooperacion::findOne($traspaso->idCentroOperacion);
        $bodega   = Bodegas::findOne($traspaso->idBodegaDestino);

        // ==== Parámetros dinámicos ====
        $cia          = 7; // siempre 7
        $tipo3tb      = trim($docBase->f350_id_tipo_docto);   // dst.f350_id_tipo_docto
        $consec3tb    = (int)$docBase->f350_consec_docto;     // dst.f350_consec_docto
        $co3tb        = $centroOp ? trim($centroOp->codigo)   : null; // CO origen
        $coAenDestino = $bodega   ? trim($bodega->codigo)     : null; // CO destino
        $diasVentana  = 10;

        // Escapar comillas simples por seguridad básica
        $tipo3tbSql      = str_replace("'", "''", $tipo3tb);
        $co3tbSql        = $co3tb        !== null ? str_replace("'", "''", $co3tb)        : null;
        $coAenDestinoSql = $coAenDestino !== null ? str_replace("'", "''", $coAenDestino) : null;

        // Build de las DECLARE con los valores reales
        $declCo3tb = $co3tbSql === null
            ? "DECLARE @Co3TB VARCHAR(10) = NULL;"
            : "DECLARE @Co3TB VARCHAR(10) = '{$co3tbSql}';";

        $declCoAen = $coAenDestinoSql === null
            ? "DECLARE @CoAENDestino VARCHAR(10) = NULL;"
            : "DECLARE @CoAENDestino VARCHAR(10) = '{$coAenDestinoSql}';";

        $sql = "
SET NOCOUNT ON;

-- ==== Parámetros ====
DECLARE @Cia              INT         = {$cia};
DECLARE @Tipo3TB          VARCHAR(10) = '{$tipo3tbSql}';   -- tipo documento ORIGEN
DECLARE @Consec3TB        INT         = {$consec3tb};      -- consecutivo siesa
{$declCo3tb}
{$declCoAen}
DECLARE @DiasVentana      INT         = {$diasVentana};    -- ventana de fechas

;WITH src AS (
    SELECT TOP (1)
        t.f350_rowid             AS rowid_3tb,
        t.f350_id_co             AS co_3tb,
        t.f350_consec_docto      AS consec_3tb,
        t.f350_fecha             AS fecha_3tb,
        t.f350_total_db          AS total_db_3tb,
        t.f350_total_cr          AS total_cr_3tb,
        t.f350_rowid_sesion      AS sesion_3tb,
        t.f350_notas             AS notas_3tb
    FROM t350_co_docto_contable t
    WHERE t.f350_id_cia        = @Cia
      AND t.f350_id_tipo_docto = @Tipo3TB
      AND t.f350_consec_docto  = @Consec3TB
      AND (@Co3TB IS NULL OR t.f350_id_co = @Co3TB)
    ORDER BY t.f350_fecha_ts_creacion ASC
),
candidatos AS (
    SELECT
        a.f350_rowid            AS rowid_aen,
        a.f350_id_co            AS co_aen,
        a.f350_id_tipo_docto    AS tipo_aen,
        a.f350_consec_docto     AS consec_aen,
        a.f350_fecha            AS fecha_aen,
        a.f350_id_periodo       AS periodo_aen,
        a.f350_total_db, 
        a.f350_total_cr,
        a.f350_rowid_docto_base,
        a.f350_rowid_sesion,
        a.f350_notas,
        s.rowid_3tb,
        s.fecha_3tb,
        s.total_db_3tb,
        s.total_cr_3tb,
        s.sesion_3tb
    FROM t350_co_docto_contable a
    CROSS JOIN src s
    WHERE a.f350_id_cia        = @Cia
      AND a.f350_id_tipo_docto = 'AEN'
      AND a.f350_id_clase_docto = 66                    -- AEN (entrada)
      AND a.f350_total_db = s.total_db_3tb              -- mismo total
      AND a.f350_total_cr = s.total_cr_3tb
      AND a.f350_fecha BETWEEN s.fecha_3tb 
                           AND DATEADD(DAY, @DiasVentana, s.fecha_3tb)
      AND (@CoAENDestino IS NULL OR a.f350_id_co = @CoAENDestino)
)
SELECT TOP (5)
    c.rowid_aen,
    c.co_aen,
    c.tipo_aen,
    c.consec_aen,
    c.fecha_aen,
    c.periodo_aen,
    c.f350_total_db,
    c.f350_total_cr,
    c.f350_rowid_docto_base,
    c.f350_rowid_sesion,
    c.f350_notas,
    CASE WHEN c.f350_rowid_docto_base = c.rowid_3tb THEN 0 ELSE 1 END AS r1_docto_base,
    ABS(DATEDIFF(DAY, c.fecha_aen, c.fecha_3tb))                      AS r2_dias,
    CASE WHEN c.f350_rowid_sesion IS NULL OR c.sesion_3tb IS NULL 
         THEN 999999 ELSE ABS(c.f350_rowid_sesion - c.sesion_3tb) END AS r3_sesion
FROM candidatos c
ORDER BY 
    CASE WHEN c.f350_rowid_docto_base = c.rowid_3tb THEN 0 ELSE 1 END,
    ABS(DATEDIFF(DAY, c.fecha_aen, c.fecha_3tb)),
    CASE WHEN c.f350_rowid_sesion IS NULL OR c.sesion_3tb IS NULL 
         THEN 999999 ELSE ABS(c.f350_rowid_sesion - c.sesion_3tb) END;
";

        // 2) Ejecutar contra Siesa SIN parámetros PDO (evitamos el 07002)
        $dbSiesa = Yii::$app->dbSiesa; // o Yii::$app->dbSiesa si tienes un componente aparte

        $rows = $dbSiesa->createCommand($sql)->queryAll();

        // Si quieres solo el mejor
        return !empty($rows) ? $rows[0] : null;
    }

    /**
     * Busca el AEN en Siesa y lo graba en documentosiesa.
     * Devuelve el array del AEN si todo salió bien, o null si falla.
     */
    public function vincularAenConDocumentosiesa(): ?array
    {
        // 1) Buscar AEN en Siesa
        $aen = $this->buscarAenAutomatico();

        if (!$aen) {
            $this->addError('id', 'No se encontró ningún AEN candidato para este documento.');
            return null;
        }

        // 2) Buscar Traspaso para obtener el idGruma
        $traspaso = Traspaso::findOne($this->id_traspaso);
        if (!$traspaso) {
            $this->addError('id', 'No se encontró el traspaso asociado al conector.');
            return null;
        }

        // 3) Armar el array en el formato que espera Documentosiesa::grabarDatos
        $resultado = [[
            // 👉 Si en tu flujo numeroDocumento debe ser otro (por ejemplo el consecutivo de Siesa),
            // cambia ESTA línea.
            'numeroDocumento'      => (int)$traspaso->id,
            'tipoDocumento'        => $aen['tipo_aen'],
            'f350_id_tipo_docto'   => $aen['tipo_aen'],
            'f350_rowid'           => (int)$aen['rowid_aen'],
            'f350_id_cia'          => 7, // lo usas fijo en el SQL
            'f350_id_co'           => $aen['co_aen'],
            'f350_consec_docto'    => (int)$aen['consec_aen'],
        ]];

        $idGruma = $this->id;
        $origen = 'Recibir Traspaso Tienda';

        // 4) Grabar en documentosiesa usando tu helper estático
        $ok = Documentosiesa::grabarDatos($resultado, $idGruma, $origen);

        if (!$ok) {
            $this->addError('id', 'AEN encontrado en Siesa, pero no se pudo grabar en documentosiesa.');
            return null;
        }

        return $aen;
    }

    /**
     * Crea el documento y mapea los movimientos desde el consolidado del preview.
     * - No usa traspasos.
     * - Re-ejecuta el preview en backend (evita depender del front).
     */
    public static function crearDocumentoYMapearDesdePreview(
        string $codigoBodega,
        string $fechaDesde,
        string $consecutivo,
        int $conectorId = 4
    ): int {
        $codigoBodega = trim($codigoBodega);
        $fechaDesde   = trim($fechaDesde);
        $consecutivo  = trim($consecutivo);

        if ($codigoBodega === '' || $fechaDesde === '' || $consecutivo === '') {
            throw new \InvalidArgumentException('Código bodega, fecha y consecutivo son obligatorios.');
        }

        // 1) Re-ejecuta el preview (determinístico)
        $searchModel = new GrumascanEnvioFisicoPreviewSearch();
        $result = $searchModel->preview([
            'GrumascanEnvioFisicoPreviewSearch' => [
                'codigoBodega' => $codigoBodega,
                'fechaDesde' => $fechaDesde,
            ],
        ]);

        $rows = $result['rows'] ?? [];
        if (empty($rows)) {
            throw new \RuntimeException('No hay datos para mapear: no se encontraron conteos terminados con los filtros seleccionados.');
        }

        // 2) Campos de mapeo (dinámicos por nombre_campo)
        $campos = SiesaConectorMovimientoCampo::find()
            ->where(['conector_id' => $conectorId])
            ->all();

        if (empty($campos)) {
            throw new \RuntimeException("No hay campos configurados en siesa_conector_movimiento_campo para conector_id={$conectorId}.");
        }

        $camposByNombre = [];
        foreach ($campos as $c) {
            $camposByNombre[trim((string)$c->nombre_campo)] = $c;
        }

        $required = ['Consecutivo', 'Bodega', 'Cantidad', 'Item', 'Color', 'Talla'];
        foreach ($required as $req) {
            if (!isset($camposByNombre[$req])) {
                throw new \RuntimeException("Falta el campo requerido '{$req}' en siesa_conector_movimiento_campo (conector_id={$conectorId}).");
            }
        }

        $db = SiesaConectorDocumento::getDb(); // o Yii::$app->dbsiesa si así lo manejas
        $tx = $db->beginTransaction();

        try {
            // 3) Crear documento (cabeza)
            $documento = new SiesaConectorDocumento();
            $documento->conector_id  = $conectorId;
            $documento->nombre       = 'ATF'; // o el código que uses internamente para físico
            $documento->descripcion  = "Envío físico Gruma Scan | Bodega {$codigoBodega} | Fecha {$fechaDesde} | Consecutivo {$consecutivo}";

            if (!$documento->save()) {
                throw new \RuntimeException("Error al crear documento: " . json_encode($documento->getErrors()));
            }

            // 4) Crear movimientos + valores por fila del consolidado
            foreach ($rows as $row) {
                $mov = new SiesaEnvioMovimiento();
                $mov->documento_id = $documento->id;

                if (!$mov->save()) {
                    throw new \RuntimeException("Error al crear movimiento: " . json_encode($mov->getErrors()));
                }

                // Valores desde el preview
                $valConsecutivo = $consecutivo;
                $valBodega      = $codigoBodega;
                $valCantidad    = (string)($row['cantidad_unidad'] ?? 0);
                $valItem        = (string)($row['item'] ?? '');
                $valColor       = (string)($row['color'] ?? '');
                $valTalla       = (string)($row['talla'] ?? '');

                $map = [
                    'Consecutivo' => $valConsecutivo,
                    'Bodega'      => $valBodega,
                    'Cantidad'    => $valCantidad,
                    'Item'        => $valItem,
                    'Color'       => $valColor,
                    'Talla'       => $valTalla,
                ];

                foreach ($map as $nombreCampo => $valor) {
                    $campo = $camposByNombre[$nombreCampo];

                    $mv = new SiesaEnvioMovimientoValor();
                    $mv->movimiento_id = $mov->id;
                    $mv->campo_id = $campo->id;
                    $mv->valor = trim((string)$valor);

                    if (!$mv->save()) {
                        throw new \RuntimeException("Error al guardar valor ({$nombreCampo}) movimiento_id={$mov->id}: " . json_encode($mv->getErrors()));
                    }
                }
            }

            $tx->commit();
            return (int)$documento->id;
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }
}
