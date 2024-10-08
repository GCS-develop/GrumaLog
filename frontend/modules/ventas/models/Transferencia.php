<?php

namespace frontend\modules\ventas\models;

use Yii;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This is the model class for table "transferencia".
 *
 * @property int $id
 * @property int $idFactura
 * @property string $codigoBarra
 * @property string $item
 * @property string $color
 * @property string $talla
 * @property string $unidadMedida
 * @property string $bodega
 * @property string $motivo
 * @property int $cantidadBase
 * @property float $precioUnitario
 * @property string|null $referencia
 * @property string|null $descripcion
 */
class Transferencia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transferencia';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbVentasPOS');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idFactura', 'codigoBarra', 'item', 'color', 'talla', 'bodega', 'cantidadBase', 'precioUnitario'], 'required'],
            [['idFactura', 'cantidadBase'], 'integer'],
            [['precioUnitario'], 'number'],
            [['codigoBarra', 'talla'], 'string', 'max' => 50],
            [['item'], 'string', 'max' => 10],
            [['color', 'referencia', 'descripcion'], 'string', 'max' => 150],
            [['unidadMedida', 'bodega'], 'string', 'max' => 5],
            [['motivo'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idFactura' => 'Factura',
            'codigoBarra' => 'Código Barra',
            'item' => 'Item',
            'color' => 'Color',
            'talla' => 'Talla',
            'unidadMedida' => 'UM',
            'bodega' => 'Bodega',
            'motivo' => 'Motivo',
            'cantidadBase' => 'Unidades',
            'precioUnitario' => 'Precio Unitario',
            'referencia' => 'Referencia',
            'descripcion' => 'Descripción',
        ];
    }

    public static function totalDocumentoOK ($idfactura){
        $total = Transferencia::find()
                            ->where(['idFactura' => $idfactura]) // Filtrar por el código
                            ->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['=', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

    public static function totalDocumentoError ($idfactura){
        $total = Transferencia::find()
                            ->where(['idFactura' => $idfactura]) // Filtrar por el código
                            ->andWhere(['>', 'cantidadBase', 0]) // Condición: atributo_positivo > 0
                            ->andWhere(['<>', 'error', 0]) // Condición: Registro OK
                            ->sum('cantidadBase * precioUnitario');

        return $total;
    }

    public static function generarArchivotransferencia ($factura){
        
        $idfactura = $factura->id;
        $archivo = Yii::getAlias('@app/web/archivos/Formato_FacturaProveedor_Entradas_GenericTransfer.xlsx'); // Ruta al archivo Excel
        
        $spreadsheet = IOFactory::load($archivo);

        $sheet = $spreadsheet->getSheetByName('Relación saldos por ítem V.3');
        $spreadsheet->setActiveSheetIndex(0);

        $modeldetalle = Transferencia::find()
                                    ->where(['idFactura' => $idfactura])
                                    ->andWhere(['<>', 'precioUnitario', 0])
                                    ->andWhere(['<>', 'bodega', ''])->all();

        $linea = 2;

        $valordocumento = 0;

        foreach($modeldetalle as $detalle){
            $sheet->setCellValue('A'.$linea, $factura->centroOperacion);
            $sheet->setCellValue('B'.$linea, $factura->tipoDocumento);
            $sheet->setCellValue('C'.$linea, $factura->consecutivoDocumento);
            $sheet->setCellValue('D'.$linea, $detalle->item);
            $sheet->setCellValue('E'.$linea, $detalle->color);
            $sheet->setCellValue('F'.$linea, $detalle->talla);
            $sheet->setCellValue('G'.$linea, $detalle->unidadMedida);
            $sheet->setCellValue('H'.$linea, $detalle->bodega);
            $sheet->setCellValue('I'.$linea, $detalle->motivo);
            $sheet->setCellValue('J'.$linea, $detalle->cantidadBase);
            $sheet->setCellValue('K'.$linea, $detalle->precioUnitario);

            $valordocumento = $valordocumento + $detalle->cantidadBase * $detalle->precioUnitario;

            $linea = $linea + 1;
        }

        // Seleccionar la hoja por su nombre
        $sheet = $spreadsheet->getSheetByName('Documentos');
        $spreadsheet->setActiveSheetIndex(1);

        $fechaDocumento = str_replace('-', '', $factura->fechaDocumento);
        $fechaDocumentoProveedor = str_replace('-', '', $factura->fechaDocumentoProveedor);
        $fechaVencimientoCuota = str_replace('-', '', $factura->fechaVencimientoCuota);
        $fechaProntoPago = str_replace('-', '', $factura->fechaProntoPago);

        $sheet->setCellValue('A2', $factura->centroOperacion);
        $sheet->setCellValue('B2', $factura->tipoDocumento);
        $sheet->setCellValue('C2', $factura->consecutivoDocumento);
        $sheet->setCellValue('D2', $fechaDocumento);
        $sheet->setCellValue('E2', $factura->proveedor->nit);
        $sheet->setCellValue('F2', $factura->codigoSucursal);
        $sheet->setCellValue('G2', $factura->prefijoDocumentoProveedor);
        $sheet->setCellValue('H2', $factura->consecutivoDocumentoProveedor);
        $sheet->setCellValue('I2', $fechaDocumentoProveedor);
        $sheet->setCellValue('J2', $factura->condicionPago);
        $sheet->setCellValue('K2', $valordocumento);
        $sheet->setCellValue('L2', $factura->tipoProveedor);

        $sheet = $spreadsheet->getSheetByName('Cuotas CxP');
        $spreadsheet->setActiveSheetIndex(2);

        $sheet->setCellValue('A2', $factura->centroOperacion);
        $sheet->setCellValue('B2', $factura->tipoDocumento);
        $sheet->setCellValue('C2', $factura->consecutivoDocumento);
        $sheet->setCellValue('D2', $factura->porcentajeCuota);
        $sheet->setCellValue('E2', $fechaVencimientoCuota);
        $sheet->setCellValue('F2', $fechaProntoPago);

        $nombreArchivo = "Entrada_Factura_" . 
                    $factura->proveedor->nit . "_" . 
                    $factura->prefijoDocumentoProveedor . $factura->consecutivoDocumentoProveedor . "_" . 
                    $fechaDocumentoProveedor . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $rutaGuardado = Yii::getAlias('@app/web/archivos/') . $nombreArchivo;
        
        // Guardar el archivo Excel
        $writer->save($rutaGuardado);

        return $rutaGuardado;
    }
    
}
