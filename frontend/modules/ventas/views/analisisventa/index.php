<?php
$reportId = "eyJrIjoiYWM4N2M2MjktZTEyMC00OTU5LWIyMjgtNzgwY2M1MTczZjFkIiwidCI6ImM1ZWYwODEzLWUxOGQtNGY1ZS1iZTA0LTg4OTFkYWE3ODBlMCJ9";
$filter = urlencode("GnralData/TIENDA eq 'JAMUNDI'"); // Asegúrate de usar el nombre real de la tabla y campo en Power BI
$url = "https://app.powerbi.com/view?r=$reportId&filter=$filter";
?>

<iframe title="Informe de Ventas Grupo Mayorista" width="1140" height="541.25" src="<?= $url ?>" frameborder="0"
    allowFullScreen="true">
</iframe>


<!-- src="https://app.powerbi.com/view?r=eyJrIjoiYWM4N2M2MjktZTEyMC00OTU5LWIyMjgtNzgwY2M1MTczZjFkIiwidCI6ImM1ZWYwODEzLWUxOGQtNGY1ZS1iZTA0LTg4OTFkYWE3ODBlMCJ9&pageName=ReportSection2"  -->