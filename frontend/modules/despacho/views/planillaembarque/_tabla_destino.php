<?php

use yii\helpers\Url;

/* @var $rows string */
/* @var $destino string */
/* @var $totalDestino int */
/* @var $fechaDespacho string */

?>

<!-- Definir los estilos CSS para la tabla -->
<style>
    table {
        font-family: Arial, sans-serif; /* Tipo de letra */
        font-size: 12px; /* Tamaño de letra */
    }
    th {
        background-color: #f2f2f2; /* Color de fondo para el encabezado */
        font-weight: bold;
    }
    td {
        padding: 5px;
    }

    .detalle-info {
        font-family: Arial, sans-serif; /* Tipo de letra */
        font-size: 12px; /* Tamaño de letra */
        margin-bottom: 20px; /* Espacio entre el div y la tabla */
    }
    .detalle-info strong {
        margin-right: 10px; /* Espaciado entre la negrita y el texto siguiente */
    }
    .detalle-info span {
        display: inline-block;
        margin-right: 20px; /* Espaciado entre los diferentes bloques de información */
    }

    .encabezado {
        font-family: Arial, sans-serif; /* Tipo de letra */
        font-size: 12px; /* Tamaño de letra */
    }
    .encabezado img {
        width: 100px; /* Ajusta el tamaño de la imagen */
        height: auto;
    }
    .encabezado td {
        vertical-align: top; /* Alinea el contenido de las celdas superiormente */
        padding-left: 15px; /* Espaciado entre la imagen y el texto */
    }
</style>

<!-- Encabezado con imagen y datos alineados -->
<table class="encabezado" width="100%">
    <tr>
        <!-- Columna para la imagen -->
        <td width="25%">
            <!-- Usar el helper Url::to para generar la ruta a la imagen -->
            <img src="<?= Url::to('@web/imagenes/logo-herpo-reporte.png', true) ?>" alt="Logo de la Empresa">
        </td>

        <!-- Columna para los datos de NIT, dirección y email -->
        <td>
            <strong>NIT:</strong> <?= $planillaData['nitEmpresa'] . ' ' . $planillaData['nombreEmpresa'] ?> <br>
            <strong>Dirección:</strong> <?= $planillaData['direccion'] ?> 
            <strong>Teléfono:</strong> <?= $planillaData['telefono'] ?> 
            <strong>Ciudad:</strong> <?= $planillaData['ciudad'] ?> <br>
            <strong>Página WEB:</strong> <?= $planillaData['paginaweb'] ?> 
            <strong>Email:</strong> <?= $planillaData['email'] ?>
        </td>
    </tr>
</table>

<hr>

<h3>PLANILLA DE EMBARQUE GRUMA PRINCIPAL No <?= $planillaembarque->id ?> </h3>

<div class="detalle-info">
<span><strong>Fecha:</strong> <?= $planillaembarque->fechaDespacho . ' ' . $planillaembarque->horaDespacho ?></span>
<span><strong>Conductor:</strong> <?= $planillaembarque->idConductor . ' ' . $planillaembarque->nombreConductor ?></span>
<span><strong>Vehículo:</strong> <?= $planillaembarque->placa ?></span>
<span><strong>Sello:</strong> <?= $planillaembarque->sello ?></span>
</div>

<table border="1" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Número</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Unidades</th>
            <th>Unid. Emp.</th>
            <th>Sello Destino</th>
            <th>Destino</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right;"><strong>Total para <?= $destino ?>:</strong></td>
            <td style="text-align: right;"><?= $totalDestino ?></td>
            <td style="text-align: right;"><?= $totalUnidadesEmp ?></td>
        </tr>
    </tfoot>
    <tbody>
        <?= $rows ?>
    </tbody>
</table>

<hr>
