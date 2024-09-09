<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspasouserbodega".
 *
 * @property int $id
 * @property int $idUserTraspaso
 * @property int $idBodega
 * @property int|null $idEstado
 *
 * @property Bodegas $idBodega0
 * @property Usertraspaso $idUserTraspaso0
 */
class Traspasouserbodega extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasouserbodega';
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
            [['idUserTraspaso', 'idBodega'], 'required'],
            [['idUserTraspaso', 'idBodega', 'idEstado'], 'integer'],
            [['idBodega', 'idUserTraspaso'], 'unique', 'targetAttribute' => ['idBodega', 'idUserTraspaso']],
            [['idUserTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Usertraspaso::class, 'targetAttribute' => ['idUserTraspaso' => 'id']],
            [['idBodega'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idBodega' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idUserTraspaso' => 'Id User Traspaso',
            'idBodega' => 'Id Bodega',
            'idEstado' => 'Id Estado',
        ];
    }

    /**
     * Gets query for [[IdBodega0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);
    }

    /**
     * Gets query for [[IdUserTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserTraspaso()
    {
        return $this->hasOne(Usertraspaso::class, ['id' => 'idUserTraspaso']);
    }

    public static function getListaData()
    {
        $data = Traspasouserbodega::find()
                        ->select(['bo.id', "(bo.codigo + ' - ' + bo.nombre) AS nombre"])
                        ->alias('tub')
                        ->distinct()
                        ->join('INNER JOIN', 'bodegas bo', 'tub.idBodega = bo.id')
                        ->join('INNER JOIN', 'usertraspaso ust', 'tub.idUserTraspaso = ust.id')
                        ->join('INNER JOIN', 'user us', 'ust.idUser = us.id')
                        ->join('INNER JOIN', 'Bodegatipodocumento tpo', 'bo.id = tpo.idBodega')
                        ->where(['tub.idEstado' => 1, 'us.status' => 10, 'us.id' => Yii::$app->user->id])
                        ->orderBy('bo.id')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

}
