<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteobylecturacodigo;

/**
 * ConteobylecturacodigoSearch represents the model behind the search form of `app\models\Conteobylecturacodigo`.
 */
class ConteobylecturacodigoSearch extends Conteobylecturacodigo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'modulo', 'idConteoDestino', 'idConteoDetalle', 'unidades', 'isMobile', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $modulo=null, $idconteodestino=null, $idconteodetalle = null)
    {
        $query = Conteobylecturacodigo::find();

        $query->andFilterWhere([
            'modulo' => $modulo,
            'idConteoDestino' => $idconteodestino,
            'idConteoDetalle' => $idconteodetalle,
        ]);

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
            'id' => $this->id,
            'modulo' => $this->modulo,
            'idConteoDestino' => $this->idConteoDestino,
            'idConteoDetalle' => $this->idConteoDetalle,
            'unidades' => $this->unidades,
            'isMobile' => $this->isMobile,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        $query->orderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }
}
