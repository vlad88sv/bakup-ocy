<?php
if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_final = $_GET['fecha_final'];
}

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$agencias = array('' => 'cualquier agencia');
$options_agencia = '<option selected="selected" value="">Mostrar todas</option>';
if (mysqli_num_rows($r) > 0)
{
  while ($registro = mysqli_fetch_assoc($r))
  {
    $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    $agencias[$registro['codigo_usuario']] = $registro['usuario'];
  }
}
?>
<h1><?php echo _('Reporte de remociones'); ?></h1>
<div class="noimprimir">
    <form action="/control.remociones.html" method="get">
        <?php if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia') { 
            $_GET['codigo_agencia'] = _F_usuario_cache('codigo_usuario');
        ?><?php echo _('Fecha inicio:'); ?> <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> <?php echo _('Fecha final:'); ?> <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> <input type="submit" value="<?php echo _('Filtrar'); ?>" />
        <? } else { ?>
        <?php echo _('Fecha inicio:'); ?> <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> <?php echo _('Fecha final:'); ?> <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> <?php echo _('Cobrado a:'); ?> <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="<?php echo _('Filtrar'); ?>" />
        <? } ?>        
    </form>
  <hr />
  <br />
</div>
<?php
$agencia = (!empty($_GET['codigo_agencia']) ? ' AND t0.cobrar_a="'.$_GET['codigo_agencia'] .'"' : '');

$c = 'SELECT DATE_FORMAT(t0.fechatiempo,"%e-%b-%y %H:%i") AS "'._('Fecha remoción').'", t4.`usuario` AS "'._('Naviera').'", t3.`usuario` AS "'._('Cobrado a').'", CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "'._('Contenedor').'", CONCAT( t2.x2,  "-", t2.y2,  "-", t0.nivel ) AS "'._('Nueva posición').'", DATE_FORMAT(`fechatiempo_ingreso`,"%e-%b-%y %H:%i") AS "'.('Ingreso').'", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS "<acronym title=\'Días en patio\'>DEA</acronym>", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "Salida", CONCAT(`arivu_ingreso`, " - " , `arivu_ingreso` + INTERVAL 89 DAY) AS "ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, NOW( ) ) AS  "<acronym title=\'Días para expiración de ARIVU\'>DPEA</acronym>", `booking_number` AS "Booking", observacion AS "Observaciones" FROM  `opsal_movimientos` AS t0 LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden) LEFT JOIN  `opsal_posicion` AS t2 ON t0.`codigo_posicion` = t2.`codigo_posicion` LEFT JOIN opsal_usuarios AS t3 ON t0.cobrar_a=t3.codigo_usuario LEFT JOIN opsal_usuarios AS t4 ON t1.codigo_agencia=t4.codigo_usuario WHERE motivo="remocion" AND flag_traslado=0 '. $agencia .' AND  DATE(t0.fechatiempo) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" ORDER BY `codigo_contenedor` ASC';
$r = db_consultar($c);

echo '<div class="exportable">';
echo sprintf( _('<h1>Mostrando <b>%s</b> remociones de <b>%s</b> a <b>%s</b> para <b>%s</b></h1>'), mysqli_num_rows($r), $fecha_inicio, $fecha_final, $agencias[@$_GET['codigo_agencia']]);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

echo '<br />';
echo '<div class="exportable" rel="Deglose por tamaño de contenedor - remociones">';
echo _('<h2>Deglose por tamaño de contenedor</h2>');
$c = 'SELECT tipo_contenedor AS "Tipo", COUNT(*) AS "Cantidad" FROM  `opsal_movimientos` AS t0 LEFT JOIN `opsal_ordenes` AS t1 USING(codigo_orden) WHERE motivo="remocion" '. $agencia .' AND DATE(t0.fechatiempo) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>