<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;
use yii\helpers\ArrayHelper;

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
    public $nombreBodega;   // <-- agrégalo
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

    public function getImportacionDetalle()
    {
        return $this->hasOne(Devolucionimportaciondetalle::class, [
            'codigoBarras' => 'codigoBarras',
            'numeroDocumento' => 'numeroDocumento', // 👈 así evitas que mezcle con otro documento
        ]);
    }

    public function getNombreProveedor()
    {
        // Si existe relación, devuelve el proveedor de la importación
        return $this->importacionDetalle?->proveedor ?? 'Proveedor No Asignado';
    }

    public static function getListaDataUsuarioRegistra(): array
    {
        $rows = User::find()
            ->alias('u')
            ->innerJoin(Devoluciondocumentodetalle::tableName() . ' dd', 'dd.usuarioRegistra = u.id')
            ->select(['u.id', 'u.username AS nombre'])   // cambia 'nombre' si prefieres nombres/apellidos
            ->distinct()
            ->orderBy(['nombre' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($rows, 'id', 'nombre'); // id => username
    }


/*public function getBodega()
{
    // Si en tu tabla usas el ID
    //return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);

    // O si usas el código (ajústalo según tu caso real)
     return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodegaSalida']);
}
*/

public function getDocumento()
{
    // Relación con la cabecera del documento
    return $this->hasOne(Devoluciondocumento::class, ['id' => 'idDocumento']);
}

public function getBodegaSalida()
{
    // Relación con la tabla bodegas a través del documento cabecera
    return $this->hasOne(Bodegas::class, ['codigo' => 'codigoBodegaSalida'])
                ->via('documento');
}

public function getNombreBodega()
{
    return $this->nombreBodega ?? 'Bodega No Asignada';
}


}
