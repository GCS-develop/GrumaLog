<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conductor;

/**
 * ConductorSearch represents the model behind the search form of `frontend\models\Conductor`.
 */
class ConductorSearch extends Conductor
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idEmpleado', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
        $query = Conductor::find()->alias('con');

        $query->join('INNER JOIN', 'empleado em', 'con.idEmpleado = em.id');
        $query->join('LEFT JOIN', 'userconteo usc', 'con.id = usc.idEmpleadoLogistica');
        $query->join('LEFT JOIN', 'user us', 'usc.idUser = us.id');

        $query->select([
            'con.id',
            'con.idEmpleado',
            'con.idEstado',
            'em.identificacion',
            'em.nombreEmpleado',
            'us.username'
        ]);

        $query->orderBy(['em.nombreEmpleado' => SORT_ASC]);

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
            'con.id' => $this->id,
            'con.idEmpleado' => $this->idEmpleado,
            'con.idEstado' => $this->idEstado,
            'con.created_at' => $this->created_at,
            'con.created_by' => $this->created_by,
            'con.updated_at' => $this->updated_at,
            'con.updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
