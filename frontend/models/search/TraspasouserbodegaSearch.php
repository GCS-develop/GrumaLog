<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasouserbodega;

/**
 * TraspasouserbodegaSearch represents the model behind the search form of `frontend\models\Traspasouserbodega`.
 */
class TraspasouserbodegaSearch extends Traspasouserbodega
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idUserTraspaso', 'idBodega', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'buscarnombreusuario', 'buscarnombrebodega'], 'safe'],
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
        $query = Traspasouserbodega::find()->alias('tub');
        $query->join('INNER JOIN', 'usertraspaso ut', 'ut.id = tub.idUserTraspaso'); // Agregando la relación
        $query->join('INNER JOIN', 'user u', 'ut.idUser = u.id');
        $query->join('INNER JOIN', 'bodegas b', 'b.id = tub.idBodega');


        // add conditions that should always apply hereFF
        $query->select([
            'tub.*',
            'u.username AS buscarnombreusuario',
            'concat(b.codigo ,b.nombre) AS buscarnombrebodega',

        ]);

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
            'tub.id' => $this->id,
            'tub.idUserTraspaso' => $this->idUserTraspaso,
            'tub.idBodega' => $this->idBodega,
            'tub.idEstado' => $this->idEstado,
            'tub.created_at' => $this->created_at,
            'tub.created_by' => $this->created_by,
            'tub.updated_at' => $this->updated_at,
            'tub.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'u.username', $this->buscarnombreusuario]);
        $query->andFilterWhere(['like', 'b.nombre', $this->buscarnombrebodega]);



        $query->orderBy(['u.username' => SORT_ASC]);

        return $dataProvider;
    }
}
