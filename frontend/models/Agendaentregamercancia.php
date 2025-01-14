<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use PhpOffice\PhpSpreadsheet\IOFactory;

use common\models\User;

/**
 * This is the model class for table "agendaentregamercancia".
 *
 * @property int $id
 * @property int $idOrdenCompra
 * @property string $fechaCita
 * @property float $unidades
 * @property int $numeroCajas
 * @property int $idTransportadora
 * @property string $contacto
 * @property string $fechaContacto
 * @property string|null $numeroGuia
 * @property string|null $observacion
 * @property int $idEstado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Estadoagenda $estado
 * @property Ordendecompra $ordenCompra
 * @property Transportadora $transportadora
 * @property Agendapresupuesto $agenda
 * @property Categoria $categoria
 */
class Agendaentregamercancia extends \yii\db\ActiveRecord
{
    public $idCentroOperacion;
    public $codigoCentroOperacion;
    public $idTipoDocumento;
    public $codigoTipoDocumento;
    public $numeroOrdenCompra;
    public $fechaOrden;
    public $fechaEntrega;
    public $totalCantidadPedida;
    public $totalCantidadEntrada;
    public $totalCantidadPendiente;
    public $dataProveedor;
    public $nombreCategoria;
    public $nombreSubcategoria;

    public $razonSocial;
    public $nit;
    public $criterioMercancia;
    public $modeloLogistico;
    public $nombreEstado;
    public $desde;
    public $hasta;

    public $fechaDesde;
    public $fechaHasta;

    public $horaAgenda;
    public $fechaAgenda;

    public $bodegaPrincipal;
    public $radicado;
    public $consecutivo;
    public $serie;
    public $horaContacto;
    public $codigoProveedor;
    public $nitProveedor;
    public $proveedor;
    public $tipoProveedor;
    public $nombreTransportadora;
    public $unidadesOC;
    public $programadas;
    public $unidadesConteo;
    public $nombreEstadoAgenda;
    public $nombreEstadoLegaliza;

    public $motivo;
    public $nroPaquetes;
    public $minFechaConteo;
    public $maxFechaConteo;
    public $usuariosConteo;
    public $numeroItemsOC;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agendaentregamercancia';
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
            /*[['idOrdenCompra', 'fechaCita', 'unidades', 'numeroCajas', 'idTransportadora', 'contacto', 
            'fechaContacto'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],*/
            [['idOrdenCompra', 'idAgenda'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idOrdenCompra', 'numeroCajas', 'idTransportadora', 'idEstado', 'idEstadoConteo', 
            'created_by', 'updated_by', 'numeroOrdenCompra', 'idAgenda', 'idAgendaEntregaMercancia', 
            'idCategoria', 'idEstadoLegalizacion', 'idUserCumplimiento', 'idUserDevolucion',
            'idUserLegalizacion', 'idBodega'], 'integer'],
            [['fechaCita', 'fechaContacto', 'created_at', 'updated_at', 'fechaAgenda', 
            'horaAgenda', 'fechaDesde', 'radicadohs', 'fechaCumplimiento', 'fechaDevolucion', 'horaCita'], 'safe'],
            [['unidades', 'numeroOrdenCompra', 'unidadesCumplidas'], 'number'],
            [['contacto', 'observacionLegalizacion'], 'string', 'max' => 150],
            [['numeroGuia', 'numeroFactura'], 'string', 'max' => 20],
            [['observacion', 'motivo', 'motivoCumplimiento', 'motivoDevolucion', 'subcategorias'], 'string', 'max' => 500],
            [['idTransportadora'], 'exist', 'skipOnError' => true, 'targetClass' => Transportadora::class, 'targetAttribute' => ['idTransportadora' => 'id']],
            [['idOrdenCompra'], 'exist', 'skipOnError' => true, 'targetClass' => Ordendecompra::class, 'targetAttribute' => ['idOrdenCompra' => 'id']],
            [['idEstado'], 'exist', 'skipOnError' => true, 'targetClass' => Estadoagenda::class, 'targetAttribute' => ['idEstado' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Radicado',
            'idOrdenCompra' => 'Id Orden Compra',
            'fechaCita' => 'Fecha Cita',
            'unidades' => 'Unidades Agenda',
            'numeroCajas' => 'Número Cajas',
            'idTransportadora' => 'Transportadora',
            'contacto' => 'Contacto',
            'fechaContacto' => 'Fecha Contacto',
            'numeroGuia' => 'Número Guia',
            'observacion' => 'Observación',
            'idEstado' => 'Estado',
            'idEstadoConteo' => 'Estado Conteo',
            'idEstadoLegalizacion' => 'Estado Legaliza',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'idCategoria' => 'Categoría',
            'nombreCategoria' => 'Categoría',

            'idCentroOperacion' => 'Centro Operación',
            'idTipoDocumento' => 'Tipo Documento',
            'numeroOrdenCompra' => 'Número Orden Compra',
            'fechaOrden' => 'Fecha Orden',
            'totalCantidadPedida' => 'Cantidad Pedida',
            'totalCantidadEntrada' => 'Cantidad Entrada',
            'totalCantidadPendiente' => 'Cantidad Pendiente',
            'dataProveedor' => 'Proveedor',
            'idOrdenCompra' => 'ID Orden Compra',

            'codigoTipoDocumento' => 'Tipo Documento',
            'codigoCentroOperacion' => 'Centro Operación',
            'consecutivo' => 'Número Orden Compra',
            'razonSocial' => 'Razón Social',
            'fechaAgenda' => 'Fecha Cita',
            'horaAgenda' => 'Hora Cita',
            'idAgendaEntregaMercancia' => 'Radicado Rel.',

            'unidadesCumplidas' => 'Unidades Cumplidas Puerta',
            'numeroFactura' => 'No. Factura',
            'observacionLegalizacion' => 'Obs. Legalización',
            'criterioMercancia' => 'Tipo Proveedor',
            'criterioModeloLogistico' => 'Modelo Logístico',

            'motivo' => 'Observaciones',
            'subcategorias' => 'Subcategorias',

            'created_at' => 'Fecha Registra',
            'created_by' => 'Usuario Registra'
        ];
    }

