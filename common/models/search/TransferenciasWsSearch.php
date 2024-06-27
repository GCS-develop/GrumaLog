<?php

namespace common\models\search;

use yii\base\Model;
use common\models\TransferenciasWs;

use yii\data\ArrayDataProvider;

/**
 * TransferenciasWsSearch represents the model behind the search form of `common\models\TransferenciasWs`.
 */
class TransferenciasWsSearch extends TransferenciasWs
{
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ConsecutivoERP', 'Co', 'Tipo_docto', 'Tercero', 'Fecha', 'Notas'], 'safe'],
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
     * @return ArrayDataProvider
     */
    public function search($params)
    {		
		$this->load($params);
		  
		$query = TransferenciasWs::getAllTransferenciasWs();
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        //$this->load($params);
		
		// Aplicar un filtro
		if ($this->Co){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Co']), strtolower($this->Co)) !== false;
			});
		}	

		if ($this->Notas){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Notas']), strtolower($this->Notas)) !== false;
			});
		}	

        return $dataProvider;
    }
	

}
