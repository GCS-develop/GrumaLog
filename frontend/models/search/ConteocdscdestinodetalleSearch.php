<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteocdscdestinodetalle;
use yii\db\Expression;

/**
 * ConteocdscdestinodetalleSearch represents the model behind the search form of `frontend\models\Conteocdscdestinodetalle`.
 */
class ConteocdscdestinodetalleSearch extends Conteocdscdestinodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idConteocdscdestino', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'created_at', 'updated_at'], 'safe'],
            [['totalUnidades'], 'number'],
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
    public function search($params, $idconteodestino = null)
    {
        $query = Conteocdscdestinodetalle::find();

        $query->andFilterWhere([
            'idConteocdscdestino' => $idconteodestino,
        ]);

        $query->andFilterWhere(['>', 'totalUnidades', 0]);

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
            'idConteocdscdestino' => $this->idConteocdscdestino,
            'idItem' => $this->idItem,
            'totalUnidades' => $this->totalUnidades,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        return $dataProvider;
    }

    public function searchSIESA($idconteofactura){
        $query = Conteocdscdestinodetalle::find()->alias('det');
        $query->join('LEFT JOIN', 'conteocdscdestino dest', 'det.idConteocdscdestino = dest.id');
        $query->join('LEFT JOIN', 'conteocdscdestinofactura fac', 'dest.idConteocdscdestinofactura = fac.id');
        $query->join('LEFT JOIN', 'ordendecompra oc', 'fac.idOrdenCompra = oc.id');
        $query->join('LEFT JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('LEFT JOIN', 'proveedor prv', 'oc.idProveedor = prv.id');
        $query->join('LEFT JOIN', 'bodegas bo', 'dest.idCentroOperacion = bo.id');
        $query->join('LEFT JOIN', 'item it', 'det.idItem = it.id');
        $query->join('LEFT JOIN', 'color col', 'it.idColor = col.id');
        $query->join('LEFT JOIN', 'talla tal', 'it.idTalla = tal.id');
        $query->join('LEFT JOIN', 'bodegas bom', 'fac.idBodegaMovimiento = bom.id');
        $query->join('LEFT JOIN', 'tipodocumento tdm', 'fac.idTipoDocumentoMovimiento = tdm.id');
        $query->join('LEFT JOIN', 'centrooperacion com', 'fac.idBodegaMovimiento = com.id');
        
        $query->join('LEFT JOIN', 'unidadempaque ue', "ISNULL(it.unidadEmpaque,'UND') = ue.codigo");

        $query->select([
            "fac.id",
            "com.codigo AS centroOperacionDocumento", 
            "tdm.codigo AS tipoDocumento", 
            "FORMAT(fac.updated_at, 'yyyyMMdd') AS fechaDocumento",
            "bom.codigo AS bodegaSalidaDocumento",
            "bo.codigo AS bodegaEntradaDocumento",
            "com.codigo AS centroOperacion",
            "tdm.codigo AS tipoDocumentoMovimiento",
            "bom.codigo AS bodegaSalidaMovimiento",
            "com.codigo AS centroOperacionMovimiento",
            "ISNULL(it.unidadEmpaque,'UND') AS unidadSalida", 
            "det.totalUnidades AS cantidadBase",
            new Expression('0 AS costoPromedioUnitario'),
            "it.item",
            "col.nombre AS color",
            "tal.nombre AS talla",
            "ue.equivalencia"
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Deshabilita la paginación
        ]);

        $query = $query->andFilterWhere(['fac.id1' => $idconteofactura]);

        //echo $query->createCommand()->getRawSql(); die("hola");
 
        // add conditions that should always apply here

        return $dataProvider;
    }
}
