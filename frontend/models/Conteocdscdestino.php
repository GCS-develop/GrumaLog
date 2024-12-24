<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "conteocdscdestino".
 *
 * @property int $id
 * @property int $idConteocdscdestinofactura
 * @property int $idCentroOperacion
 * @property int $numeroCajas
 * @property int $idUserConteo
 * @property int|null $idItemUltimoConteo
 * @property int|null $total
 * @property int|null $idEstado
 * @property int|null $idLegalizado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestinodetalle[] $conteocdscdestinodetalles
 * @property Centrooperacion $centroperacion
 * @property Conteocdscdestinofactura $factura
 * @property Userconteocdsc $idUserConteo0
 */
class Conteocdscdestino extends \yii\db\ActiveRecord
{
    public $almacen;
    public $codigoAlmacen;
    public $codigoProveedor;
    public $nit;
    public $razonSocial;
    public $tipoProveedor;
    public $idEstadoFactura;
    public $idLegalizadoFactura;
    public $idEstadoEntrada;
    public $idEstadoTraspaso;
    public $numeroFactura;
    public $radicado;
    public $fecha;
    public $nombreEmpleado;
    public $identificacion;
    public $fechaDesde;
    public $fechaHasta;
    public $codigoAlmacenLegaliza;
    public $nombreAlmacenLegaliza;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscdestino';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idConteocdscdestinofactura', 'idCentroOperacion', 'numeroCajas', 'idUserConteo', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idConteocdscdestinofactura', 'idCentroOperacion', 'numeroCajas', 'idUserConteo', 'idItemUltimoConteo', 'total', 'idEstado', 'idLegalizado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['idUserConteo'], 'exist', 'skipOnError' => true, 'targetClass' => Userconteocdsc::class, 'targetAttribute' => ['idUserConteo' => 'id']],
            [['idCentroOperacion'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idCentroOperacion' => 'id']],
            [['idConteocdscdestinofactura'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestinofactura::class, 'targetAttribute' => ['idConteocdscdestinofactura' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'No. Conteo',
            'idConteocdscdestinofactura' => 'Id Conteocdscdestinofactura',
            'idCentroOperacion' => 'Id Centro Operacion',
            'numeroCajas' => 'No. Cajas',
            'idUserConteo' => 'Id User Conteo',
            'idItemUltimoConteo' => 'Id Item Ultimo Conteo',
            'total' => 'Total',
            'idEstado' => 'Id Estado',
            'idLegalizado' => 'Id Legalizado',
            'created_at' => 'Inicio Conteo',
            'created_by' => 'Created By',
            'updated_at' => 'Fin Conteo',
            'updated_by' => 'Updated By',

            'codigoProveedor' => 'Cód. Proveedor',
            'razonSocial' => 'Proveedor',
            'tipoProveedor' => 'Tipo Proveedor',

            'idEstadoFactura' => 'Estado',
            'idLegalizadoFactura' => 'Legalizado',
            'numeroFactura' => 'Factura',
            'idEstadoEntrada' => 'Entrada',
            'idEstadoTraspaso' => 'Traspaso',

            'nombreEmpleado' => 'Usuario Conteo',
            'identificacion' => 'Identificación',
            'codigoAlmacen' => 'Código Almacén',
            'almacen' => 'Almacén',

            'fechaDesde' => 'Fecha Desde',
            'fechaHasta' => 'Fecha Hasta',

            'codigoAlmacenLegaliza' => 'Cód. Almacén Legaliza',
            'nombreAlmacenLegaliza' => 'Almacén Legaliza',
        ];
    }

    /**
     * Gets query for [[Conteocdscdestinodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConteocdscdestinodetalles()
    {
        return $this->hasMany(Conteocdscdestinodetalle::class, ['idConteocdscdestino' => 'id']);
    }

    /**
     * Gets query for [[CentroOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentrooperacion()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idCentroOperacion']);
    }

    /**
     * Gets query for [[Factura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Conteocdscdestinofactura::class, ['id' => 'idConteocdscdestinofactura']);
    }

    /**
     * Gets query for [[IdUserConteo0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarioconteo()
    {
        return $this->hasOne(Userconteocdsc::class, ['id' => 'idUserConteo']);
    }
}
