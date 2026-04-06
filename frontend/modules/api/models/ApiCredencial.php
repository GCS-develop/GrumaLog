<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * Modelo de credenciales API por NIT de proveedor.
 * Tabla: agendaapicredencial
 */
class ApiCredencial extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'agendaapicredencial';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['nit', 'token'], 'required'],
            [['activo', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['nit'], 'string', 'max' => 20],
            [['razonSocial'], 'string', 'max' => 200],
            [['token'], 'string', 'max' => 64],
            [['token'], 'unique'],
        ];
    }

    /**
     * Genera y guarda un token nuevo para el NIT dado.
     * Si ya existe uno activo para ese NIT, lo reemplaza.
     */
    public static function generarToken(string $nit, string $razonSocial = null): ?self
    {
        // Desactivar credencial anterior si existe
        self::updateAll(['activo' => 0], ['nit' => $nit, 'activo' => 1]);

        $credencial = new self();
        $credencial->nit        = $nit;
        $credencial->razonSocial = $razonSocial;
        $credencial->token      = bin2hex(random_bytes(32));
        $credencial->activo     = 1;

        return $credencial->save() ? $credencial : null;
    }

    /**
     * Busca una credencial activa por token.
     */
    public static function findByToken(string $token): ?self
    {
        return self::findOne(['token' => $token, 'activo' => 1]);
    }
}
