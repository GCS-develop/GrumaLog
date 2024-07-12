<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Facturadetalle;

use common\models\InventariosWs;
use frontend\modules\ventas\models\Tempexistencia;
use frontend\modules\ventas\models\Transferencia;

/**
 * FacturadetalleSearch represents the model behind the search form of `frontend\modules\ventas\models\Facturadetalle`.
 */
class FacturadetalleSearch extends Facturadetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idFactura', 'cantidadBase', 'error'], 'integer'],
            [['codigoBarra', 'item', 'color', 'talla', 'unidadMedida', 'bodega', 
            'motivo', 'descripcion', 'referencia', 'tipoMovimiento'], 'safe'],
            [['precioUnitario'], 'number'],
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
    public function search($params, $idfactura)
    {
        $query = Facturadetalle::find()->where(['idFactura' => $idfactura]);

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
            'idFactura' => $this->idFactura,
            'error' => $this->error,
            'cantidadBase' => $this->cantidadBase,
            'precioUnitario' => $this->precioUnitario,
            'tipoMovimiento' => $this->tipoMovimiento,
        ]);

        $query->andFilterWhere(['like', 'codigoBarra', $this->codigoBarra])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'unidadMedida', $this->unidadMedida])
            ->andFilterWhere(['like', 'bodega', $this->bodega])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'motivo', $this->motivo]);

        return $dataProvider;
    }

    public static function buscarBodega ($idfactura){

        $inventario = new InventariosWs();

        ini_set('memory_limit', '8G'); // Aumentar el límite de memoria a 256 MB (puedes ajustar este valor según tus necesidades)
        ini_set('max_execution_time', '7200'); //300 seconds = 5 minutes

        $bodega = array();

        $modeldetalle = Facturadetalle::find()
                                    ->select([
                                        'codigoBarra'
                                        , 'item'
                                        , 'referencia'
                                        , 'descripcion'
                                        , 'color'
                                        , 'talla'
                                        , 'precioUnitario'
                                        , 'SUM(cantidadBase) AS cantidadBase' 
                                    ])
                                    ->where(['idFactura' => $idfactura])
                                    ->andWhere(['<>', 'codigoBarra', ''])
                                    ->andWhere(['<>', 'cantidadBase', 0])
                                    ->andWhere(['=', 'bodega', ''])
                                    ->groupBy([
                                        'codigoBarra'
                                        , 'item'
                                        , 'referencia'
                                        , 'descripcion'
                                        , 'color'
                                        , 'talla'
                                        , 'precioUnitario'
                                    ])
                                    ->orderBy(['codigoBarra' => SORT_ASC])->all();

        $affectedRows = Transferencia::deleteAll(['idFactura' => $idfactura]);

        foreach($modeldetalle as $detalle){
            
            $ean = $detalle->codigoBarra;

            $responseData = $inventario->getAllInventariosSiesa ($ean);

            if (!is_array($responseData)){
                continue;
            }

            $cantidadtotal = $detalle->cantidadBase;

            foreach($responseData as $data){
                $salir = false;
                if (isset($data["CantidadDisponible"])) {
                    if ($data["CantidadDisponible"] > 0){

                        if ($data["CantidadDisponible"] >= $cantidadtotal){
                            $cantidad = $cantidadtotal;
                            $salir = true;
                        }else{
                            $cantidadtotal = $cantidadtotal - $data["CantidadDisponible"];
                            $cantidad = $data["CantidadDisponible"];
                        }

                        $item = new Transferencia();
                        $item->idFactura = $idfactura;
                        $item->bodega = $data['Bodega'];
                        $item->codigoBarra = $ean;
                        $item->cantidadBase = $cantidad;
                        $item->precioUnitario = $detalle->precioUnitario;
                        $item->item = $detalle->item;
                        $item->talla = $detalle->talla;
                        $item->color = $detalle->color;
                        $item->unidadMedida = 'UND';
                        $item->motivo = '02';
                        $item->referencia = $detalle->referencia;
                        $item->descripcion = $detalle->descripcion;
                        $item->save();

                        if ($salir){
                            break;
                        }
                    }
                }
            }

            /*$model = Facturadetalle::findOne(['id' => $detalle->id]);
            $model->cantidadTotal = $cantidadtotal;

            if ($cantidadtotal > 0){
                $model->error = 1;
            }
            $model->save();*/
        }
    }
}
