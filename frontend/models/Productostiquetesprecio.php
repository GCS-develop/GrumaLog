<?php

namespace frontend\models;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;
use yii\db\IntegrityConstraintViolationException;
use yii\db\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use frontend\models\Bodegas;

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
 * @property int $oferta
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
            [
                [
                    'descBodega',
                    'item',
                    'descItem',
                    'detalleExt1',
                    'detalleExt2',
                    'existencia',
                    'proveedor',
                    'referencia',
                    'categoria',
                    'subcategoria',
                    'precio'
                ],
                'required'
            ],
            [['item', 'codigoBarra', 'existencia', 'precio', 'created_by', 'updated_by','oferta'], 'integer'],
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
            'descItem' => 'Descipcion',
            'detalleExt1' => 'Color',
            'detalleExt2' => 'Talla',
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

    public function getBodeganombre()
    {
        return $this->hasOne(Impresoraspaxarbodega::class, ['descBodega' => 'descBodega']);

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

        // Define la ruta temporal
        $tempPath = Yii::getAlias('@app/temp/');

        // Crea el directorio si no existe
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true); // Crear directorio con permisos adecuados
        }

        $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;

        // Guardar el archivo en la ruta temporal
        if (!$file->saveAs($tempFileName)) {
            Yii::error("Error al guardar el archivo temporal: $tempFileName", __METHOD__);
            return false; // Error al guardar el archivo
        }

        // Procesar el archivo
        $respuesta = self::extraer_data_archivo($tempFileName);

        // Verificar si el archivo existe antes de eliminarlo
        if (file_exists($tempFileName)) {
            unlink($tempFileName); // Eliminar archivo temporal
        } else {
            Yii::warning("El archivo temporal $tempFileName no existe o ya fue eliminado.", __METHOD__);
        }

        return $respuesta;
    }


    public static function extraer_data_archivo($archivoExcel)
    {
        Yii::trace("Inicio de la extracion de datos con 51200M y 36000 /tiempo", __METHOD__);

        // ini_set('memory_limit', '51200M'); // Aumentar el límite de memoria
        // ini_set('max_execution_time', '36000'); // Tiempo de ejecución
        ini_set('memory_limit', '91200M'); // Aumentar el límite de memoria
        ini_set('max_execution_time', '56000'); // Tiempo de ejecución

        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($archivoExcel);

        // Obtener la hoja específica por su nombre
        $sheet = $spreadsheet->getSheetByName('Data');

        // Obtener el número total de filas
        $totalFilas = $sheet->getHighestRow();
        $contadorInsert = 0; //contar cuantos inserto
        $grabar = [
            'estado' => false,
            'rows' => $totalFilas, // cantidad de insets esperados
            'insert' => $contadorInsert,
            'errores' => '',
        ];
        $data = []; // Arreglo para almacenar los registros que se insertarán

        // Iterar por cada fila
        for ($fila = 2; $fila <= $totalFilas; $fila++) {
            // Obtener y asignar los valores de cada celda
            $descBodega = $sheet->getCell('A' . $fila)->getValue();
            if (!$descBodega) {
                Yii::error("Bodega en blanco en la fila $fila. Detalles: ");
                continue;
            } // Si no hay descripción de bodega, saltar la fila

            $codigoBarra = $sheet->getCell('B' . $fila)->getValue();
            $item = $sheet->getCell('C' . $fila)->getValue();

            if (!$item) {
                Yii::error("item en blanco en la fila $fila. Detalles: ");
                continue; // Si no hay item, saltar la fila
            }

            // Buscar si ya existe el producto en la base de datos
            $modeldocumento = Productostiquetesprecio::find()
                ->where(['descBodega' => $descBodega, 'item' => $item])
                ->andWhere(['or', ['codigoBarra' => $codigoBarra], ['codigoBarra' => null]])
                ->one();

            // Si el producto ya existe, saltar la iteración
            if ($modeldocumento !== null) {
                continue; // Salta esta iteración y pasa a la siguiente fila
            }

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

            // Preparar los datos para la inserción
            $data[] = [
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
            ];

            if ($data) {
                // Realizar inserción masiva de los datos
                try {
                    // $modeldocumento = Productostiquetesprecio::find()->where(['descBodega' => $descBodega, 'codigoBarra' => $codigoBarra, 'item' => $item])->one();

                    if (empty($descBodega) || empty($item)) {
                        throw new \InvalidArgumentException('descBodega e item son obligatorios.');
                    }

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

                            // Concatenar errores con el número de fila
                            $grabar['errores'] .= "Fila: $fila --> $errores; ";  // Agregar la fila a los errores

                            // Lanzar una excepción con los detalles del error
                            throw new Exception("Error al guardar el producto en la fila $fila: $errores");
                        }

                        $contadorInsert++;
                        $grabar['estado'] = true;
                    }
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Violación de la restricción de integridad') !== false) {
                        // Aquí se maneja el error específico de violación de integridad
                        Yii::error('Violación de integridad en la base de datos: ' . $e->getMessage());
                    } else {
                        Yii::error('Error desconocido de base de datos: ' . $e->getMessage());
                    }
                }
            }
        }
        $grabar['insert'] = $contadorInsert;
        return $grabar;
    }

}

