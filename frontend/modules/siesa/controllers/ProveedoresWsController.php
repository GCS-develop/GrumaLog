<?php

namespace frontend\modules\siesa\controllers;

use Yii;
use common\models\ProveedoresWs;
use common\models\search\ProveedoresWsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProveedoresWsController implements the CRUD actions for ProveedoresWs model.
 */
class ProveedoresWsController extends Controller
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
     * Lists all ProveedoresWs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProveedoresWsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $this->request->queryParams,
        ]);
    }

    /**
     * Creates a new ProveedoresWs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionSincronizarerp($nit = null)
    {
        $respuesta = ProveedoresWs::sincronizarERP($nit);

        if ($respuesta){
            Yii::$app->session->setFlash('success', 'Sincronización ha finalizado correctamente.');
        }else{
            Yii::$app->session->setFlash('error', 'Sincronización ha fallado.');
        }
        return $this->redirect(['index']);
    }

    /**
     * Sincroniza TODOS los proveedores directamente desde SIESA DB.
     * 1 query masiva en vez de llamadas individuales por NIT.
     */
    public function actionSincronizarsiesa()
    {
        $sql = "
            SELECT DISTINCT
                ter.f200_nit                       AS nit,
                ter.f200_razon_social              AS razonSocial,
                CASE ter.f200_id_tipo_ident
                    WHEN 'C' THEN 'CC'
                    WHEN 'N' THEN 'NIT'
                    WHEN 'E' THEN 'CE'
                    ELSE ter.f200_id_tipo_ident
                END                                AS tipoIdentificacion,
                prv.f202_id_sucursal               AS sucursal,
                prv.f202_descripcion_sucursal      AS descripcionSucursal,
                TRIM(prvc.f221_id_criterio_mayor)  AS criterioMercancia
            FROM t202_mm_proveedores prv
            INNER JOIN t200_mm_terceros ter
                ON prv.f202_rowid_tercero = ter.f200_rowid
            INNER JOIN t221_mm_criterios_proveedores prvc
                ON prv.f202_rowid_tercero = prvc.f221_rowid_tercero
               AND prv.f202_id_sucursal   = prvc.f221_id_sucursal
               AND prvc.f221_id_plan_criterios = '001'
            WHERE ter.f200_id_cia = 1
              AND TRIM(prvc.f221_id_criterio_mayor) IN ('0001', '0002')
            ORDER BY ter.f200_razon_social
        ";

        try {
            $proveedoresSiesa = Yii::$app->dbSiesa->createCommand($sql)->queryAll();
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Error al consultar SIESA: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        $insertados = 0;
        $actualizados = 0;
        $errores = 0;

        foreach ($proveedoresSiesa as $prov) {
            $nit     = trim($prov['nit']);
            $sucursal = trim($prov['sucursal'] ?? '');

            if (empty($nit)) continue;

            $model = \frontend\models\Proveedor::findOne(['nit' => $nit, 'sucursal' => $sucursal]);
            $esNuevo = ($model === null);

            if ($esNuevo) {
                $model = new \frontend\models\Proveedor();
                $model->idProveedor = $nit;
                $model->nit         = $nit;
                $model->sucursal    = $sucursal;
            }

            $model->razonSocial         = $prov['razonSocial'];
            $model->tipoIdentificacion  = $prov['tipoIdentificacion'] ?? null;
            $model->descripcionSucursal = $prov['descripcionSucursal'] ?? null;
            $model->criterioMercancia   = $prov['criterioMercancia'] ?? null;

            if ($model->save()) {
                $esNuevo ? $insertados++ : $actualizados++;
            } else {
                $errores++;
            }
        }

        Yii::$app->session->setFlash(
            $errores === 0 ? 'success' : 'warning',
            "Sincronización SIESA completada: $insertados nuevos, $actualizados actualizados, $errores errores."
        );

        return $this->redirect(['index']);
    }


}
