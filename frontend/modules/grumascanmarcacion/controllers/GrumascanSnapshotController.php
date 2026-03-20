<?php

namespace frontend\modules\grumascanmarcacion\controllers;

use frontend\models\Bodegas;
use frontend\models\Grumascanconteo;
use frontend\models\GrumascanSnapshot;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use Yii;

class GrumascanSnapshotController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete'    => ['POST'],
                    'desasignar' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista todos los snapshots, más recientes primero.
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => GrumascanSnapshot::find()->orderBy(['fecha_snapshot' => SORT_DESC, 'id' => SORT_DESC]),
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Formulario para crear un nuevo snapshot.
     * POST: guarda cabecera → captura Siesa → redirige a view.
     */
    public function actionCreate()
    {
        $model    = new GrumascanSnapshot();
        $bodegas  = Bodegas::find()->orderBy(['nombre' => SORT_ASC])->all();

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            $model->idbodega     = $post['idbodega']    ?? null;
            $model->codigoBodega = trim($post['codigoBodega'] ?? '');
            $model->descripcion  = trim($post['descripcion'] ?? '');
            $model->created_by   = Yii::$app->user->id;

            // Normalizar código a 3 dígitos si es numérico
            if (ctype_digit($model->codigoBodega)) {
                $model->codigoBodega = str_pad($model->codigoBodega, 3, '0', STR_PAD_LEFT);
            }

            if (empty($model->codigoBodega)) {
                Yii::$app->session->setFlash('error', 'Debe indicar el código de bodega.');
                return $this->render('create', compact('model', 'bodegas'));
            }

            // Guardar cabecera primero para obtener el ID
            if (!$model->save()) {
                Yii::$app->session->setFlash('error', 'Error al guardar: ' . json_encode($model->getFirstErrors(), JSON_UNESCAPED_UNICODE));
                return $this->render('create', compact('model', 'bodegas'));
            }

            // Capturar inventario Siesa
            $result = $model->capturarDesdeSiesa();

            if (!empty($result['errors'])) {
                Yii::$app->session->setFlash('warning',
                    'Snapshot creado con advertencias: ' . implode(' | ', $result['errors'])
                );
            } else {
                Yii::$app->session->setFlash('success',
                    "Snapshot #{$model->id} creado: {$result['rows']} items capturados de Siesa."
                );
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', compact('model', 'bodegas'));
    }

    /**
     * Ver un snapshot: detalle + conteos asignados + formulario para asignar por rango de fechas.
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Conteos ya asignados a este snapshot
        $conteosAsignados = Grumascanconteo::find()
            ->with(['marcacion.bodega', 'estado'])
            ->where(['idSnapshot' => $model->id])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        // Conteos disponibles (bodega coincide, estado=1, sin snapshot)
        $conteosDisponibles = Grumascanconteo::find()
            ->with(['marcacion.bodega', 'estado'])
            ->innerJoin('grumascanmarcacion m', 'm.id = grumascanconteo.idmarcacion')
            ->innerJoin('bodegas b', 'b.id = m.idbodega')
            ->where(['grumascanconteo.idestado' => 1, 'grumascanconteo.idSnapshot' => null])
            ->andWhere(new \yii\db\Expression("
                CASE
                    WHEN TRY_CONVERT(int, LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))) IS NOT NULL
                        THEN RIGHT('000' + LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50)))), 3)
                    ELSE LTRIM(RTRIM(CAST(b.codigo AS NVARCHAR(50))))
                END = '{$model->codigoBodega}'
            "))
            ->orderBy(['grumascanconteo.created_at' => SORT_DESC])
            ->limit(200)
            ->all();

        return $this->render('view', compact('model', 'conteosAsignados', 'conteosDisponibles'));
    }

    /**
     * Asigna conteos a este snapshot.
     * Acepta: por rango de fechas O por IDs individuales (checkboxes).
     */
    public function actionAsignarConteos($id)
    {
        $model = $this->findModel($id);
        $post  = Yii::$app->request->post();

        $modo         = $post['modo'] ?? 'fecha';
        $sobrescribir = (bool)($post['sobrescribir'] ?? false);

        if ($modo === 'fecha') {
            $desde = trim($post['desde'] ?? '');
            $hasta = trim($post['hasta'] ?? '');

            if (!$desde || !$hasta) {
                Yii::$app->session->setFlash('error', 'Debe indicar fecha desde y hasta.');
                return $this->redirect(['view', 'id' => $id]);
            }

            $rows = $model->asignarConteosPorFecha($desde, $hasta, $sobrescribir);
            Yii::$app->session->setFlash('success', "Asignados {$rows} conteos al snapshot #{$id}.");

        } elseif ($modo === 'ids') {
            $ids = array_filter(array_map('intval', (array)($post['conteo_ids'] ?? [])));

            if (empty($ids)) {
                Yii::$app->session->setFlash('warning', 'No seleccionó ningún conteo.');
                return $this->redirect(['view', 'id' => $id]);
            }

            $rows = Yii::$app->db->createCommand()
                ->update('grumascanconteo', ['idSnapshot' => $model->id], ['id' => $ids])
                ->execute();
            Yii::$app->session->setFlash('success', "Asignados {$rows} conteos al snapshot #{$id}.");
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Quita el snapshot de uno o varios conteos (los deja sin snapshot).
     */
    public function actionDesasignar($id)
    {
        $model = $this->findModel($id);
        $ids   = array_filter(array_map('intval', (array)(Yii::$app->request->post('conteo_ids') ?? [])));

        if (empty($ids)) {
            Yii::$app->session->setFlash('warning', 'No seleccionó ningún conteo.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Verificar que los conteos pertenezcan a este snapshot
        $rows = Yii::$app->db->createCommand()
            ->update('grumascanconteo', ['idSnapshot' => null], ['id' => $ids, 'idSnapshot' => $model->id])
            ->execute();

        Yii::$app->session->setFlash('success', "Desasignados {$rows} conteos del snapshot #{$id}.");
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Elimina un snapshot (solo si no tiene conteos asignados).
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $conteos = Grumascanconteo::find()->where(['idSnapshot' => $id])->count();
        if ($conteos > 0) {
            Yii::$app->session->setFlash('error', "No se puede eliminar: {$conteos} conteos están asignados a este snapshot. Desasígnalos primero.");
            return $this->redirect(['view', 'id' => $id]);
        }

        // Borrar detalles y cabecera
        Yii::$app->db->createCommand()->delete('grumascan_snapshot_detalle', ['idSnapshot' => $id])->execute();
        $model->delete();

        Yii::$app->session->setFlash('success', "Snapshot #{$id} eliminado.");
        return $this->redirect(['index']);
    }

    protected function findModel($id): GrumascanSnapshot
    {
        if (($model = GrumascanSnapshot::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Snapshot no encontrado.');
    }
}
