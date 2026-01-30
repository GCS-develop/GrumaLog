<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use common\models\InventariosWs;
use common\models\User;

class InventariosWsPrintSearch extends Model
{
    public $Item;
    public $EAN;
    public $Extension1;
    public $Extension2;

    public function rules()
    {
        return [
            [['Item', 'EAN', 'Extension1', 'Extension2'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Search específico para la vista de impresión:
     * - Si buscas por EAN -> solo la bodega del usuario.
     * - Si buscas por Item -> TODAS las bodegas (sin filtrar por bodega).
     */
    public function search($params)
    {
        $this->load($params);

        $ean   = $this->EAN ? trim($this->EAN) : null;
        $item  = $this->Item ? trim($this->Item) : null;
        $ext1  = $this->Extension1 ? trim($this->Extension1) : null;
        $ext2  = $this->Extension2 ? trim($this->Extension2) : null;

        $byEan  = !empty($ean);
        $byItem = !empty($item);

        $inv  = new InventariosWs();
        $rows = [];

        // 1) INVENTARIO DESDE WS "Inventarios"
        if ($byEan) {
            // Buscar directamente por EAN en Inventarios
            $rows = $inv->getAllInventariosSiesa($ean, null, 'EAN', 'Inventarios');
        } elseif ($byItem) {
            // Buscar primero en Productos por Item para obtener los EAN
            $productos = $inv->getAllInventariosSiesa(null, $item, 'Item', 'Productos');
            $eans = array_values(array_unique(array_filter(array_column($productos, 'CodigoBarras'))));

            if (!empty($eans)) {
                foreach ($eans as $eanFromItem) {
                    // OJO: aquí Inventarios normalmente trae TODAS las bodegas para ese EAN
                    $chunk = $inv->getAllInventariosSiesa($eanFromItem, null, 'EAN', 'Inventarios');
                    if (!empty($chunk)) {
                        $rows = array_merge($rows, $chunk);
                    }
                }
            } else {
                $rows = [];
            }
        } else {
            // Sin Item ni EAN no tiene sentido llamar al WS
            $rows = [];
        }

        if (empty($rows)) {
            return new ArrayDataProvider([
                'allModels'  => [],
                'pagination' => ['pageSize' => 50],
            ]);
        }

        // Normalizador
        $norm = function ($v) {
            $v = is_scalar($v) ? (string)$v : '';
            $v = strtoupper(trim($v));
            $v = ltrim($v, '0');
            return $v;
        };

        // 2) FILTROS
        if ($byEan) {
            // 🔹 SOLO cuando se busca por EAN filtramos por bodega del usuario
            $codigoBodega = User::getCodigoCOUsuario(\Yii::$app->user->id);
            if (!$codigoBodega) {
                return new ArrayDataProvider([
                    'allModels'  => [],
                    'pagination' => ['pageSize' => 50],
                ]);
            }
            $codigoRef = $norm($codigoBodega);

            $rows = array_values(array_filter($rows, function ($r) use ($norm, $codigoRef, $ext1, $ext2) {
                $b1 = array_key_exists('Bodega', $r) ? $norm($r['Bodega']) : null;
                if ($b1 === null || $b1 !== $codigoRef) {
                    return false;
                }

                if ($ext1) {
                    $rExt1 = $norm($r['Extension1'] ?? '');
                    if (strpos($rExt1, $norm($ext1)) === false) {
                        return false;
                    }
                }
                if ($ext2) {
                    $rExt2 = $norm($r['Extension2'] ?? '');
                    if (strpos($rExt2, $norm($ext2)) === false) {
                        return false;
                    }
                }
                return true;
            }));
        }

        if ($byItem) {
            // 🔹 Cuando se busca por ITEM, NO filtramos por bodega
            // solo opcionalmente por color / talla
            if ($ext1) {
                $needle = $norm($ext1);
                $rows = array_values(array_filter($rows, function ($r) use ($norm, $needle) {
                    $rExt1 = $norm($r['Extension1'] ?? '');
                    return strpos($rExt1, $needle) !== false;
                }));
            }

            if ($ext2) {
                $needle = $norm($ext2);
                $rows = array_values(array_filter($rows, function ($r) use ($norm, $needle) {
                    $rExt2 = $norm($r['Extension2'] ?? '');
                    return strpos($rExt2, $needle) !== false;
                }));
            }
        }

        // 3) PRECIOS DESDE SQL (TU QUERY getUltimoPrecioSiesa)
        $preciosRows = [];
        if ($byEan) {
            $preciosRows = $inv->getUltimoPrecioSiesa($ean, null);
        } elseif ($byItem) {
            $preciosRows = $inv->getUltimoPrecioSiesa(null, $item);
        }

        $mapPrecios = InventariosWs::mapPreciosPorEANDesdeUltimoPrecio($preciosRows);

        foreach ($rows as &$r) {
            $eanRow = $r['EAN'] ?? $r['CodigoBarras'] ?? null;
            if ($eanRow && isset($mapPrecios[$eanRow])) {
                $info = $mapPrecios[$eanRow];

                $r['Price']       = $info['precio_venta'];
                $r['PrecioCampo'] = $info['campo_precio'];

                if (!empty($info['costo'])) {
                    $r['CostoPromedioUnitario'] = $info['costo'];
                }
            }
        }
        unset($r);

        return new ArrayDataProvider([
            'allModels'  => $rows,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'attributes' => [
                    'Item',
                    'Referencia',
                    'Descripcion',
                    'EAN',
                    'Extension1',
                    'Extension2',
                    'Bodega',
                    'NombreBodega',
                    'CantidadExistente',
                    'CantidadDisponible',
                    'CantidadComprometida',
                    'CantidadDisponible_POS',
                    'CostoPromedioUnitario',
                ],
            ],
        ]);
    }
}
