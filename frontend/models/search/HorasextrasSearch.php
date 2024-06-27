<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Horasextras;

/**
 * HorasextrasSearch represents the model behind the search form of `frontend\models\Horasextras`.
 */
class HorasextrasSearch extends Horasextras
{
    public $fechaInicio;
    public $fechaFin;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idEmpleado', 'idCO', 'created_by', 'updated_by', 'idEstado'], 'integer'],
            [['fecha', 'observacion', 'created_at', 'updated_at', 'nombreCO', 'nombreEmpleado',
                'codigoCO', 'identificacion', 'fechaInicio', 'fechaFin'], 'safe'],
            [['numeroHoras'], 'number'],
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
        $query = Horasextras::find()->alias('he');

        $query->join('INNER JOIN', 'empleado em', 'he.idEmpleado = em.id');
        $query->join('INNER JOIN', 'centrooperacion .co', 'he.idCO = co.id');

        $query->select([
            'he.id',
            'he.idEmpleado',
            'he.fecha',
            'he.idCO',
            'he.numeroHoras',
            'he.observacion',
            'he.created_at',
            'he.created_by',
            'he.updated_at',
            'he.updated_by',
            'he.idEstado',
            'em.identificacion',
            'em.nombreEmpleado',
            'co.codigo AS codigoCO',
            'co.nombre AS nombreCO',
        ]);

        $query->orderBy(['he.fecha' => SORT_DESC]);

        // add conditions that should always apply here

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
            'he.id' => $this->id,
            'he.fecha' => $this->fecha,
            'he.idEmpleado' => $this->idEmpleado,
            'he.idCO' => $this->idCO,
            'he.numeroHoras' => $this->numeroHoras,
            'he.created_at' => $this->created_at,
            'he.created_by' => $this->created_by,
            'he.updated_at' => $this->updated_at,
            'he.updated_by' => $this->updated_by,
            'he.idEstado' => $this->idEstado,
            'em.identificacion' => $this->identificacion,
            'co.codigo' => $this->codigoCO
        ]);

        $query->andFilterWhere(['like', 'he.observacion', $this->observacion])
            ->andFilterWhere(['like', 'em.nombreEmpleado', $this->nombreEmpleado])
            ->andFilterWhere(['like', 'co.nombre', $this->nombreCO]);

        // Filtrar por rango de fechas si se han proporcionado
        if ($this->fechaInicio && $this->fechaFin) {
            $query->andFilterWhere(['between', 'he.fecha', $this->fechaInicio, $this->fechaFin]);
        }

        return $dataProvider;
    }
}
