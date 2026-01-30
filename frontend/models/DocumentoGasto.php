<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo DocumentoGasto (Hoja 1)
 * Ahora usa ID_TRANSACCION como PK autonumérica
 */
class DocumentoGasto extends ActiveRecord
{
    public static function tableName()
    {
        return 'dbo.DocumentoGasto';
    }

    public static function getDb()
    {
        // Conexión GRUMALOG
        return Yii::$app->db;
    }

    /**
     * Definir la clave primaria real
     */
    public static function primaryKey()
    {
        return ['ID_TRANSACCION']; // 👈 ahora es autonumérica
    }

   public function rules()
{
    return [
        [['F350_ID_CO','F350_ID_TIPO_DOCTO','F350_ID_TERCERO'], 'filter', 'filter' => function($v){ 
            return $v === null ? null : trim((string)$v); 
        }],

        [['F350_ID_CO','F350_FECHA','F350_ID_TERCERO'], 'required', 'message' => '{attribute} no puede estar vacío.'],
        [['F350_CONSEC_DOCTO'], 'integer'],
        [['F350_FECHA'], 'date', 'format' => 'php:Y-m-d'],

        // 👇 Restricción: solo números y entre 5 y 15 dígitos (ajusta a tu necesidad)
        ['F350_ID_TERCERO', 'match', 'pattern' => '/^\d{5,15}$/', 'message' => 'El campo Tercero debe contener solo números (mínimo 5 y máximo 15 dígitos).'],

        [['F350_ID_CO','F350_ID_TIPO_DOCTO'], 'string', 'max' => 50],
        [['F350_NOTAS'], 'string', 'max' => 1000],
        [['F350_IND_ESTADO'], 'safe'],
        [['CONSEC_LOCAL','CONSEC_SIESA'], 'safe'],
        [['estado_envio','usuario_envio','fecha_envio'], 'safe'],
        [['respuesta_siesa'], 'string'],
    ];
}


    public function attributeLabels()
    {
        return [
            'ID_TRANSACCION'    => 'ID Transacción',
            'F350_ID_CO'        => 'F350 ID CO',
            'F350_ID_TIPO_DOCTO'=> 'F350 ID Tipo Docto',
            'F350_CONSEC_DOCTO' => 'F350 Consec Docto',
            'F350_FECHA'        => 'Fecha',
            'F350_ID_TERCERO'   => 'Tercero',
            'F350_NOTAS'        => 'Notas',
            'CONSEC_LOCAL'      => 'Consecutivo Local',
            'CONSEC_SIESA'      => 'Consecutivo Siesa',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) return false;

        // Tipo fijo 22
        $this->F350_ID_TIPO_DOCTO = '22';

        // Normalizaciones con trim
        foreach (['F350_ID_CO','F350_ID_TERCERO','F350_ID_TIPO_DOCTO'] as $a) {
            if ($this->$a !== null) $this->$a = trim((string)$this->$a);
        }
        if (!empty($this->F350_NOTAS)) {
            $this->F350_NOTAS = trim($this->F350_NOTAS);
        }
        if (!empty($this->F350_FECHA)) {
            $ts = strtotime(str_replace('/', '-', $this->F350_FECHA));
            if ($ts !== false) $this->F350_FECHA = date('Y-m-d', $ts);
        }

        // Respaldo: si no tiene consecutivo, calcularlo
        $this->ensureConsecutivoCia7Tipo22();

        return true;
    }

    public function beforeSave($insert)
    {
        foreach (['F350_ID_CO','F350_ID_TIPO_DOCTO','F350_ID_TERCERO'] as $a) {
            if ($this->$a !== null) $this->$a = trim((string)$this->$a);
        }
        $this->ensureConsecutivoCia7Tipo22();
        return parent::beforeSave($insert);
    }

    /**
     * Asigna F350_CONSEC_DOCTO = MAX+1 en Siesa para CIA=7 y Tipo=22.
     */
    public function ensureConsecutivoCia7Tipo22(): void
    {
        if ((int)$this->F350_CONSEC_DOCTO > 0) return;

        try {
            $sql = "SELECT ISNULL(MAX(a.f350_consec_docto),0)
                    FROM t350_co_docto_contable a
                    WHERE a.f350_id_tipo_docto='22' AND a.f350_id_cia='7'";
            $max = (int)Yii::$app->dbSiesa->createCommand($sql)->queryScalar();
            $this->F350_CONSEC_DOCTO = $max + 1;
        } catch (\Throwable $e) {
            // Silenciado
        }
    }
}
