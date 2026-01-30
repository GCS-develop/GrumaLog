<?php

namespace common\models\search;

use yii\base\Model;
use common\models\InventariosWs;

use yii\data\ArrayDataProvider;

/**
 * InventariosWsSearch represents the model behind the search form of `common\models\InventariosWs`.
 */
class InventariosWsSearch extends InventariosWs
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Item', 'Referencia', 'Descripcion', 'Extension1', 'Extension2', 'Bodega', 'NombreBodega', 'EAN'], 'safe'],
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

        $query = InventariosWs::getAllInventariosSiesa();

        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $query,
        ]);

        //$this->load($params);
        // 🔹 FILTRO EXTRA: si viene Item, filtramos por los EAN del WS de Productos
        if (!empty($this->Item)) {
            $svc = new InventariosWs();
            // Buscar en Productos por Item y extraer CodigoBarras
            $productos = $svc->getAllInventariosSiesa(null, $this->Item, 'Item', 'Productos');
            $eans = array_values(array_unique(array_filter(array_column($productos, 'CodigoBarras'))));

            if (!empty($eans)) {
                $allow = array_flip($eans); // para lookup O(1)
                $dataProvider->allModels = array_values(array_filter($query, function ($row) use ($allow) {
                    $ean = $row['EAN'] ?? null;
                    return $ean && isset($allow[$ean]);
                }));
            } else {
                // Si no hay match, devolvemos vacío para ser explícitos
                $dataProvider->allModels = [];
            }
        }
        // Aplicar un filtro
        if ($this->Extension1) {
            $dataProvider->allModels = array_filter($query, function ($model) {
                // Aquí puedes definir tu lógica de filtro
                return strpos(strtolower($model['Extension1']), strtolower($this->Extension1)) !== false;
            });
        }

        if ($this->Extension2) {
            $dataProvider->allModels = array_filter($query, function ($model) {
                // Aquí puedes definir tu lógica de filtro
                return strpos(strtolower($model['Extension2']), strtolower($this->Extension2)) !== false;
            });
        }

        return $dataProvider;
    }
}
