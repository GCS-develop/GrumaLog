<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Devoluciondocumentodetalle;
use yii\db\Query;

/**
 * DevoluciondocumentodetalleSearch represents the model behind the search form of `frontend\models\Devoluciondocumentodetalle`.
 */
class DevoluciondocumentodetalleSearch extends Devoluciondocumentodetalle
{
    public $fechaDesde;
    public $fechaHasta;
    public $nombreProveedor;
    public $tipoInventario;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idDocumento', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'codigoBarras',
                    'item',
                    'talla',
                    'color',
                    'referencia',
                    'itemResumen',
                    'created_at',
                    'updated_at',
                    'numeroDocumento',
                    'codigoBodegaSalida',
                    'unidadMedida',
                    'fechaRegistra',
                    'fechaDesde',
                    'fechaHasta',
                    'nombreProveedor',
                    'tipoInventario'
                ],
                'safe'
            ],
            [['cantidadDevolucion', 'cantidadRegistrada', 'registrada'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Normal search (individual details)
     */
    public function search($params, $iddocumento = null, $enviosiesa = false)
    {
        if ($iddocumento == null) {
            $query = Devoluciondocumentodetalle::find()->alias('det');
        } else {
            $query = Devoluciondocumentodetalle::find()->alias('det')->where(['idDocumento' => $iddocumento]);
        }

        $query->join('INNER JOIN', 'devoluciondocumento dct', 'det.idDocumento = dct.id');
        $query->join('LEFT JOIN', 'item i', 'det.item = i.item');
        $query->distinct();

        $query->select([
            'det.id',
            'det.codigoBarras',
            'det.item',
            'det.talla',
            'det.color',
            'det.referencia',
            'det.itemResumen',
            'det.unidadMedida',
            'det.cantidadDevolucion',
            'det.cantidadRegistrada',
            'det.created_at',
            'det.created_by',
            'det.updated_at',
            'det.updated_by',
            'det.registrada',
            'det.fechaRegistra',
            'det.usuarioRegistra',
            'dct.numeroDocumento',
            'dct.codigoBodegaSalida',
            "COALESCE(i.nombreProveedor, 'Proveedor No Asignado') AS nombreProveedor",
     
        ]);

        $query->orderBy([
            'dct.codigoBodegaSalida' => SORT_ASC,
            'dct.numeroDocumento' => SORT_ASC
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

         /* 3) Filtro por VMI / FIRME --------------------------------------------------- */
   /* if (!empty($this->tipoInventario)) {
    $query->andWhere(['i.tipoInventario' => $this->tipoInventario]);
}*/


        $query->andFilterWhere([
            'det.id' => $this->id,
            'det.idDocumento' => $this->idDocumento,
            'det.cantidadDevolucion' => $this->cantidadDevolucion,
            'det.cantidadRegistrada' => $this->cantidadRegistrada,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
            'det.registrada' => $this->registrada,
            'det.unidadMedida' => $this->unidadMedida,
            'dct.numeroDocumento' => $this->numeroDocumento,
            'dct.codigoBodegaSalida' => $this->codigoBodegaSalida,
            
            
        ]);

        if ($this->fechaDesde || $this->fechaHasta) {
            $fechaInicio = $this->fechaDesde ? date('Y-m-d', strtotime($this->fechaDesde)) : null;
            $fechaFin = $this->fechaHasta ? date('Y-m-d', strtotime($this->fechaHasta)) : null;

            if ($fechaInicio && !$fechaFin) {
                $query->andWhere(['>=', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaInicio]);
            } elseif (!$fechaInicio && $fechaFin) {
                $query->andWhere(['<=', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaFin]);
            } elseif ($fechaInicio && $fechaFin) {
                $query->andWhere(['between', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaInicio, $fechaFin]);
            }
        }

        if (!empty($this->fechaRegistra)) {
            $fecha = date('Y-m-d', strtotime($this->fechaRegistra));
            $query->andWhere("CAST(det.fechaRegistra AS DATE) = :fecha", [':fecha' => $fecha]);
        }

        if (!empty($this->nombreProveedor)) {
            $query->andWhere(['like', 'i.nombreProveedor', $this->nombreProveedor]);
        }

        $query->andFilterWhere(['like', 'det.codigoBarras', $this->codigoBarras])
            ->andFilterWhere(['like', 'det.item', $this->item])
            ->andFilterWhere(['like', 'det.talla', $this->talla])
            ->andFilterWhere(['like', 'det.color', $this->color])
            ->andFilterWhere(['like', 'det.referencia', $this->referencia])
            ->andFilterWhere(['like', 'det.itemResumen', $this->itemResumen]);

               // ✅ Filtro de tipoInventario (post-procesamiento)
    if (!empty($this->tipoInventario)) {
        $models = $dataProvider->getModels();
        $filtered = array_filter($models, function ($model) {
            return $model->getTipoInventario() === $this->tipoInventario;
        });
        $dataProvider->setModels(array_values($filtered));
        $dataProvider->setTotalCount(count($filtered));
    }

        return $dataProvider;
    }



















    
    /**
     * Envios IESA search (grouped by provider)
     */
/*public function searchenviosiesa($params)
{
    $query = Devoluciondocumentodetalle::find()->alias('det');

    $query->where(['!=', 'det.registrada', 0]);

    $query->join('INNER JOIN', 'devoluciondocumento dct', 'det.idDocumento = dct.id');
    $query->join('LEFT JOIN', 'item i', 'det.item = i.item');

    $query->select([
        'dct.id',
        'dct.numeroDocumento',
        'COALESCE(i.nombreProveedor, \'Proveedor No Asignado\') AS nombreProveedor',
        'SUM(det.cantidadDevolucion) AS cantidadDevolucion',
        'SUM(det.cantidadRegistrada) AS cantidadRegistrada',
        'COUNT(DISTINCT det.id) AS totalItems',
        // Formato para SQL Server: 'YYYY-MM-DD HH:MI'
        'CONVERT(VARCHAR(16), MIN(det.fechaRegistra), 120) AS fechaRegistra',
    ]);

    $query->groupBy(['i.nombreProveedor', 'dct.numeroDocumento',  'dct.id',]);
    $query->orderBy(['nombreProveedor' => SORT_ASC]);

    $this->load($params);

    if (!$this->validate()) {
        return new ActiveDataProvider(['query' => $query]);
    }

    if (!empty($this->nombreProveedor)) {
        $query->andWhere(['like', 'i.nombreProveedor', $this->nombreProveedor]);
    }

    if ($this->fechaDesde || $this->fechaHasta) {
        $fechaInicio = $this->fechaDesde ? date('Y-m-d', strtotime($this->fechaDesde)) : null;
        $fechaFin = $this->fechaHasta ? date('Y-m-d', strtotime($this->fechaHasta)) : null;

        $query->andFilterWhere([
            'between', 
            new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), 
            $fechaInicio, 
            $fechaFin
        ]);
    }

    return new ActiveDataProvider([
        'query' => $query,
        'pagination' => ['pageSize' => 50],
        'sort' => [
            'attributes' => [
                'numeroDocumento',
                'nombreProveedor',
                'cantidadDevolucion',
                'cantidadRegistrada',
                'totalItems',
                'fechaRegistra'
            ],
        ],
    ]);
}*/
}
