<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "ordendecompradetalle".
 *
 * @property int $id
 * @property int|null $idOrdenCompra
 * @property int|null $idItem
 * @property float|null $cantidadPedida
 * @property float|null $cantidadEntrada
 * @property float|null $cantidadPendiente
 * @property string|null $fechaEntrega
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Ordendecompradetalle extends \yii\db\ActiveRecord
{
    public $categoria;
    public $subcategoria;
    public $cantidad;

    public $item;
    public $consecutivo;
    public $referencia;
    public $descripcion;
    public $marca;
    public $talla;
    public $color;
    public $unidadEmpaque;
    public $codigoBarras;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordendecompradetalle';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idOrdenCompra', 'idItem', 'created_by', 'updated_by', 'idCategoria', 'idSubcategoria'], 
            'integer'],
            [['cantidadPedida', 'cantidadEntrada', 'cantidadPendiente'], 'number'],
            [['fechaEntrega', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idOrdenCompra' => 'Id Orden Compra',
            'idItem' => 'Id Item',
            'cantidadPedida' => 'Cantidad Pedida',
            'cantidadEntrada' => 'Cantidad Entrada',
            'cantidadPendiente' => 'Cantidad Pendiente',
            'fechaEntrega' => 'Fecha Entrega',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

        /**
     * Gets query for [[OrdenCompra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrdenCompra()
    {
        return $this->hasOne(Ordencompra::class, ['id' => 'idOrdenCompra']);
    }

    public static function listarCategoriasOrdenCompra ($idordencompra)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'det.idCategoria', 
                                'cat.nombre AS categoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'categoria cat','det.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','det.idSubcategoria = sub.id')
                    ->groupBy(['det.idOrdenCompra', 'det.idCategoria', 'cat.nombre'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra]);

        $resultados = $query->all();

        return $resultados;
    }

    public static function listarSubcategoriasOrdenCompra ($idordencompra)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'cat.nombre AS categoria', 
                                'sub.nombre AS subcategoria', 
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'categoria cat','det.idCategoria = cat.id')
                    ->join('INNER JOIN', 'subcategoria sub','det.idSubcategoria = sub.id')
                    ->groupBy(['det.idOrdenCompra', 'cat.nombre' , 'sub.nombre'])
                    ->andWhere(['det.idOrdenCompra' => $idordencompra]);

        $resultados = $query->all();

        foreach ($resultados as $resultado){
            return $resultado->categoria;
        }

        return null;
    }

    public static function totalCantidadPendiente ($idordencompra,$idcategoria){

        $total = Ordendecompradetalle::find()
                                            ->select(['SUM(cantidadPendiente) AS total'])
                                            ->andFilterWhere(['idOrdenCompra' => $idordencompra])
                                            ->andFilterWhere(['idCategoria' => $idcategoria])
                                            ->scalar();                                            
        return $total;
    }

    public static function totalCantidadPendientexItem ($idordencompra,$item){

        $total = Ordendecompradetalle::find()
                                            ->alias('det')
                                            ->select(['SUM(cantidadPendiente) AS total'])
                                            ->join('INNER JOIN', 'item AS it', 'det.idItem = it.id')
                                            ->andFilterWhere(['det.idOrdenCompra' => $idordencompra])
                                            ->andFilterWhere(['it.item' => $item])
                                            ->scalar();                                            
        return $total;
    }

    public static function listaReferenciasOrdenCompra ($idordencompra, $idcategoria)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'it.item', 
                                'it.referencia',
                                'it.descripcion',
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'item it','det.idItem = it.id')
                    ->where(['>', 'det.cantidadPendiente', 0]) 
                    ->andFilterWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['det.idCategoria' => $idcategoria])
                    ->groupBy(['det.idOrdenCompra', 'it.item', 'it.referencia', 'it.descripcion']);

        $resultados = $query->all();

        return $resultados;
    }

    public static function listaItemsOrdenCompra ($idordencompra, $item)
    {
        $query = Ordendecompradetalle::find()
                    ->select([  'det.idOrdenCompra', 
                                'det.idItem', 
                                'it.codigoBarras',
                                'it.item',
                                'SUM(det.cantidadPendiente) AS cantidad'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'item it','det.idItem = it.id')
                    ->where(['>', 'det.cantidadPendiente', 0]) 
                    ->andFilterWhere(['det.idOrdenCompra' => $idordencompra])
                    ->andFilterWhere(['it.item' => $item])
                    ->groupBy(['det.idOrdenCompra', 'det.idItem', 'it.codigoBarras', 'it.item']);

        $resultados = $query->all();

        return $resultados;
    }

    public static function subcategoriasOrdenCompra ($idordencompra){
        /*$query = "
            SELECT STRING_AGG(nombreSubcategoria, ',') AS nombreSubcategoria
            FROM (
                SELECT DISTINCT ocd.idOrdenCompra, sub.nombre AS nombreSubcategoria
                FROM ordendecompradetalle ocd
                INNER JOIN subcategoria sub ON ocd.idSubcategoria = sub.id
                WHERE ocd.idOrdenCompra = :idOrdenCompra
            ) AS Q1
        ";

        $nombresSubcategorias = Yii::$app->db->createCommand($query, [
            ':idOrdenCompra' => $idordencompra,
        ])->queryScalar();*/

        $query = "
                SELECT DISTINCT sub.nombre AS nombreSubcategoria
                FROM ordendecompradetalle ocd
                INNER JOIN subcategoria sub ON ocd.idSubcategoria = sub.id
                WHERE ocd.idOrdenCompra = :idOrdenCompra
        ";

        $nombresSubcategorias = Yii::$app->db->createCommand($query, [
            ':idOrdenCompra' => $idordencompra,
        ])->queryColumn();

        if (!empty($nombresSubcategorias)) {
            return implode(', ', $nombresSubcategorias);
        }

        return '-';
    }
}
