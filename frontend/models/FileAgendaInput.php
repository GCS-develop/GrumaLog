<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DateTime;

use common\models\ProcedimientosGenerales;
use common\models\ProveedoresWs;
use common\models\OrdenesCompraWs;

class FileAgendaInput extends Model
{
    public $archivo;

    public function rules()
    {
        return [
            [['archivo', ], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['archivo'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx, xls'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'archivo' => 'Nombre Archivo',
        ];
    }

    public function upload($idagendaprespuesto)
    {
        if ($this->validate()) {
            $file = $this->archivo;

            $tempPath = Yii::getAlias('@app/temp/');
            $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;
            $file->saveAs($tempFileName);

            $destinationPath = Yii::getAlias('@app/destination/');
            $destinationFileName = $destinationPath . $file->baseName . '.' . $file->extension;
            copy($tempFileName, $destinationFileName);

            $numRegistrosBorrados = Agendapresupuestodetalle::deleteAll(['idAgendaPresupuesto' => $idagendaprespuesto]);

            //var_dump($destinationFileName); die("hola");
            FileAgendaInput::extraer_data_archivo ($destinationFileName, $idagendaprespuesto);

            $respuesta = FileAgendaInput::actualizarPresupuestoDetalle ($idagendaprespuesto);

            $respuesta = FileAgendaInput::actualizarPresupuestoSemana ($idagendaprespuesto);
            $respuesta = FileAgendaInput::actualizarPresupuestoSubcategoria ($idagendaprespuesto);
            $respuesta = FileAgendaInput::actualizarPresupuestoCategoria ($idagendaprespuesto);
            
            unlink($tempFileName);
            return true;
        } else {
            var_dump($this->getErrors()); die("hola");
            return false;
        }
    }

    public static function extraer_data_archivo ($archivoExcel, $id){

        ini_set('memory_limit', '2048M'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)

        ini_set('max_execution_time', '1500'); //300 seconds = 5 minutes

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja activa
        //$sheet = $spreadsheet->getActiveSheet();

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Consolidado');
 
        // Obtener el número total de filas en la hoja activa
        $totalFilas = $sheet->getHighestRow();

        $grabar = 0;

        // Iterar por cada fila
        for ($fila = 1; $fila <= $totalFilas; $fila++) {
        

            if ($fila <= 2){
                continue;
            }

            $valor_cantidad = $sheet->getCell('J' . $fila)->getValue();
            if (!$valor_cantidad || $valor_cantidad = ""){
                continue;
            }

            $valor_fecha = $sheet->getCell('L' . $fila)->getValue();
            if (!$valor_fecha || $valor_fecha = ""){
                continue;
            }

            $codigo = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda){
                $codigo = $valor_celda;
            }

            $subcategoria = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();
            if ($valor_celda){
                $subcategoria = trim($valor_celda);
            }

            if ($subcategoria == null){
                break;
            }

            $consumidor = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda){
                $consumidor = $valor_celda;
            }

            $universo = null;
            $valor_celda = $sheet->getCell('D' . $fila)->getValue();
            if ($valor_celda){
                $universo = $valor_celda;
            }

            $producto = null;
            $valor_celda = $sheet->getCell('E' . $fila)->getValue();
            if ($valor_celda){
                $producto = $valor_celda;
            }

            $tendencia = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda){
                $tendencia = $valor_celda;
            }

            $talla = null;
            $valor_celda = $sheet->getCell('H' . $fila)->getValue();
            if ($valor_celda){
                $talla = $valor_celda;
            }

            $valor_celda = $sheet->getCell('J' . $fila)->getValue();

            if ($valor_celda){
                $primercaracter = substr($valor_celda, 0, 1);
                if ($primercaracter == "="){
                    $valor_celda = $sheet->getCell('J' . $fila)->getCalculatedValue();
                }
            }
            
            $cantidad = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);

            if ($cantidad == null){
                $cantidad = 0;
            }

