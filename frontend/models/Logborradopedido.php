<?php

namespace frontend\models;

use Yii;

/**
 * Auditoría de eliminaciones en pedidos (OC, Item, SKU).
 *
 * @property int         $id
 * @property int         $idPedido
 * @property int|null    $idOrdenCompra
 * @property string|null $consecutivoOC
 * @property string|null $tipoDocumentoOC
 * @property string|null $codigoCO
 * @property string      $accion
 * @property int|null    $idItem
 * @property string|null $itemCodigo
 * @property string|null $colorCodigo
 * @property string|null $tallaCodigo
 * @property int|null    $idBodega
 * @property string|null $bodegaCodigo
 * @property int|null    $unidadesAntes
 * @property string      $created_at
 * @property int         $created_by
 */
class Logborradopedido extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'logborradopedido';
    }

    public function rules()
    {
        return [
            [['idPedido', 'accion'], 'required'],
            [['idPedido', 'idOrdenCompra', 'idItem', 'idBodega', 'unidadesAntes', 'created_by'], 'integer'],
            [['accion'], 'string', 'max' => 30],
            [['consecutivoOC', 'itemCodigo', 'colorCodigo', 'tallaCodigo', 'bodegaCodigo'], 'string', 'max' => 50],
            [['tipoDocumentoOC', 'codigoCO'], 'string', 'max' => 10],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'idPedido'        => 'Pedido',
            'idOrdenCompra'   => 'ID OC',
            'consecutivoOC'   => 'Consecutivo OC',
            'tipoDocumentoOC' => 'Serie OC',
            'codigoCO'        => 'C.O.',
            'accion'          => 'Acción',
            'idItem'          => 'ID Item',
            'itemCodigo'      => 'Item',
            'colorCodigo'     => 'Color',
            'tallaCodigo'     => 'Talla',
            'idBodega'        => 'ID Bodega',
            'bodegaCodigo'    => 'Bodega',
            'unidadesAntes'   => 'Unidades Borradas',
            'created_at'      => 'Fecha',
            'created_by'      => 'Usuario',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }

    /**
     * Registra la eliminación de una OC completa del pedido.
     *
     * @param Pedidoordendecompra $poc
     * @param int $unidadesAntes   totalUnidades de la OC antes de borrar
     */
    public static function registrarEliminarOC(Pedidoordendecompra $poc, int $unidadesAntes): void
    {
        $log = new self();
        $log->idPedido      = $poc->idPedido;
        $log->idOrdenCompra = $poc->idOrdenCompra;
        $log->accion        = 'ELIMINAR_OC';
        $log->unidadesAntes = $unidadesAntes;
        $log->created_by    = Yii::$app->user->id;

        try {
            $oc = $poc->ordencompra;
            if ($oc) {
                $log->consecutivoOC   = $oc->consecutivo;
                $log->tipoDocumentoOC = $oc->tipoDocumento ? $oc->tipoDocumento->codigo : null;
                $log->codigoCO        = $oc->cO ? $oc->cO->codigo : null;
            }
        } catch (\Exception $e) {
            // guarda aunque no se puedan obtener los datos de OC
        }

        $log->save(false);
    }

    /**
     * Registra la eliminación de un item (pedidoordendecompraitem) del pedido.
     *
     * @param Pedidoordendecompraitem $poci
     * @param int $unidadesAntes   totalUnidades del item antes de borrar
     */
    public static function registrarEliminarItem(Pedidoordendecompraitem $poci, int $unidadesAntes): void
    {
        $log = new self();
        $log->idPedido      = $poci->idPedido;
        $log->idOrdenCompra = $poci->idOrdenCompra;
        $log->idItem        = $poci->idItem;
        $log->idBodega      = $poci->idBodega;
        $log->accion        = 'ELIMINAR_ITEM';
        $log->unidadesAntes = $unidadesAntes;
        $log->created_by    = Yii::$app->user->id;

        try {
            $oc = $poci->ordencompra;
            if ($oc) {
                $log->consecutivoOC   = $oc->consecutivo;
                $log->tipoDocumentoOC = $oc->tipoDocumento ? $oc->tipoDocumento->codigo : null;
                $log->codigoCO        = $oc->cO ? $oc->cO->codigo : null;
            }
            $item = $poci->item;
            if ($item) {
                $log->itemCodigo  = $item->item;
                $log->colorCodigo = $item->color ? $item->color->codigo : null;
                $log->tallaCodigo = $item->talla ? $item->talla->codigo : null;
            }
            $bodega = $poci->bodega;
            if ($bodega) {
                $log->bodegaCodigo = $bodega->codigo;
            }
        } catch (\Exception $e) {
            // guarda aunque no se puedan obtener datos de relaciones
        }

        $log->save(false);
    }

    /**
     * Registra la eliminación de un SKU específico (pedidodetalle) del pedido.
     *
     * @param Pedidodetalle $det
     * @param int $unidadesAntes   unidades del SKU antes de borrar
     */
    public static function registrarEliminarSku(Pedidodetalle $det, int $unidadesAntes): void
    {
        $log = new self();
        $log->idPedido      = $det->idPedido;
        $log->idOrdenCompra = $det->idOrdenCompra;
        $log->idItem        = $det->idItem;
        $log->idBodega      = $det->idBodega;
        $log->accion        = 'ELIMINAR_SKU';
        $log->unidadesAntes = $unidadesAntes;
        $log->created_by    = Yii::$app->user->id;

        try {
            $oc = $det->ordencompra;
            if ($oc) {
                $log->consecutivoOC   = $oc->consecutivo;
                $log->tipoDocumentoOC = $oc->tipoDocumento ? $oc->tipoDocumento->codigo : null;
                $log->codigoCO        = $oc->cO ? $oc->cO->codigo : null;
            }
            $item = $det->item;
            if ($item) {
                $log->itemCodigo  = $item->item;
                $log->colorCodigo = $item->color ? $item->color->codigo : null;
                $log->tallaCodigo = $item->talla ? $item->talla->codigo : null;
            }
            $bodega = $det->bodega;
            if ($bodega) {
                $log->bodegaCodigo = $bodega->codigo;
            }
        } catch (\Exception $e) {
            // guarda aunque no se puedan obtener datos de relaciones
        }

        $log->save(false);
    }
}
