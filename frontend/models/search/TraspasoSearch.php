<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspaso;
use frontend\models\Tipodocumento;
use common\models\User;
use yii\helpers\ArrayHelper;

/**
 * TraspasoSearch represents the model behind the search form of `app\models\Traspaso`.
 */
class TraspasoSearch extends Traspaso
{
    public $serie;
    public $und_empaque;
    public $und_traspaso;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado'], 'integer'],
            [['updated_at', 'created_by', 'updated_by' , 'fechaDesde', 'fechaHasta',], 'safe'],
            [['consecutivo',], 'number'],
            [['serie'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
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
        $query = Traspaso::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '100',
            ],
        ]);

        $query->orderBy(['created_at' => SORT_DESC]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'idBodegaOrigen' => $this->idBodegaOrigen,
            'idBodegaDestino' => $this->idBodegaDestino,
            'numeroCajas' => $this->numeroCajas,
            'idTipoDocumento' => $this->idTipoDocumento,
            'idEstado' => $this->idEstado,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            // 'created_at' => $this->created_at,
            

            

        ]);

        // $query->andFilterWhere(['LIKE', 'updated_at', $this->updated_at]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));

            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), created_at, 23)', $fechaInicio, $fechaFin]);
        }

        $query->andFilterWhere(['like', 'consecutivo', $this->consecutivo]);

        $query->andFilterWhere(['like', 'created_at', $this->created_at]);

        if ($this->created_by !== null) {
            $usuarios = User::find()
                ->where(['LIKE', 'username', '%' . trim($this->created_by) . '%', false])
                ->all();

            // Verificar si se encontraron usuarios
            if (!empty($usuarios)) {
                $userIds = array_map(function ($usuario) {
                    return $usuario->id;
                }, $usuarios);

                // Filtrar por IDs de usuario encontrados
                $query->andFilterWhere(['IN', 'created_by', $userIds]);
            } else {
                // Manejar el caso en que no se encuentren usuarios
                // Por ejemplo, puedes aplicar un filtro predeterminado
                $query->andFilterWhere(['created_by' => null]); // Filtro predeterminado
            }
        }


        if ($this->serie !== null) {
            $tipoDocumento = Tipodocumento::findOne(['codigo' => $this->serie]);
            if ($tipoDocumento !== null) {
                $query->andFilterWhere(['idTipoDocumento' => $tipoDocumento->id]);
            } else {
                // Si el tipo de documento no se encuentra, no se filtrará por tipo de documento
                $query->andFilterWhere(['idTipoDocumento' => null]);
            }
        }

        return $dataProvider;
        
    }

}
