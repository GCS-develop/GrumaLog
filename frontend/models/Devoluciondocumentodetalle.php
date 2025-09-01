<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;

/**
 * This is the model class for table "devoluciondocumentodetalle".
 *
 * @property int $id
 * @property int $idDocumento
 * @property string $codigoBarras
 * @property float $cantidadDevolucion
 * @property float $cantidadRegistrada
 * @property string $item
 * @property string $talla
 * @property string $color
 * @property string $referencia
 * @property string $itemResumen
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Devoluciondocumentodetalle extends \yii\db\ActiveRecord
{
    public $numeroDocumento;
    public $codigoBodegaSalida;
    public $fechaDesde;
    public $fechaHasta;
    public $codigolistaprecios;
    public $codigobarras;

    public $nombreProveedor;

    public $tipoInventario;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'devoluciondocumentodetalle';
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
            [['idDocumento', 'codigoBarras', 'item', 'talla', 'color', 'referencia', 'itemResumen'], 'required'],
            [['idDocumento', 'created_by', 'updated_by', 'registrada', 'usuarioRegistra'], 'integer'],
            [['cantidadDevolucion', 'cantidadRegistrada'], 'number'],
            [['item', 'created_at', 'updated_at', 'fechaRegistra'], 'safe'],
            [['codigoBarras', 'talla'], 'string', 'max' => 20],
            [['color', 'referencia'], 'string', 'max' => 50],
            [['itemResumen'], 'string', 'max' => 300],
            [['unidadMedida'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idDocumento' => 'Id Documento',
            'codigoBarras' => 'Código Barras',
            'cantidadDevolucion' => 'Cantidad Devolución',
            'cantidadRegistrada' => 'Cantidad Registrada',
            'item' => 'Item',
            'talla' => 'Talla',
            'color' => 'Color',
            'referencia' => 'Referencia',
            'itemResumen' => 'Item Resumen',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'unidadMedida' => 'UM',
        ];
    }

    public function getUsuarioregistra()
    {
        return $this->hasOne(User::class, ['id' => 'usuarioRegistra']);
    }

    public function getUnidadempaque()
    {
        return $this->hasOne(Unidadempaque::class, ['codigo' => 'unidadMedida']);
    }

    public function getDiferencia()
    {
        return $this->cantidadDevolucion - $this->cantidadRegistrada;
    }


    public function getTransferdevdocumento()
{
    // Establecer la relación inversa
    return $this->hasOne(Transferdevdocumentos::class, ['id' => 'id_devoluciondocumento']);
}


public function getCosto(): ?float
{
    $ean = (string)$this->codigoBarras; // asegúrate que no venga null

    $sql = "
        WITH UltimoCosto AS (
            SELECT 
                it.f131_id AS EAN,
                exis.f400_costo_prom_uni AS Costo,
                ROW_NUMBER() OVER (
                    PARTITION BY it.f131_id
                    ORDER BY d.f126_fecha_activacion DESC
                ) AS RowNum
            FROM t120_mc_items a
            JOIN t121_mc_items_extensiones b ON a.f120_rowid = b.f121_rowid_item
            JOIN t400_cm_existencia exis     ON exis.f400_rowid_item_ext = b.f121_rowid
            JOIN t126_mc_items_precios d     ON d.f126_rowid_item = a.f120_rowid
            JOIN t131_mc_items_barras it     ON b.f121_rowid = it.f131_rowid_item_ext
            WHERE exis.f400_rowid_bodega = 6
        )
        SELECT Costo 
        FROM UltimoCosto 
        WHERE RowNum = 1 AND EAN = :ean
    ";

    $valor = Yii::$app->dbSiesa
        ->createCommand($sql, [':ean' => $ean])
        ->queryScalar();

    // <- AQUÍ: normalizamos. Si no hay filas, regresa null (no 0).
    return ($valor !== false && $valor !== null) ? (float)$valor : null;
}



public function getTipoInventario(): string
{
    $sql = "
        SELECT IC.f125_id_criterio_mayor
        FROM   t125_mc_items_criterios   IC
        JOIN   t120_mc_items             I  ON I.f120_rowid      = IC.f125_rowid_item
        JOIN   t121_mc_items_extensiones IE ON IE.f121_rowid_item = I.f120_rowid
        WHERE  IC.f125_id_plan = '008'
          AND  IE.f121_id_barras_principal = :ean
    ";

    $criterio = Yii::$app->dbSiesa
                 ->createCommand($sql)
                 ->bindValue(':ean', $this->codigoBarras)
                 ->queryScalar();

    return match ($criterio) {
        '0001' => 'VMI',
        '0002' => 'FIRME',
        default => 'N/A',
    };
}




}