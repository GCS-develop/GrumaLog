<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\DocumentoGasto;

class DocumentoGastoSearch extends DocumentoGasto
{
    /**
     * Reglas de validación para los filtros
     */
    public function rules()
    {
        return [
            [['F350_ID_CO', 'F350_ID_TIPO_DOCTO', 'F350_CONSEC_DOCTO', 'F350_FECHA', 'F350_ID_TERCERO', 'F350_NOTAS'], 'safe'],
            [['ID_TRANSACCION', 'estado_envio', 'F350_IND_ESTADO'], 'integer'],
        ];
    }

    /**
     * Escenarios (usamos los de la clase base Model)
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Genera el dataProvider con los filtros aplicados
     */
    public function search($params)
    {
        $query = DocumentoGasto::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['ID_TRANSACCION' => SORT_DESC],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // 🔎 Filtros
        $query->andFilterWhere(['like', 'F350_ID_CO', $this->F350_ID_CO])
              ->andFilterWhere(['like', 'F350_ID_TIPO_DOCTO', $this->F350_ID_TIPO_DOCTO])
              ->andFilterWhere(['like', 'F350_CONSEC_DOCTO', $this->F350_CONSEC_DOCTO])
              ->andFilterWhere(['like', 'F350_FECHA', $this->F350_FECHA])
              ->andFilterWhere(['like', 'F350_ID_TERCERO', $this->F350_ID_TERCERO])
              ->andFilterWhere(['like', 'F350_NOTAS', $this->F350_NOTAS])
              ->andFilterWhere(['ID_TRANSACCION' => $this->ID_TRANSACCION])
              ->andFilterWhere(['estado_envio' => $this->estado_envio])
              ->andFilterWhere(['F350_IND_ESTADO' => $this->F350_IND_ESTADO]);

        return $dataProvider;
    }
}
