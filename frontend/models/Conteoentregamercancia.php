<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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

    public static function grabarItemParaConteo ($idordencompra, $idprogramacionentregamercancia, $item)
    {
        $respuesta = false;

        $listaitems = Ordendecompradetalle::listaItemsOrdenCompra ($idordencompra, $item);

        foreach ($listaitems as $item){
            //var_dump($item->codigoBarras); die("aqui");
            
            $respuesta = true;
            $iditem = $item['idItem'];

            $model = Conteoentregamercancia::findOne([
                                                        'idProgramacionEntregaMercancia' => $idprogramacionentregamercancia,
                                                        'idItem' => $iditem
                                                    ]);

            if ($model == null){
                $model = new Conteoentregamercancia ();
                $model->idProgramacionEntregaMercancia = $idprogramacionentregamercancia;
                $model->idItem = $iditem;
                $model->codigoBarras = $item['codigoBarras'];
                $model->unidadesAsignadas = $item['cantidad'];
                $model->item = $item['item'];
                $model->unidadesConteo = 0;
                
                if (!$model->save()){
                    var_dump($model->getErrors()); die("hola");
                    $respuesta = false;
                    break;
                }
            }

        }

        return $respuesta;

    }

    public static function generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo, $idprogramacion=null) {

        if ($idordencompra){
            $arrayresultado = self::procesoOrdenCompra ($idordencompra, $idcategoria);
        }

        if ($idagenda){
            $arrayresultado = self::procesoAgenda ($idagenda, $iduserconteo);
        }

        if ($idprogramacion){
            $arrayresultado = self::procesoProgramacion ($idprogramacion);
        }

        return $arrayresultado;
    }

    public static function procesoProgramacion ($idprogramacion){
        
        $informaciondataconteo = self::generarQueryProgramacion ($idprogramacion);

        $arrayresultado = self::generarArregloCurvas ($informaciondataconteo);

        return $arrayresultado;
    }

    public static function procesoAgenda ($idagenda, $iduserconteo){
        
        $informaciondataconteo = self::generarQueryAgenda ($idagenda, $iduserconteo);

        $arrayresultado = self::generarArregloCurvas ($informaciondataconteo);

        return $arrayresultado;
    }

    public static function procesoOrdenCompra ($idordencompra, $idcategoria){
        
        $informaciondataconteo = self::generarQueryOC ($idordencompra, $idcategoria);

        $arrayresultado = self::generarArregloCurvas ($informaciondataconteo);

        return $arrayresultado;
    }

    public static function generarQueryOC ($idordencompra, $idcategoria)
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

    public static function generarQueryAgenda ($idagenda, $iduserconteo)
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
            WHERE aem.id = :idagenda ";
            
        if ($iduserconteo){
            $sql = $sql . " AND pem.idUserConteo = :iduserconteo";
        }

        $sql = $sql . " ORDER BY it.item, col.codigo;";

        if ($iduserconteo){
            $data = self::getDb()->createCommand($sql, [
                                                            ':idagenda' => $idagenda,
                                                            ':iduserconteo' => $iduserconteo
                                                        ])->queryAll();
        }else{
            $data = self::getDb()->createCommand($sql, [':idagenda' => $idagenda])->queryAll();
        }

        return $data;
    }

    public static function generarQueryProgramacion ($idprogramacion)
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

    public static function generarArregloCurvas ($filasConsulta){

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
        
            // Agregar los demás valores a la fila
            // Puedes agregar aquí las demás columnas que quieras incluir en la fila
            $filas[$identificador][$fila['talla']] = [
                'unidadesAsignadas' => $fila['unidadesAsignadas'],
                'unidadesConteo' => $fila['unidadesConteo'],
            ];
        }

        return $filas;
    }

    public static function totalCantidadConteo ($idprogramacion,$item,$iduserconteo, $idagenda = null){

        $total = Conteoentregamercancia::find()
                                            ->alias('det')
                                            ->select(['SUM(det.unidadesConteo) AS total'])
                                            ->join('INNER JOIN', 'programacionentregamercancia pem','det.idProgramacionEntregaMercancia = pem.id')
                                            ->andFilterWhere(['det.idProgramacionEntregaMercancia' => $idprogramacion])
                                            ->andFilterWhere(['det.item' => $item])
                                            ->andFilterWhere(['pem.idUserConteo' => $iduserconteo])
                                            ->andFilterWhere(['pem.idAgendaEntregaMercancia' => $idagenda])
                                            ->scalar();                                            
        return $total;
    }

    public static function generarExcelConteoCurvas ($idagenda){

        $modelagenda = Agendaentregamercancia::findOne(['id' => $idagenda]);

        $params = null;
        $item = null;
        $idprogramacion = null;
        $iduserconteo = null;

        $searchModel = new ConteoentregamercanciaSearch();
        $dataProviderBD = $searchModel->search($params, $idprogramacion, $item, $idagenda, $iduserconteo);

        $idordencompra = null;
        $idcategoria = null;
        $dataProvider = Conteoentregamercancia::generarDataConteoCurvas ($idagenda, $idordencompra, $idcategoria, $iduserconteo);

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

        // Crea un nuevo objeto Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()->setCreator('GRUMA Logistica Total')
                ->setLastModifiedBy('GRUMA Logistica Total')
                ->setTitle('Excel creado con PhpSpreadSheet')
                ->setSubject('Excel Demostración')
                ->setDescription('Excel generado como prueba')
                ->setKeywords('office openxml php')
                ->setCategory('PHPSpreadsheet');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Data");

        // Agrega los encabezados de las columnas
        $sheet->setCellValue('A1', 'Radicado');
        $sheet->setCellValue('B1', $modelagenda->id);
        $sheet->setCellValue('A2', 'Almacén');
        $sheet->setCellValue('B2', $modelagenda->bodega->codigo . ' - ' . $modelagenda->bodega->nombre);
        $sheet->setCellValue('A3', 'Orden');
        $sheet->setCellValue('B3', $modelagenda->ordenCompra->tipoDocumento->codigo . ' - ' . $modelagenda->ordenCompra->consecutivo);
        $sheet->setCellValue('A4', 'Proveedor');
        $sheet->setCellValue('B4', $modelagenda->ordenCompra->proveedor->nit . ' - ' . $modelagenda->ordenCompra->proveedor->razonSocial);
        $sheet->setCellValue('A5', 'Número Factura');
        $sheet->setCellValue('B5', $modelagenda->numeroFactura);
        $sheet->setCellValue('A6', 'Tipo de Carga');
        $sheet->setCellValue('B6', $modelagenda->ordenCompra->proveedor->criterioModeloLogistico);
        $sheet->setCellValue('A7', 'Fecha');
        $sheet->setCellValue('B7', date('Y-m-d H:i'));

        $minDate = Conteobylecturacodigo::find()
                            ->select(new Expression('MIN(created_at)'))
                            ->where(['modulo' => 1, 'idConteoFactura' => $modelagenda->id])
                            ->scalar();

        $maxDate = Conteobylecturacodigo::find()
                            ->select(new Expression('MAX(created_at)'))
                            ->where(['modulo' => 1, 'idConteoFactura' => $modelagenda->id])
                            ->scalar();

        $sheet->setCellValue('A8', 'Fecha Inicio Conteo');
        $sheet->setCellValue('B8', $minDate);
        $sheet->setCellValue('A9', 'Fecha Fin Conteo');
        $sheet->setCellValue('B9', $minDate);

        return $spreadsheet;
    }

}
