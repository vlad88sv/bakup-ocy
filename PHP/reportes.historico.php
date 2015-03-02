<?php
$fecha = '';
if (isset($_POST['filtrar']))
{
 $fecha = 'AND t1.fechatiempo_ingreso >= "'.$_POST['fecha_inicio'].'" AND t1.fechatiempo_ingreso <= "'.$_POST['fecha_final'].'"';
}

// Reporte de TEU's por agencia
$c = '
SELECT t2.usuario AS  "Agencia", SUM( t3.`TEU` ) AS  "TEUS"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE 1 '.$fecha.'
GROUP BY t1.codigo_agencia
';

$rTEUS = db_consultar($c);

$tTEUS = 0;

while ($f = mysqli_fetch_array($rTEUS))
{
    $tTEUS += $f[1];
}

mysqli_data_seek($rTEUS,0);

// Reporte de contenedores/ingresos por agencia
$c = '
SELECT t2.usuario AS  "Agencia", COUNT(*) AS  "Ingresos"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
WHERE 1 '.$fecha.'
GROUP BY t1.codigo_agencia
';

$rContenedores = db_consultar($c);

$tContenedores = 0;

while ($f = mysqli_fetch_array($rContenedores))
{
    $tContenedores += $f[1];
}

mysqli_data_seek($rContenedores,0);


// REPORTE de tipo de contenedores por patio
$c = '
SELECT t3.nombre AS  "Tipo de contenedor", COUNT(*) AS  "Cantidad"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE 1 '.$fecha.'
GROUP BY t1.tipo_contenedor
';

$rTipos = db_consultar($c);

$tTipos = 0;

while ($f = mysqli_fetch_array($rTipos))
{
    $tTipos += $f[1];
}
mysqli_data_seek($rTipos,0);

// Reporte de movimientos según agencia afectada
$c = '
SELECT t2.usuario AS  "Agencia", COUNT( * ) AS  "Movimientos"
FROM  `opsal_movimientos` AS t0
LEFT JOIN  `opsal_ordenes` AS t1
USING ( codigo_orden ) 
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE 1 '.$fecha.' AND motivo="remocion"
GROUP BY t1.codigo_agencia
';

$rMovimientos = db_consultar($c);

$tMovimientos = 0;

while ($f = mysqli_fetch_array($rMovimientos))
{
    $tMovimientos += $f[1];
}
mysqli_data_seek($rMovimientos,0);

// Reporte de movimientos segun cobro
$c = '
SELECT t2.grupo AS "Cobrado a", COUNT( * ) AS  "Cantidad"
FROM  `opsal_movimientos` AS t0
LEFT JOIN  `opsal_ordenes` AS t1 USING ( codigo_orden ) 
LEFT JOIN  `opsal_usuarios` AS t2 ON t0.cobrar_a = t2.codigo_usuario
WHERE 1 '.$fecha.' AND motivo="remocion"
GROUP BY t2.grupo
';

$rMovimientosCobro = db_consultar($c);

$tMovimientosCobro = 0;

while ($f = mysqli_fetch_array($rMovimientosCobro))
{
    $tMovimientosCobro += $f[1];
}
mysqli_data_seek($rMovimientosCobro,0);
?>
<h1>Reporte de patio en base a historia de contenedores</h1>
<div class="noimprimir" style="border-bottom:1px solid gray;">
    <form action="/reportes.html?modo=historico" method="post">
        Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="" /> <input type="submit" id="filtrar" name="filtrar" value="Filtrar" />
    </div>
</div>
<p>
   <?php
   if (isset($_POST['filtrar']))
   {
    echo 'Mostrando datos del periodo <b>'.$_POST['fecha_inicio'].'</b> a <b>'.$_POST['fecha_final'].'</b>';
   } else {
    echo 'Mostrando datos historicos';
   }
   ?>
</p>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTEUS,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de TEUs: <b><?php echo $tTEUS; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rContenedores,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de contenedores: <b><?php echo $tContenedores; ?></b></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v3"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTipos,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de contenedores: <b><?php echo $tTipos; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v4"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rMovimientos,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de movimientos: <b><?php echo $tMovimientos; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v5"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rMovimientosCobro,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
  function drawVisualization() {
<?php echo gpie($rTEUS,"TEU por naviera",'v1','Agencia','TEU'); ?>
<?php echo gpie($rContenedores,"Cantidad de Contenedores",'v2','Contenedores','Cantidad'); ?>
<?php echo gpie($rTipos,"Tipos de contenedores",'v3','Tipo de contenedor','Cantidad'); ?>
<?php echo gpie($rMovimientos,"Remoción de contenedores según naviera afectada",'v4','Agencia','Cantidad de movimientos'); ?>
<?php echo gpie($rMovimientosCobro,"Remoción de contenedores según naviera cobrada",'v5','Agencia','Cantidad de movimientos'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>