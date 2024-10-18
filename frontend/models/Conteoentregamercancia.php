<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use yii\web\Response;

use frontend\models\search\ConteoentregamercanciaSearch;

/**
 * This is the model class for table "conteoentregamercancia".
 *
 * @property int $id
 * @property int $idProgramacionEntregaMercancia
 * @property int $idItem
 * @property int $unidadesConteo
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Item $item
 * @property Programacionentregamercancia $programacionEntregaMercancia
 */
class Conteoentregamercancia extends \yii\db\ActiveRecord
{
    public $username;
    public $referencia;
    public $descripcion;
    public $talla;
    public $color;
    public $marca;
    public $unidadEmpaque;
    public $categoria;
    public $subcategoria;
    public $tipoDocumento;
    public $consecutivo;
    public $razonSocial;
    public $codigoCentroOperacion;
    public $codigoTipoDocumento;
    public $numeroRegistro;
    public $numeroFila;
    public $fechaEntrega;
    public $equivalencia;
    //
    //
    public $codigoCentroOperacionDocumentoEntrada;
    public $codigoTipoDocumentoEntrada;
    public $consecutivoDocumentoEntrada;
    public $fechaDocumentoEntrada;
    public $codigoCentroOperacionOC;
    public $codigoTipoDoctoOC;
    public $consecutivoOC;
    public $codigointernomovto;
    public $bodega;
    public $nroRegistro10;
    public $tercero;
    public $numeroFactura;
    public $sucursalProveedor;
    public $nitcomprador;
    public $consignacion;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteoentregamercancia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idProgramacionEntregaMercancia', 'idItem', 'unidadesConteo'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idProgramacionEntregaMercancia', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'unidadesConteo', 'unidadesAsignadas', 'codigoBarras', 'item'], 'safe'],
            [['idProgramacionEntregaMercancia'], 'exist', 'skipOnError' => true, 'targetClass' => Programacionentregamercancia::class, 'targetAttribute' => ['idProgramacionEntregaMercancia' => 'id']],
            [['idProgramacionEntregaMercancia', 'idItem'], 'unique', 'targetAttribute' => ['idProgramacionEntregaMercancia', 'idItem'], 'message' => 'La Referencia Ya Esta Asignado a la Orden de Compra.'],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idProgramacionEntregaMercancia' => 'Programación',
            'idItem' => 'Item',
            'unidadesConteo' => 'UND Conteo',
            'unidadesAsignadas' => 'UND Asignadas',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[ProgramacionEntregaMercancia]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProgramacionEntregaMercancia()
    {
        return $this->hasOne(Programacionentregamercancia::class, ['id' => 'idProgramacionEntregaMercancia']);
    }

    public static function grabarItemParaConteo($idordencompra, $idprogramacionentregamercancia, $item)
    {
        $respuesta = false;

        $listaitems = Ordendecompradetalle::listaItemsOrdenCompra($idordencompra, $item);

        foreach ($listaitems as $item) {
            //var_dump($item->codigoBarras); die("aqui");

            $respuesta = true;
            $iditem = $item['idItem'];

            $model = Conteoentregamercancia::findOne([
                'idProgramacionEntregaMercancia' => $idprogramacionentregamercancia,
                'idItem' => $iditem
            ]);

            if ($model == null) {
                $model = new Conteoentregamercancia();
                $model->idProgramacionEntregaMercancia = $idprogramacionentregamercancia;
                $model->idItem = $iditem;
                $model->codigoBarras = $item['codigoBarras'];
                $model->unidadesAsignadas = $item['cantidad'];
                $model->item = $item['item'];
                $model->unidadesConteo = 0;

                if (!$model->save()) {
                    var_dump($model->getErrors());
                    die("hola");
                    $respuesta = false;
                    break;
                }
            }

        }

        return $respuesta;

    }

    public static function generarDataConteoCurvas($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion = null)
    {

        //var_dump($idordencompra . ' - ' .$idagenda . ' - ' . $idprogramacion);die("hola");

        if ($idordencompra) {
            $arrayresultado = self::procesoOrdenCompra($idordencompra, $idcategoria);
        }

        if ($idagenda) {
            $arrayresultado = self::procesoAgenda($idagenda, $iduserconteo);
        }

        if ($idprogramacion) {
            $arrayresultado = self::procesoProgramacion($idprogramacion);
        }

        return $arrayresultado;
    }

    public static function procesoProgramacion($idprogramacion)
    {

        $informaciondataconteo = self::generarQueryProgramacion($idprogramacion);

        $arrayresultado = self::generarArregloCurvas($informaciondataconteo);

        return $arrayresultado;
    }

    public static function procesoAgenda($idagenda, $iduserconteo)
    {

        $informaciondataconteo = self::generarQueryAgenda($idagenda, $iduserconteo);

        $arrayresultado = self::generarArregloCurvas($informaciondataconteo);

        return $arrayresultado;
    }

    public static function procesoOrdenCompra($idordencompra, $idcategoria)
    {

        $informaciondataconteo = self::generarQueryOC($idordencompra, $idcategoria);

        $arrayresultado = self::generarArregloCurvas($informaciondataconteo);

        return $arrayresultado;
    }

    public static function generarQueryOC($idordencompra, $idcategoria)
    {
        $sql = "
            SELECT aem.id AS radicado, pem.id AS numProgramacion, aem.idOrdenCompra,
            (td.codigo + '-' +  CAST(oc.consecutivo AS nvarchar(50))) AS numeroOrden,
            cat.nombre AS categoria, prv.razonSocial,
            it.item, col.codigo AS color, tal.codigo AS talla, 
            cem.unidadesAsignadas, cem.unidadesConteo
            FROM conteoentregamercancia cem 
            INNER JOIN programacionentregamercancia pem ON cem.idProgramacionEntregaMercancia = pem.id
            INNER JOIN agendaentregamercancia aem ON pem.idAgendaEntregaMercancia = aem.id 
            INNER JOIN ordendecompra oc ON aem.idOrdenCompra = oc.id 
            INNER JOIN tipodocumento td ON oc.idTipoDocumento = td.id 
            INNER JOIN item it ON cem.idItem = it.id
            INNER JOIN talla tal ON it.idTalla = tal.id
            INNER JOIN color col ON it.idColor = col.id
            INNER JOIN categoria cat ON aem.idCategoria = cat.id 
            LEFT JOIN proveedor prv ON oc.idProveedor = prv.id 
            WHERE aem.idOrdenCompra = :idordencompra AND aem.idCategoria = :idcategoria
            ORDER BY it.item, col.codigo;
        ";

        $data = self::getDb()->createCommand($sql, [
            ':idordencompra' => $idordencompra,
            ':idcategoria' => $idcategoria
        ])->queryAll();

        return $data;
    }

    public static function generarQueryAgenda($idagenda, $iduserconteo)
    {
        $sql = "
            SELECT aem.id AS radicado, pem.id AS numProgramacion, aem.idOrdenCompra,
            (td.codigo + '-' +  CAST(oc.consecutivo AS nvarchar(50))) AS numeroOrden,
            cat.nombre AS categoria, prv.razonSocial,
            it.item, it.idColor, 
            (it.descripcion + ' - ' + ISNULL(it.unidadEmpaque, 'UND')) AS descripcion,
            col.codigo AS color, tal.codigo AS talla, 
            pem.unidadesAsignadas AS unidadesAsignadasUser,
            cem.unidadesAsignadas, cem.unidadesConteo
            FROM conteoentregamercancia cem 
            INNER JOIN programacionentregamercancia pem ON cem.idProgramacionEntregaMercancia = pem.id
            INNER JOIN agendaentregamercancia aem ON pem.idAgendaEntregaMercancia = aem.id 
            INNER JOIN ordendecompra oc ON aem.idOrdenCompra = oc.id 
            INNER JOIN tipodocumento td ON oc.idTipoDocumento = td.id 
            INNER JOIN item it ON cem.idItem = it.id
            INNER JOIN talla tal ON it.idTalla = tal.id
            INNER JOIN color col ON it.idColor = col.id
            INNER JOIN categoria cat ON aem.idCategoria = cat.id 
            LEFT JOIN proveedor prv ON oc.idProveedor = prv.id 
            WHERE aem.id = :idagenda ";

        if ($iduserconteo) {
            $sql = $sql . " AND pem.idUserConteo = :iduserconteo";
        }

        $sql = $sql . " ORDER BY it.item, col.codigo;";

        if ($iduserconteo) {
            $data = self::getDb()->createCommand($sql, [
                ':idagenda' => $idagenda,
                ':iduserconteo' => $iduserconteo
            ])->queryAll();
        } else {
            $data = self::getDb()->createCommand($sql, [':idagenda' => $idagenda])->queryAll();
        }

        return $data;
    }

    public static function generarQueryProgramacion($idprogramacion)
    {
        $sql = "
            SELECT aem.id AS radicado, pem.id AS numProgramacion, aem.idOrdenCompra,
            (td.codigo + '-' +  CAST(oc.consecutivo AS nvarchar(50))) AS numeroOrden,
            cat.nombre AS categoria, prv.razonSocial,
            it.item, it.idColor, it.descripcion,
            col.codigo AS color, tal.codigo AS talla, 
            pem.unidadesAsignadas AS unidadesAsignadasUser,
            cem.unidadesAsignadas, cem.unidadesConteo
            FROM conteoentregamercancia cem 
            INNER JOIN programacionentregamercancia pem ON cem.idProgramacionEntregaMercancia = pem.id
            INNER JOIN agendaentregamercancia aem ON pem.idAgendaEntregaMercancia = aem.id 
            INNER JOIN ordendecompra oc ON aem.idOrdenCompra = oc.id 
            INNER JOIN tipodocumento td ON oc.idTipoDocumento = td.id 
            INNER JOIN item it ON cem.idItem = it.id
            INNER JOIN talla tal ON it.idTalla = tal.id
            INNER JOIN color col ON it.idColor = col.id
            INNER JOIN categoria cat ON aem.idCategoria = cat.id 
            LEFT JOIN proveedor prv ON oc.idProveedor = prv.id 
            WHERE pem.id = :idprogramacion ";

        $sql = $sql . " ORDER BY it.item, col.codigo;";

        $data = self::getDb()->createCommand($sql, [
            ':idprogramacion' => $idprogramacion
        ])->queryAll();

        return $data;
    }

    public static function generarArregloCurvas($filasConsulta)
    {

        $filas = [];

        foreach ($filasConsulta as $fila) {
            // Crear un identificador de fila único
            $identificador = $fila['numeroOrden'] . '-' . $fila['item'] . '-' . $fila['color'] . '-' . $fila['descripcion'];

            // Verificar si la fila ya existe en el array
            if (!isset($filas[$identificador])) {
                // Si no existe, crear la fila con los valores predeterminados
                $filas[$identificador] = [
                    'numeroOrden' => $fila['numeroOrden'],
                    'item' => $fila['item'],
                    'color' => $fila['color'],
                    'descripcion' => $fila['descripcion'],
                    'totalUnidadesAsignadas' => 0,
                    'totalUnidadesConteo' => 0,
                ];
            }

            // Sumar las unidades asignadas y de conteo a las totales de la fila
            $filas[$identificador]['totalUnidadesAsignadas'] += $fila['unidadesAsignadas'];
            $filas[$identificador]['totalUnidadesConteo'] += $fila['unidadesConteo'];

            if (!isset($filas[$identificador][$fila['talla']])) {
                $filas[$identificador][$fila['talla']] = [
                    'unidadesAsignadas' => 0,
                    'unidadesConteo' => 0
                ];
            }
            // Agregar los demás valores a la fila
            // Puedes agregar aquí las demás columnas que quieras incluir en la fila
            $filas[$identificador][$fila['talla']] = [
                //'unidadesAsignadas' => $fila['unidadesAsignadas'],
                'unidadesAsignadas' => $filas[$identificador][$fila['talla']]['unidadesAsignadas'] += $fila['unidadesAsignadas'],
                'unidadesConteo' => $filas[$identificador][$fila['talla']]['unidadesConteo'] += $fila['unidadesConteo'],
                //'unidadesConteo' => $fila['unidadesConteo'],
            ];
        }

        //die("hola");

        return $filas;
    }

    public static function totalCantidadConteo($idprogramacion, $item, $iduserconteo, $idagenda = null)
    {

        $total = Conteoentregamercancia::find()
            ->alias('det')
            ->select(['SUM(det.unidadesConteo) AS total'])
            ->join('INNER JOIN', 'programacionentregamercancia pem', 'det.idProgramacionEntregaMercancia = pem.id')
            ->andFilterWhere(['det.idProgramacionEntregaMercancia' => $idprogramacion])
            ->andFilterWhere(['det.item' => $item])
            ->andFilterWhere(['pem.idUserConteo' => $iduserconteo])
            ->andFilterWhere(['pem.idAgendaEntregaMercancia' => $idagenda])
            ->scalar();
        return $total;
    }

    public static function generarExcelConteoCurvas($idagenda)
    {

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $params = null;
        $item = null;
        $idprogramacion = null;
        $iduserconteo = null;

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($params, $idprogramacion, $item, $idagenda, $iduserconteo);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas($idagenda, $idordencompra, $idcategoria, $iduserconteo);

        // Agrupar los datos por bodega
        $dataByItem = [];
        foreach ($dataProvider as $model) {
            $item = $model['item'];
            if (!isset($dataByItem[$item])) {
                $dataByItem[$item] = [];
            }
            $dataByItem[$item][] = $model;
        }

        // Obtener todas las tallas únicas
        $tallasUnicas = [];
        foreach ($dataProvider as $fila) {

            foreach (array_keys($fila) as $columna) {
                if ($columna !== 'numeroOrden' && $columna !== 'item' && $columna !== 'color' && $columna !== 'descripcion' && $columna !== 'Item' && $columna !== 'Color' && $columna !== 'totalUnidadesAsignadas' && $columna !== 'totalUnidadesConteo') {
                    if (!in_array($columna, $tallasUnicas)) {
                        $tallasUnicas[] = $columna;
                    }
                }
            }
        }

        // Obtener los encabezados dinámicamente del primer elemento del array

        $data = $dataByItem;

        $programacion = Programacionentregamercancia::find()
            ->where(['idAgendaEntregaMercancia' => $idagenda])
            ->all();

        $usuariosconteo = "";
        $numerousuarios = 0;
        foreach ($programacion as $registro) {
            $nombreempleado = null;
            if ($registro->userConteo != null) {
                //var_dump($modelagenda->id . ' - ' . $item); die("hola");
                $nombreempleado = $registro->userConteo->empleadoLogistica->empleado->nombreEmpleado;
            }

            if ($nombreempleado == null) {
                $nombreempleado = $registro->empleadoLogistica->empleado->nombreEmpleado;
            }
            if ($numerousuarios == 0) {
                $usuariosconteo = $nombreempleado;
            } else {
                if (!str_contains($usuariosconteo, $nombreempleado)) {
                    $usuariosconteo = $nombreempleado . " , " . $usuariosconteo;
                }
            }
            $numerousuarios = $numerousuarios + 1;
        }

        //var_dump($data); die("hola");

        $headers = [];
        $columnsToExport = [];
        if (!empty($data)) {
            $firstItem = reset($data); // Obtener el primer elemento del array
            if (is_array($firstItem)) {
                foreach ($firstItem[0] as $key => $value) {
                    if (strpos($key, 'numeroOrden') === false) { // Omitir 'numeroOrden'
                        if (is_array($value)) {
                            foreach ($value as $subKey => $subValue) {
                                if (strpos($subKey, 'Asignadas') === false) {
                                    $headers[] = $key . ' ' . ucfirst($subKey); // Ejemplo: "6-12 unidadesConteo"
                                    $columnsToExport[] = [$key, $subKey]; // Guardar qué columnas exportar
                                }
                            }
                        } else {
                            if (strpos($key, 'Asignadas') === false) {
                                $headers[] = $key;
                                $columnsToExport[] = $key; // Guardar qué columnas exportar
                            }
                        }
                    }
                }
            }
        }

        $estructura = [
            'item',
            'color',
            'descripcion',
            'totalUnidadesConteo'
        ]; // Aquí almacenaremos la estructura con los nombres de columnas
        
        $tallasEncontradas = []; // Aquí almacenaremos todas las tallas encontradas
        
        // Recorrer todos los registros
        foreach ($data as $itemKey => $subArray) {
            foreach ($subArray as $item) {
                // Iterar sobre cada item para buscar las posibles tallas
                foreach ($item as $key => $value) {
                    if (is_array($value) && isset($value['unidadesConteo'])) {
                        // Si es una talla, la agregamos al array de tallas encontradas
                        $tallasEncontradas[$key] = [$key, 'unidadesConteo'];
                    }
                }
            }
        }
        
        // Añadir todas las tallas encontradas a la estructura
        foreach ($tallasEncontradas as $talla) {
            $estructura[] = $talla;
        }

        $nuevaEstructura = []; // Para almacenar la nueva estructura

        // Recorrer la estructura para crear la nueva estructura
        foreach ($estructura as $columna) {
            if (is_array($columna)) {
                // Si es un array (una talla), unimos la talla con "UnidadesConteo"
                $nuevaEstructura[] = $columna[0] . ' UnidadesConteo';
            } else {
                // Si no es un array, solo añadimos la clave tal cual
                $nuevaEstructura[] = $columna;
            }
        }

        /*var_dump($columnsToExport); 
        echo("<br><br>");
        var_dump($estructura);
        die("hola");*/

        //var_dump($headers); die("hola");

        $columnsToExport = $estructura;
        $headers = $nuevaEstructura;

        //var_dump($data); die("hole");

        // Crea un nuevo objeto Spreadsheet
        $archivo = Yii::getAlias('@app/web/archivos/Formato_Legalizacion_Conteo.xlsx'); // Ruta al archivo Excel

        $spreadsheet = IOFactory::load($archivo);
        $sheet = $spreadsheet->getSheetByName('Data');
        $spreadsheet->setActiveSheetIndex(0);

        // Relleno de color desde A1 hasta A9
        $sheet->getStyle('A1:A10')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D3D3D3', // Color gris claro
                ],
            ],
        ]);

        // Agrega los encabezados de las columnas
        //$sheet->setCellValue('A1', 'Radicado');
        $sheet->setCellValue('B1', $modelagenda->id);
        //$sheet->setCellValue('A2', 'Almacén');
        $sheet->setCellValue('B2', $modelagenda->bodega->codigo . ' - ' . $modelagenda->bodega->nombre);
        //$sheet->setCellValue('A3', 'Orden');
        $sheet->setCellValue('B3', $modelagenda->ordenCompra->tipoDocumento->codigo . ' - ' . $modelagenda->ordenCompra->consecutivo);
        //$sheet->setCellValue('A4', 'Proveedor');
        $sheet->setCellValue('B4', $modelagenda->ordenCompra->proveedor->nit . ' - ' . $modelagenda->ordenCompra->proveedor->razonSocial);
        //$sheet->setCellValue('A5', 'Número Factura');
        $sheet->setCellValue('B5', $modelagenda->numeroFactura);
        //$sheet->setCellValue('A6', 'Tipo de Carga');
        $sheet->setCellValue('B6', $modelagenda->ordenCompra->proveedor->criterioModeloLogistico);
        //$sheet->setCellValue('A7', 'Fecha');
        $sheet->setCellValue('B7', date('Y-m-d H:i'));

        $minDate = Conteobylecturacodigo::find()
            ->select(new Expression('MIN(created_at)'))
            ->where(['modulo' => 1, 'idConteoFactura' => $modelagenda->id])
            ->scalar();

        $maxDate = Conteobylecturacodigo::find()
            ->select(new Expression('MAX(created_at)'))
            ->where(['modulo' => 1, 'idConteoFactura' => $modelagenda->id])
            ->scalar();

        //$sheet->setCellValue('A8', 'Fecha Inicio Conteo');
        $sheet->setCellValue('B8', $minDate);
        //$sheet->setCellValue('A9', 'Fecha Fin Conteo');
        $sheet->setCellValue('B9', $maxDate);

        $sheet->setCellValue('B10', $usuariosconteo);

        // Ajuste para intercambiar el orden de las columnas B y C
        if (isset($columnsToExport[1]) && isset($columnsToExport[2])) {
            $temp = $columnsToExport[1];
            $columnsToExport[1] = $columnsToExport[2];
            $columnsToExport[2] = $temp;

            // También intercambiar los encabezados correspondientes
            $tempHeader = $headers[1];
            $headers[1] = $headers[2];
            $headers[2] = $tempHeader;
        }

        // Escribir los encabezados en la primera fila
        $column = 'A';
        foreach ($headers as $header) {
            $textoABuscar = 'UnidadesConteo';
            $cadena = $header;
            if (strpos($header, $textoABuscar) !== false) {
                $cadena = '[' . str_replace($textoABuscar, "", $header) . ']';
            }

            $sheet->setCellValue($column . '12', strtoupper($cadena));
            $column++;
        }

        // Aplicar formato a los encabezados
        $sheet->getStyle('A11:' . $column . '12')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'D3D3D3', // Color amarillo
                ],
            ],
        ]);

        // Fila inicial para los datos
        $row = 13;

        //var_dump($columnsToExport); die("hola");

        // Recorrer el array y escribir los datos en las celdas
        foreach ($data as $items) {
            foreach ($items as $item) {
                $column = 'A';
                foreach ($columnsToExport as $key) {
                    if (is_array($key)) {

                        if (!isset($item[$key[0]])) {
                            $item[$key[0]] = []; // Si no existe, la inicializamos como un array
                        }
                        
                        // Verificamos si la clave anidada existe
                        if (!isset($item[$key[0]][$key[1]])) {
                            $item[$key[0]][$key[1]] = 0; // Si no existe, la inicializamos con valor 0
                        }

                        //if (array_key_exists($key[0], $item) && array_key_exists($key[1], $item[$key[0]])) {
                            $sheet->setCellValue($column . $row, $item[$key[0]][$key[1]]);
                        //}
                    } else {
                        $sheet->setCellValue($column . $row, $item[$key]);
                    }
                    $column++;
                }

                // Aplicar formato a cada fila de datos
                $sheet->getStyle('A' . $row . ':' . $column . $row)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $row++;
            }
        }

        // Relacion_Conteo_Matriz_CurvaTallasColores_002-2EA-386_2024-09-02

        $nombreArchivo = "Legalizacion_Conteo_Matriz_CurvaTallasColores_" .
            $modelagenda->ordenCompra->tipoDocumento->codigo . '_' .
            $modelagenda->ordenCompra->consecutivo . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $rutaGuardado = Yii::getAlias('@app/web/archivos/') . $nombreArchivo;

        // Guardar el archivo Excel
        $writer->save($rutaGuardado);

        return $rutaGuardado;
    }

    public static function crearRegistroTransferencia($idagenda, $dataProviderBD)
    {

        $agenda = Agendaentregamercancia::findOne(['id' => $idagenda]);
        $ordencompra = Ordendecompra::findOne(['id' => $agenda->idOrdenCompra]);

        $transferenciaerp = Transferenciaerp::findOne(['idOrdenCompra' => $agenda->idOrdenCompra]);

        if ($transferenciaerp) {
            $numRegistrosBorrados = Transferenciaerp::deleteAll(['id' => $transferenciaerp->id]);
            $numRegistrosBorrados = Transferenciaordencompraexcel::deleteAll(['idTransferenciaerp' => $transferenciaerp->id]);
        }

        $idtransferenciaerp = Conteoentregamercancia::cabeceraTransferencia($agenda, $ordencompra);

        $respuesta = Conteoentregamercancia::detalleTransferencia($idtransferenciaerp, $dataProviderBD);

        if ($respuesta) {
            $count = Transferenciaordencompraexcel::find()->where(['idTransferenciaerp' => $idtransferenciaerp])->count();

            $model = Transferenciaerp::findOne(['id' => $idtransferenciaerp]);
            $model->numeroRegistros = $count;
            $model->save();
        }
        return $idtransferenciaerp;
    }

    public static function cabeceraTransferencia($agenda, $ordencompra)
    {

        $conector = Conectoresdinamicos::find()->where(['idDocumento' => '165604'])->one();

        $descripcion = 'Transferencia: ' .
            $ordencompra->proveedor->razonSocial . ' ' .
            $ordencompra->tipoDocumento->codigo . '-' .
            $ordencompra->consecutivo . ' - ' .
            'No. Factura: ' . $agenda->numeroFactura;

        $notas = 'Fecha Documento: ' .
            $ordencompra->fechaDocumentoEntrada . ' ' .
            'Documento Entrada: ' . $ordencompra->tipoDocumentoentrada->codigo . '-' .
            $ordencompra->consecutivoDocumentoEntrada;

        $model = new Transferenciaerp();
        $model->descripcion = $descripcion;
        $model->notas = $notas;
        $model->documento = $agenda->id;
        $model->numeroRegistros = 0;
        $model->enviadoWS = 0;
        $model->origen = 'C';
        $model->idConectorDinamico = $conector->id;
        $model->idOrdenCompra = $ordencompra->id;

        if (!$model->save()) {
            var_dump($model->getErrors());
            die("STOP");
        }

        return $model->id;
    }

    public static function detalleTransferencia($idtransferenciaerp, $dataProviderBD)
    {

        $ok = true;
        $models = $dataProviderBD->getModels();

        foreach ($models as $registro) {
            $model = new Transferenciaordencompraexcel();
            $model->centroOperacionDocumento = $registro->codigoCentroOperacionDocumentoEntrada;
            $model->tipoDocumento = $registro->codigoTipoDocumentoEntrada;
            $model->consecutivoDocumento = $registro->consecutivoDocumentoEntrada;
            $model->fechaDocumento = $registro->fechaDocumentoEntrada;
            $model->tercero = $registro->tercero;
            $model->numeroFactura = $registro->numeroFactura;
            $model->sucursal = $registro->sucursalProveedor;
            $model->idTerceroComprador = $registro->nitcomprador;
            $model->consignacion = $registro->consignacion;
            $model->centroOperacionOrdenCompra = $registro->codigoCentroOperacionOC;
            $model->tipoDocumentoOrdenCompra = $registro->codigoTipoDoctoOC;
            $model->consecutivoOrdenCompra = $registro->consecutivoOC;
            $model->centroOperacionMovimiento = $registro->codigoCentroOperacionDocumentoEntrada;
            $model->tipoDocumentoMovimiento = $registro->codigoTipoDocumentoEntrada;
            $model->consecutivoMovimiento = $registro->consecutivoDocumentoEntrada;
            $model->numeroRegistroMovimiento = 1;
            $model->bodegaMovimiento = $registro->bodega;
            $model->unidadMovimiento = 'UND';
            $model->cantidadBase = $registro->unidadesConteo;
            $model->fechaEntregaMovimiento = $registro->fechaEntrega;
            $model->item = $registro->item;
            $model->color = $registro->color;
            $model->talla = $registro->talla;
            $model->rowid = $registro->codigointernomovto;
            $model->idTransferenciaerp = $idtransferenciaerp;

            if (!$model->save()) {
                $ok = false;
                //var_dump($model->getErrors()); die("hola");
                continue;
            }
        }


        return $ok;

    }

}
