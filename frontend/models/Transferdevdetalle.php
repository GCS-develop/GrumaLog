<?php

namespace frontend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "transferdevdetalle".
 *
 * @property int $id
 * @property int $id_transferencia
 * @property int $id_devoluciondocumento
 * @property string|null $codigoBarras
 * @property int|null $cantidadDevolucion
 * @property int|null $cantidadRegistrada
 * @property string|null $item
 * @property string|null $talla
 * @property string|null $color
 * @property string|null $referencia
 * @property string|null $itemResumen
 * @property string|null $unidadMedida
 * @property int|null $registrada
 * @property string|null $created_at
 * @property int|null $created_by
 * @property float|null $costo
 */
class Transferdevdetalle extends ActiveRecord
{
    public static function tableName()
    {
        return 'transferdevdetalle';
    }

    public function behaviors()
    {
        return [
            // Solo created_at (usa GETDATE() en SQL Server)
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
                'value' => new \yii\db\Expression('GETDATE()'),
            ],
            // Auditoría: solo created_by
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
                'value' => fn() => Yii::$app->user->id,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['id_transferencia', 'id_devoluciondocumento'], 'required'],
            [['id_transferencia', 'id_devoluciondocumento', 'cantidadDevolucion', 'cantidadRegistrada', 'registrada', 'created_by'], 'integer'],
            [['costo'], 'number'],
            [['created_at'], 'safe'], // opcional
            [['codigoBarras', 'item', 'talla', 'color', 'referencia', 'itemResumen', 'unidadMedida'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_transferencia' => 'ID Transferencia',
            'id_devoluciondocumento' => 'ID Devolución Documento',
            'codigoBarras' => 'Código de Barras',
            'cantidadDevolucion' => 'Cantidad Devuelta',
            'cantidadRegistrada' => 'Cantidad Registrada',
            'item' => 'Item',
            'talla' => 'Talla',
            'color' => 'Color',
            'referencia' => 'Referencia',
            'itemResumen' => 'Resumen',
            'unidadMedida' => 'Unidad de Medida',
            'registrada' => 'Registrada',
            'created_at' => 'Fecha Creación',
            'created_by' => 'Creado Por',
            'costo' => 'Costo Unitario',
        ];
    }

    // 🔹 Diferencia entre devoluciones y registradas
    public function getDiferencia()
    {
        return $this->cantidadDevolucion - $this->cantidadRegistrada;
    }

    // 🔹 Costo unitario consultado en Siesa
    public function getCosto(): ?float
    {
        $ean = (string)$this->codigoBarras;

        $sql = "
        SELECT 
            CAST(SUM(a.f_costo_prom_tot_ins) / NULLIF(SUM(a.f_cant_existencia_actual),0) AS DECIMAL(18,6)) AS Costo
        FROM BI_T400_1 a
        JOIN t121_mc_items_extensiones b ON a.f_rowid_item_ext = b.f121_rowid
        JOIN t120_mc_items c ON a.f_rowid_item = c.f120_rowid
        JOIN t131_mc_items_barras it ON b.f121_rowid = it.f131_rowid_item_ext
        WHERE a.f_id_bodega = '214'
          AND a.f_cant_existencia_actual > 0
          AND a.f_parametro_biable = '1'
          AND it.f131_id = :ean
        ";

        $valor = Yii::$app->dbSiesa
            ->createCommand($sql, [':ean' => $ean])
            ->queryScalar();

        return ($valor !== false && $valor !== null) ? (float)$valor : null;
    }


    public function getCantidadBase(): int
{
    $factor = (int)($this->eq_um ?: 1);
    return (int)$this->cantidadRegistrada * $factor;
}

public function getValorBruto(): float
{
    return $this->getCantidadBase() * ($this->costo ?? 0);
}

}
