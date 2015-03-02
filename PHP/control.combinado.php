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

$agencias = array('' => 'todas las agencias');
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
<div class="noimprimir">
  <h1><?php echo _('Control combinado de entradas y salidas'); ?></h1>
    <form action="" method="get">
        <?php if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia') { 
                $_GET['codigo_agencia'] = _F_usuario_cache('codigo_usuario');
        ?>
        <?php echo _('Fecha inicio:'); ?> <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> <?php echo _('Fecha final:'); ?> <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> | <?php echo _('Tipo:'); ?> <select name="tipo_salida"><option value=""><?php echo _('Cualquiera'); ?></option><option value="terrestre"><?php echo _('Terrestre'); ?></option><option value="embarque"><?php echo _('Embarque'); ?></option></select> <input type="submit" value="<?php echo _('Filtrar'); ?>" />
        <? } else { ?>
        <?php echo _('Fecha inicio:'); ?> <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> <?php echo _('Fecha final:'); ?> <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> | Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> | <?php echo _('Tipo:'); ?> <select name="tipo_salida"><option value=""><?php echo _('Cualquiera'); ?></option><option value="terrestre"><?php echo _('Terrestre'); ?></option><option value="embarque"><?php echo _('Embarque'); ?></option></select> <input type="submit" value="<?php echo _('Filtrar'); ?>" />
        <?php } ?>
        
    </form>
    <hr />
    <br />
</div>
<?php
$tipo_salida = (!empty($_GET['tipo_salida']) ? ' AND (estado="dentro" || tipo_salida="'.$_GET['tipo_salida'] .'")' : '');
$agencia = (!empty($_GET['codigo_agencia']) ? ' AND codigo_agencia="'.$_GET['codigo_agencia'] .'"' : '');

$c = 'SELECT CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "'._('Contenedor').'", tipo_contenedor AS "'._('Tipo').'",  clase AS "'._('Clase').'", CONCAT( x2,  "-", y2,  "-", nivel ) AS "'._('Posición').'", `fechatiempo_ingreso` AS  "'._('Ingreso').'", (IF (cepa_salida = "0000-00-00 00:00:00", "N/D", cepa_salida) ) AS "'._('CEPA').'", transportista_ingreso AS "'._('Transportista<br />ingreso').'", buque_ingreso AS "'._('Busque<br />ingreso').'", DATE( `fechatiempo_egreso` ) AS  "'._('Salida').'", arivu_referencia AS "# ARIVU", CONCAT(`arivu_ingreso`, " - " , `arivu_ingreso` + INTERVAL 89 DAY) AS "ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, COALESCE(fechatiempo_egreso,NOW()) ) AS  "<acronym title=\''._('Días para expiración de ARIVU').'\'>'._('DPEA').'</acronym>", (DATEDIFF( fechatiempo_egreso,  `fechatiempo_ingreso` ) + 1) AS "<acronym title=\''._('Días en patio').'\'>'._('DEA').'</acronym>", `transportista_egreso` AS "'._('Transportista').'", `buque_egreso` AS "'._('Buque').'", IF(estado="dentro", "N/A", tipo_salida) AS "'._('Tipo salida').'", `observaciones_egreso` AS "'._('Observaciones').'" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE DATE(t1.fechatiempo_ingreso) >= "'.$fecha_inicio.'" AND (t1.fechatiempo_egreso IS NULL OR DATE(t1.fechatiempo_egreso) <= "'.$fecha_final.'") '.$agencia . $tipo_salida .' ORDER BY fechatiempo_egreso';
$r = db_consultar($c);

$fechas = ($fecha_inicio == $fecha_final ? 'del <b>' . $fecha_inicio . '</b>' : 'de <b>'.$fecha_inicio.'</b> a <b>'.$fecha_final.'</b>');
$titulo = sprintf(_('Reporte de <b>%s</b> entradas y salidas, %s para <b>%s</b>'), mysqli_num_rows($r), $fechas, $agencias[@$_GET['codigo_agencia']]);

echo '<div class="exportable" rel="'.strip_tags($titulo).'">';
echo '<h1>'.$titulo.'</h1>';
echo '<div style="overflow-x:auto;">';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-una-linea"');
echo '</div>';

echo '<br />';
echo '<div class="exportable">';
echo _('<h2>Deglose por tamaño de contenedor</h2>');
$c = 'SELECT tipo_contenedor AS "'._('Tipo').'", COUNT(*) AS "'._('Cantidad').'" FROM `opsal_ordenes` AS t1 WHERE t1.fechatiempo_egreso IS NOT NULL AND DATE(t1.fechatiempo_egreso) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" '.$agencia .' '.$tipo_salida.' GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>