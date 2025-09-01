<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "comprador".
 *
 * @property int $id
 * @property float $documento
 * @property string $nombre
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Comprador extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comprador';
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
            [['documento', 'nombre', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['documento'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by'], 'integer'],
            [['nombre'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'documento' => 'Documento',
            'nombre' => 'Nombre',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public static  function  getListaData()
    {
        $data = Comprador::find()
            ->select(['id', "(CAST(documento AS VARCHAR) + ' - ' + nombre) AS nombre"])
            ->orderBy('nombre')->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }

    public static function getListaDocumentoComoClave()
    {
        $data = Comprador::find()
            ->select(['documento', new \yii\db\Expression("CAST(documento AS VARCHAR) + ' - ' + nombre AS nombre")])
            ->orderBy('nombre')
            ->asArray()
            ->all();

        return ArrayHelper::map($data, 'documento', 'nombre');
    }
}
