<?php
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
<h1><?php echo _('Generar reporte de recepciones para contenedores presentes en patio'); ?></h1>
<?php if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia') { 
    $_GET['codigo_agencia'] = _F_usuario_cache('codigo_usuario');
} else { 
?>
  <form action="" method="get">
    Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> | Ordenamiento: <input type="radio" value="antiguedad" name="ordenamiento" id="ordenamiento_antiguedad" checked="checked" /> <label for="ordenamiento_antiguedad">Por antiguedad</label> / <input type="radio" value="posicion" name="ordenamiento" id="ordenamiento_posicion" /> <label for="ordenamiento_antiguedad">Por posición</label> <input type="submit" value="Filtrar" />
  </form>
  <hr />
  <br />
</div>
<?php
}
$agencia = (!empty($_GET['codigo_agencia']) ? ' AND codigo_agencia="'.$_GET['codigo_agencia'] .'"' : '');
$orden = ( (@$_GET['ordenamiento'] == 'antiguedad' || empty($_GET['ordenamiento'])) ? 'DATEDIFF( COALESCE(fechatiempo_egreso,NOW()) ,  `fechatiempo_ingreso` ) DESC' : 'y2+0 ASC, x2 ASC');

$c = 'SELECT CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "'._('Contenedor').'", tipo_contenedor AS "'._('Tipo').'", IF(clase_taller="0", clase, CONCAT(clase, " -> ", clase_taller)) AS "'._('Clase').'", CONCAT(FORMAT(tara,0), " kg") AS "'._('Tara').'", CONCAT( x2,  "-", y2,  "-", nivel ) AS "'._('Posición').'", DATE(  `fechatiempo_ingreso` ) AS  "'._('Ingreso').'", DATEDIFF( COALESCE(fechatiempo_egreso,NOW()) ,  `fechatiempo_ingreso` ) AS  "'._('Días<br />\nen<br />\npatio').'", IF (arivu_referencia = "", "N/A", arivu_referencia) AS "# '._('ARIVU').'", CONCAT(`arivu_ingreso`, " - " , `arivu_ingreso` + INTERVAL 89 DAY) AS "'._('ARIVU').'", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, COALESCE(fechatiempo_egreso,NOW()) ) AS  "'._('Días<br />\npara<br />\nexp.<br />\nARIVU').'", `transportista_ingreso` AS "'._('Transportista').'", `booking_number` AS "Booking", IF(`observaciones_taller` = "", CONCAT(`observaciones_ingreso`, " -> ", `observaciones_taller`), `observaciones_ingreso`) AS "'._('Observaciones').'" FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE estado="dentro" '.$agencia .' ORDER BY '. $orden;
$r = db_consultar($c);

echo '<div class="exportable" rel="Reporte de patio al '.mysql_date().' para '.mysqli_num_rows($r).' contenedores actualmente en patio - '.$agencias[@$_GET['codigo_agencia']].'">';
echo sprintf(_('<h1>Mostrando recepciones para los <b>%s</b> contenedores actualmente en patio de <b>%s</b></h1>'), mysqli_num_rows($r), $agencias[@$_GET['codigo_agencia']]);
echo '<div style="overflow-x:auto;">';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-una-linea"');
echo '</div>';
echo '</div>';

echo '<br />';
echo '<div class="exportable">';
echo _('<h2>Deglose por tamaño de contenedor</h2>');
$c = 'SELECT tipo_contenedor AS "'._('Tipo').'", COUNT(*) AS "'._('Cantidad').'" FROM `opsal_ordenes` AS t1 WHERE estado="dentro" '.$agencia .' GROUP BY t1.tipo_contenedor';
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>