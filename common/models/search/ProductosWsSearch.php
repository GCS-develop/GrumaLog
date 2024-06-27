<?php

namespace common\models\search;

use yii\base\Model;
use common\models\ProductosWs;

use yii\data\ArrayDataProvider;

/**
 * ProductosWsSearch represents the model behind the search form of `common\models\ProductosWs`.
 */
class ProductosWsSearch extends ProductosWs
{
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Item','Referencia', 'Descripcion', 'Ext_1', 'Descripcion_Ext_1', 'Ext_2', 'Descripcion_Ext_2', 'Equivalencia_UM',
			'UM', 'criterio_PROVEEDOR', 'criterio_CATEGORIA', 'criterio_SUBCATEGORIA', 'criterio_Producto', 'criterio_Marca'], 'safe'],
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
		  
		$query = ProductosWs::getAllProductosWs();

        //var_dump($query); die("hola");
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        //$this->load($params);
		
		// Aplicar un filtro
		if ($this->Descripcion_Ext_1){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Descripcion_Ext_1']), strtolower($this->Descripcion_Ext_1)) !== false;
			});
		}	

		if ($this->Descripcion_Ext_2){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Descripcion_Ext_2']), strtolower($this->Descripcion_Ext_2)) !== false;
			});
		}
        
        if ($this->CodigoBarras){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['CodigoBarras']), strtolower($this->CodigoBarras)) !== false;
			});
		}
			
        return $dataProvider;
    }
	

}
