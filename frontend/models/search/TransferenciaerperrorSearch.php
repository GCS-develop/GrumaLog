<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferenciaerperror;

/**
 * TransferenciaerperrorSearch represents the model behind the search form of `frontend\models\Transferenciaerperror`.
 */
class TransferenciaerperrorSearch extends Transferenciaerperror
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTransferenciaerp'], 'integer'],
            [['centroOperacionDocumento', 'tipoDocumento', 'numeroLinea', 'tipoRegistro', 
            'subTipoRegistro', 'version', 'nivel', 'valor', 'detalle', 'fechaDocumento'], 'safe'],
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
    public function search($params, $idtransferenciaerp)
    {
        $query = Transferenciaerperror::find()->where(['idTransferenciaerp' => $idtransferenciaerp]);

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
            'idTransferenciaerp' => $this->idTransferenciaerp,
        ]);

        $query->andFilterWhere(['like', 'centroOperacionDocumento', $this->centroOperacionDocumento])
            ->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'fechaDocumento', $this->fechaDocumento])
            ->andFilterWhere(['like', 'numeroLinea', $this->numeroLinea])
            ->andFilterWhere(['like', 'tipoRegistro', $this->tipoRegistro])
            ->andFilterWhere(['like', 'subTipoRegistro', $this->subTipoRegistro])
            ->andFilterWhere(['like', 'version', $this->version])
            ->andFilterWhere(['like', 'nivel', $this->nivel])
            ->andFilterWhere(['like', 'valor', $this->valor])
            ->andFilterWhere(['like', 'detalle', $this->detalle]);

        return $dataProvider;
    }
}
