<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use common\models\User;

/**
 * Cabecera del Pedido Monacho.
 *
 * @property int    $id
 * @property string $proveedor
 * @property string $oc_siesa
 * @property string $oc_icg
 * @property string $fecha_despacho
 * @property int    $contador_ean_inicio
 * @property string $estado             BORRADOR | ENVIADO
 * @property string $notas
 * @property string $created_at
 * @property int    $created_by
 * @property string $updated_at
 * @property int    $updated_by
 */
class MonachoPedido extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'monacho_pedido';
    }

    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => new Expression('GETDATE()'),
            ],
            [
                'class'              => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value'              => function () { return Yii::$app->user->id; },
            ],
        ];
    }

    public function rules()
    {
        return [
            [['proveedor'], 'required'],
            [['proveedor'], 'string', 'max' => 200],
            [['oc_siesa', 'oc_icg'], 'string', 'max' => 50],
            [['fecha_despacho', 'created_at', 'updated_at'], 'safe'],
            [['contador_ean_inicio'], 'integer', 'min' => 1],
            [['estado'], 'string', 'max' => 20],
            [['notas'], 'string'],
            [['created_by', 'updated_by'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'proveedor'           => 'Proveedor',
            'oc_siesa'            => 'OC SIESA',
            'oc_icg'              => 'OC ICG',
            'fecha_despacho'      => 'Fecha Despacho',
            'contador_ean_inicio' => 'Contador EAN Inicial',
            'estado'              => 'Estado',
            'notas'               => 'Notas',
            'created_at'          => 'Creado',
            'created_by'          => 'Creado Por',
            'updated_at'          => 'Actualizado',
        ];
    }

    public function getArticulos()
    {
        return $this->hasMany(MonachoArticulo::class, ['pedido_id' => 'id']);
    }

    public function getUsuariocrea()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /** Total de artículos del pedido */
    public function getTotalArticulos()
    {
        return (int) MonachoArticulo::find()->where(['pedido_id' => $this->id])->count();
    }

    /** Total de subartículos (combinaciones color/talla) */
    public function getTotalSubarticulos()
    {
        return (int) Yii::$app->db->createCommand(
            'SELECT COUNT(*) FROM monacho_detalle d
             INNER JOIN monacho_articulo a ON a.id = d.articulo_id
             WHERE a.pedido_id = :pid',
            [':pid' => $this->id]
        )->queryScalar();
    }

    /** Total de unidades pedidas */
    public function getTotalUnidades()
    {
        return (int) Yii::$app->db->createCommand(
            'SELECT ISNULL(SUM(d.cantidad), 0) FROM monacho_detalle d
             INNER JOIN monacho_articulo a ON a.id = d.articulo_id
             WHERE a.pedido_id = :pid',
            [':pid' => $this->id]
        )->queryScalar();
    }

    /** Badge de estado */
    public function getEstadoBadge()
    {
        $map = [
            'BORRADOR' => '<span class="badge badge-secondary">✏ Borrador</span>',
            'APROBADO' => '<span class="badge badge-primary">✔ Aprobado</span>',
            'ENVIADO'  => '<span class="badge badge-success">✉ Enviado</span>',
        ];
        return $map[$this->estado] ?? '<span class="badge badge-light">' . htmlspecialchars($this->estado) . '</span>';
    }
}
