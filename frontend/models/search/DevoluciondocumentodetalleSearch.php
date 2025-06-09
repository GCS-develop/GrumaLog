<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Devoluciondocumentodetalle;

/**
 * DevoluciondocumentodetalleSearch represents the model behind the search form of `frontend\models\Devoluciondocumentodetalle`.
 */
class DevoluciondocumentodetalleSearch extends Devoluciondocumentodetalle
{
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
                    'nombreProveedor'
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
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $iddocumento = null)
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
            'i.nombreProveedor as nombreProveedor'

        ]);

        $query->orderBy([
            'dct.codigoBodegaSalida' => SORT_ASC,
            'dct.numeroDocumento' => SORT_ASC
        ]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
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
            // 'det.fechaRegistra' => trim($this->fechaRegistra),
        ]);

        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['>=', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(det.fechaRegistra AS DATE)'), $fechaInicio, $fechaFin]);
            }
        }

        if (!empty($this->fechaRegistra)) {
            $fecha = date('Y-m-d', strtotime($this->fechaRegistra));
            $query->andWhere("CAST(det.fechaRegistra AS DATE) = :fecha", [':fecha' => $fecha]);

        }
        if (!empty($this->nombreProveedor)) {
            // Solo filtra por nombreProveedor si se ha ingresado un valor
            $query->andWhere(['like', 'i.nombreProveedor', $this->nombreProveedor]);
        }


        $query->andFilterWhere(['like', 'det.codigoBarras', $this->codigoBarras])
            ->andFilterWhere(['like', 'det.item', $this->item])
            ->andFilterWhere(['like', 'det.talla', $this->talla])
            ->andFilterWhere(['like', 'det.color', $this->color])
            ->andFilterWhere(['like', 'det.referencia', $this->referencia])
            ->andFilterWhere(['like', 'det.itemResumen', $this->itemResumen]);

        return $dataProvider;
    }
}
