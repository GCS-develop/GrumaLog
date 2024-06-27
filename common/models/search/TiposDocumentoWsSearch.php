<?php

namespace common\models\search;

use yii\base\Model;
use common\models\TiposDocumentoWs;

use yii\data\ArrayDataProvider;

/**
 * TiposDocumentoWsSearch represents the model behind the search form of `common\models\TiposDocumentoWs`.
 */
class TiposDocumentoWsSearch extends TiposDocumentoWs
{
	public $Id_tipodocto;
	public $Descripción_Id_tipodocto;
    public $Consecutivo_Proximo;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Id_tipodocto', 'Descripción_Id_tipodocto', 'Consecutivo_Proximo'], 'safe'],
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
        $this->load($params);
        
		$query = TiposDocumentoWs::getAllTiposDocumentoWs($this->Id_tipodocto);
		
        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);
		
		// Aplicar un filtro
		/*if ($this->Id_tipodocto){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Id_tipodocto']), strtolower($this->Id_tipodocto)) !== false;
			});
		}*/
		
		if ($this->Descripción_Id_tipodocto){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Descripción_Id_tipodocto']), strtolower($this->Descripción_Id_tipodocto)) !== false;
			});
		}
        
        if ($this->Consecutivo_Proximo){
			$dataProvider->allModels = array_filter($query, function($model) {
				// Aquí puedes definir tu lógica de filtro
				 return strpos(strtolower($model['Consecutivo_Proximo']), strtolower($this->Consecutivo_Proximo)) !== false;
			});
		}
        return $dataProvider;
    }
	

}
