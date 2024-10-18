<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "estadodespacho".
 *
 * @property int $id
 * @property int $codigo
 * @property string $nombre
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Estadodespacho extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estadodespacho';
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
            [['codigo', 'nombre'], 'required'],
            [['codigo', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre', 'nombresiesa'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Codigo',
            'nombre' => 'Nombre',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function getEstadodespacho()
    {
        return $this->hasOne(Estadodespacho::class, ['id' => 'id']);
    }

    public static function getListaData()
    {
        $data = Estadodespacho::find()
            ->select(['id', 'nombre'])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
    public static function getListaDataMenosEliminado()
    {
        $data = Estadodespacho::find()
            ->select(['id', 'nombre'])
            ->where(['!=', 'id', 3]) // Excluir registros con idEstado igual a 3
            ->orderBy('nombre')
            ->asArray()
            ->all();

        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function getListaDataSinEnviar()
    {
        $data = Estadodespacho::find()
            ->select(['id', 'nombre'])
            ->where(['=', 'id', 1]) // Excluir registros con idEstado igual a 3
            ->orderBy('nombre')
            ->asArray()
            ->all();

        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

}
