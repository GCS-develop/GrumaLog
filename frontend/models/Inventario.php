<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "inventario".
 *
 * @property int $id
 * @property string $codigoBarras
 * @property int $idItem
 * @property string $codigoBodega
 * @property float $existencia
 * @property string $fechaUltimaActualizacion
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $codigoBodega0
 * @property Item $idItem0
 */
class Inventario extends \yii\db\ActiveRecord
{
    public $talla;
    public $color;
    // public $item;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inventario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigoBarras', 'idItem', 'codigoBodega', 'existencia', 'fechaUltimaActualizacion', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idItem', 'created_by', 'updated_by'], 'integer'],
            [['existencia'], 'number'],
            [['existencia'], 'compare', 'compareValue' => 0, 'operator' => '>=', 'message' => 'La existencia no puede ser negativa.'],
            [['fechaUltimaActualizacion', 'created_at', 'updated_at', 'talla', 'color'], 'safe'],
            [['codigoBarras'], 'string', 'max' => 50],
            [['codigoBodega'], 'string', 'max' => 5],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
            [['codigoBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['codigoBodega' => 'codigo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigoBarras' => 'Codigo Barras',
            'idItem' => 'Id Item',
            'codigoBodega' => 'Codigo Bodega',
            'existencia' => 'Existencia',
            'fechaUltimaActualizacion' => 'Fecha Ultima Actualizacion',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CodigoBodega0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoBodega()
    {
        return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodega']);
    }

    /**
     * Gets query for [[IdItem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    // ============================
    //  TUS MÉTODOS EXISTENTES
    // ============================

    /**
     * Normaliza el parámetro de bodega(s) a un array limpio de códigos.
     * Acepta null, string o array.
     */
    private static function normalizeBodegas($codigoBodega): array
    {
        if (empty($codigoBodega)) return [];
        $list = is_array($codigoBodega) ? $codigoBodega : [$codigoBodega];
        return array_values(array_filter(array_map('trim', $list)));
    }

    public static function getotalExistenciasGruma($codigoBodega = null)
    {
        $query = self::find();
        $bodegas = self::normalizeBodegas($codigoBodega);
        if (!empty($bodegas)) {
            $query->andWhere(['in', 'codigoBodega', $bodegas]);
        }
        return $query->sum('existencia');
    }

    public static function getTotalExistenciasSiesa($codigoBodega = null)
    {
        $bodegas = self::normalizeBodegas($codigoBodega);
        if (empty($bodegas)) {
            $bodegas = self::getCodigosBodegaUnicos();
        }
        if (empty($bodegas)) return 0;

        $placeholders = [];
        foreach ($bodegas as $i => $cod) {
            $placeholders[] = ":id{$i}";
        }

        $sql = "
        SELECT SUM(t400.f400_cant_existencia_1)
        FROM t400_cm_existencia t400
        INNER JOIN t150_mc_bodegas t150 ON t400.f400_rowid_bodega = t150.f150_rowid
        LEFT JOIN t131_mc_items_barras t131 ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
        WHERE f150_id IN (" . implode(',', $placeholders) . ")";

        $command = \Yii::$app->dbSiesa->createCommand($sql);
        foreach ($bodegas as $i => $cod) {
            $command->bindValue(":id{$i}", $cod, \PDO::PARAM_STR);
        }

        $existencia = $command->queryScalar();
        return $existencia !== false ? $existencia : 0;
    }

    public static function getCodigosBodegaUnicos()
    {
        return self::find()
            ->select('codigoBodega')
            ->distinct()
            ->column();
    }

    // ==========================================
    //  NUEVO: TOTALES "REAL" (SIN DUPLICAR SKU)
    // ==========================================

    /**
     * Total REAL Gruma: suma 1 vez por SKU lógico.
     * Como tu "SKU lógico" está en idItem (y tu regla de espejo mantiene existencia igual en todos los EAN),
     * se agrupa por (codigoBodega, idItem) y se suma MAX(existencia).
     */
    public static function getotalExistenciasGrumaReal($codigoBodega = null)
    {
        $bodegas = self::normalizeBodegas($codigoBodega);
        $params = [];
        $where = "1=1";

        if (!empty($bodegas)) {
            $placeholders = [];
            foreach ($bodegas as $i => $cod) {
                $placeholders[] = ":bod{$i}";
                $params[":bod{$i}"] = $cod;
            }
            $where .= " AND inv.codigoBodega IN (" . implode(',', $placeholders) . ")";
        }

        $sql = "
        SELECT COALESCE(SUM(x.existencia),0) AS total_real
        FROM (
            SELECT
                inv.codigoBodega,
                i.item,
                i.idColor,
                i.idTalla,
                MAX(COALESCE(inv.existencia,0)) AS existencia
            FROM inventario inv
            JOIN item i ON i.id = inv.idItem
            WHERE {$where}
            GROUP BY inv.codigoBodega, i.item, i.idColor, i.idTalla
        ) x
    ";

        $total = Yii::$app->db->createCommand($sql, $params)->queryScalar();
        return $total !== false ? (float)$total : 0;
    }

    /**
     * Total REAL Siesa: suma 1 vez por SKU lógico (idItem) para la bodega.
     *
     * Estrategia:
     * 1) Tomar los idItem únicos desde inventario local (por bodega si aplica)
     * 2) Mapearlos a barras en Siesa (t131)
     * 3) Consultar t400 por bodega y rowid_item_ext
     * 4) Agrupar por item_ext y sumar MAX(existencia) por item para evitar duplicados por múltiples barras
     *
     * NOTA: Se asume que inventario.codigoBarras corresponde a t131.f131_id_codigo_barras.
     */
    public static function getTotalExistenciasSiesaReal($codigoBodega = null)
    {
        $bodegas = self::normalizeBodegas($codigoBodega);
        if (empty($bodegas)) {
            $bodegas = self::getCodigosBodegaUnicos();
        }
        if (empty($bodegas)) return 0;

        $placeholders = [];
        foreach ($bodegas as $i => $cod) {
            $placeholders[] = ":bod{$i}";
        }

        $sql = "
        SELECT COALESCE(SUM(t400.f400_cant_existencia_1),0) AS total_existencia
        FROM t400_cm_existencia t400
        JOIN t150_mc_bodegas t150
          ON t400.f400_rowid_bodega = t150.f150_rowid
        WHERE t150.f150_id IN (" . implode(',', $placeholders) . ")
    ";

        $cmd = Yii::$app->dbSiesa->createCommand($sql);
        foreach ($bodegas as $i => $cod) {
            $cmd->bindValue(":bod{$i}", $cod, \PDO::PARAM_STR);
        }

        $total = $cmd->queryScalar();
        return $total !== false ? (float)$total : 0;
    }

    /**
     * Helper: construye :bod0,:bod1,...
     */
    private static function buildInPlaceholders(array $values, string $prefix)
    {
        $out = [];
        foreach ($values as $i => $v) {
            $out[] = ':' . $prefix . $i;
        }
        return implode(',', $out);
    }
}
