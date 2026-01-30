<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Planillaembarque;

/**
 * PlanillaembarqueSearch represents the model behind the search form of `frontend\models\Planillaembarque`.
 */
class PlanillaembarqueSearch extends Planillaembarque
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTransportadora', 'idVehiculo',  'idConductor', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'fechaDespacho',
                    'horaDespacho',
                    'placa',
                    'nombreConductor',
                    'sello',
                    'created_at',
                    'updated_at',
                    'updated_by',
                    'fechaDesde',
                    'fechaHasta',
                    'numeroDocumento',
                    'consecutivo',
                    'numeroDocumentoInterno',
                    'Codigobodegaorigen'
                ],
                'safe'
            ],
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
    public function search($params)
    {
        $query = Planillaembarque::find()->alias('pe');

        // Hacemos los joins correspondientes con LEFT JOIN para cada tabla
        $query->joinWith([
            'planillaembarquetraspaso pt' => function ($q) {
                $q->joinWith([
                    'traspaso t' => function ($q2) {
                        $q2->leftJoin('documentosiesa ds', 'ds.idGruma = t.id'); // Left join con documentosiesa
                    }
                ]);
            }
        ], false); // El `false` evita SELECT * de las tablas relacionadas
        
        $query->select(['pe.*']); // Solo campos de pe
        
        $query->distinct(); // Evita duplicados
        
        

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'pe.id' => $this->id,
            'pe.fechaDespacho' => $this->fechaDespacho,
            'pe.idTransportadora' => $this->idTransportadora,
            'pe.idVehiculo' => $this->idVehiculo,
            'pe.idConductor' => $this->idConductor,
            'pe.idEstado' => $this->idEstado,
            'pe.created_at' => $this->created_at,
            'pe.created_by' => $this->created_by,
            'pe.updated_at' => $this->updated_at,
            'pe.updated_by' => $this->updated_by,
            'pe.idBodegaDespacho' => $this->Codigobodegaorigen,
        ]);

        $query->andFilterWhere(['like', 'pe.horaDespacho', $this->horaDespacho])
            ->andFilterWhere(['like', 'pe.placa', $this->placa])
            ->andFilterWhere(['like', 'pe.nombreConductor', $this->nombreConductor])
            ->andFilterWhere(['like', 'pe.sello', $this->sello]);

        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['=', new \yii\db\Expression('CAST(pe.fechaDespacho AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(pe.fechaDespacho AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(pe.fechaDespacho AS DATE)'), $fechaInicio, $fechaFin]);
            }
        } 
        

        if (!empty($this->numeroDocumento)) {
            $query->andWhere(['like', 'ds.f350_consec_docto', $this->numeroDocumento]);
        }

        if (!empty($this->numeroDocumentoInterno)) {

            $query->andWhere(['like', 't.id', $this->numeroDocumentoInterno]);
        }

        $query->orderBy([
            'pe.id' => SORT_DESC,
        ]);

        return $dataProvider;
    }
}
