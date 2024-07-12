<?php

// Definimos una clase para representar un artículo en la bodega
class Existencia {
    public $codigoBodega;
    public $codigoEAN;
    public $cantidadDisponible;
    public $cantidadSolicitada;
    public $cantidadTotal;

    public function __construct($codigoBodega, $codigoEAN, $cantidadExistente) {
        $this->codigoBodega = $codigoBodega;
        $this->codigoEAN = $codigoEAN;
        $this->cantidadExistente = $cantidadExistente;
    }

    public function calcularCantidadDisponible($cantidadSolicitada) {
        return $this->cantidadExistente - $cantidadSolicitada;
    }
}

// Ejemplo de uso:


// Creamos una función para buscar un artículo en una bodega
function buscarItem($codigoEAN, $cantidad, $bodega) {
    
    foreach ($bodega as $item) {
        // Verificamos si ya existe un artículo con ese EAN y si tiene cantidad
        if ($item->codigoEAN === $codigoEAN) {
            if ($item['cantidadTotal'] >= $cantidad){
                return $item;
            }
        }
    }

    return null;
}

// Creamos una función para agregar un artículo a la bodega
function agregarItem($codigoBodega, $codigoEAN, $cantidadDisponible, &$bodega) {
    // Verificamos si ya existe un artículo con ese código de bodega y código de item
    foreach ($bodega as $item) {
        if ($item->codigoBodega === $codigoBodega && $item->codigoEAN === $codigoEAN) {
            // Si ya existe, mostramos un mensaje y no hacemos nada
            return $item;
        }
    }

    // Si no existe, creamos el artículo y lo agregamos al arreglo de la bodega
    $bodega[] = new Existencia ($codigoBodega, $codigoEAN, $cantidadExistente);
    return null;
}

// Ejemplo de uso:

// Arreglo para simular la bodega
$bodega = array();

// Agregamos algunos artículos a la bodega
agregarArticulo('B001', 'A001', 50, 'Proveedor A', $bodega);
agregarArticulo('B002', 'A002', 30, 'Proveedor B', $bodega);
agregarArticulo('B001', 'A001', 60, 'Proveedor C', $bodega); // Intentamos agregar un duplicado

// Mostramos todos los artículos en la bodega
echo "Artículos en la bodega:<br>";
foreach ($bodega as $articulo) {
    echo $articulo . "<br>";
}
?>
