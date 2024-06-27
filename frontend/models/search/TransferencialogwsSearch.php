<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferencialogws;

/**
 * TransferencialogwsSearch represents the model behind the search form of `frontend\models\Transferencialogws`.
 */
class TransferencialogwsSearch extends Transferencialogws
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'numeroRegistros', 'idConectorDinamico', 'idTransferenciaerp'], 'integer'],
            [['centroOperacionDocumento', 'tipoDocumento', 'fechaDocumento', 
            'bodegaSalidaDocumento', 'bodegaEntradaDocumento', 'startDate', 'endDate', 
            'mensaje', 'consecutivoOrdenCompra'], 'safe'],
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
    public function search($params, $idtransferenciaerp = null)
    {
        $query = Transferencialogws::find();

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
            'idTransferenciaerp' => $idtransferenciaerp,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'numeroRegistros' => $this->numeroRegistros,
            'idConectorDinamico' => $this->idConectorDinamico,
        ]);

        $query->andFilterWhere(['like', 'centroOperacionDocumento', $this->centroOperacionDocumento])
            ->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'fechaDocumento', $this->fechaDocumento])
            ->andFilterWhere(['like', 'bodegaSalidaDocumento', $this->bodegaSalidaDocumento])
            ->andFilterWhere(['like', 'bodegaEntradaDocumento', $this->bodegaEntradaDocumento])
            ->andFilterWhere(['like', 'mensaje', $this->mensaje]);

        return $dataProvider;
    }
}
