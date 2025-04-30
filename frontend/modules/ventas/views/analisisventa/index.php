<?php
$reportId = "eyJrIjoiYmZiYTVlOTUtZTlmMC00Y2ZjLWEzMDAtOTExMDM0ZjJkY2M1IiwidCI6ImM1ZWYwODEzLWUxOGQtNGY1ZS1iZTA0LTg4OTFkYWE3ODBlMCJ9";
$filter = urlencode("GnralData/TIENDA eq 'JAMUNDI'"); // Asegúrate de usar el nombre real de la tabla y campo en Power BI
$url = "https://app.powerbi.com/view?r=$reportId&filter=$filter";
?>

<iframe title="Informe de Ventas Grupo Mayorista" width="1140" height="541.25" src="<?= $url ?>" frameborder="0"
    allowFullScreen="true">
</iframe>
