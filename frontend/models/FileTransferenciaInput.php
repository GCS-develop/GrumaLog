<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DateTime;

use common\models\ProcedimientosGenerales;
use common\models\ProductosWs;

class FileTransferenciaInput extends Model
{
    public $archivo;

    public function rules()
    {
        return [
            [['archivo', ], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],

            [['archivo'], 'safe'],
            [['archivo'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'],
            [['archivo'], 'file', 'maxSize' => 15000000, 'tooBig' => 'El archivo es demasiado grande. El tamaño máximo permitido es 15 MiB.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'archivo' => 'Nombre Archivo',
        ];
    }

    public function importTransferencia($id)
    {
        if ($this->validate()) {
            $file = $this->archivo;

            $tempPath = Yii::getAlias('@app/temp/');
            $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;
            $file->saveAs($tempFileName);

            $destinationPath = Yii::getAlias('@app/destination/');
            $destinationFileName = $destinationPath . $file->baseName . '.' . $file->extension;
            copy($tempFileName, $destinationFileName);

            $model = Transferenciaerp::findOne(['id' => $id]);

            switch($model->idConectorDinamico){
                case 1:
                    FileTransferenciaInput::cargar_data_transferenciatransito ($destinationFileName, $id);
                    break;
                case 3:
                    FileTransferenciaInput::cargar_data_desdeordencompra ($destinationFileName, $id);
                    break;
            }
            
            
            unlink($tempFileName);
            return true;
        } else {
            //var_dump($this->getErrors()); die("hola");
            return false;
        }
    }

    public static function cargar_data_transferenciatransito ($archivoExcel, $id){

        ini_set('memory_limit', '8G'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)

        ini_set('max_execution_time', '1200'); //300 seconds = 5 minutes

        $numRegistrosBorrados = Transferenciatransitoexcel::deleteAll(['idTransferenciaerp' => $id]);

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja activa
        //$sheet = $spreadsheet->getActiveSheet();

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Transferencia transito');
 
        // Obtener el número total de filas en la hoja activa
        $totalFilas = $sheet->getHighestRow();

        $grabar = 0;

        // Iterar por cada fila
        for ($fila = 1; $fila <= $totalFilas; $fila++) {

            if ($fila == 1){
                continue;
            }

            $centroOperacionDocumento = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacionDocumento = $valor_celda;
            }else{
                break;
            }

            $tipoDocumento = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();
            if ($valor_celda){
                $tipoDocumento = $valor_celda;
            }

            $fechaDocumento = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda){
                $fechaDocumento = $valor_celda;
            }

            $bodegaSalidaDocumento = null;
            $valor_celda = $sheet->getCell('D' . $fila)->getValue();
            if ($valor_celda){
                $bodegaSalidaDocumento = $valor_celda;
            }

            $bodegaEntradaDocumento = null;
            $valor_celda = $sheet->getCell('E' . $fila)->getValue();
            if ($valor_celda){
                $bodegaEntradaDocumento = $valor_celda;
            }

            $centroOperacion = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacion = $valor_celda;
            }
            
            $tipoDocumentoMovimiento = null;
            $valor_celda = $sheet->getCell('G' . $fila)->getValue();
            if ($valor_celda){
                $tipoDocumentoMovimiento = $valor_celda;
            }

            $bodegaSalidaMovimiento = null;
            $valor_celda = $sheet->getCell('H' . $fila)->getValue();
            if ($valor_celda){
                $bodegaSalidaMovimiento = $valor_celda;
            }

            $centroOperacionMovimiento = null;
            $valor_celda = $sheet->getCell('I' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacionMovimiento = $valor_celda;
            }

            $unidadSalida = null;
            $valor_celda = $sheet->getCell('J' . $fila)->getValue();
            if ($valor_celda){
                $unidadSalida = $valor_celda;
            }

            $cantidadBase = 0;
            $valor_celda = $sheet->getCell('K' . $fila)->getValue();
            if ($valor_celda){
                $cantidadBase = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);
                if ($cantidadBase == null){
                    $cantidadBase = 0;
                }
            }

            $costoPromedioUnitario = 0;
            $valor_celda = $sheet->getCell('L' . $fila)->getValue();
            if ($valor_celda){
                $costoPromedioUnitario = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);
                if ($costoPromedioUnitario == null){
                    $costoPromedioUnitario = 0;
                }
            }

            $item = null;
            $valor_celda = $sheet->getCell('M' . $fila)->getValue();
            if ($valor_celda){
                $item = $valor_celda;
            }

            $color = null;
            $valor_celda = $sheet->getCell('N' . $fila)->getValue();
            if ($valor_celda){
                $color = $valor_celda;
            }

            $talla = null;
            $valor_celda = $sheet->getCell('O' . $fila)->getValue();
            if ($valor_celda){
                $talla = $valor_celda;
            }

