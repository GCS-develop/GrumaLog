<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "userbodegaasignacion".
 *
 * @property int $id
 * @property int $idUserBodega
 * @property int $idBodega
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Bodegas $idBodega0
 * @property Userbodega $idUserBodega0
 */
class Userbodegaasignacion extends \yii\db\ActiveRecord
{
    public $identificacion;
    public $nombreEmpleado;
    public $codigoAlmacen;
    public $almacen;
    public $username;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'userbodegaasignacion';
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
            [['idUserBodega', 'idBodega'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idUserBodega', 'idBodega', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodega' => 'id']],
            [['idUserBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Userbodega::class, 'targetAttribute' => ['idUserBodega' => 'id']],
            [['idUserBodega', 'idBodega'], 'unique', 'targetAttribute' => ['idUserBodega', 'idBodega'], 'message' => 'El Almacén Ya Esta Asignado al Usuario.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idUserBodega' => 'Id User Bodega',
            'idBodega' => 'Almacén',
            'idEstado' => 'Estado',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',

            'username' => 'Usuario', 
            'nombreEmpleado' => 'Empleado', 
            'identificacion' => 'Identificación',
            'codigoAlmacen' => 'Código Almacén', 
            'almacen' => 'Almacén',
        ];
    }

    /**
     * Gets query for [[Bodega]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);
    }

    /**
     * Gets query for [[UserBodega]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserBodega()
    {
        return $this->hasOne(Userbodega::class, ['id' => 'idUserBodega']);
    }

    public static  function  getListaData($iduser){
        $data = Userbodegaasignacion::find()
                        ->alias('usba')
                        ->select(['usb.id', "bo.codigo + ' - ' + bo.nombre AS nombre"])
                        ->join('INNER JOIN', 'Userbodega usb', 'usba.idUserBodega = usb.id')
                        ->join('INNER JOIN', 'empleado em', 'usb.idEmpleado = em.id')
                        ->join('INNER JOIN', 'user us', 'usb.idUser = us.id')
                        ->join('INNER JOIN', 'bodegas bo', 'usba.idBodega = bo.id')
                        ->where(['eml.idEstado' => 1, 'us.status' => 10])
                        ->andFilterWhere(['usb.idUser' => $iduser])
                        ->orderBy('em.nombreEmpleado')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
