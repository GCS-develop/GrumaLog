<?php
namespace frontend\modules\contabilidad\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\data\SqlDataProvider;
use yii\filters\AccessControl;
use yii\base\Model;

use frontend\models\DocumentoGasto;
use frontend\models\MovimientoGasto;
use frontend\models\GrupoConceptoCuenta;
use frontend\modules\ventas\models\Transferencia;

class GastotiendasController extends Controller
{
    private const UN_FIJA  = '99';
    private const TIPO_DOC = '22';

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index','create-all','enviar-siesa'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    /** ====== LISTA DOCUMENTOS ====== */
   /** ====== LISTA DOCUMENTOS ====== */
public function actionIndex()
{
    $idUser    = Yii::$app->user->id;
    $coUsuario = \common\models\User::getCodigoCOUsuario($idUser);

    $filtroCO = Yii::$app->request->get('F350_ID_CO'); // 👈 Recuperamos el filtro desde la URL

    if ($coUsuario) {
        $sql = "SELECT F350_ID_CO,F350_ID_TIPO_DOCTO,F350_CONSEC_DOCTO,
                       F350_FECHA,F350_ID_TERCERO,F350_IND_ESTADO,
                       F350_NOTAS,ID_TRANSACCION,estado_envio
                  FROM DocumentoGasto
                 WHERE F350_ID_CO = :co";
        $countSql = "SELECT COUNT(1) FROM DocumentoGasto WHERE F350_ID_CO = :co";
        $params   = [':co' => $coUsuario];
    } else {
        $sql      = "SELECT F350_ID_CO,F350_ID_TIPO_DOCTO,F350_CONSEC_DOCTO,
                            F350_FECHA,F350_ID_TERCERO,F350_IND_ESTADO,
                            F350_NOTAS,ID_TRANSACCION,estado_envio
                       FROM DocumentoGasto WHERE 1=1";
        $countSql = "SELECT COUNT(1) FROM DocumentoGasto WHERE 1=1";
        $params   = [];

        if (!empty($filtroCO)) {
            $sql      .= " AND F350_ID_CO = :co";
            $countSql .= " AND F350_ID_CO = :co";
            $params[':co'] = $filtroCO;
        }
    }

    $totalCount = (int)Yii::$app->db->createCommand($countSql, $params)->queryScalar();

    $dataProvider = new \yii\data\SqlDataProvider([
        'db'         => Yii::$app->db,
        'sql'        => $sql,
        'params'     => $params,
        'totalCount' => $totalCount,
        'pagination' => ['pageSize' => 20],
        'sort'       => [
            'attributes'   => ['ID_TRANSACCION','F350_FECHA','F350_ID_TERCERO'],
            'defaultOrder' => ['ID_TRANSACCION' => SORT_DESC],
        ],
    ]);

    // 🔥 Recuperar lista de COs para el combo
    $cos = Yii::$app->db->createCommand("
        SELECT DISTINCT F350_ID_CO 
        FROM DocumentoGasto 
        ORDER BY F350_ID_CO
    ")->queryColumn();

    return $this->render('index', [
        'dataProvider' => $dataProvider,
        'filtroCO'     => $filtroCO, // 👈 lo pasamos
        'cos'          => $cos,      // 👈 también la lista
    ]);
}




    /** ====== VER DOCUMENTO ====== */
   public function actionView($id)
{
    $idUser    = Yii::$app->user->id;
    $coUsuario = \common\models\User::getCodigoCOUsuario($idUser);

    $query = DocumentoGasto::find()->where(['ID_TRANSACCION' => $id]);

    if ($coUsuario) {
        $query->andWhere(['F350_ID_CO' => $coUsuario]);
    }

    $cabecera = $query->one();

    if (!$cabecera) {
        throw new \yii\web\ForbiddenHttpException('No tiene permisos para ver este documento.');
    }

  $detalle = MovimientoGasto::find()
    ->where(['ID_TRANSACCION' => $cabecera->ID_TRANSACCION])
    ->all();


    return $this->render('view', [
        'cabecera' => $cabecera,
        'detalle'  => $detalle,
    ]);
}




    /** ====== CREAR (CABECERA + DETALLE) ====== */
public function actionCreateAll()
{
    $idUser    = Yii::$app->user->id;
    $coUsuario = \common\models\User::getCodigoCOUsuario($idUser);

    $doc = new DocumentoGasto();
    $doc->F350_ID_TIPO_DOCTO = self::TIPO_DOC;
    $doc->F350_IND_ESTADO    = 1;
    $doc->estado_envio       = 0;

    if ($coUsuario) {
        $doc->F350_ID_CO = $coUsuario;
    }

    $rows = [new MovimientoGasto()];

    if (Yii::$app->request->isPost) {

        $post = Yii::$app->request->post();
        $rows = [];

        foreach ($post['MovimientoGasto'] ?? [] as $i => $data) {
            $rows[$i] = new MovimientoGasto();
        }

        $doc->load($post);

        // Validación de CO vs usuario
        if ($coUsuario && $doc->F350_ID_CO !== $coUsuario) {
            throw new \yii\web\ForbiddenHttpException('No puede crear documentos en este CO.');
        }

        Model::loadMultiple($rows, $post);

        // ===========================
        //  ASIGNAR VALORES A DETALLE
        // ===========================
        foreach ($rows as $r) {

            $valor = (float)($r->valor ?? 0);

            // Naturaleza → Débito o Crédito
            if (strtoupper($r->naturaleza) === 'DEBITO') {
                $r->F351_VALOR_DB = $valor;
                $r->F351_VALOR_CR = 0;
            } else {
                $r->F351_VALOR_DB = 0;
                $r->F351_VALOR_CR = $valor;
            }

            // Valores fijos
            $r->F351_ID_UN = self::UN_FIJA;
            $r->F351_ID_FE = '0';
            $r->F_CIA      = 7;

            // Centro de costo desde tabla de cuentas
            $cuenta = GrupoConceptoCuenta::findOne(['cuenta' => $r->F351_ID_AUXILIAR]);
            $r->F351_ID_CCOSTO = $cuenta ? $cuenta->F351_ID_CCOSTO : null;
        }

        // Validar
        if ($doc->validate() && Model::validateMultiple($rows)) {

            $tx = Yii::$app->db->beginTransaction();
            try {

                // ==============================================
                //  CONSECUTIVO LOCAL → SIEMPRE CRECE (AUTÓNOMO)
                // ==============================================
                $ultimoId = DocumentoGasto::find()
                    ->where([
                        'F350_ID_CO'         => $doc->F350_ID_CO,
                        'F350_ID_TIPO_DOCTO' => self::TIPO_DOC,
                    ])
                    ->max('ID_TRANSACCION');

                $doc->F350_CONSEC_DOCTO = ($ultimoId ? $ultimoId + 1 : 1);

                // Guardar cabecera
                $doc->save(false);

                // Eliminar detalles previos (si existían)
                Yii::$app->db->createCommand()->delete(
                    MovimientoGasto::tableName(),
                    ['ID_TRANSACCION' => $doc->ID_TRANSACCION]
                )->execute();

                // Guardar detalle
                foreach ($rows as $r) {

                    $r->ID_TRANSACCION     = $doc->ID_TRANSACCION;
                    $r->F350_ID_CO         = $doc->F350_ID_CO;
                    $r->F350_ID_TIPO_DOCTO = $doc->F350_ID_TIPO_DOCTO;

                    // 👇 Este es tu consecutivo local CORRECTO
                    $r->F350_CONSEC_DOCTO  = $doc->F350_CONSEC_DOCTO;

                    // Tercero y notas desde cabecera
                    $r->F351_ID_TERCERO = $doc->F350_ID_TERCERO;
                    $r->F351_NOTAS      = $doc->F350_NOTAS;

                    $r->F351_ID_CO_MOV = $doc->F350_ID_CO;

                    $r->save(false);
                }

                $tx->commit();

                Yii::$app->session->setFlash('success', 'Movimiento guardado correctamente.');
                return $this->redirect(['view', 'id' => $doc->ID_TRANSACCION]);

            } catch (\Throwable $e) {
                $tx->rollBack();
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
    }

    return $this->render('create_all_unificado', [
        'doc'  => $doc,
        'rows' => $rows,
    ]);
}







  /** ====== ENVIAR A SIESA ====== */
public function actionEnviarSiesa($id)
{
    $doc = DocumentoGasto::findOne($id);

    if (!$doc) {
        Yii::$app->session->setFlash('error', 'Documento no encontrado.');
        return $this->redirect(['index']);
    }

    if ($doc->estado_envio == 1) {
        Yii::$app->session->setFlash('warning', 'Este documento ya fue enviado.');
        return $this->redirect(['view', 'id' => $id]);
    }

    // ====== JSON ======
    $json = [
        "Documentocontable" => [[
            "F350_ID_CO"        => $doc->F350_ID_CO,
            "F350_ID_TIPO_DOCTO"=> $doc->F350_ID_TIPO_DOCTO,
            "F350_CONSEC_DOCTO" => 1,
            "F350_FECHA"        => str_replace("-", "", $doc->F350_FECHA),
            "F350_ID_TERCERO"   => (string)$doc->F350_ID_TERCERO,
            "F350_NOTAS"        => str_replace(['&','<','>'], [' ',' ',' '], $doc->F350_NOTAS),
        ]],
        "Movimientocontable" => [],
        "Caja"               => [],
    ];

   $movs = MovimientoGasto::find()
    ->where(['ID_TRANSACCION' => $doc->ID_TRANSACCION])
    ->all();


    $totalDebitos = 0;
$totalCreditos = 0;
$feCaja = '1233'; // valor por defecto

foreach ($movs as $m) {
    $coMov = ($m->F351_ID_AUXILIAR === '24082010') ? '002' : $m->F351_ID_CO_MOV;

    $json["Movimientocontable"][] = [
        "F350_ID_CO"         => $m->F350_ID_CO,
        "F350_ID_TIPO_DOCTO" => $m->F350_ID_TIPO_DOCTO,
        "F350_CONSEC_DOCTO"  => 1,
        "F351_ID_AUXILIAR"   => $m->F351_ID_AUXILIAR,
        "F351_ID_TERCERO"    => (string)$m->F351_ID_TERCERO,
        "F351_ID_CO_MOV"     => $coMov,
        "F351_ID_UN"         => self::UN_FIJA,
        "F351_ID_CCOSTO"     => $m->F351_ID_CCOSTO,
        "F351_ID_FE"         => $m->F351_ID_FE,
        "F351_VALOR_DB"      => (int)$m->F351_VALOR_DB,
        "F351_VALOR_CR"      => (int)$m->F351_VALOR_CR,
        "F351_BASE_GRAVABLE" => '1',
        "F351_NOTAS"         => str_replace(['&','<','>'], [' ',' ',' '], $m->F351_NOTAS),
    ];

    $totalDebitos  += (float)$m->F351_VALOR_DB;
    $totalCreditos += (float)$m->F351_VALOR_CR;

    // FE dinámico según la cuenta
    if ($m->F351_ID_AUXILIAR === '280510') {
        $feCaja = '1105';
    } elseif ($m->F351_ID_AUXILIAR === '13659506') {
        $feCaja = '1101';
    }
}

$cajaId = ($doc->F350_ID_CO === '016') ? '000' : '001';

if ($totalDebitos > 0) {
    // Caja crédito (contrapartida de débitos)
    $json["Caja"][] = [
        "CENTRO DE OPERACION" => $doc->F350_ID_CO,
        "F350_ID_TIPO_DOCTO"  => $doc->F350_ID_TIPO_DOCTO,
        "F351_ID_AUXILIAR"    => "11050501",
        "F351_ID_CO_MOV"      => $doc->F350_ID_CO,
        "F351_ID_FE"          => $feCaja,
        "F351_VALOR_DB"       => 0,                 // 👈 siempre enviar
        "F351_VALOR_CR"       => (int)$totalDebitos,
        //"F358_ID_CAJA"        => "001",
        "F358_ID_CAJA"        => $cajaId,  //POR LA TIENDA BASE
        "F358_ID_MEDIOS_PAGO" => "EFE",
    ];
} elseif ($totalCreditos > 0) {
    // Caja débito (contrapartida de créditos)
    $json["Caja"][] = [
        "CENTRO DE OPERACION" => $doc->F350_ID_CO,
        "F350_ID_TIPO_DOCTO"  => $doc->F350_ID_TIPO_DOCTO,
        "F351_ID_AUXILIAR"    => "11050501",
        "F351_ID_CO_MOV"      => $doc->F350_ID_CO,
        "F351_ID_FE"          => $feCaja,
        "F351_VALOR_DB"       => (int)$totalCreditos,
        "F351_VALOR_CR"       => 0,                 // 👈 siempre enviar
        //"F358_ID_CAJA"        => "001",
        "F358_ID_CAJA"        => $cajaId,  
        "F358_ID_MEDIOS_PAGO" => "EFE",
    ];
}



/*// =================== DEPURACIÓN: devolver JSON puro ===================
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return $json;*/


    // ====== Enviar a Siesa ======
    $client = new \yii\httpclient\Client([
        'transport' => 'yii\httpclient\CurlTransport'
    ]);

    $url = 'https://servicios.siesacloud.com/api/siesa/v3.1/conectoresimportar?' . http_build_query([
        'idCompania'      => 8203,
        'idSistema'       => 8203,
        'idDocumento'     => 219051,
        'nombreDocumento' => 'GASTO TIENDAS3',
    ]);

    $response = $client->createRequest()
        ->setMethod('POST')
        ->setUrl($url)
        ->addHeaders([
            'Content-Type' => 'application/json',
            'ConniKey'     => '461ee4af939cfd45fe2a20e596c02346',
            'ConniToken'   => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1laWRlbnRpZmllciI6IjJhNjU1YjdjLTY1ODQtNGMyZS1iYTI3LTMwNGYxNjVmN2U0ZiIsImh0dHA6Ly9zY2hlbWFzLm1pY3Jvc29mdC5jb20vd3MvMjAwOC8wNi9pZGVudGl0eS9jbGFpbXMvcHJpbWFyeXNpZCI6IjVlNmE2OTgyLTMxMTEtNGY2OS1hMjViLWEzOWU1ODI5NmY2MiJ9.l78vnBKJJ15ND7ro-VE52RS7ChSYnIR8Qu8UpvR9obY',
        ])
        ->setOptions([CURLOPT_SSL_VERIFYPEER => false])
        ->setContent(json_encode($json, JSON_UNESCAPED_UNICODE))
        ->send();

    $doc->estado_envio   = $response->isOk ? 1 : 2;
    $doc->respuesta_siesa = $response->getContent();
    $doc->save(false);

    if ($response->isOk) {
        Yii::$app->session->setFlash('success', 'Documento enviado a Siesa.');
    } else {
        Yii::$app->session->setFlash('error', 'Error al enviar: ' . $doc->respuesta_siesa);
    }

    return $this->redirect(['view', 'id' => $id]);
}

    /** ====== CONSULTAS AUXILIARES ====== */
    public function actionCuentasPorGrupo($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $cuentas = \frontend\models\GrupoConceptoCuenta::find()
            ->where(['grupo_id' => $id])
            ->all();

        return array_map(function($cuenta) {
            return [
                'id'   => $cuenta->cuenta,
                'text' => $cuenta->cuenta . ' - ' . $cuenta->descripcion,
            ];
        }, $cuentas);
    }

    public function actionNaturalezaCuenta($cuenta)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $modelo = \frontend\models\GrupoConceptoCuenta::findOne(['cuenta' => $cuenta]);

        if ($modelo === null) {
            return ['success' => false, 'error' => 'Cuenta no encontrada'];
        }

        return ['success' => true, 'naturaleza' => $modelo->naturaleza];
    }



    /** ====== ANULAR DOCUMENTO ====== */
public function actionAnular($id)
{
    $doc = DocumentoGasto::findOne($id);

    if (!$doc) {
        Yii::$app->session->setFlash('error', 'Documento no encontrado.');
        return $this->redirect(['index']);
    }

    // Solo permitir anular si está pendiente (0) o en error (2)
    if (!in_array($doc->estado_envio, [0, 2])) {
        Yii::$app->session->setFlash('warning', 'Solo se pueden anular documentos en estado Pendiente o Error.');
        return $this->redirect(['index']);
    }

    // Cambiar estados
    $doc->F350_IND_ESTADO = 0; // estado lógico
    $doc->estado_envio = 3;    // 👈 estado visual de anulado

    if ($doc->save(false)) {
        Yii::$app->session->setFlash('success', 'Documento anulado correctamente.');
    } else {
        Yii::$app->session->setFlash('error', 'Error al anular el documento.');
    }

    return $this->redirect(['index']);
}



/** ====== EDITAR DETALLES ====== */
/** ====== EDITAR DETALLES ====== */
/** ====== EDITAR DETALLES ====== */
public function actionUpdateDetalle($id)
{
    $doc = DocumentoGasto::findOne($id);

    if (!$doc) {
        Yii::$app->session->setFlash('error', 'Documento no encontrado.');
        return $this->redirect(['index']);
    }

    // Solo permitir editar si está pendiente o error
    if ($doc->estado_envio == 1 || $doc->F350_IND_ESTADO == 0) {
        Yii::$app->session->setFlash('warning', 'No se pueden editar documentos enviados o anulados.');
        return $this->redirect(['view', 'id' => $id]);
    }

    $detalle = MovimientoGasto::find()
        ->where([
            'F350_ID_CO'         => $doc->F350_ID_CO,
            'F350_ID_TIPO_DOCTO' => $doc->F350_ID_TIPO_DOCTO,
            'ID_TRANSACCION'     => $id,
        ])->all();

    if (Yii::$app->request->isPost) {
        $post = Yii::$app->request->post();

        if (\yii\base\Model::loadMultiple($detalle, $post)) {
            foreach ($detalle as $row) {
                $valor = (float)($row->valor ?? 0);

                // Usar naturaleza enviada desde el formulario
                if (strtoupper($row->naturaleza) === 'DEBITO') {
                    $row->F351_VALOR_DB = $valor;
                    $row->F351_VALOR_CR = 0;
                } elseif (strtoupper($row->naturaleza) === 'CREDITO') {
                    $row->F351_VALOR_DB = 0;
                    $row->F351_VALOR_CR = $valor;
                } else {
                    $row->F351_VALOR_DB = 0;
                    $row->F351_VALOR_CR = 0;
                }

                $row->save(false);
            }

            Yii::$app->session->setFlash('success', 'Detalles actualizados correctamente.');
            return $this->redirect(['view', 'id' => $id]);
        }
    }

    return $this->render('update_detalle', [
        'doc' => $doc,
        'detalle' => $detalle,
    ]);
}








}
