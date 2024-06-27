<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "transportadora".
 *
 * @property int $id
 * @property string|null $nombre
 * @property int|null $idEstado
 * @property int|null $idProveedor
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Agendaentregamercancia[] $agendaentregamercancias
 * @property Proveedor $idProveedor0
 */
class Transportadora extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transportadora';
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
            [['nombre', 'idEstado'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idEstado', 'idProveedor', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre'], 'string', 'max' => 50],
            ['nombre', 'unique', 'message' => 'Nombre Transportadora ya está registrado.'],
            [['idProveedor'], 'exist', 'skipOnError' => true, 'targetClass' => Proveedor::class, 'targetAttribute' => ['idProveedor' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'idEstado' => 'Estado',
            'idProveedor' => 'Id Proveedor',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Agendaentregamercancias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgendaentregamercancias()
    {
        return $this->hasMany(Agendaentregamercancia::class, ['idTransportadora' => 'id']);
    }

    /**
     * Gets query for [[IdProveedor0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdProveedor0()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'idProveedor']);
    }

    public static  function  getListaData(){
        $data = Transportadora::find()
                        ->select(['id', 'nombre'])
                        ->orderBy('nombre')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
