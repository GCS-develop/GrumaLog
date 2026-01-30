<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Devoluciondocumentodetalle;
use yii\db\Query;
use frontend\models\Bodegas;

class DevoluciondocumentodetalleSearch extends Devoluciondocumentodetalle
{
    public $fechaDesde;
    public $fechaHasta;
    public $nombreProveedor;
    public $tipoInventario;
    public $nombreBodega; // 👈 añadimos este atributo para la búsqueda

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
                    'tipoInventario',
                    'usuarioRegistra',
                    'nombreBodega', // 👈 nueva regla safe
                ],
                'safe'
            ],
            [['cantidadDevolucion', 'cantidadRegistrada', 'registrada'], 'number'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Search principal
     */
    public function search($params, $iddocumento = null, $enviosiesa = false)
    {
        if ($iddocumento == null) {
            $query = Devoluciondocumentodetalle::find()
                ->alias('det')
                ->with(['bodegaSalida', 'unidadempaque', 'usuarioregistra']);
        } else {
            $query = Devoluciondocumentodetalle::find()
                ->alias('det')
                ->with(['bodegaSalida', 'unidadempaque', 'usuarioregistra'])
                ->where(['idDocumento' => $iddocumento]);
        }

        // Joins adicionales
        $query->join('INNER JOIN', 'devoluciondocumento dct', 'det.idDocumento = dct.id');
        $query->join('LEFT JOIN', 'item i', 'det.item = i.item');
        $query->join(
            'LEFT JOIN',
            'devolucionimportaciondetalle did',
            'det.codigoBarras = did.codigoBarras AND dct.numeroDocumento = did.numeroDocumento'
        );
        $query->join('LEFT JOIN', 'bodegas b', 'b.codigo = dct.codigoBodegaSalida');



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
            'b.nombre AS nombreBodega', // 👈 nombre de la bodega
            // Proveedor
            "COALESCE(
                i.nombreProveedor,
                LTRIM(SUBSTRING(did.proveedor, CHARINDEX('-', did.proveedor) + 1, LEN(did.proveedor))),
                'Proveedor No Asignado'
            ) AS nombreProveedor",
        ]);

        $query->orderBy([
            'dct.codigoBodegaSalida' => SORT_ASC,
            'dct.numeroDocumento' => SORT_ASC
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros básicos
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

        // Filtro por fechas
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

        // Filtro por proveedor
        if (!empty($this->nombreProveedor)) {
            $query->andWhere([
                'or',
                ['like', 'i.nombreProveedor', $this->nombreProveedor],
                ['like', "LTRIM(SUBSTRING(did.proveedor, CHARINDEX('-', did.proveedor) + 1, LEN(did.proveedor)))", $this->nombreProveedor],
            ]);
        }

        // Filtro por bodega
        if (!empty($this->nombreBodega)) {
            $query->andFilterWhere(['like', 'b.nombre', $this->nombreBodega]);
        }

        // Filtros adicionales
        $query->andFilterWhere(['like', 'det.codigoBarras', $this->codigoBarras])
            ->andFilterWhere(['like', 'det.item', $this->item])
            ->andFilterWhere(['like', 'det.talla', $this->talla])
            ->andFilterWhere(['like', 'det.color', $this->color])
            ->andFilterWhere(['like', 'det.referencia', $this->referencia])
            ->andFilterWhere(['like', 'det.itemResumen', $this->itemResumen]);

        if (!empty($this->usuarioRegistra)) {
            $query->andFilterWhere(['IN', 'det.usuarioRegistra', $this->usuarioRegistra]);
        }

        // Filtro de tipoInventario (post-procesamiento)
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
}
