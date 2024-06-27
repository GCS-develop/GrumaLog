<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Agendapresupuestosubcategoria;

use yii\data\SqlDataProvider;
use yii\db\Connection;

/**
 * AgendapresupuestosubcategoriaSearch represents the model behind the search form of `frontend\models\Agendapresupuestosubcategoria`.
 */
class AgendapresupuestosubcategoriaSearch extends Agendapresupuestosubcategoria
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaPresupuesto', 'periodoAnio', 'periodoMes'], 'integer'],
            [['crossDocking', 'categoria', 'subcategoria', 'fechaLlegada'], 'safe'],
            [['cantidad'], 'number'],
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
    public function search($params, $idagendapresupuesto)
    {
        $query = Agendapresupuestosubcategoria::find()->where(['idAgendaPresupuesto' => $idagendapresupuesto]);

        $query->orderBy(['crossDocking' => SORT_ASC,
                        'categoria' => SORT_ASC,
                        'subcategoria' => SORT_ASC,
                        'fechaLlegada' => SORT_ASC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100, // Mostrar 50 registros por página
            ],
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
            'idAgendaPresupuesto' => $this->idAgendaPresupuesto,
            'periodoAnio' => $this->periodoAnio,
            'periodoMes' => $this->periodoMes,
            'fechaLlegada' => $this->fechaLlegada,
            'cantidad' => $this->cantidad,
        ]);

        $query->andFilterWhere(['like', 'crossDocking', $this->crossDocking])
            ->andFilterWhere(['like', 'categoria', $this->categoria])
            ->andFilterWhere(['like', 'subcategoria', $this->subcategoria]);

        return $dataProvider;
    }

    public function searchResumenSubcategoria($idagenda, $categoria, $lista)
    {
        $query = Agendapresupuestosubcategoria::find()
                    ->select([  'det.crossDocking', 
                                'det.categoria', 
                                'det.subcategoria', 
                                'SUM(det.cantidad) AS cantidad',
                                'SUM(det.cantidadAgendada) AS cantidadAgendada'
                            ])
                    ->alias('det')
                    ->groupBy(['det.crossDocking', 'det.categoria' , 'det.subcategoria'])
                    ->andWhere(['<>', 'det.cantidad', 0])
                    ->andWhere(['det.idAgendaPresupuesto' => $idagenda])
                    ->andWhere(['det.categoria' => $categoria])
                    ->andFilterWhere(['IN', 'det.crossDocking', $lista]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }

    public function searchPivotSubcategoriaDia($params, $idagenda, $lista = null, $categoria = null, $subactegoria = null)
    {

        $db = Yii::$app->db;

        if ($params){
            $this->load($params);
        }

        $datos = "(
            SELECT 
            CASE 
                WHEN crossDocking = 'CEDI' THEN 'CEDI + TIENDAS'
                WHEN crossDocking = 'TIENDAS' THEN 'CEDI + TIENDAS'
                ELSE crossDocking
            END AS crossDocking, categoria, subcategoria, DAY(fechaLlegada) AS dia, cantidad
            FROM agendapresupuestosubcategoria 
            WHERE idAgendaPresupuesto = :idagenda ";

        if ($this->crossDocking){
            $datos = $datos . ' AND crossDocking = ' . "'" . $this->crossDocking . "'";
        }

        if ($this->categoria){
            $categoria = $this->categoria;
        }

        if ($categoria) {
            $datos = $datos . ' AND categoria = ' . "'" . $categoria . "'";
        }

        if ($this->subcategoria){
            $datos = $datos . ' AND subcategoria = ' . "'" . $this->subcategoria . "'";
        }

        if ($lista){
            $valoresEscapados = array_map([$db, 'quoteValue'], $lista);

            $listaValores = implode(",", $valoresEscapados);

            $datos = $datos . ' AND crossDocking IN (' . $listaValores . ')';
        }

        $datos = $datos . ")";

        // Consulta SQL con PIVOT para obtener los datos
        $sql = "
            SELECT crossDocking, categoria, subcategoria,
            COALESCE([1],0) AS dia1, 
            COALESCE([2],0) AS dia2, 
            COALESCE([3],0) AS dia3, 
            COALESCE([4],0) AS dia4, 
            COALESCE([5],0) AS dia5, 
            COALESCE([6],0) AS dia6, 
            COALESCE([7],0) AS dia7, 
            COALESCE([8],0) AS dia8, 
            COALESCE([9],0) AS dia9, 
            COALESCE([10],0) AS dia10, 
            COALESCE([11],0) AS dia11, 
            COALESCE([12],0) AS dia12, 
            COALESCE([13],0) AS dia13, 
            COALESCE([14],0) AS dia14, 
            COALESCE([15],0) AS dia15, 
            COALESCE([16],0) AS dia16, 
            COALESCE([17],0) AS dia17, 
            COALESCE([18],0) AS dia18, 
            COALESCE([19],0) AS dia19, 
            COALESCE([20],0) AS dia20, 
            COALESCE([21],0) AS dia21, 
            COALESCE([22],0) AS dia22, 
            COALESCE([23],0) AS dia23, 
            COALESCE([24],0) AS dia24, 
            COALESCE([25],0) AS dia25, 
            COALESCE([26],0) AS dia26, 
            COALESCE([27],0) AS dia27, 
            COALESCE([28],0) AS dia28, 
            COALESCE([29],0) AS dia29, 
            COALESCE([30],0) AS dia30, 
            COALESCE([31],0) AS dia31
            FROM ";
            
        $sql = $sql . $datos . 
        " AS datos
            PIVOT (
                SUM(cantidad)
                FOR dia IN ([1], [2], [3], [4], [5], [6], [7], [8], [9], [10], 
                [11], [12], [13], [14], [15], [16], [17], [18], [19], [20], 
                [21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31])
                -- Agrega más días aquí si es necesario
            ) AS pivoted
            ORDER BY crossDocking, categoria, subcategoria
        ";


        $dataProvider = new  SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':idagenda' => $idagenda,
            ],
            'pagination' => false,
            'db' => $db,
        ]);

        return $dataProvider;
    }

    public function searchPivotCupoSubcategoriaDia($params, $idagenda, $lista = null, $categoria = null, $subactegoria = null)
    {

        $db = Yii::$app->db;

        if ($params){
            $this->load($params);
        }

        $datos = "(
            SELECT 
            CASE 
                WHEN crossDocking = 'CEDI' THEN 'CEDI + TIENDAS'
                WHEN crossDocking = 'TIENDAS' THEN 'CEDI + TIENDAS'
                ELSE crossDocking
            END AS crossDocking, categoria, subcategoria, DAY(fechaLlegada) AS dia, 
            (cantidad - COALESCE(cantidadAgendada,0)) AS cupo
            FROM agendapresupuestosubcategoria 
            WHERE idAgendaPresupuesto = :idagenda ";

        if ($this->crossDocking){
            $datos = $datos . ' AND crossDocking = ' . "'" . $this->crossDocking . "'";
        }

        if ($this->categoria){
            $categoria = $this->categoria;
        }

        if ($categoria) {
            $datos = $datos . ' AND categoria = ' . "'" . $categoria . "'";
        }

        if ($this->subcategoria){
            $datos = $datos . ' AND subcategoria = ' . "'" . $this->subcategoria . "'";
        }

        if ($lista){
            $valoresEscapados = array_map([$db, 'quoteValue'], $lista);

            $listaValores = implode(",", $valoresEscapados);

            $datos = $datos . ' AND crossDocking IN (' . $listaValores . ')';
        }

        $datos = $datos . ")";

        // Consulta SQL con PIVOT para obtener los datos
        $sql = "
            SELECT crossDocking, categoria, subcategoria,
            COALESCE([1],0) AS dia1, 
            COALESCE([2],0) AS dia2, 
            COALESCE([3],0) AS dia3, 
            COALESCE([4],0) AS dia4, 
            COALESCE([5],0) AS dia5, 
            COALESCE([6],0) AS dia6, 
            COALESCE([7],0) AS dia7, 
            COALESCE([8],0) AS dia8, 
            COALESCE([9],0) AS dia9, 
            COALESCE([10],0) AS dia10, 
            COALESCE([11],0) AS dia11, 
            COALESCE([12],0) AS dia12, 
            COALESCE([13],0) AS dia13, 
            COALESCE([14],0) AS dia14, 
            COALESCE([15],0) AS dia15, 
            COALESCE([16],0) AS dia16, 
            COALESCE([17],0) AS dia17, 
            COALESCE([18],0) AS dia18, 
            COALESCE([19],0) AS dia19, 
            COALESCE([20],0) AS dia20, 
            COALESCE([21],0) AS dia21, 
            COALESCE([22],0) AS dia22, 
            COALESCE([23],0) AS dia23, 
            COALESCE([24],0) AS dia24, 
            COALESCE([25],0) AS dia25, 
            COALESCE([26],0) AS dia26, 
            COALESCE([27],0) AS dia27, 
            COALESCE([28],0) AS dia28, 
            COALESCE([29],0) AS dia29, 
            COALESCE([30],0) AS dia30, 
            COALESCE([31],0) AS dia31
            FROM ";
            
        $sql = $sql . $datos . 
        " AS datos
            PIVOT (
                SUM(cupo)
                FOR dia IN ([1], [2], [3], [4], [5], [6], [7], [8], [9], [10], 
                [11], [12], [13], [14], [15], [16], [17], [18], [19], [20], 
                [21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31])
                -- Agrega más días aquí si es necesario
            ) AS pivoted
            ORDER BY crossDocking, categoria, subcategoria
        ";


        $dataProvider = new  SqlDataProvider([
            'sql' => $sql,
            'params' => [
                ':idagenda' => $idagenda,
            ],
            'pagination' => false,
            'db' => $db,
        ]);

        return $dataProvider;
    }
}
