<?php
// Movimientos de contenedores por TEUS en los ultimos 30 dias
$c = '
SELECT DATE( t0.fechatiempo ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM `opsal_movimientos` AS t0
LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden)
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY DATE( t0.fechatiempo ) 
ORDER BY DATE( t0.fechatiempo ) ASC
LIMIT 30
';
$rMovimientos = db_consultar($c);


// Ingreso de contenedores por TEUS en los ultimos 12 meses
$c = '
SELECT DATE_FORMAT( t0.fechatiempo,"%m/%y" ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM `opsal_movimientos` AS t0
LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden)
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY DATE_FORMAT( t0.fechatiempo,"%m/%y" ) 
ORDER BY DATE_FORMAT( t0.fechatiempo,"%m/%y" ) ASC
LIMIT 12
';

$rMovimientosMes = db_consultar($c);

// Ingreso de contenedores por TEUS en los ultimos 5 años
$c = '
SELECT YEAR( t0.fechatiempo ) AS  "Fecha", SUM( t3.`TEU` ) AS  "TEU ingresados"
FROM `opsal_movimientos` AS t0
LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden)
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
GROUP BY YEAR( t0.fechatiempo ) 
ORDER BY YEAR( t0.fechatiempo ) ASC
LIMIT 5
';

$rMovimientosAno = db_consultar($c);
?>
<h1>Movimientos de contenedores (por TEU)</h1>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rMovimientos,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rMovimientosMes,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v3"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rMovimientosAno,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
  function drawVisualization() {
<?php echo gCol($rMovimientos,"TEU en los ultimos 30 días con movimientos",'v1','TEU','Cantidad'); ?>
<?php echo gCol($rMovimientosMes,"TEU en los ultimos 12 meses con movimientos",'v2','TEU','Cantidad'); ?>
<?php echo gCol($rMovimientosAno,"TEU en los ultimos 5 años con movimientos",'v3','TEU','Cantidad'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>