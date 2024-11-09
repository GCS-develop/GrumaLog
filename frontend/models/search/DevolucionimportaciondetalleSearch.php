<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Devolucionimportaciondetalle;

/**
 * DevolucionimportaciondetalleSearch represents the model behind the search form of `frontend\models\Devolucionimportaciondetalle`.
 */
class DevolucionimportaciondetalleSearch extends Devolucionimportaciondetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idInterfase'], 'integer'],
            [['co', 'fecha', 'bodegaSalida', 'item', 'talla', 'color', 'numeroDocumento', 'notasDocumento', 'bodegaEntrada', 'codigoBodegaEntrada', 'codigoBodegaSalida', 'referencia', 'itemResumen', 'unidadMedida', 'categoria', 'proveedor', 'codigoBarras'], 'safe'],
            [['cantidad'], 'number'],
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
    public function search($params, $idinterfase)
    {
        $query = Devolucionimportaciondetalle::find()->where(['idInterfase' => $idinterfase]);

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
            'idInterfase' => $this->idInterfase,
            'fecha' => $this->fecha,
            'cantidad' => $this->cantidad,
        ]);

        $query->andFilterWhere(['like', 'co', $this->co])
            ->andFilterWhere(['like', 'bodegaSalida', $this->bodegaSalida])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'numeroDocumento', $this->numeroDocumento])
            ->andFilterWhere(['like', 'notasDocumento', $this->notasDocumento])
            ->andFilterWhere(['like', 'bodegaEntrada', $this->bodegaEntrada])
            ->andFilterWhere(['like', 'codigoBodegaEntrada', $this->codigoBodegaEntrada])
            ->andFilterWhere(['like', 'codigoBodegaSalida', $this->codigoBodegaSalida])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'itemResumen', $this->itemResumen])
            ->andFilterWhere(['like', 'unidadMedida', $this->unidadMedida])
            ->andFilterWhere(['like', 'categoria', $this->categoria])
            ->andFilterWhere(['like', 'proveedor', $this->proveedor])
            ->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        return $dataProvider;
    }
}
