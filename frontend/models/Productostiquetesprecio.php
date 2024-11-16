<?php

namespace frontend\models;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\IntegrityConstraintViolationException;
use yii\db\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "productostiquetesprecio".
 *
 * @property int $id
 * @property string $descBodega
 * @property string|null $codigoBarra
 * @property int $item
 * @property string $descItem
 * @property string $detalleExt1
 * @property string $detalleExt2
 * @property int $existencia
 * @property string $proveedor
 * @property string $marca
 * @property string $referencia
 * @property string $categoria
 * @property string $subcategoria
 * @property int $precio
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Productostiquetesprecio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'productostiquetesprecio';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descBodega', 'item', 'descItem', 'detalleExt1', 'detalleExt2', 'existencia', 'proveedor', 'referencia', 'categoria', 'subcategoria', 'precio'], 'required'],
            [['item', 'codigoBarra', 'existencia', 'precio', 'created_by', 'updated_by'], 'integer'],
            [['marca', 'created_at', 'updated_at'], 'safe'],
            [['descBodega', 'descItem', 'detalleExt1', 'detalleExt2', 'proveedor', 'marca', 'referencia', 'categoria', 'subcategoria'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descBodega' => 'Desc Bodega',
            'codigoBarra' => 'Codigo Barras',
            'item' => 'Item',
            'descItem' => 'Desc Item',
            'detalleExt1' => 'Detalle Ext1',
            'detalleExt2' => 'Detalle Ext2',
            'existencia' => 'Existencia',
            'proveedor' => 'Proveedor',
            'marca' => 'Marca',
            'referencia' => 'Referencia',
            'categoria' => 'Categoria',
            'subcategoria' => 'Subcategoria',
            'precio' => 'Precio',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public static function getListaDataCategoria()
    {
        $data = Productostiquetesprecio::find()
            ->select(['categoria']) // Solo necesitamos la categoría
            ->distinct()
            ->orderBy('categoria')
            ->asArray()
            ->all();

        // Mapear los datos a un array adecuado para un dropdown
        $listadata = ArrayHelper::map($data, 'categoria', 'categoria');
        return $listadata;
    }
    public static function getListaDataDescBodega()
    {
        $data = Productostiquetesprecio::find()
            ->select(['descBodega']) // Solo necesitamos la categoría
            ->distinct()
            ->orderBy('descBodega')
            ->asArray()
            ->all();

        // Mapear los datos a un array adecuado para un dropdown
        $listadata = ArrayHelper::map($data, 'descBodega', 'descBodega');
        return $listadata;
    }
    public static function upload($archivo)
    {
        $file = $archivo;

        $tempPath = Yii::getAlias('@app/temp/');
        $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;

        if (!$file->saveAs($tempFileName)) {
            return false; // Error al guardar el archivo
        }

        $respuesta = self::extraer_data_archivo($tempFileName);

        // Elimina el archivo temporal después de procesarlo
        unlink($tempFileName);

        return $respuesta;
    }

    public static function extraer_data_archivo($archivoExcel)
    {
        ini_set('memory_limit', '2048M'); // Aumentar el límite de memoria
        ini_set('max_execution_time', '3500'); // Tiempo de ejecución

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Data');

        // Obtener el número total de filas
        $totalFilas = $sheet->getHighestRow();

        $grabar = false;
        $data = []; // Arreglo para almacenar los registros que se insertarán

        // Iterar por cada fila
        for ($fila = 2; $fila <= $totalFilas; $fila++) {
            // Obtener y asignar los valores de cada celda
            $descBodega = $sheet->getCell('A' . $fila)->getValue();
            if (!$descBodega) {
                continue;
            } // Si no hay descripción de bodega, saltar la fila

            $codigoBarra = $sheet->getCell('B' . $fila)->getValue();
            $item = $sheet->getCell('C' . $fila)->getValue();
            $descItem = $sheet->getCell('D' . $fila)->getValue();
            $detalleExt1 = $sheet->getCell('E' . $fila)->getValue();
            $detalleExt2 = $sheet->getCell('F' . $fila)->getValue();
            $existencia = (int) $sheet->getCell('G' . $fila)->getValue(); // Asegurarse de que sea un número entero
            $proveedor = $sheet->getCell('H' . $fila)->getValue();
            $marca = $sheet->getCell('I' . $fila)->getValue();
            $referencia = $sheet->getCell('J' . $fila)->getValue();
            $categoria = $sheet->getCell('K' . $fila)->getValue();
            $subcategoria = $sheet->getCell('L' . $fila)->getValue();
            $precio = (int) $sheet->getCell('M' . $fila)->getValue(); // Asegurarse de que sea un número entero

            if (!$item) {
                continue; // Si no hay item, saltar la fila
            }
            // Preparar los datos para la inserción
            $data[] = [
                // 'id' => $id,
                'descBodega' => $descBodega,
                'codigoBarra' => $codigoBarra,
                'item' => $item,
                'descItem' => $descItem,
                'detalleExt1' => $detalleExt1,
                'detalleExt2' => $detalleExt2,
                'existencia' => $existencia,
                'proveedor' => $proveedor,
                'marca' => $marca,
                'referencia' => $referencia,
                'categoria' => $categoria,
                'subcategoria' => $subcategoria,
                'precio' => $precio,
                // 'created_at' => date('Y-m-d H:i:s'),
                // 'created_by' => Yii::$app->user->id,
            ];


            if ($data) {
                // Realizar inserción masiva de los datos
                try {
                    // Yii::$app->db->createCommand()->batchInsert(
                    //     self::tableName(),
                    //     ['descBodega', 'codigoBarra', 'item', 'descItem', 'detalleExt1', 'detalleExt2', 'existencia', 'proveedor', 'marca', 'referencia', 'categoria', 'subcategoria', 'precio'],
                    //     $data
                    // )->execute();


                    try {
                        $modeldocumento = Productostiquetesprecio::find()->where(['codigoBarra' => $codigoBarra, 'item' => $item])->one();

                        if ($modeldocumento === null) {
                            $modeldocumento = new Productostiquetesprecio();
                            $modeldocumento->descBodega = $descBodega;
                            $modeldocumento->codigoBarra = $codigoBarra;
                            $modeldocumento->item = $item;
                            $modeldocumento->descItem = $descItem;
                            $modeldocumento->detalleExt1 = $detalleExt1;
                            $modeldocumento->detalleExt2 = $detalleExt2;
                            $modeldocumento->existencia = $existencia;
                            $modeldocumento->proveedor = $proveedor;
                            $modeldocumento->marca = $marca;
                            $modeldocumento->referencia = $referencia;
                            $modeldocumento->categoria = $categoria;
                            $modeldocumento->subcategoria = $subcategoria;
                            $modeldocumento->precio = $precio;

                            if (!$modeldocumento->save()) {
                                // Capturar errores en una cadena
                                $errores = implode(', ', array_map(function ($error) {
                                    return implode(', ', $error);
                                }, $modeldocumento->getErrors()));

                                // Lanzar una excepción con los detalles del error
                                throw new Exception("Error al guardar el producto: $errores");
                            }

                            $grabar = true;
                        }
                    } catch (Exception $e) {
                        // Mostrar un mensaje amigable al usuario
                        Yii::$app->session->setFlash('error', $e->getMessage());
                    }



                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Integrity constraint violation') !== false) {
                        // Aquí se maneja el error específico de violación de integridad
                        Yii::error('Violación de integridad en la base de datos: ' . $e->getMessage());
                    } else {
                        Yii::error('Error desconocido de base de datos: ' . $e->getMessage());
                    }
                }
            }
        }

        return $grabar;
    }

}

