<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
</script>
<h1 class="opsal_titulo"><?php echo _('OPSAL - Contenedores en patio') . ' - ' . _F_usuario_cache('usuario'); ?></h1>
<p><?php echo _('Detalle de sus contenedores en patio'); ?> </p>
<p><?php echo _('Columnas en rojo marcan contenedores con atención, figurán en esta categoría aquellos que tengan menos de 30 días restantes de ARIVU o aquellos con mas de 60 días en patio.'); ?></p>
<?php
$agencia = ' AND codigo_agencia="'.  _F_usuario_cache('codigo_usuario') .'"' ;

$_POST['codigo_agencia'] = _F_usuario_cache('codigo_usuario');

$c = 'SELECT t1.clase, usuario AS "naviera", CONCAT( x2,  "-", y2,  "-", t1.nivel ) AS "posicion", `codigo_contenedor` AS "contenedor", tipo_contenedor, `fechatiempo_ingreso` AS  "fecha_ingreso", (DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) +1) AS  "dias_en_patio", (IF (t1.cepa_salida = "0000-00-00 00:00:00", "N/D", t1.cepa_salida) ) AS cepa_salida, ( `arivu_ingreso` + INTERVAL 89 DAY) AS "expiracion_arivu", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, NOW( ) ) AS  "dias_expiracion_arivu", observaciones_ingreso FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE estado =  "dentro" '.$agencia.' ORDER BY codigo_agencia, `fechatiempo_ingreso` ASC';
$resultado = db_consultar($c);

echo '<div class="exportable" rel="Contenedores - instantánea del '.date('Y-m-d Hi').'">';
    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    echo '<tr><th>'. _('Naviera') .'</th><th>' . _('Contenedor') . '</th><th>' . _('Tipo') . '</th><th>' . _('Clase') . '</th><th>' . _('Posición') . '</th><th>' . _('Recepción') . '</th><th>' . _('CEPA') .'</th><th><acronym title="'._('Días en patio').'">'._('DEP').'</acronym></th><th>' . _('Exp. ARIVU').'</th><th><acronym title="'._('Días para expiración de ARIVU') . '">' . _('DPEA').'</acronym></th><th style="width:400px;">'._('Observaciones').'</th></tr>';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        echo '<tr>';
        echo '<td>'.$f['naviera'].'</td>';
        echo '<td><a href="#" rel="'.$f['contenedor'].'" class="ejecutar_busqueda_codigo_contenedor">'.$f['contenedor'].'</a></td>';
        echo '<td>'.$f['tipo_contenedor'].'</td>';
        echo '<td>'.$f['clase'].'</td>';
        echo '<td>'.$f['posicion'].'</td>';
        echo '<td>'.$f['fecha_ingreso'].'</td>';
        echo '<td>'.$f['cepa_salida'].'</td>';
        echo '<td>'.($f['dias_en_patio'] >= 60 ? '<b style="color:red">'.$f['dias_en_patio'].'</b>' : $f['dias_en_patio']) .'</td>';
        echo '<td>'.($f['expiracion_arivu'] ? $f['expiracion_arivu'] : 'No disponible').'</td>';
        echo '<td>'.($f['dias_expiracion_arivu'] && $f['dias_expiracion_arivu'] <= 30 && $f['dias_expiracion_arivu'] >= 0  ? ' <b style="color:red">'.$f['dias_expiracion_arivu'].'</b>' : $f['dias_expiracion_arivu']) . ($f['dias_expiracion_arivu'] < 0 ? ' <b style="color:red">¿ERROR?</b>' : '') .'</td>';
        echo '<td>'. ellipsis ($f['observaciones_ingreso'], 70) .'</td>';
        echo '</tr>';
    }
    echo '</table>';
echo '</div>';

// REPORTE de tipo de contenedores por patio
$c = '
SELECT t3.nombre AS  "Tipo de contenedor", COUNT(*) AS  "Cantidad"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t1.tipo_contenedor
';

$rTipos = db_consultar($c);

$tTipos = 0;

while ($f = mysqli_fetch_array($rTipos))
{
    $tTipos += $f[1];
}
mysqli_data_seek($rTipos,0);

// REPORTE de clase de contenedores por patio
$c = '
SELECT CONCAT("[", t1.clase, "] ", t3.nombre) AS  "'._('Clase').'", COUNT(*) AS  "'._('Cantidad').'"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t3.tipo_contenedor, t1.clase
';

$rClases = db_consultar($c);

// REPORTE de tipo de contenedores por patio
$c = '
SELECT t3.nombre AS  "'._('Tipo de contenedor').'", COUNT(*) AS  "'._('Cantidad').'"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t1.tipo_contenedor
';

$rTipos = db_consultar($c);

$tTipos = 0;

while ($f = mysqli_fetch_array($rTipos))
{
    $tTipos += $f[1];
}
mysqli_data_seek($rTipos,0);

// REPORTE de clase de contenedores por patio
$c = '
SELECT CONCAT("[", t1.clase, "] ", t3.nombre) AS  "'._('Clase').'", COUNT(*) AS  "'._('Cantidad').'"
FROM  `opsal_ordenes` AS t1
LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
GROUP BY t3.tipo_contenedor, t1.clase
';

$rClases = db_consultar($c);
?>
<br />

<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTipos,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de contenedores: <b><?php echo $tTipos; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rClases,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>

<script type="text/javascript">
function drawVisualization() {
<?php echo gpie($rTipos,_('Tipos de contenedores'),'v1',_('Tipo de contenedor'),_('TEU')); ?>
<?php echo gpie($rClases,_('Clase de Contenedores'),'v2',_('Clase de Contenedores'),_('Cantidad')); ?>
}
  google.setOnLoadCallback(drawVisualization);
</script>