            $fechaFormateada = null;

            $valor = $sheet->getCell('L' . $fila)->getValue();
            $valor_limpio = preg_replace('/[\x00-\x1F\x7F-\xA0\xAD]/u', '', $valor);

            $valor_celda = $valor_limpio;

            if ($valor_celda){
                if ($valor_celda != '-'){
                    try {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor_celda);
                        if ($date !== false) {
                            $fechaFormateada = $date->format('Y-m-d');
                        }
                    } catch (Exception $e){
                        $fechaFormateada = null;
                    }
                }
            }

            $tipo = null;
            $valor_celda = $sheet->getCell('N' . $fila)->getValue();
            if ($valor_celda){
                $tipo = $valor_celda;
            }

            $modelo = null;
            $valor_celda = $sheet->getCell('P' . $fila)->getValue();
            if ($valor_celda){
                $modelo = $valor_celda;
            }

            $crossDocking = null;
            $valor_celda = $sheet->getCell('Q' . $fila)->getValue();
            if ($valor_celda){
                $crossDocking = $valor_celda;
            }

            $modeldetalle = new Agendapresupuestodetalle();
            $modeldetalle->fila = $fila;
            $modeldetalle->idAgendaPresupuesto = $id;
            $modeldetalle->subcategoria = $subcategoria;
            $modeldetalle->codigo = $codigo;
            $modeldetalle->fechaLlegada = $fechaFormateada;
            $modeldetalle->consumidor = $consumidor;
            $modeldetalle->universo = $universo;
            $modeldetalle->producto = $producto;
            $modeldetalle->tendencia = $tendencia;
            $modeldetalle->talla = $talla;
            $modeldetalle->tipo = $tipo;
            $modeldetalle->modelo = $modelo;
            $modeldetalle->cantidad = $cantidad;
            $modeldetalle->crossDocking = $crossDocking;

            if ($subcategoria){
                $modeldetalle->subcategoria_id = $modeldetalle->nombreSubcategoria->id;

                $modeldetalle->categoria_id = $modeldetalle->nombreSubcategoria->categoria->id;
            }

            if ($crossDocking){
                $modeldetalle->crossdocking_id = $modeldetalle->nombreCrossdocking->id;
            }

            if (($modeldetalle->subcategoria_id == null) || ($modeldetalle->categoria_id == null) || 
                ($modeldetalle->crossDocking == null) || ($modeldetalle->fechaLlegada == null)){
                $modeldetalle->inconsistencia = 1;
            }

            //$modeldetalle->categoria = $modeldetalle->subcategoria->categoria->nombre;
            
            $ok = $modeldetalle->save();

            if (!$ok){
                var_dump($modeldetalle->getErrors()); die("hola");
            }

        }

    }

    public static function actualizarPresupuestoDetalle ($idagendapresupuesto){

        $sql = "
        UPDATE apd 
        SET apd.categoria = cat.nombre, 
        numeroSemanaAnio = DATEPART(wk, apd.fechaLLegada),
        numeroDiaSemana = DATEPART(dw, apd.fechaLLegada),
        nombreDiaSemana = (CASE DATEPART(dw, apd.fechaLLegada) 
                            WHEN 1 THEN 'Domingo' 
                            WHEN 2 THEN 'Lunes'
                            WHEN 3 THEN 'Martes'
                            WHEN 4 THEN 'Miércoles'
                            WHEN 5 THEN 'Jueves'
                            WHEN 6 THEN 'Viernes'
                            WHEN 7 THEN 'Sábado' 
                            ELSE '' 
                        END)
        FROM agendapresupuestodetalle apd 
        INNER JOIN subcategoria sub ON apd.subcategoria_id = sub.id
        INNER JOIN categoria cat ON sub.idCategoria = cat.id
        WHERE apd.idAgendaPresupuesto = :idagendapresupuesto";

        // Ejecutar la sentencia SQL utilizando createCommand
        $resultado = Yii::$app->db->createCommand($sql, [':idagendapresupuesto' => $idagendapresupuesto
                                            ])->execute();

        return $resultado;
    }

    public static function actualizarPresupuestoSemana ($idagendapresupuesto){

        $numRegistrosBorrados = Agendapresupuestosemana::deleteAll(['idAgendaPresupuesto' => $idagendapresupuesto]);

        $sql = "
        INSERT INTO agendapresupuestosemana (idAgendaPresupuesto, periodoAnio, periodoMes, 
        crossdocking_id, crossDocking, categoria_id, categoria, numeroSemanaAnio, cantidad)
        SELECT apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, apd.crossDocking, 
        apd.categoria_id, apd.categoria, apd.numeroSemanaAnio, SUM(apd.cantidad) AS cantidad
        FROM agendapresupuestodetalle apd 
        INNER JOIN agendapresupuesto ap ON apd.idAgendaPresupuesto = ap.id
        WHERE apd.idAgendaPresupuesto = :idagendapresupuesto AND apd.fechaLlegada IS NOT NULL
        GROUP BY apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, 
        apd.crossDocking, apd.categoria_id, apd.categoria, apd.numeroSemanaAnio";

        // Ejecutar la sentencia SQL utilizando createCommand
        $resultado = Yii::$app->db->createCommand($sql, [':idagendapresupuesto' => $idagendapresupuesto
                                            ])->execute();

        return $resultado;
    }

    public static function actualizarPresupuestoCategoria ($idagendapresupuesto){

        $numRegistrosBorrados = Agendapresupuestocategoria::deleteAll(['idAgendaPresupuesto' => $idagendapresupuesto]);

        $sql = "
        INSERT INTO agendapresupuestocategoria (idAgendaPresupuesto, periodoAnio, periodoMes, 
        crossdocking_id, crossDocking, categoria_id, categoria, fechaLlegada, cantidad)
        SELECT apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, apd.crossDocking,
        apd.categoria_id, apd.categoria, apd.fechaLlegada, SUM(apd.cantidad) AS cantidad
        FROM agendapresupuestodetalle apd 
        INNER JOIN agendapresupuesto ap ON apd.idAgendaPresupuesto = ap.id
        WHERE apd.idAgendaPresupuesto = :idagendapresupuesto AND apd.fechaLlegada IS NOT NULL
        GROUP BY apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, apd.crossDocking, 
        apd.categoria_id, apd.categoria, apd.fechaLlegada";

        // Ejecutar la sentencia SQL utilizando createCommand
        $resultado = Yii::$app->db->createCommand($sql, [':idagendapresupuesto' => $idagendapresupuesto
                                            ])->execute();

        return $resultado;
    }

    public static function actualizarPresupuestoSubcategoria ($idagendapresupuesto){

        $numRegistrosBorrados = Agendapresupuestosubcategoria::deleteAll(['idAgendaPresupuesto' => $idagendapresupuesto]);

        $sql = "
        INSERT INTO agendapresupuestosubcategoria (idAgendaPresupuesto, periodoAnio, periodoMes, 
        crossdocking_id, crossDocking, categoria_id, categoria, subcategoria_id, subcategoria, 
        fechaLlegada, cantidad)
        SELECT apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, 
        apd.crossDocking, apd.categoria_id, apd.categoria, apd.subcategoria_id,
        apd.subcategoria, apd.fechaLlegada, SUM(apd.cantidad) AS cantidad
        FROM agendapresupuestodetalle apd 
        INNER JOIN agendapresupuesto ap ON apd.idAgendaPresupuesto = ap.id
        WHERE apd.idAgendaPresupuesto = :idagendapresupuesto AND apd.fechaLlegada IS NOT NULL
        GROUP BY apd.idAgendaPresupuesto, ap.periodoAnio, ap.periodoMes, apd.crossdocking_id, apd.crossDocking, 
        apd.categoria_id, apd.categoria, apd.subcategoria_id, apd.subcategoria, apd.fechaLlegada";

        // Ejecutar la sentencia SQL utilizando createCommand
        $resultado = Yii::$app->db->createCommand($sql, [':idagendapresupuesto' => $idagendapresupuesto
                                            ])->execute();

        return $resultado;
    }

    public function importaAgendamiento($idagendaprespuesto)
    {
        if ($this->validate()) {
            $file = $this->archivo;

            $tempPath = Yii::getAlias('@app/temp/');
            $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;
            $file->saveAs($tempFileName);

            $destinationPath = Yii::getAlias('@app/destination/');
            $destinationFileName = $destinationPath . $file->baseName . '.' . $file->extension;
            copy($tempFileName, $destinationFileName);

            FileAgendaInput::cargar_data_agendamiento ($destinationFileName, $idagendaprespuesto);
            
            unlink($tempFileName);
            return true;
        } else {
            var_dump($this->getErrors()); die("hola");
            return false;
        }
    }

    public static function cargar_data_agendamiento ($archivoExcel, $id){

        ini_set('memory_limit', '2048M'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)

        ini_set('max_execution_time', '1200'); //300 seconds = 5 minutes

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja activa
        //$sheet = $spreadsheet->getActiveSheet();

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Agenda');
 
        // Obtener el número total de filas en la hoja activa
        $totalFilas = $sheet->getHighestRow();

        $grabar = 0;

        // Iterar por cada fila
        for ($fila = 1; $fila <= $totalFilas; $fila++) {
        
            if ($fila <= 2){
                continue;
            }

            $radicado = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda){
                $radicado = $valor_celda;
            }

            $model = Agendaentregamercancia::find()->where(['radicadohs' => $radicado])->one();
            if ($model){
                continue;
            }

            $fechaCita = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();
            if ($valor_celda){
                if ($valor_celda != '-'){
                    try {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor_celda);
                        if ($date !== false) {
                            $fechaCita = $date->format('Y-m-d');
                        }
                    } catch (Exception $e){
                        $fechaCita = null;
                    }
                }
            }

            $horaCita = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda){
                $horaCita = date("H:i", strtotime($valor_celda));
            }else{
                $horaCita = '00:00';
            }

            $contacto = null;
            $valor_celda = $sheet->getCell('D' . $fila)->getValue();
            if ($valor_celda){
                $contacto = $valor_celda;
            }

            $fechaContacto = null;
            $valor_celda = $sheet->getCell('E' . $fila)->getValue();
            if ($valor_celda){
                if ($valor_celda != '-'){
                    try {
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor_celda);
                        if ($date !== false) {
                            $fechaContacto = $date->format('Y-m-d');
                        }
                    } catch (Exception $e){
                        $fechaContacto = null;
                    }
                }
            }

            $horaContacto = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda){
                $horaContacto = date("H:i", strtotime($valor_celda));
            }else{
                $horaContacto = '00:00';
            }

            $idProveedor = null;
            $valor_celda = $sheet->getCell('H' . $fila)->getValue();
            if ($valor_celda){
                $nit = $valor_celda;
                $model = Proveedor::findOne(['nit' => $nit]);
                if ($model == null){
                    $respuesta = ProveedoresWs::sincronizarERP($nit);
                    if ($respuesta){
                        $model = Proveedor::findOne(['nit' => $nit]);
                    }
                }
                if ($model == null){
                    die("Error Proveedor No Existe: " . $valor_celda);
                }
                $idProveedor = $model->id;
            }

            $idCategoria = null;
            $valor_celda = $sheet->getCell('K' . $fila)->getValue();
            if ($valor_celda){
                $model = Categoria::findOne(['nombre' => $valor_celda]);
                $idCategoria = $model->id;
            }

            $idOrdenCompra = null;
            $unidadesordencompra = 0;
            $codigoCO = '002';
            $codigoTipoDocumento = $sheet->getCell('M' . $fila)->getValue();
            $numeroOrdenCompra = $sheet->getCell('O' . $fila)->getValue();
            
            if ($valor_celda){
                $model = Centrooperacion::findOne(['codigo' => $codigoCO]);
                $idCO = $model->id;

                $model = Tipodocumento::findOne(['codigo' => $codigoTipoDocumento]);
                $idTipoDocumento = $model->id;

                $model = Ordendecompra::find()->where(['idCO'=>$idCO, 'idTipoDocumento' => $idTipoDocumento, 'consecutivo' => $numeroOrdenCompra])->one();
                if ($model == null){
                    $respuesta = OrdenesCompraWs::sincronizarERP($codigoCO, $codigoTipoDocumento, $numeroOrdenCompra);
                    if ($respuesta){
                        $model = Ordendecompra::find()->where(['idCO'=>$idCO, 'idTipoDocumento' => $idTipoDocumento, 'consecutivo' => $numeroOrdenCompra])->one();
                    }
                }
                
                if ($model == null){
                    //continue;
                    die("Error Orden de Compra: " . $codigoTipoDocumento . '-' . $numeroOrdenCompra);
                }
                $idOrdenCompra = $model->id;
                $unidadesordencompra = $model->totalCantidadPendiente;
            }

            $idTransportadora = null;
            $valor_celda = $sheet->getCell('P' . $fila)->getValue();
            if ($valor_celda){
                $model = Transportadora::findOne(['nombre' => $valor_celda]);
                if ($model == null){
                    $model = new Transportadora();
                    $model->nombre = $valor_celda;
                    $model->idEstado = 1;
                    $model->save();
                }
                $idTransportadora = $model->id;
            }

            $numeroGuia = null;
            $valor_celda = $sheet->getCell('Q' . $fila)->getValue();
            if ($valor_celda){
                $numeroGuia = $valor_celda;
            }

            $unidades = 0;
            $valor_celda = $sheet->getCell('R' . $fila)->getValue();
            if ($valor_celda){
                $unidades = ProcedimientosGenerales::convertirValorTextoNumero ($valor_celda);
                if ($unidades == null){
                    $unidades = 0;
                }
            }

            $observacion = null;
            $valor_celda = $sheet->getCell('V' . $fila)->getValue();
            if ($valor_celda){
                $tipo = $valor_celda;
            }

            $idEstadoAgenda = null;
            $valor_celda = $sheet->getCell('U' . $fila)->getValue();
            if ($valor_celda){
                $model = Estadoagenda::findOne(['nombre' => $valor_celda]);
                if ($model == null){
                    die($valor_celda);
                }
                $idEstadoAgenda = $model->id;
            }

            $modeldetalle = new Agendaentregamercancia();
            $modeldetalle->idBodega = 2;
            $modeldetalle->idAgenda = $id;
            $modeldetalle->idOrdenCompra = $idOrdenCompra;
            $modeldetalle->fechaCita = $fechaCita . " " . $horaCita;
            $modeldetalle->idCategoria = $idCategoria;
            $modeldetalle->unidades = $unidades;
            $modeldetalle->unidadesOrdenCompra = $unidadesordencompra;
            $modeldetalle->idTransportadora = $idTransportadora;
            $modeldetalle->contacto = $contacto;
            $modeldetalle->fechaContacto = $fechaContacto . " " . $horaContacto;
            $modeldetalle->observacion = $observacion;
            $modeldetalle->idEstado = $idEstadoAgenda;
            $modeldetalle->radicadohs = $radicado;

            $ok = $modeldetalle->save();

            if ($ok){
                Agendaentregamercancia::actualizarUnidadesAgendamiento (
                    $modeldetalle->idAgenda,
                    $modeldetalle->idOrdenCompra, 
                    $fechaCita
                );
            }

            if (!$ok){
                var_dump($modeldetalle->getErrors()); die("hola");
            }

        }

    }


}

?>
