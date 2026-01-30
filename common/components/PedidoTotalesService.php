<?php

// app/services/TotalesPedidoService.php
namespace common\components;

use Yii;

class PedidoTotalesService
{
    /** Recalcula totales a partir de la OC (poc.id) */
    public static function recalcFromOcId($idpedido, $idordencompra): void
    {
        $db = Yii::$app->db;

        try {
            Yii::$app->db->createCommand(
                'EXEC [dbo].[usp_Pedido_RecalcularTotalesPorOC] :idPedido, :idOrdenCompra',
                [':idPedido' => $idpedido, ':idOrdenCompra' => $idordencompra]
            )->execute();
        } catch (\yii\db\Exception $e) {
            Yii::error($e->getMessage() . "\n" . $e->getTraceAsString(), __METHOD__);
            throw new \DomainException('No se pudo recalcular los totales.');
        }
    }
}
