<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "temptransferenciatransitoexcel".
 *
 * @property int $id
 * @property int $idTransferenciaerp
 * @property string $centroOperacionDocumento
 * @property string $tipoDocumento
 * @property string $fechaDocumento
 * @property string $bodegaSalidaDocumento
 * @property string $bodegaEntradaDocumento
 * @property string $centroOperacion
 * @property string $tipoDocumentoMovimiento
 * @property string $bodegaSalidaMovimiento
 * @property string $centroOperacionMovimiento
 * @property string $unidadSalida
 * @property int $cantidadBase
 * @property float $costoPromedioUnitario
 * @property int $item
 * @property string $color
 * @property string $talla
 * @property string|null $numero
 * @property int|null $procesado
 * @property int|null $fila
 * @property string|null $notas
 * @property string|null $codigoBarras
 * @property string|null $codigoUnidadEmpaque
 * @property int|null $unidadesConteoEmpaque
 */
class Temptransferenciatransitoexcel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temptransferenciatransitoexcel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTransferenciaerp', 'centroOperacionDocumento', 'tipoDocumento', 'fechaDocumento', 'bodegaSalidaDocumento', 'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 'bodegaSalidaMovimiento', 'centroOperacionMovimiento', 'unidadSalida', 'cantidadBase', 'costoPromedioUnitario', 'item', 'color', 'talla'], 'required'],
            [['idTransferenciaerp', 'cantidadBase', 'item', 'procesado', 'fila', 'unidadesConteoEmpaque'], 'integer'],
            [['costoPromedioUnitario'], 'number'],
            [['centroOperacionDocumento', 'tipoDocumento', 'bodegaSalidaDocumento', 'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 'bodegaSalidaMovimiento', 'centroOperacionMovimiento'], 'string', 'max' => 5],
            [['fechaDocumento', 'unidadSalida', 'codigoUnidadEmpaque'], 'string', 'max' => 10],
            [['color'], 'string', 'max' => 50],
            [['talla', 'numero', 'codigoBarras'], 'string', 'max' => 20],
            [['notas'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTransferenciaerp' => 'Id Transferenciaerp',
            'centroOperacionDocumento' => 'CO Documento',
            'tipoDocumento' => 'Tipo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'bodegaSalidaDocumento' => 'Bodega Salida Documento',
            'bodegaEntradaDocumento' => 'Bodega Entrada Documento',
            'centroOperacion' => 'CO',
            'tipoDocumentoMovimiento' => 'Tipo Documento Movimiento',
            'bodegaSalidaMovimiento' => 'Bodega Salida Movimiento',
            'centroOperacionMovimiento' => 'Centro Operación Movimiento',
            'unidadSalida' => 'Unidad Salida',
            'cantidadBase' => 'Cantidad Base',
            'costoPromedioUnitario' => 'Costo Promedio Unitario',
            'item' => 'Item',
            'color' => 'Color',
            'talla' => 'Talla',
            'numero' => 'Numero',
            'procesado' => 'Procesado',
            'fila' => 'Fila',
            'notas' => 'Notas',
            'codigoBarras' => 'Codigo Barras',
            'codigoUnidadEmpaque' => 'Codigo Unidad Empaque',
            'unidadesConteoEmpaque' => 'Unidades Conteo Empaque',
        ];
    }

    public static function getTotalUnidadesConteo($id)
    {
        return self::find()
            ->where(['idTransferenciaerp' => $id])
            ->sum('cantidadBase');
    }

    public static function getTotalUnidadesConteoEmpaque($id)
    {
        return self::find()
            ->where(['idTransferenciaerp' => $id])
            ->sum('unidadesConteoEmpaque');
    }
}
