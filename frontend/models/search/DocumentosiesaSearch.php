<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use frontend\models\Documentosiesa;

use common\models\OrdendecompraSIESA;

/**
 * DocumentosiesaSearch represents the model behind the search form of `frontend\models\Documentosiesa`.
 */
class DocumentosiesaSearch extends Documentosiesa
{
    public $idContable;
    public $cia;
    public $co;
    public $tipoDocumento;
    public $consecutivo;
    public $item;
    public $referencia;
    public $descripcion;
    public $cantidadBase;
    public $bodega;
    public $codigoBarras;
    public $color;
    public $talla;
    public $proveedor;
    public $notas;
    public $costoPromedio;
    public $idEstadoDocumento;
    public $total;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'numeroDocumento', 'f350_id_cia', 'f350_rowid', 'f350_consec_docto', 'idGruma', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'tipoDocumento',
                    'f350_id_co',
                    'f350_id_tipo_docto',
                    'origen',
                    'created_at',
                    'updated_at',
                    'item'
                ],
                'safe'
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Documentosiesa::find();

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
            'numeroDocumento' => $this->numeroDocumento,
            'f350_id_cia' => $this->f350_id_cia,
            'f350_rowid' => $this->f350_rowid,
            'f350_consec_docto' => $this->f350_consec_docto,
            'idGruma' => $this->idGruma,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'tipoDocumento', $this->tipoDocumento])
            ->andFilterWhere(['like', 'f350_id_co', $this->f350_id_co])
            ->andFilterWhere(['like', 'f350_id_tipo_docto', $this->f350_id_tipo_docto])
            ->andFilterWhere(['like', 'origen', $this->origen]);

        return $dataProvider;
    }

    public function searchSIESA($params, $tipoconsulta)
    {
        $this->load($params);

        $tipodocumento = $this->tipoDocumento;
        $numero = $this->numeroDocumento;

        $rows = OrdendecompraSIESA::obtenerDatosDocumentoContable($tipodocumento, $numero, $tipoconsulta);

        if (!empty($rows)) {
            $this->consecutivo = $rows[0]['consecutivo'] ?? null;
            $this->notas = $rows[0]['notas'] ?? null;
            $this->idEstadoDocumento = $rows[0]['idEstadoDocumento'] ?? null;
            $this->total = $rows[0]['total'] ?? null;
        }

        // Filtrado manual por item si fue proporcionado
        if ($this->item) {
            $rows = array_filter($rows, function ($row) {
                return stripos($row['item'], $this->item) !== false;
            });
        }

        return new ArrayDataProvider([
            'allModels' => $rows,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => [
                    'IdContable',
                    'Cia',
                    'CO',
                    'TipoDocumento',
                    'Consecutivo',
                    'Notas',
                    'idItem_Ext',
                    'codigointernomovto',
                ],
            ],
        ]);
    }

    public static function searchAgrupados($params)
    {
        // Llamamos al método de obtención de datos con la consulta personalizada
        $rows = OrdendecompraSIESA::obtenerDocumentosRepetidos();

        return new ArrayDataProvider([
            'allModels' => $rows,
            'pagination' => [
                'pageSize' => 100,  // Ajusta el tamaño de la página según sea necesario
            ],
            'sort' => [
                'attributes' => [
                    'f350_id_tipo_docto',
                    'f350_notas',
                    'cantidad',
                ],
            ],
        ]);
    }


}
