<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "auditoriaentradadetalle".
 *
 * @property int $id
 * @property int $idauditoriaentrada
 * @property string|null $ean
 * @property int $item
 * @property string|null $color
 * @property string|null $talla
 * @property int $cantidad
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 *
 * @property Auditoriaentrada $auditoriaentrada
 */
class Auditoriaentradadetalle extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auditoriaentradadetalle';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::class,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['idauditoriaentrada', 'item'], 'required'],
            [['idauditoriaentrada', 'item', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [['cantidad'], 'default', 'value' => 1],
            [['ean'], 'string', 'max' => 50],
            [['color'], 'string', 'max' => 50],
            [['talla'], 'string', 'max' => 20],
        ];
    }

    public function getAuditoriaentrada()
    {
        return $this->hasOne(Auditoriaentrada::class, ['id' => 'idauditoriaentrada']);
    }
}
