<?php

namespace common\models\search;

use yii\base\Model;
use common\models\ProveedoresWs;

use yii\data\ArrayDataProvider;

/**
 * ProveedoresWsSearch represents the model behind the search form of `common\models\ProveedoresWs`.
 */
class ProveedoresWsSearch extends ProveedoresWs
{
	
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Id', 'Nit', 'Razon_Social', 'Tipo_Identificacion', 'Sucursal', 'Descripcion_Sucursal',
                'Contacto', 'Direccion', 'Pais', 'Ciudad', 'Departamento', 'Telefono', 'Email',
                'Celular', 'CriterioMercancia', 'CriterioModeloLogistico'
            ], 'safe'
            ],
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
		  
		$query = ProveedoresWs::getAllProveedoresWs();

        //var_dump($query); die("hola");
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        //$this->load($params);
		
		// Aplicar un filtro
		if ($this->Id){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Id']), strtolower($this->Id)) !== false;
			});
		}	

		if ($this->Nit){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Nit']), strtolower($this->Nit)) !== false;
			});
		}		

		if ($this->Razon_Social){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Razon_Social']), strtolower($this->Razon_Social)) !== false;
			});
		}

        return $dataProvider;
    }
	

}
