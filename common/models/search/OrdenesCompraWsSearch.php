<?php

namespace common\models\search;

use yii\base\Model;
use common\models\OrdenesCompraWs;

use yii\data\ArrayDataProvider;

/**
 * OrdenesCompraWsSearch represents the model behind the search form of `common\models\OrdenesCompraWs`.
 */
class OrdenesCompraWsSearch extends OrdenesCompraWs
{
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CentroOperacion', 'TipoDocumento', 'Consecutivo', 'Fecha', 'IdTercero', 'RazonSocial',
                'Estado_Doc', 'Item', 'Referencia_Item', 'Descripcion_Item', 'CantidadPedida', 
                'CantidadEntrada', 'CantidadPendiente', 'UnidadMedida', 'Fecha_Entrega'], 'safe'],
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
		  
		$query = OrdenesCompraWs::getAllOrdenesCompraWs();
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        //$this->load($params);
		
		// Aplicar un filtro
		if ($this->CentroOperacion){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['CentroOperacion']), strtolower($this->CentroOperacion)) !== false;
			});
		}	

		if ($this->TipoDocumento){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['TipoDocumento']), strtolower($this->TipoDocumento)) !== false;
			});
		}	

		if ($this->Consecutivo){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Consecutivo']), strtolower($this->Consecutivo)) !== false;
			});
		}
        return $dataProvider;
    }
	

}
