<?php

namespace frontend\models;

use Yii;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This is the model class for table "devolucionimportaciondetalle".
 *
 * @property int $id
 * @property int $idInterfase
 * @property string $co
 * @property string $fecha
 * @property string $bodegaSalida
 * @property string $item
 * @property string $talla
 * @property string $color
 * @property string $numeroDocumento
 * @property string|null $notasDocumento
 * @property string $bodegaEntrada
 * @property string $codigoBodegaEntrada
 * @property string $codigoBodegaSalida
 * @property string $referencia
 * @property string $itemResumen
 * @property string $unidadMedida
 * @property float $cantidad
 * @property string $categoria
 * @property string $proveedor
 * @property string $codigoBarras
 *
 * @property Devolucionimportacion $idInterfase0
 */
class Devolucionimportaciondetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'devolucionimportaciondetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                [
                    'idInterfase',
                    'co',
                    'fecha',
                    'bodegaSalida',
                    'item',
                    'talla',
                    'color',
                    'numeroDocumento',
                    'bodegaEntrada',
                    'codigoBodegaEntrada',
                    'codigoBodegaSalida',
                    'referencia',
                    'itemResumen',
                    'unidadMedida',
                    'cantidad',
                    'categoria',
                    'proveedor',
                    'codigoBarras'
                ],
                'required'
            ],
            [['idInterfase'], 'integer'],
            [['fecha', 'item'], 'safe'],
            [['cantidad'], 'number'],
            [['co', 'codigoBodegaEntrada', 'codigoBodegaSalida'], 'string', 'max' => 5],
            [['bodegaSalida', 'bodegaEntrada', 'proveedor'], 'string', 'max' => 150],
            [['talla', 'numeroDocumento'], 'string', 'max' => 20],
            [['color', 'referencia', 'codigoBarras'], 'string', 'max' => 50],
            [['notasDocumento'], 'string', 'max' => 500],
            [['itemResumen'], 'string', 'max' => 300],
            [['unidadMedida'], 'string', 'max' => 10],
            [['categoria'], 'string', 'max' => 100],
            [['idInterfase'], 'exist', 'skipOnError' => true, 'targetClass' => Devolucionimportacion::class, 'targetAttribute' => ['idInterfase' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idInterfase' => 'Id Interfase',
            'co' => 'C.O.',
            'fecha' => 'Fecha',
            'bodegaSalida' => 'Bodega Salida',
            'item' => 'Item',
            'talla' => 'Talla',
            'color' => 'Color',
            'numeroDocumento' => 'Nro Documento',
            'notasDocumento' => 'Notas dOCTO',
            'bodegaEntrada' => 'Bodega Entrada',
            'codigoBodegaEntrada' => 'Cod. Bodega Entrada',
            'codigoBodegaSalida' => 'Cod. Bodega Salida',
            'referencia' => 'Referencia',
            'itemResumen' => 'Item Resumen',
            'unidadMedida' => 'UM',
            'cantidad' => 'Cant. Saldo',
            'categoria' => 'Categoría',
            'proveedor' => 'Proveedor',
            'codigoBarras' => 'Código Barras',
        ];
    }

    /**
     * Gets query for [[IdInterfase0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdInterfase0()
    {
        return $this->hasOne(Devolucionimportacion::class, ['id' => 'idInterfase']);
    }

    public static function upload($archivo)
    {
        $file = $archivo;

        $tempPath = Yii::getAlias('@app/temp/');
        $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;
        $file->saveAs($tempFileName);

        $model = new Devolucionimportacion();
        $model->numeroRegistros = 0;
        $model->totalCantidad = 0;
        if (!$model->save()) {
            var_dump($model->getErrors());
            die("hola");
        }
        $id = $model->id;

        $respuesta = Devolucionimportaciondetalle::extraer_data_archivo($tempFileName, $model->id);

        $model = Devolucionimportacion::findOne($id);
        $model->numeroRegistros = Devolucionimportaciondetalle::find()->where(['idInterfase' => $id])->count();
        $model->totalCantidad = Devolucionimportaciondetalle::find()->where(['idInterfase' => $id])->sum('cantidad');
        $model->save();

        unlink($tempFileName);
        return $respuesta;
    }

    public static function extraer_data_archivo($archivoExcel, $id)
    {

        ini_set('memory_limit', '2048M'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)

        ini_set('max_execution_time', '1500'); //300 seconds = 5 minutes

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja activa
        //$sheet = $spreadsheet->getActiveSheet();

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Data');

        // Obtener el número total de filas en la hoja activa
        $totalFilas = $sheet->getHighestRow();

        $grabar = false;

        // Iterar por cada fila
        for ($fila = 2; $fila <= $totalFilas; $fila++) {


            $grabar = true;

            $fecha = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();

            if (!$valor_celda) {
                continue;
            }
            $fecha = $valor_celda;

            // Si la celda tiene un número (posible serial de Excel), convertirla
            if (is_numeric($valor_celda)) {
                $fecha = date('Y-m-d', strtotime('1899-12-30 + ' . $valor_celda . ' days'));
            } else {
                // Si ya viene en formato de fecha, mantenerla tal cual
                $fecha = date('Y-m-d', strtotime($valor_celda));
            }

            $codigoBarras = null;
            $valor_celda = $sheet->getCell('R' . $fila)->getValue();
            if (!$valor_celda) {
                continue;
            }
            $codigoBarras = $valor_celda;

            $model = new Devolucionimportaciondetalle();
            $model->idInterfase = $id;
            $model->fecha = $fecha;
            $model->codigoBarras = $codigoBarras;

            $model->co = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda) {
                $model->co = $valor_celda;
            }

            $model->bodegaSalida = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda) {
                $model->bodegaSalida = $valor_celda;
            }

            $model->item = null;
            $valor_celda = $sheet->getCell('D' . $fila)->getValue();
            if ($valor_celda) {
                $model->item = $valor_celda;
            }


            $model->talla = null;
            $valor_celda = $sheet->getCell('E' . $fila)->getValue();
            if ($valor_celda) {
                $model->talla = $valor_celda;
            }

            $model->color = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda) {
                $model->color = $valor_celda;
            }

            $model->numeroDocumento = null;
            $valor_celda = $sheet->getCell('G' . $fila)->getValue();
            if ($valor_celda) {
                $model->numeroDocumento = $valor_celda;
            }

            $model->notasDocumento = $sheet->getCell('H' . $fila)->getValue();

            $model->bodegaEntrada = null;
            $valor_celda = $sheet->getCell('I' . $fila)->getValue();
            if ($valor_celda) {
                $model->bodegaEntrada = $valor_celda;
            }

            $model->codigoBodegaEntrada = null;
            $valor_celda = $sheet->getCell('J' . $fila)->getValue();
            if ($valor_celda) {
                $model->codigoBodegaEntrada = $valor_celda;
            }

            $model->codigoBodegaSalida = null;
            $valor_celda = $sheet->getCell('K' . $fila)->getValue();
            if ($valor_celda) {
                $model->codigoBodegaSalida = $valor_celda;
            }

            $model->referencia = null;
            $valor_celda = $sheet->getCell('L' . $fila)->getValue();
            if ($valor_celda) {
                $model->referencia = $valor_celda;
            }

            $model->itemResumen = null;
            $valor_celda = $sheet->getCell('M' . $fila)->getValue();
            if ($valor_celda) {
                $model->itemResumen = $valor_celda;
            }

            $model->unidadMedida = null;
            $valor_celda = $sheet->getCell('N' . $fila)->getValue();
            if ($valor_celda) {
                $model->unidadMedida = $valor_celda;
            }

            $model->cantidad = null;
            $valor_celda = $sheet->getCell('O' . $fila)->getValue();
            $model->cantidad = floatval($valor_celda);
            if ($valor_celda) {
            } else {

                $model->cantidad = 0;

            }

            $model->categoria = null;
            $valor_celda = $sheet->getCell('P' . $fila)->getValue();
            if ($valor_celda) {
                $model->categoria = $valor_celda;
            }

            $model->proveedor = null;
            $valor_celda = $sheet->getCell('Q' . $fila)->getValue();
            if ($valor_celda) {
                $model->proveedor = $valor_celda;
            }

            // var_dump($model);
            // die('g');
            if (!$model->save()) {
                var_dump($model->errors);
                die();
            }


            $model->save();

            $modeldocumento = Devoluciondocumento::find()->where(['codigoBodegaSalida' => $model->codigoBodegaSalida, 'numeroDocumento' => $model->numeroDocumento])->one();
            if ($modeldocumento == null) {
                $modeldocumento = new Devoluciondocumento();
                $modeldocumento->codigoBodegaSalida = $model->codigoBodegaSalida;
                $modeldocumento->numeroDocumento = $model->numeroDocumento;
                $modeldocumento->fecha = $model->fecha;
                $modeldocumento->notasDocumento = $model->notasDocumento;
                $modeldocumento->idInterfase = $model->idInterfase;
                $modeldocumento->save();
            }

            $detalle = Devoluciondocumentodetalle::find()->where(['idDocumento' => $modeldocumento->id, 'codigoBarras' => $model->codigoBarras])->one();
            if ($detalle == null) {
                $detalle = new Devoluciondocumentodetalle();
                $detalle->idDocumento = $modeldocumento->id;
                $detalle->codigoBarras = $model->codigoBarras;
                $detalle->cantidadDevolucion = $model->cantidad;
                $detalle->cantidadRegistrada = 0;
                $detalle->item = $model->item;
                $detalle->talla = $model->talla;
                $detalle->color = $model->color;
                $detalle->referencia = $model->referencia;
                $detalle->itemResumen = $model->itemResumen;
                $detalle->unidadMedida = $model->unidadMedida;
                if (!$detalle->save()) {
                    var_dump($detalle->getErrors());
                    die("hola");
                }


            } else {
                $detalle->cantidadDevolucion = $detalle->cantidadDevolucion + $model->cantidad;
                $detalle->save();
            }
        }

        return $grabar;

    }
}
