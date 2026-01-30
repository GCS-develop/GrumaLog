<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

class MovimientoGasto extends ActiveRecord
{
    public $grupo_id;     // Para filtrar cuentas por grupo
    public $valor;        // Valor único (UI)
    public $naturaleza;   // Naturaleza de la cuenta

    public static function tableName()
    {
        return 'dbo.MovimientoGasto';
    }

    public static function getDb()
    {
        return Yii::$app->db;
    }

    public function rules()
    {
        return [
            [['F351_ID_AUXILIAR','valor','naturaleza'], 'required'],

            [['F_CIA','F350_CONSEC_DOCTO','F351_VALOR_DB','F351_VALOR_CR','F351_BASE_GRAVABLE'], 'integer'],
            [['F350_ID_CO','F350_ID_TIPO_DOCTO','F351_ID_AUXILIAR','F351_ID_TERCERO',
              'F351_ID_CO_MOV','F351_ID_UN','F351_ID_CCOSTO','F351_ID_FE'], 'string', 'max'=>50],
            [['F351_DOCTO_BANCO','F351_NRO_DOCTO_BANCO'], 'string', 'max'=>100],
            [['F351_NOTAS'], 'string', 'max'=>1000],

            [['F350_ID_CO','F350_ID_TIPO_DOCTO','F351_ID_AUXILIAR','F351_ID_TERCERO',
              'F351_ID_CO_MOV','F351_ID_UN','F351_ID_CCOSTO','F351_ID_FE',
              'F351_DOCTO_BANCO','F351_NRO_DOCTO_BANCO','F351_NOTAS'], 'safe'],

            ['F351_VALOR_DB', 'default', 'value' => 0],
            ['F351_VALOR_CR', 'default', 'value' => 0],

            // XOR validación débito/crédito
            ['F351_VALOR_DB', 'validateXorCredito'],
            ['F351_VALOR_CR', 'validateXorCredito'],

            // Campos virtuales
            [['grupo_id','valor','naturaleza'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ID_TRANSACCION'       => 'ID Transacción',
            'F_CIA'                => 'F_CIA',
            'F350_ID_CO'           => 'Compañía',
            'F350_ID_TIPO_DOCTO'   => 'Tipo Documento',
            'F350_CONSEC_DOCTO'    => 'Consecutivo',
            'F351_ID_AUXILIAR'     => 'Cuenta Auxiliar',
            'F351_ID_TERCERO'      => 'Tercero',
            'F351_ID_CO_MOV'       => 'Compañía Movimiento',
            'F351_ID_UN'           => 'Unidad de Negocio',
            'F351_ID_CCOSTO'       => 'Centro de Costo',
            'F351_ID_FE'           => 'Factura Electrónica',
            'F351_VALOR_DB'        => 'Débito',
            'F351_VALOR_CR'        => 'Crédito',
            'F351_BASE_GRAVABLE'   => 'Base Gravable',
            'F351_DOCTO_BANCO'     => 'Docto Banco',
            'F351_NRO_DOCTO_BANCO' => 'Nro Docto Banco',
            'F351_NOTAS'           => 'Notas',
            'grupo_id'             => 'Grupo de Cuentas',
            'valor'                => 'Valor',
            'naturaleza'           => 'Naturaleza',
        ];
    }

    public function validateXorCredito($attribute)
    {
        $db = (int)$this->F351_VALOR_DB;
        $cr = (int)$this->F351_VALOR_CR;

        if (($db > 0 && $cr > 0) || ($db <= 0 && $cr <= 0)) {
            $this->addError('F351_VALOR_DB', 'Debe diligenciar únicamente Débito o Crédito.');
            $this->addError('F351_VALOR_CR', 'Debe diligenciar únicamente Débito o Crédito.');
        }
    }

    /*public function afterFind()
    {
        parent::afterFind();

        // Valor unificado
        $this->valor = $this->F351_VALOR_DB > 0 ? $this->F351_VALOR_DB : $this->F351_VALOR_CR;

        // Naturaleza desde GrupoConceptoCuenta
        $cuenta = \frontend\models\GrupoConceptoCuenta::findOne(['cuenta' => $this->F351_ID_AUXILIAR]);
        if ($cuenta) {
            $this->naturaleza = $cuenta->naturaleza;
        }
    }*/

    /**
     * 🔑 La PK real es ID_TRANSACCION + un identificador de línea
     * (si tu tabla no tiene un campo autonumérico para cada detalle, 
     * puedes usar también F351_ID_AUXILIAR como parte de la PK).
     */
    /*public static function primaryKey()
    {
        return ['ID_TRANSACCION'];
    }*/

 public function beforeSave($insert)
    {
        // Limpiar espacios en los CO
        if ($this->F350_ID_CO !== null) {
            $this->F350_ID_CO = trim($this->F350_ID_CO);
        }
        if ($this->F351_ID_CO_MOV !== null) {
            $this->F351_ID_CO_MOV = trim($this->F351_ID_CO_MOV);
        }

        return parent::beforeSave($insert);
    }


}