            $numero = null;
            $valor_celda = $sheet->getCell('P' . $fila)->getValue();
            if ($valor_celda){
                $numero = $valor_celda;
            }

            $model = new Transferenciatransitoexcel();
            $model->centroOperacionDocumento = $centroOperacionDocumento;
            $model->tipoDocumento = $tipoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->bodegaSalidaDocumento = $bodegaSalidaDocumento;
            $model->bodegaEntradaDocumento = $bodegaEntradaDocumento;
            $model->centroOperacion = $centroOperacion;
            $model->tipoDocumentoMovimiento = $tipoDocumentoMovimiento;
            $model->bodegaSalidaMovimiento = $bodegaSalidaMovimiento;
            $model->centroOperacionMovimiento = $centroOperacionMovimiento;
            $model->unidadSalida = $unidadSalida;
            $model->cantidadBase = $cantidadBase;
            $model->costoPromedioUnitario = $costoPromedioUnitario;
            $model->item = $item;
            $model->color = $color;
            $model->talla = $talla;
            $model->numero = $numero;
            $model->idTransferenciaerp = $id;

            $ok = $model->save();

            if (!$ok){
                var_dump($model->getErrors()); die("hola");
            }else{
                $modelcolor = Color::find()->where(['codigo' => $color])->one(); 
                $modeltalla = Talla::find()->where(['codigo' => $talla])->one();
                $modelitem = Item::find()->where([
                                                    'item' => $item,
                                                    'idColor' => $modelcolor->id,
                                                    'idTalla' => $modeltalla->id            
                                                ])->one();
            }
        }

        if ($ok){
            $total = Transferenciatransitoexcel::find()->where(['idTransferenciaerp' => $id])->count();
            $model = Transferenciaerp::findOne (['id' => $id]);
            $model->numeroRegistros = $total;
            $model->save();
        }

    }

    public static function cargar_data_desdeordencompra ($archivoExcel, $id){

        ini_set('memory_limit', '2048M'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)

        ini_set('max_execution_time', '1200'); //300 seconds = 5 minutes

        $numRegistrosBorrados = Transferenciaordencompraexcel::deleteAll(['idTransferenciaerp' => $id]);

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja activa
        //$sheet = $spreadsheet->getActiveSheet();

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Entrada OC');
 
        // Obtener el número total de filas en la hoja activa
        $totalFilas = $sheet->getHighestRow();

        $grabar = 0;

        // Iterar por cada fila
        for ($fila = 1; $fila <= $totalFilas; $fila++) {

            if ($fila == 1){
                continue;
            }

            $centroOperacionDocumento = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacionDocumento = $valor_celda;
            }else{
                break;
            }

            $tipoDocumento = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();
            if ($valor_celda){
                $tipoDocumento = $valor_celda;
            }

            $consecutivoDocumento = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda){
                $consecutivoDocumento = $valor_celda;
            }

            $fechaDocumento = null;
            $valor_celda = $sheet->getCell('D' . $fila)->getValue();
            if ($valor_celda){
                $fechaDocumento = $valor_celda;
            }

            $tercero = null;
            $valor_celda = $sheet->getCell('E' . $fila)->getValue();
            if ($valor_celda){
                if (is_numeric($valor_celda)) {
                    $tercero = strval($valor_celda);
                } else {
                    $tercero = $valor_celda;;
                }
            }

            $numeroFactura = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda){
                $numeroFactura = $valor_celda;
            }

            $sucursal = null;
            $valor_celda = $sheet->getCell('G' . $fila)->getValue();
            if ($valor_celda){
                $sucursal = $valor_celda;
            }

            $idTerceroComprador = null;
            $valor_celda = $sheet->getCell('H' . $fila)->getValue();
            if ($valor_celda){
                if (is_numeric($valor_celda)) {
                    $idTerceroComprador = strval($valor_celda);
                } else {
                    $idTerceroComprador = $valor_celda;;
                }
            }

            $consignacion = 0;
            $valor_celda = $sheet->getCell('I' . $fila)->getValue();
            if ($valor_celda){
                $consignacion = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);
                if ($consignacion == null){
                    $consignacion = 0;
                }
            }

            $centroOperacionOrdenCompra = null;
            $valor_celda = $sheet->getCell('J' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacionOrdenCompra = $valor_celda;
            }

            $tipoDocumentoOrdenCompra = null;
            $valor_celda = $sheet->getCell('K' . $fila)->getValue();
            if ($valor_celda){
                $tipoDocumentoOrdenCompra = $valor_celda;
            }

            $consecutivoOrdenCompra = null;
            $valor_celda = $sheet->getCell('L' . $fila)->getValue();
            if ($valor_celda){
                $consecutivoOrdenCompra = $valor_celda;
            }

            $centroOperacionMovimiento = null;
            $valor_celda = $sheet->getCell('M' . $fila)->getValue();
            if ($valor_celda){
                $centroOperacionMovimiento = $valor_celda;
            }
            
            $tipoDocumentoMovimiento = null;
            $valor_celda = $sheet->getCell('N' . $fila)->getValue();
            if ($valor_celda){
                $tipoDocumentoMovimiento = $valor_celda;
            }

            $consecutivoMovimiento = null;
            $valor_celda = $sheet->getCell('O' . $fila)->getValue();
            if ($valor_celda){
                $consecutivoMovimiento = $valor_celda;
            }

            $numeroRegistroMovimiento = null;
            $valor_celda = $sheet->getCell('P' . $fila)->getValue();
            if ($valor_celda){
                $numeroRegistroMovimiento = $valor_celda;
            }

            $bodegaMovimiento = null;
            $valor_celda = $sheet->getCell('Q' . $fila)->getValue();
            if ($valor_celda){
                if (is_numeric($valor_celda)) {
                    $bodegaMovimiento = strval($valor_celda);
                } else {
                    $bodegaMovimiento = $valor_celda;;
                }
            }

            $unidadMovimiento = null;
            $valor_celda = $sheet->getCell('R' . $fila)->getValue();
            if ($valor_celda){
                $unidadMovimiento = $valor_celda;
            }

            $fechaEntregaMovimiento = null;
            $valor_celda = $sheet->getCell('S' . $fila)->getValue();
            if ($valor_celda){
                $fechaEntregaMovimiento = $valor_celda;
            }

            /*$fechaEntregaMovimiento = null;
            $valor_celda = $sheet->getCell('S' . $fila)->getValue();
            if ($valor_celda){
                if ($valor_celda != '-'){
                    try {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor_celda);
                        if ($date !== false) {
                            $fechaEntregaMovimiento = $date->format('Ymd');
                        }
                    } catch (Exception $e){
                        $fechaEntregaMovimiento = null;
                    }
                }
            }*/

            $cantidadBase = 0;
            $valor_celda = $sheet->getCell('T' . $fila)->getValue();
            if ($valor_celda){
                $cantidadBase = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);
                if ($cantidadBase == null){
                    $cantidadBase = 0;
                }
            }

            $item = null;
            $valor_celda = $sheet->getCell('U' . $fila)->getValue();
            if ($valor_celda){
                $item = $valor_celda;
            }

            $color = null;
            $valor_celda = $sheet->getCell('V' . $fila)->getValue();
            if ($valor_celda){
                $color = $valor_celda;
            }

            $talla = null;
            $valor_celda = $sheet->getCell('W' . $fila)->getValue();
            if ($valor_celda){
                $talla = $valor_celda;
            }

            $rowid = null;
            $valor_celda = $sheet->getCell('X' . $fila)->getValue();
            if ($valor_celda){
                $rowid = $valor_celda;
            }

            $model = new Transferenciaordencompraexcel();
            $model->centroOperacionDocumento = $centroOperacionDocumento;
            $model->tipoDocumento = $tipoDocumento;
            $model->consecutivoDocumento = $consecutivoDocumento;
            $model->fechaDocumento = $fechaDocumento;
            $model->tercero = $tercero;
            $model->numeroFactura = $numeroFactura;
            $model->sucursal = $sucursal;
            $model->idTerceroComprador = $idTerceroComprador;
            $model->consignacion = $consignacion;
            $model->centroOperacionOrdenCompra = $centroOperacionOrdenCompra;
            $model->tipoDocumentoOrdenCompra = $tipoDocumentoOrdenCompra;
            $model->consecutivoOrdenCompra = $consecutivoOrdenCompra;
            $model->centroOperacionMovimiento = $centroOperacionMovimiento;
            $model->tipoDocumentoMovimiento = $tipoDocumentoMovimiento;
            $model->consecutivoMovimiento = $consecutivoMovimiento;
            $model->numeroRegistroMovimiento = $numeroRegistroMovimiento;
            $model->bodegaMovimiento = $bodegaMovimiento;
            $model->unidadMovimiento = $unidadMovimiento;
            $model->cantidadBase = $cantidadBase;
            $model->fechaEntregaMovimiento = $fechaEntregaMovimiento;
            $model->item = $item;
            $model->color = $color;
            $model->talla = $talla;
            $model->rowid = $rowid;
            $model->idTransferenciaerp = $id;

            $ok = $model->save();

            if (!$ok){
                var_dump($model->getErrors()); die("hola");
            }

        }

        if ($ok){
            $total = Transferenciaordencompraexcel::find()->where(['idTransferenciaerp' => $id])->count();
            $model = Transferenciaerp::findOne (['id' => $id]);
            $model->numeroRegistros = $total;
            $model->save();
        }

    }


}

?>
