<?php

namespace common\models\search;

use yii\base\Model;
use common\models\BodegasWs;

use yii\data\ArrayDataProvider;

/**
 * BodegasSearch represents the model behind the search form of `common\models\BodegasWs`.
 */
class BodegasWsSearch extends BodegasWs
{
	public $Codigo_bodega;
	public $Descripcion_bodega;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Codigo_bodega', 'Descripcion_bodega'], 'safe'],
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
		$query = BodegasWs::getAllBodegasWs();
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        $this->load($params);
		
		// Aplicar un filtro
		if ($this->Codigo_bodega){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Codigo_bodega']), strtolower($this->Codigo_bodega)) !== false;
			});
		}
		
		if ($this->Descripcion_bodega){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Descripcion_bodega']), strtolower($this->Descripcion_bodega)) !== false;
			});
		}		
        return $dataProvider;
    }
	

}
