<?php

namespace frontend\models\search;

use Yii;
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
            [['id', 'idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'tipoMovimiento'], 'integer'],
            [['updated_at', 'created_by', 'updated_by', 'fechaDesde', 'fechaHasta', 'estadoPlanilla',], 'safe'],
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
        $query = Traspaso::find()->alias('tr');

        $query->join('INNER JOIN', 'traspasodetalle td', 'td.idTraspaso = tr.id');
        $query->join('INNER JOIN', 'item i', 'i.id = td.iditem');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');
        $query->join('INNER JOIN', 'estadotraspaso e', 'e.id = tr.idestado');
        $query->join('left JOIN', 'documentosiesa ds', 'tr.id = ds.idGruma');
        $query->join(
            'LEFT JOIN',
            'planillaembarquetraspaso pet',
            'pet.id = (SELECT MAX(id) FROM planillaembarquetraspaso WHERE idTraspaso = tr.id)'
        );
        $query->join('LEFT JOIN', 'estadorecepcion er', 'er.id = pet.idEstado AND er.id <> 5'); // Agregando la relación


        $query->select([
            'tr.*',
            'td.updated_by',
            'tr.consecutivo',
            'ds.f350_consec_docto',
            'i.item',
            'c.nombre',
            't.codigo',
            'e.codigo',
            'tr.tipoMovimiento',
            'i.nombreProveedor',
            'tr.idBodegaOrigen',
            'tr.idBodegaDestino',
            'er.nombre as estadoPlanilla',
            'tr.created_by' // Agregar esto
        ]);


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

        $this->load($params);
        // Yii::debug($this->fechaDesde, 'fechaDesde');
        // Yii::debug($this->fechaHasta, 'fechaHasta');

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'idBodegaOrigen' => $this->idBodegaOrigen,
            'idBodegaDestino' => $this->idBodegaDestino,
            'numeroCajas' => $this->numeroCajas,
            'idTipoDocumento' => $this->idTipoDocumento,
            'idEstado' => $this->idEstado,
            'created_by' => $this->created_by,
            'tr.created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'tipoMovimiento' => $this->tipoMovimiento,
            'er.id' => $this->estadoPlanilla,

            // 'fechaDesde' => $this->fechaDesde,
            // 'fechaHasta' => $this->fechaHasta,



        ]);

        // $query->andFilterWhere(['LIKE', 'updated_at', $this->updated_at]);
        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['=', new \yii\db\Expression('CAST(created_at AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(created_at AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(created_at AS DATE)'), $fechaInicio, $fechaFin]);
            }
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
                $query->andFilterWhere(['IN', 'tr.created_by', $userIds]);
            } else {
                // Manejar el caso en que no se encuentren usuarios
                // Por ejemplo, puedes aplicar un filtro predeterminado
                $query->andFilterWhere(['tr.created_by' => null]); // Filtro predeterminado
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
