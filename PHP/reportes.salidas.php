<?php
$c = '
SELECT DATE( t1.fechatiempo_egreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU despachados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY DATE( t1.fechatiempo_egreso ) 
ORDER BY DATE( t1.fechatiempo_egreso ) ASC
LIMIT 30
';
$rSalidas = db_consultar($c);


$c = '
SELECT DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU despachados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") 
ORDER BY DATE_FORMAT(t1.fechatiempo_egreso,"%m/%y") ASC
LIMIT 12
';

$rSalidasMes = db_consultar($c);

$c = '
SELECT YEAR( t1.fechatiempo_egreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU despachados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE t1.fechatiempo_egreso IS NOT NULL
GROUP BY YEAR( t1.fechatiempo_egreso ) 
ORDER BY YEAR( t1.fechatiempo_egreso ) ASC
LIMIT 5';

$rSalidasAno = db_consultar($c);

$c = '
SELECT tipo_salida AS "Tipo de salida", COUNT(*) AS "Cantidad" FROM opsal_ordenes WHERE estado="fuera" GROUP BY tipo_salida
';

$rTipoSalida = db_consultar($c);

?>
<h1>Salidas de contenedores (por TEU)</h1>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v5"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTipoSalida,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidas,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidasMes,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v3"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rSalidasAno,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
  function drawVisualization() {
<?php echo gpie($rTipoSalida,"Tipos de salida",'v5','Tipo de salida','Cantidad'); ?>
<?php echo gCol($rSalidas,"Despachos de TEU en los ultimos 30 días",'v1','TEU','Cantidad'); ?>
<?php echo gCol($rSalidasMes,"Despachos de TEU en los ultimos 12 meses",'v2','TEU','Cantidad'); ?>
<?php echo gCol($rSalidasAno,"Despachos de TEU en los ultimos 5 años",'v3','TEU','Cantidad'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>