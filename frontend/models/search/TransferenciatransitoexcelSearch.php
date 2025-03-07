<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferenciatransitoexcel;

/**
 * TransferenciatransitoexcelSearch represents the model behind the search form of `frontend\models\Transferenciatransitoexcel`.
 */
class TransferenciatransitoexcelSearch extends Transferenciatransitoexcel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTransferenciaerp', 'cantidadBase', 'item', 'procesado', 'fila'], 'integer'],
            [['centroOperacionDocumento', 'tipoDocumento', 'fechaDocumento', 'bodegaSalidaDocumento', 'bodegaEntradaDocumento', 'centroOperacion', 'tipoDocumentoMovimiento', 'bodegaSalidaMovimiento', 'centroOperacionMovimiento', 'unidadSalida', 'color', 'talla', 'numero', 'notas', 'codigoBarras'], 'safe'],
            [['costoPromedioUnitario'], 'number'],
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
        $query = Transferenciatransitoexcel::find()->where(['idTransferenciaerp' => $idtransferenciaerp]);

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
            'cantidadBase' => $this->cantidadBase,
            'costoPromedioUnitario' => $this->costoPromedioUnitario,
            'item' => $this->item,
            'procesado' => $this->procesado,
            'fila' => $this->fila,
        ]);

        $query->andFilterWhere(['like', 'centroOperacionDocumento', $this->centroOperacionDocumento])
            ->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'fechaDocumento', $this->fechaDocumento])
            ->andFilterWhere(['like', 'bodegaSalidaDocumento', $this->bodegaSalidaDocumento])
            ->andFilterWhere(['like', 'bodegaEntradaDocumento', $this->bodegaEntradaDocumento])
            ->andFilterWhere(['like', 'centroOperacion', $this->centroOperacion])
            ->andFilterWhere(['like', 'tipoDocumentoMovimiento', $this->tipoDocumentoMovimiento])
            ->andFilterWhere(['like', 'bodegaSalidaMovimiento', $this->bodegaSalidaMovimiento])
            ->andFilterWhere(['like', 'centroOperacionMovimiento', $this->centroOperacionMovimiento])
            ->andFilterWhere(['like', 'unidadSalida', $this->unidadSalida])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'numero', $this->numero])
            ->andFilterWhere(['like', 'notas', $this->notas])
            ->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        return $dataProvider;
    }
}