    /**
     * Gets query for [[Estado]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstado()
    {
        return $this->hasOne(Estadoagenda::class, ['id' => 'idEstado']);
    }

    public function getUsuariocrea()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getEstadoconteo()
    {
        return $this->hasOne(Estadoconteo::class, ['id' => 'idEstadoConteo']);
    }

    public function getEstadolegalizacion()
    {
        return $this->hasOne(Estadolegalizacion::class, ['id' => 'idEstadoLegalizacion']);
    }

    public function getCategoria()
    {
        return $this->hasOne(Categoria::class, ['id' => 'idCategoria']);
    }

    /**
     * Gets query for [[OrdenCompra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrdenCompra()
    {
        return $this->hasOne(Ordendecompra::class, ['id' => 'idOrdenCompra']);
    }

    /**
     * Gets query for [[Transportadora]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransportadora()
    {
        return $this->hasOne(Transportadora::class, ['id' => 'idTransportadora']);
    }

    public function getProgramacionentregamercancia()
    {
        return $this->hasMany(Programacionentregamercancia::class, ['idAgendaEntregaMercancia' => 'id']);
    }

    /**
     * Gets query for [[Agenda]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAgenda()
    {
        return $this->hasOne(Agendapresupuesto::class, ['id' => 'idAgenda']);
    }

    public function getBodega()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idBodega']);
    }

    public static function actualizarUnidadesAgendamiento ($idagenda, $idordencompra, $fechaagenda, $operacion = null)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'cat.nombre AS categoria', 
                                'sub.nombre AS subcategoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'categoria cat','det.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','det.idSubcategoria = sub.id')
                    ->groupBy(['det.idOrdenCompra', 'cat.nombre' , 'sub.nombre'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra]);

        $resultados = $query->all();

        foreach ($resultados as $resultado){
            $subcategoria = $resultado->subcategoria;
            $categoria = $resultado->categoria;

            $model = Agendapresupuestosubcategoria::findOne([
                                                        'idAgendaPresupuesto' => $idagenda,
                                                        'crossDocking' => 'CEDI',
                                                        'fechaLlegada' => $fechaagenda,
                                                        'categoria' => $categoria,
                                                        'subcategoria' => $subcategoria]);

            if ($model){
                $cantidadAgendada = $model->cantidadAgendada ?? 0;

                //var_dump($cantidadAgendada . ' - ' . $resultado->cantidad); die("hola");

                if ($operacion == 'restar'){
                    $model->cantidadAgendada = $cantidadAgendada - $resultado->cantidad;
                }else{
                    $model->cantidadAgendada = $cantidadAgendada + $resultado->cantidad;
                }
            }else{
                $model = new Agendapresupuestosubcategoria();
                $model->idAgendaPresupuesto = $idagenda;
                $model->periodoAnio = $model->agendaPresupuesto->periodoAnio;
                $model->periodoMes = $model->agendaPresupuesto->periodoMes;
                $model->crossDocking = 'CEDI';
                $model->crossdocking_id = $model->nombreCrossdocking->id;
                $model->categoria = $categoria;
                $model->categoria_id = $model->nombreCategoria->id;
                $model->subcategoria = $subcategoria;
                $model->subcategoria_id = $model->nombreSubcategoria->id;
                $model->fechaLlegada = $fechaagenda;
                $model->cantidad = 0;
                $model->cantidadAgendada = $resultado->cantidad;
            }

            $model->save();

            $model = Agendapresupuestocategoria::findOne([
                'idAgendaPresupuesto' => $idagenda,
                'crossDocking' => 'CEDI',
                'fechaLlegada' => $fechaagenda,
                'categoria' => $categoria]);

            if ($model){
                $cantidadAgendada = $model->cantidadAgendada ?? 0;

                if ($operacion == 'restar'){
                    $model->cantidadAgendada = $cantidadAgendada - $resultado->cantidad;
                }else{
                    $model->cantidadAgendada = $cantidadAgendada + $resultado->cantidad;
                }
            }else{
                $model = new Agendapresupuestocategoria();
                $model->idAgendaPresupuesto = $idagenda;
                $model->periodoAnio = $model->agendaPresupuesto->periodoAnio;
                $model->periodoMes = $model->agendaPresupuesto->periodoMes;
                $model->crossDocking = 'CEDI';
                $model->crossdocking_id = $model->nombreCrossdocking->id;
                $model->categoria = $categoria;
                $model->categoria_id = $model->nombreCategoria->id;
                $model->fechaLlegada = $fechaagenda;
                $model->cantidad = 0;
                $model->cantidadAgendada = $resultado->cantidad;
            }
            $model->save();

            $numeroSemana = date('W', strtotime($fechaagenda));
            $model = Agendapresupuestosemana::findOne([
                'idAgendaPresupuesto' => $idagenda,
                'crossDocking' => 'CEDI',
                'numeroSemanaAnio' => $numeroSemana,
                'categoria' => $categoria]);

            if ($model){
                $cantidadAgendada = $model->cantidadAgendada ?? 0;
                if ($operacion == 'restar'){
                    $model->cantidadAgendada = $cantidadAgendada - $resultado->cantidad;
                }else{
                    $model->cantidadAgendada = $cantidadAgendada + $resultado->cantidad;
                }
            }else{
                $model = new Agendapresupuestosemana();
                $model->idAgendaPresupuesto = $idagenda;
                $model->periodoAnio = $model->agendaPresupuesto->periodoAnio;
                $model->periodoMes = $model->agendaPresupuesto->periodoMes;
                $model->crossDocking = 'CEDI';
                $model->crossdocking_id = $model->nombreCrossdocking->id;
                $model->categoria = $categoria;
                $model->categoria_id = $model->nombreCategoria->id;
                $model->numeroSemanaAnio = $numeroSemana;
                $model->cantidad = 0;
                $model->cantidadAgendada = $resultado->cantidad;
            }
            $model->save();

        }

    }

    public function getNumeroPersonasProgramadas (){
        $query = Programacionentregamercancia::find()
            ->alias('pem')
            ->join('INNER JOIN', 'estadoprogramacion est', 'pem.idEstado = est.id') 
            ->where(['est.codigo' => 1, 'pem.idAgendaEntregaMercancia' => $this->id]); 

        $count = $query->count();

        return $count;
    }

    public static function actualizarEstado ($id, $codigo){
        
        $modelestado = Estadoagenda::findOne(['codigo' => $codigo]);

        $model = Agendaentregamercancia::findOne(['id' => $id ]);
        $model->idEstado = $modelestado->id;

        if ($model->save()){
            return $model;
        }
        return null;
    }

    public static function grabarOrdenCompraCategoria ($model){
        $respuesta = false;
        $modelestado = Estadoagenda::findOne(['codigo' => 0]);

        $categorias = Ordendecompradetalle::listarCategoriasOrdenCompra ($model->idOrdenCompra);

        foreach($categorias as $categoria){
            
            $respuesta = false;
            $modelagendaoc = new Agendaentregamercancia();
            $modelagendaoc->idAgenda = $model->idAgenda;
            $modelagendaoc->idOrdenCompra = $model->idOrdenCompra;
            $modelagendaoc->idEstado = $modelestado->id;
            $modelagendaoc->idCategoria = $categoria->idCategoria;
            $modelagendaoc->subcategorias = Ordendecompradetalle::subcategoriasOrdenCompra ($model->idOrdenCompra);

            if ($modelagendaoc->ordenCompra->totalCantidadPendiente > 0){
                $modelagendaoc->unidades = $categoria->cantidad;
                $id = $modelagendaoc->save();
                $respuesta = true;
            }
        }
        return $respuesta;
    }

    public static function uploadocsiesa($archivo)
    {
        $file = $archivo;

        $tempPath = Yii::getAlias('@app/temp/');
        $tempFileName = $tempPath . $file->baseName . '.' . $file->extension;
        $file->saveAs($tempFileName);

        $respuesta = Agendaentregamercancia::extraer_data_archivo ($tempFileName);
            
        unlink($tempFileName);
        return $respuesta;
    }

    public static function extraer_data_archivo ($archivoExcel){

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
        for ($fila = 1; $fila <= $totalFilas; $fila++) {
        

            $grabar = true;

            if ($fila < 2){
                continue;
            }

            $idtipodcto = null;
            $valor_celda = $sheet->getCell('B' . $fila)->getValue();
            if ($valor_celda){
                $tipodcto = Tipodocumento::find()->where(['codigo' => trim($valor_celda)])->one();
                $idtipodcto = $tipodcto->id;
            }

            $consecutivo = null;
            $valor_celda = $sheet->getCell('C' . $fila)->getValue();
            if ($valor_celda){
                $consecutivo = floatval(substr($valor_celda,4,8));
            }

            $idCO = null;
            $valor_celda = $sheet->getCell('AC' . $fila)->getValue();
            if ($valor_celda){
                $centrooperacion = Centrooperacion::find()->where(['codigo' => trim($valor_celda)])->one();
                $idCO = $centrooperacion->id;
            }

            $orden = Ordendecompra::find()->where([
                                                'idCO' => $idCO,
                                                'idTipoDocumento' => $idtipodcto,
                                                'consecutivo' => $consecutivo
                                                ])->one();

            //var_dump($orden); die("STOP");

            if ($orden == null){
                continue;
            }

            $codigobarras = null;
            $valor_celda = $sheet->getCell('F' . $fila)->getValue();
            if ($valor_celda){
                $codigobarras = $valor_celda;
            }

            if ($codigobarras == null){
                continue;
            }

            $bodega = null;
            $valor_celda = $sheet->getCell('A' . $fila)->getValue();
            if ($valor_celda){
                $bodega = $valor_celda;
            }

            $sucursal = null;
            $valor_celda = $sheet->getCell('H' . $fila)->getValue();
            if ($valor_celda){
                $sucursal = $valor_celda;
            }

            $comprador = null;
            $nitcomprador = null;
            $valor_celda = $sheet->getCell('AA' . $fila)->getValue();
            if ($valor_celda){
                $comprador = $valor_celda;

                $modelcomprador = Comprador::find()->where(['nombre' => $comprador])->one();
                $nitcomprador = $modelcomprador->documento;
            }

            $codigointernomovto = null;
            $valor_celda = $sheet->getCell('AK' . $fila)->getValue();
            if ($valor_celda){
                $codigointernomovto = $valor_celda;
            }

            $orden->nitcomprador = $nitcomprador;
            $orden->comprador = $comprador;
            $orden->sucursalProveedor = $sucursal;
            
            if (!$orden->save()){
                var_dump($orden->getErrors()); die("hola");
            };

            $item = Item::find()->where(['codigoBarras' => $codigobarras])->one();

            $detalle = Ordendecompradetalle::find()->where([
                                                            'idOrdenCompra' => $orden->id,
                                                            'idItem' => $item->id
                                                        ])->one();

            if ($detalle){
                $detalle->bodega = $bodega;
                $detalle->codigointernomovto = $codigointernomovto;

                if (!$detalle->save()){
                    var_dump($detalle->getErrors()); die("hola");
                }
            }
        }

        return $grabar;

    }

    public static function actualizarItemsOC (){
        
    }

}
