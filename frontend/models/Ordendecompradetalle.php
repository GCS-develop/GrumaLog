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
            [['idOrdenCompra', 'idItem', 'created_by', 'updated_by', 'idCategoria', 'idSubcategoria',
            'nroPaquetes'], 'integer'],
            [['cantidadPedida', 'cantidadEntrada', 'cantidadPendiente'], 'number'],
            [['fechaEntrega', 'created_at', 'updated_at', 'unidadPaquete', 'nitcomprador', 
            'comprador', 'bodega', 'codigointernomovto'], 'safe'],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
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

    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
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
                                            ->andWhere(['IS NOT', 'idItem', null]) 
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

    public static function insertarDetalleOC($idordencompra, $datos){

        $numeroitems = 0;

        $totalCantidadPedida = 0;
        $totalCantidadEntrada = 0;
        $totalCantidadPendiente = 0;
        $totalPaquetes = 0;
        $fechaentrega = null;

        $numRegistrosBorrados = Ordendecompradetalle::deleteAll(['idOrdenCompra' => $idordencompra]);

        foreach ($datos as $registro) {

            $modelitem = Ordendecompradetalle::actualizarItemOC ($registro);
            if ($modelitem){

                $unidadPaquete = 'UND';
                $cantidadPendiente = $registro['cantidadPendiente'];

                if ($modelitem->unidadEmpaque){
                    $unidadPaquete = $modelitem->unidadEmpaque;
                    $nroPaquetes = $cantidadPendiente / 
                                    $modelitem->unidadempaque->equivalencia;
                }else{
                    $nroPaquetes = $cantidadPendiente;
                }

                if (fmod($nroPaquetes, 1) != 0){
                    $numeroitems = -1;
                    break;
                }

                $modeldetalle = new Ordendecompradetalle();
                $modeldetalle->idOrdenCompra = $idordencompra;
                $modeldetalle->idItem = $modelitem->id;
                $modeldetalle->idCategoria = $modelitem->idCategoria;
                $modeldetalle->idSubcategoria = $modelitem->idSubcategoria;
                $modeldetalle->fechaEntrega = $registro['fechaEntrega'];
                $fechaentrega = $registro['fechaEntrega'];

                $modeldetalle->unidadPaquete = $unidadPaquete;
                // $modeldetalle->nroPaquetes = (int) $nroPaquetes;
                $modeldetalle->nroPaquetes = $nroPaquetes;

                $modeldetalle->cantidadPedida = $registro['cantidadPedida'];
                $modeldetalle->cantidadEntrada = $registro['cantidadEntrada'];
                $modeldetalle->cantidadPendiente = $registro['cantidadPendiente'];

                $totalCantidadPedida = $totalCantidadPedida + $registro['cantidadPedida'];
                $totalCantidadEntrada = $totalCantidadEntrada + $registro['cantidadEntrada'];
                $totalCantidadPendiente = $totalCantidadPendiente + $registro['cantidadPendiente'];
                $totalPaquetes = $totalPaquetes + $modeldetalle->nroPaquetes;

                $numeroitems++;

                if (!$modeldetalle->save()){
                    echo $nroPaquetes;
                    var_dump($modeldetalle->getErrors()); die("hola");
                }
            }

            $modelordencompra = Ordendecompra::findOne(['id' => $idordencompra]);
            if ($modelordencompra){
                $modelordencompra->totalCantidadPedida = $totalCantidadPedida;
                $modelordencompra->totalCantidadEntrada = $totalCantidadEntrada;
                $modelordencompra->totalCantidadPendiente = $totalCantidadPendiente;
                $modelordencompra->nroPaquetes = $totalPaquetes;
                $modelordencompra->fechaEntrega = $fechaentrega;

                $modelordencompra->save();
            }

        }

        return $numeroitems;

    }

    public static function actualizarItemOC ($fila) {

        $codigobarras = $fila['codigoBarras'];
        $item = $fila['item'];

        $codigo = $fila['idTalla'];
		$nombre = $fila['talla'];
        $idtalla = Talla::actualizarRegistro ($codigo, $nombre);

		$codigo = $fila['idColor'];
		$nombre = $fila['color'];
		$idcolor = Color::actualizarRegistro ($codigo, $nombre);

        if ($codigobarras){
            $model = Item::findOne(['codigoBarras' => $codigobarras]);
            if ($model == null){
                $model = new Item();
                $model->codigoBarras = $codigobarras;
            }
        }else{
            $model = Item::findOne([
                                            'item' => $item,
                                            'idTalla' => $idtalla,
                                            'idColor' => $idcolor
                                        ]);

            if ($model == null){
                $model = new Item();
                $model->codigoBarras = null;
            }
        }

        $modelaux = new Categoria ();
        $modelaux->codigoERP = $fila['idCategoria'];
        $modelaux->nombre = $fila['categoria'];
		$idcategoria = Categoria::actualizarRegistro ($modelaux);

        $modelaux = new Subcategoria ();
        $modelaux->codigoERP = $fila['idSubcategoria'];
        $modelaux->nombre = $fila['subcategoria'];
        $modelaux->idCategoria = $idcategoria;
		$idsubcategoria = Subcategoria::actualizarRegistro ($modelaux);

        $modelaux = new Marca();
		$modelaux->codigo = $fila['idMarca'];
        $modelaux->nombre = $fila['marca'];
        $idmarca = Marca::actualizarRegistro($modelaux);

        $modelaux = new Producto();
		$modelaux->codigo = $fila['idProducto'];
        $modelaux->nombre = $fila['producto'];
        $idproducto = Producto::actualizarRegistro($modelaux);

        $model->item = $item;
        $model->referencia = $fila['referencia'];
		$model->descripcion = $fila['descripcion'];
        $model->idCategoria = $idcategoria;
        $model->idSubcategoria = $idsubcategoria;
        $model->idColor = $idcolor;
        $model->idTalla = $idtalla;
        $model->idMarca = $idmarca;
        $model->idProducto = $idproducto;
        $model->codigoProveedor = $fila['idProveedor'];
		$model->nombreProveedor = $fila['proveedor'];

        $model->unidadEmpaque = $fila['unidadEmpaque'];
		$model->unidadOrden = $fila['unidadOrden'];
        $model->idEstado = $fila['estadoItem'];

        if ($model->save()){
            return $model;
        } 

        return null;
    }

}
