<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use common\models\User;

/**
 * This is the model class for table "auditoriamanualdocumentodetalle".
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
class Auditoriamanualdocumentodetalle extends \yii\db\ActiveRecord
{
    public $co;
    public $categoria;
    public $proveedor;
    public $numeroDocumento;
    public $codigoBodegaSalida;
    public $fechaDesde;
    public $fechaHasta;
    public $unidades;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auditoriamanualdocumentodetalle';
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
            [['item', 'created_at', 'updated_at', 'fechaRegistra', 'unidades'], 'safe'],
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
            'cantidadDevolucion' => 'Cantidad Inicial',
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
        return $this->cantidadRegistrada - $this->cantidadDevolucion ;
    }
}
