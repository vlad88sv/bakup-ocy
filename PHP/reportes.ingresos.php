<?php
// Ingresos de contenedores por TEUS en los ultimos 30 dias
$c = '
SELECT DATE( t1.fechatiempo_ingreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY DATE( t1.fechatiempo_ingreso ) 
ORDER BY DATE( t1.fechatiempo_ingreso ) ASC
LIMIT 30
';
$rIngresos = db_consultar($c);


// Ingreso de contenedores por TEUS en los ultimos 12 meses
$c = '
SELECT DATE_FORMAT(t1.fechatiempo_ingreso,"%m/%y") AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY DATE_FORMAT(t1.fechatiempo_ingreso,"%m/%y") 
ORDER BY DATE_FORMAT(t1.fechatiempo_ingreso,"%m/%y") ASC
LIMIT 12
';

$rIngresosMes = db_consultar($c);

// Ingreso de contenedores por TEUS en los ultimos 5 años
$c = '
SELECT YEAR( t1.fechatiempo_ingreso ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY YEAR( t1.fechatiempo_ingreso ) 
ORDER BY YEAR( t1.fechatiempo_ingreso ) ASC
LIMIT 5';

$rIngresosAno = db_consultar($c);
?>
<h1>Ingresos de contenedores (por TEU)</h1>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rIngresos,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rIngresosMes,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v3"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rIngresosAno,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
  function drawVisualization() {
<?php echo gCol($rIngresos,"TEU en los ultimos 30 días con ingresos",'v1','TEU','Cantidad'); ?>
<?php echo gCol($rIngresosMes,"TEU en los ultimos 12 meses con ingresos",'v2','TEU','Cantidad'); ?>
<?php echo gCol($rIngresosAno,"TEU en los ultimos 5 años con ingresos",'v3','TEU','Cantidad'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>