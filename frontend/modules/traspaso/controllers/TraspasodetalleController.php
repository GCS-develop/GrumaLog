<?php

namespace frontend\modules\traspaso\controllers;

use frontend\models\Impresora;
use frontend\models\Item;
use frontend\models\search\TraspasodetalleSearch;
use frontend\models\Tipodocumento;
use frontend\models\Traspaso;
use frontend\models\Traspasodetalle;
use Exception;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use xstreamka\mobiledetect\Device;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/** @var yii\widgets\ActiveForm $form */

/**
 * TraspasodetalleController implements the CRUD actions for Traspasodetalle model.
 */
class TraspasodetalleController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }
    /**
     * Lists all Traspasodetalle models.
     *
     * @return string
     */
    public function actionIndex($idtraspaso = null)
    {
        $searchModel = new TraspasodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idtraspaso);

        $traspaso = Traspaso::findOne($idtraspaso);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'idtraspaso' => $idtraspaso,
            'traspaso' => $traspaso,
        ]);
    }

    /**
     * Displays a single Traspasodetalle model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Traspasodetalle model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($idtraspaso)
    {
        $modeltraspaso = Traspaso::findOne(['id' => $idtraspaso]);

        if ($modeltraspaso->idEstado != 0) {
            return $this->redirect(['/traspaso/index']);
        }

        $ultimo_codigo = null;

        if ($modeltraspaso->idUltimoItem != null) {
            $modelitem = Item::find()
                ->where(['id' => $modeltraspaso->idUltimoItem])
                ->andWhere(['idEstado' => 'ACTIVO'])
                ->one();
            $ultimo_codigo = $modelitem->codigoBarras;
        }

        $cantidad_paquetes = Traspasodetalle::find()
            ->alias('td')
            ->join('INNER JOIN', 'item as it', 'td.idItem = it.id')
            ->where(['idTraspaso' => $idtraspaso])
            ->andWhere('unidadEmpaque IS NOT NULL')
            ->sum('cantidad');


        $model = new Traspasodetalle();
        $model->idTraspaso = $idtraspaso;
        $model->cantidad = 1;

        $count = Traspasodetalle::find()
            ->alias('td')
            ->select([
                'total' => new \yii\db\Expression('SUM(
                CASE
                    WHEN ue.equivalencia IS NOT NULL THEN td.cantidad * ue.equivalencia
                    ELSE td.cantidad
                END
            )')
            ])
            ->innerJoin('item as it', 'td.idItem = it.id')
            ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
            ->where(['idTraspaso' => $idtraspaso])
            ->scalar();


        $model->bodegaorigen = $model->traspaso->bodegaOrigen->nombre;
        $model->bodegadestino = $model->traspaso->bodegaDestino->nombre;

        $searchModel = new TraspasodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idtraspaso);

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {

                $modelitem = Item::find()
                    ->where(['codigoBarras' => $model->codigoitem])
                    ->andWhere(['idEstado' => 'ACTIVO'])
                    ->one();

                if ($modelitem == null) {

                    Yii::$app->session->setFlash('error', 'No existe codigo de barras: ' . $model->codigoitem);

                } else {

                    $model->idItem = $modelitem->id;
                    $modeldetalle = Traspasodetalle::find()->where([
                        'idTraspaso' => $idtraspaso,
                        'idItem' => $model->idItem
                    ])->one();

                    if ($modeldetalle == null) {
                        $modeldetalle = new Traspasodetalle();
                        $modeldetalle->idTraspaso = $model->idTraspaso;
                        $modeldetalle->idItem = $model->idItem;
                        $modeldetalle->cantidad = 0;
                    }

                    $inventario = $modeldetalle->getInventario($model->codigoitem, $model->traspaso->bodegaOrigen->codigo);

                    if ($inventario > 0) {

                        $modeldetalle->codigoitem = $model->idItem;
                        $modeldetalle->cantidad = $modeldetalle->cantidad + $model->cantidad;

                        Yii::trace('Guardando el modelo detalle', __METHOD__);

                        if ($modeldetalle->validate()) {

                            Yii::trace('Modelo válido, guardando', __METHOD__);
                            $modeldetalle->save();

                            $modeltraspaso->idUltimoItem = $modeldetalle->idItem;
                            $modeltraspaso->save();

                            Yii::$app->session->setFlash('success', 'Guardado exitosamente!');
                            Yii::trace('Modelo guardado correctamente', __METHOD__);

                        } else {

                            Yii::error('El modelo no es válido. Verifica los datos.', __METHOD__);

                            Yii::$app->session->setFlash('error', 'El modelo no es válido, verifica los datos.' . __METHOD__);

                        }
                    } else {

                        Yii::$app->session->setFlash('error', 'Articulo sin existencia para traspaso: ' . $model->codigoitem . ' en bodega ' . $model->bodegaorigen . ' inv ' . $inventario);
                    }

                    return $this->redirect(['create', 'idtraspaso' => $idtraspaso]);

                }

                return $this->redirect(['create', 'idtraspaso' => $idtraspaso]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'count' => $count,
            'ultimo_codigo' => $ultimo_codigo,
            'cantidad_paquetes' => $cantidad_paquetes,
        ]);
    }

    public function actionDoTraspasoAjax($idtraspaso)
    {
        $modeltraspaso = Traspaso::findOne(['id' => $idtraspaso]);

        if ($modeltraspaso->idEstado != 0) {
            return $this->redirect(['/traspaso/index']);
        }

        $ultimo_codigo = null;

        if ($modeltraspaso->idUltimoItem != null) {
            $modelitem = Item::find()
                ->where(['id' => $modeltraspaso->idUltimoItem])
                ->andWhere(['idEstado' => 'ACTIVO'])
                ->one();
            $ultimo_codigo = $modelitem->codigoBarras;
        }

        $cantidad_paquetes = null;

        $cantidad_paquetes = Traspasodetalle::find()
            ->select(['total_cantidad' => new \yii\db\Expression('SUM(cantidad)')])
            ->where(['idTraspaso' => $idtraspaso])
            ->scalar();

        $model = new Traspasodetalle();
        $model->idTraspaso = $idtraspaso;
        $model->cantidad = 1;

        $count = Traspasodetalle::find()
            ->alias('td')
            ->select([
                'total' => new \yii\db\Expression('SUM(
                CASE
                    WHEN ue.equivalencia IS NOT NULL THEN td.cantidad * ue.equivalencia
                    ELSE td.cantidad
                END
            )')
            ])
            ->innerJoin('item as it', 'td.idItem = it.id')
            ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
            ->where(['idTraspaso' => $idtraspaso])
            ->scalar();

        $model->bodegaorigen = $model->traspaso->bodegaOrigen->nombre;
        $model->bodegadestino = $model->traspaso->bodegaDestino->nombre;

        $searchModel = new TraspasodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $idtraspaso);

        return $this->render('_form_ajax', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'count' => $count,
            'ultimo_codigo' => $ultimo_codigo,
            'cantidad_paquetes' => $cantidad_paquetes,
        ]);
    }
    public function actionProcesarFormulario($idtraspaso)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $codigoEAN = Yii::$app->request->post('codigoBarras');

        $total = Yii::$app->request->post('total');

        $totalRegistros = Yii::$app->request->post('totalRegistros');

        /*$idprogramacion = Yii::$app->request->post('idprogramacion');
        $unidadesconteo = Yii::$app->request->post('unidades');
        $totalregistros = Yii::$app->request->post('totalRegistros');
        $totalunidadesconteo = Yii::$app->request->post('totalunidadesconteo');*/

        $error = false;

        $modelitem = Item::find()
            ->where(['codigoBarras' => $codigoEAN])
            ->andWhere(['idEstado' => 'ACTIVO'])
            ->one();

        if ($modelitem == null) {
            Yii::$app->session->setFlash('error', 'No Existe Artículo ' . $codigoEAN);

            $error = true;

            return [
                'success' => false,
                'message' => 'No Existe Artículo',
            ];
        }

        if ($modelitem->unidadOrden == null and $modelitem->unidadEmpaque == null) {
            Yii::$app->session->setFlash('error', 'El codigo de barras no tiene equivalencia ' . $codigoEAN);

            $error = true;

            return [
                'success' => false,
                'message' => 'El codigo de barras no tiene equivalencia',
            ];
        }

        $modeltraspaso = Traspaso::findOne(['id' => $idtraspaso]);

        $inventario = Traspasodetalle::getInventario($codigoEAN, $modeltraspaso->bodegaOrigen->codigo);
        // $inventario = 1;
        if ($inventario == null or $inventario <= 0) {
            Yii::$app->session->setFlash('error', 'La Bodega No Tiene Inventario para EAN ' . $codigoEAN);

            $error = true;

            return [
                'success' => false,
                'message' => 'La Bodega No Tiene Inventario para EAN',
            ];
        }

        $modeldetalle = Traspasodetalle::find()->where([
            'idTraspaso' => $idtraspaso,
            'idItem' => $modelitem->id
        ])->one();

        // si no existe creo uno

        if ($modeldetalle == null) {
            $modeldetalle = new Traspasodetalle();
            $modeldetalle->idTraspaso = $idtraspaso;
            $modeldetalle->idItem = $modelitem->id;
            $modeldetalle->cantidad = 0;
        }

        $modeldetalle->codigoitem = $modelitem->id;  // REVISAR ALMACENA 2 VECES EN DIFERENTE VARIABLE
        $modeldetalle->cantidad = $modeldetalle->cantidad + 1;

        $modeldetalle->save();

        Traspaso::updateAll(['idUltimoItem' => $modelitem->id], ['id' => $idtraspaso]);

        /*if ($error){
            return $this->redirect(['/programacionentregamercancia/index']); 
        }*/

        $ultimo_codigo = $codigoEAN;
        // $count = 1;

        // $cantidad_paquetes = $totalRegistros + 1;

        $count = Traspasodetalle::find()
            ->alias('td')
            ->select([
                'total' => new \yii\db\Expression('SUM(
                            CASE
                                WHEN ue.equivalencia IS NOT NULL THEN td.cantidad * ue.equivalencia
                                ELSE td.cantidad
                            END
                        )')
            ])
            ->innerJoin('item as it', 'td.idItem = it.id')
            ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
            ->where(['idTraspaso' => $idtraspaso])
            ->scalar();

        $cantidad_paquetes = Traspasodetalle::find()
            ->select(['total_cantidad' => new \yii\db\Expression('SUM(cantidad)')])
            ->where(['idTraspaso' => $idtraspaso])
            ->scalar();

        // Aquí puedes procesar los datos del formulario como lo necesites
        return [
            'success' => true,
            'message' => 'Datos procesados correctamente',
            'count' => $count,
            'ultimo_codigo' => $ultimo_codigo,
            'cantidad_paquetes' => $cantidad_paquetes,
        ];


    }

    public function actionProcesarFormularioV1($idtraspaso)
    {

        // Valida que la solicitud sea AJAX

        if (Yii::$app->request->isAjax) {
            // recibo datos del ajax

            $codigoBarras = Yii::$app->request->post('codigoBarras');

            $total = Yii::$app->request->post('total');

            $totalRegistros = Yii::$app->request->post('totalRegistros');

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

            // Verifico que el codigo de barras este activo

            $modelitem = Item::find()
                ->where(['codigoBarras' => $codigoBarras])
                ->andWhere(['idEstado' => 'ACTIVO'])
                ->one();

            // si esta nulo es por que no existe este codigo de barras en siesa y muestro el mensaje

            if ($modelitem == null) {

                Yii::$app->session->setFlash('error', 'No existe codigo de barras: ' . $codigoBarras);

            } else {

                // encuentro mi modelo traspaso

                $modeltraspaso = Traspaso::findOne(['id' => $idtraspaso]);

                // Almaceno  el id del ultimo item que este en estado activo en el traspaso

                $modeltraspaso->idUltimoItem = $modelitem->id;

                // Busco el traspasodetalle que tenga el id y un item especifico

                $modeldetalle = Traspasodetalle::find()->where([
                    'idTraspaso' => $idtraspaso,
                    'idItem' => $modelitem->id
                ])->one();

                // si no existe creo uno

                if ($modeldetalle == null) {
                    $modeldetalle = new Traspasodetalle();
                    $modeldetalle->idTraspaso = $idtraspaso;
                    $modeldetalle->idItem = $modelitem->id;
                    $modeldetalle->cantidad = 0;
                }

                // Verificar si unidadempaque y unidadorden está vacía o es nula
                if (empty($modeldetalle->item->unidadempaque) && empty($modeldetalle->item->unidadorden)) {

                    Yii::trace('unidadempaque y unidadorden están vacías o nulas.', __METHOD__);

                    Yii::$app->session->setFlash('error', ' El codigo de barras no tiene equivalencia : ' . $codigoBarras);

                    return [
                        'success' => false,
                        'error' => ' El item no tiene equivalencia : ' . $modelitem->item
                        // . $modeldetalle->errors . ' - ' . $modeltraspaso->errors
                    ];

                }

                // verifico que en siesa el codigo de barras exista en la bodega origen

                // $inventario = $modeldetalle->getInventario($codigoBarras, $modeltraspaso->bodegaOrigen->codigo);

                // para probar sin consultar inventario podemos comentar la 320 y descomentar la 323

                $inventario = 1;

                if ($inventario > 0) {

                    // En caso de existir entonces sumo 1 por pistoliada

                    $modeldetalle->codigoitem = $modelitem->id;
                    $modeldetalle->cantidad = $modeldetalle->cantidad + 1;

                    // guardo y dejo mensajes

                    if ($modeldetalle->save() && $modeltraspaso->save()) {

                        // parametros que usa el modelo

                        $searchModel = new TraspasodetalleSearch();
                        $dataProvider = $searchModel->search($this->request->queryParams, $idtraspaso);

                        // asigno ultimo codigo escaneado

                        $ultimo_codigo = $codigoBarras;

                        // encuentro cuantos registros hay en el momento

                        // Si tomo el total de el doAjax y aumento en cantidad , deberia poder comerme esta consulta

                        // $cantidad_paquetes = Traspasodetalle::find()
                        //     ->select(['total_cantidad' => new \yii\db\Expression('SUM(cantidad)')])
                        //     ->where(['idTraspaso' => $idtraspaso])
                        //     ->scalar();

                        $cantidad_paquetes = $totalRegistros + 1;

                        // encuentro cuantos items en total llevo

                        // Tomo el total que se consulto al hacer el doajax y en procesar 
                        // Si aumento 1 cada pistolaso , creo que no necesito hacer esta consulta, deberia probar, lo envio por el ajax y aumento aca

                        //     $count = Traspasodetalle::find()
                        //         ->alias('td')
                        //         ->select([
                        //             'total' => new \yii\db\Expression('SUM(
                        //     CASE
                        //         WHEN ue.equivalencia IS NOT NULL THEN td.cantidad * ue.equivalencia
                        //         ELSE td.cantidad
                        //     END
                        // )')
                        //         ])
                        //         ->innerJoin('item as it', 'td.idItem = it.id')
                        //         ->leftJoin('unidadEmpaque as ue', 'ue.codigo = it.unidadEmpaque')
                        //         ->where(['idTraspaso' => $idtraspaso])
                        //         ->scalar();

                        $equivalencia = 1;

                        if ($modeldetalle->item->unidadempaque) {
                            $equivalencia = $modeldetalle->item->unidadempaque->equivalencia;
                        } else {
                            $equivalencia = $modeldetalle->item->unidadorden->equivalencia;
                        }

                        $count = $total + $equivalencia;

                        // Yii::$app->session->setFlash('success', $equivalencia);

                        Yii::trace('Modelo guardado correctamente', __METHOD__);

                        Yii::$app->session->setFlash('success', 'Guardado exitosamente!');

                        // retorno variables a usar en el ajax

                        return [
                            'success' => true,
                            'message' => 'Datos procesados correctamente',
                            // 'model' => $modeltraspaso,
                            'searchModel' => $searchModel,
                            'dataProvider' => $dataProvider,
                            'count' => $count,
                            'ultimo_codigo' => $ultimo_codigo,
                            'cantidad_paquetes' => $cantidad_paquetes,
                        ];

                    } else {

                        // si no puedo guardar dejo mensajes para revisar

                        Yii::error('El modelo no es válido. Verifica los datos.', __METHOD__);

                        Yii::$app->session->setFlash('error', 'El modelo no es válido, verifica los datos.' . __METHOD__);

                        return ['success' => false, 'errors' => $modeldetalle->errors . ' - ' . $modeltraspaso->errors];

                    }

                } else {

                    Yii::$app->session->setFlash('error', 'Articulo sin existencia para traspaso: ' . $modelitem->id . ' en bodega ' . $modeltraspaso->bodegaOrigen->nombre . ' inventario ' . $inventario);

                }

            }

        } else {
            throw new \yii\web\BadRequestHttpException('Solicitud no válida');
        }

    }



    /**
     * Updates an existing Traspasodetalle model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $searchModel = new TraspasodetalleSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, $id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionEnd($idtraspaso)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = Traspaso::findOne(['id' => $idtraspaso]);
        $tipoDocumento = Tipodocumento::findOne(['id' => $model->idTipoDocumento]);

        $model->idEstado = 1;
        $model->consecutivo = $tipoDocumento->consecutivoProximo;

        $model->save();

        $tipoDocumento->consecutivoProximo += 1;
        $tipoDocumento->save();

        $modeldetalles = $model->traspasodetalles;

        $model = Traspaso::findOne(['id' => $idtraspaso]);
        $isMobile = Device::$isMobile;
        $impresoras = Impresora::getListaData();

        // $nombreEmpresa = Parametroscontrol::getValorparametro('001');
        // $nitEmpresa = Parametroscontrol::getValorparametro('002');
        // $direccion = Parametroscontrol::getValorparametro('003');
        // $telefono = Parametroscontrol::getValorparametro('004');
        // $email = Parametroscontrol::getValorparametro('006');
        // $ciudad = Parametroscontrol::getValorparametro('007');
        // $paginaweb = Parametroscontrol::getValorparametro('008');

        return $this->render('view_recibo', [
            'model' => $model,
            'modeldetalles' => $modeldetalles,
            'isMobile' => $isMobile,
            'impresoras' => $impresoras,
        ]);
    }

    public function actionPrint($idtraspaso)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $model = Traspaso::findOne(['id' => $idtraspaso]);
        $modeldetalles = $model->traspasodetalles;

        $impresoras = Impresora::getListaData();
        // $isMobile = Device::$isMobile;
        $isMobile = true;

        return $this->render('view_recibo', [
            'model' => $model,
            'modeldetalles' => $modeldetalles,
            'impresoras' => $impresoras,
            'isMobile' => $isMobile,
        ]);
    }

    public function actionImpresion()
    {
        Yii::info('llego hasta impresion.', __METHOD__);

        $impresoraSeleccionada = Yii::$app->request->post('impresoraSeleccionada');
        $idTraspaso = Yii::$app->request->post('idTraspaso');

        $model = Traspaso::findOne(['id' => (int) $idTraspaso]);
        $modeldetalles = $model->traspasodetalles;

        // $isMobile = Device::$isMobile;
        $isMobile = null;

        $impresora = Impresora::findOne(['id' => (int) $impresoraSeleccionada]);

        $connector = new NetworkPrintConnector($impresora->ip, "9100");
        $printer = new Printer($connector);
        Yii::error('No es error, impresora :   ' . $impresora->ip, __METHOD__);

        try {
            //conectarse a la impresora

            //variables para factura
            $totalGeneral = 0;
            $totalPaquetes = 0;
            $serie = $model->tipodocumento->codigo;
            $numero_serie = $model->codigoerp ? $model->codigoerp->f350_consec_docto : $model->consecutivo;
            $categoria = '';
            $descipcion = '';
            $isMobile = $isMobile ? 'PKM' : 'PC';
            //primera parte de la factura
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->text(Yii::$app->params['tituloTraspaso'] . "\n \n");
            $printer->selectPrintMode();

            $printer->text(Yii::$app->params['grupo'] . "\n");
            $printer->text("NIT: " . Yii::$app->params['nit'] . " \n");
            $printer->text("Direccion: " . Yii::$app->params['direccion'] . ' ' . "TEL: " . Yii::$app->params['tel'] . "\n");
            $printer->text("__________________________________________\n");
            $printer->text("SERIE: " . $serie . "   NUMERO: " . $numero_serie . "   CAJA:" . $isMobile . "\n");
            $printer->text("FECHA:" . Yii::$app->formatter->asDatetime($model->updated_at, 'php:d-m-Y H:i:s') . "\n");
            $printer->text("ORIGEN:" . trim($model->bodegaOrigen->codigo) . ' ' . $model->bodegaOrigen->nombre . "\n");
            $printer->text("DESTINO:" . trim($model->bodegaDestino->codigo) . ' ' . $model->bodegaDestino->nombre . "\n");
            $printer->text("USUARIO:" . $model->usuario->username . "\n");
            $printer->text("________________________________________\n");
            // Encabezados lista items
            $encabezados = "REFER.  COLOR  TALLA  TIPO  CANT  TOTAL/UM\n";
            $printer->text($encabezados);

            foreach ($modeldetalles as $detalle) {
                // Obtener los datos del detalle
                $categoria = explode(' ', $detalle->item->categoria->nombre)[0];
                $descipcion = explode(' ', $detalle->item->descripcion)[0];
                $referencia = $detalle->item->item;
                $color = explode(' ', $detalle->item->color->nombre)[0];
                $talla = $detalle->item->talla->nombre;
                $cantidad = $detalle->cantidad;
                $total = $cantidad * ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->equivalencia : 1);
                $tipo = ($detalle->item->unidadempaque ? $detalle->item->unidadempaque->codigo : $detalle->item->unidadOrden);

                // Formatear el texto del detalle
                $detalleText = sprintf("%-7s %-7s %-6s %-6s %-6s %-1s\n", $referencia, $color, trim($talla), trim($tipo), $cantidad, $total);

                // Imprimir el detalle
                $printer->text($detalleText);

                // Sumar al total
                $totalPaquetes += $cantidad;
                $totalGeneral += $total;

                // Imprimir la descripción del artículo concatenada con categoria
                $printer->text($categoria . ' ' . $descipcion . "\n");
            }
            $printer->text("------------------------------------------\n");
            $totalText = sprintf("Total unidades: %16s %5s\n", $totalPaquetes, $totalGeneral);
            $printer->text($totalText);
            $printer->text("__________________________________________\n\n");


            //IMPRIMIR CODIGO DE BARRAS 1
            $printer->setBarcodeHeight(80);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->barcode("{A" . $serie, Printer::BARCODE_CODE128);
            $printer->feed();

            //Info en medio de los codigos de barras
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->text("NUMERO CAJAS:" . $model->numeroCajas . "\n");
            $printer->text("ORIGEN:" . trim($model->bodegaOrigen->codigo) . '-' . $model->bodegaOrigen->nombre . "\n");
            $printer->text("DESTINO:" . trim($model->bodegaDestino->codigo) . '-' . $model->bodegaDestino->nombre . "\n");
            $printer->selectPrintMode();

            $printer->text("USUARIO:" . $model->usuario->username . "\n\n");


            //IMPRIMIR CODIGO DE BARRAS 2
            $printer->setBarcodeHeight(80);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer->barcode("{A" . $numero_serie, Printer::BARCODE_CODE128);
            $printer->feed();

            return $this->asJson([
                'success' => true,
                'message' => 'Impresión ejecutada correctamente en la impresora: ' . $impresora->ip,
            ]);

        } catch (Exception $e) {
            // Captura de errores específicos como ErrorException
            if (strpos($e->getMessage(), 'trim(): Passing null to parameter #1') !== false) {
                // Maneja el caso específico de null en funciones
                Yii::error('Se intentó usar una función con un valor null.', __METHOD__);
                Yii::$app->session->setFlash(
                    'error',
                    'Se intentó usar una función con un valor null, por favor verifica los datos. '
                    . $e->getFile() . ' linea ' . $e->getLine()
                );
            } else {
                // Maneja otros tipos de ErrorException
                Yii::error('Error general: ' . $e->getMessage(), __METHOD__);
                Yii::$app->session->setFlash(
                    'error',
                    'Error general: ' . $e->getMessage()
                );
            }
            return ['success' => false, $this->redirect(Yii::$app->request->referrer)];

        } finally {

            Yii::trace('Impresion finalizada ' . $impresora->ip);

            $printer->cut();
            $printer->close();

        }

    }
    /**
     * Deletes an existing Traspasodetalle model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id, $idtraspaso)
    {
        $model = $this->findModel($id, $idtraspaso);
        $model->codigoitem = $model->item->codigoBarras;

        if ($model->cantidad > 1) {
            $model->cantidad--;
            $model->save();
        } else {
            $model->delete();
        }

        return $this->redirect(['/traspasodetalle/create', 'idtraspaso' => $idtraspaso]);
    }

    /**
     * Finds the Traspasodetalle model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Traspasodetalle the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Traspasodetalle::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('La página solicitada no existe.');
    }

    public function actionGenerararchivo($idtraspaso)
    {

        $traspaso = Traspaso::findOne(['id' => $idtraspaso]);

        // $rutaGuardado = Traspasodetalle::generarArchivotransferencia($traspaso);
        $envio = Traspasodetalle::generarTransferenciaWS($traspaso);


        Yii::$app->session->setFlash('Transferencia', $envio == 1 ? 'Enviado' : 'error');

        return $this->redirect(['traspaso/index']);
    }

}
