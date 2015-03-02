<?php
$agencia = '';
if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia') {
    $agencia = 'AND codigo_agencia = "' . _F_usuario_cache('codigo_usuario') . '"';
}

$c = 'SELECT buque_egreso, codigo_agencia, COUNT(*) AS "cantidad", CONCAT(DATE_FORMAT(MIN(`fechatiempo_egreso`),"%e/%b/%y"), " al ", DATE_FORMAT(MAX(`fechatiempo_egreso`),"%e/%b/%y"), ".", usuario) AS "fecha_embarque" FROM opsal_ordenes LEFT JOIN opsal_usuarios ON codigo_agencia=codigo_usuario WHERE tipo_salida="embarque" '.$agencia.' GROUP BY `buque_egreso`, codigo_agencia ORDER BY MAX(`fechatiempo_egreso`) DESC LIMIT 60';
$r = db_consultar($c);

$options_buque = '<option selected="selected" value="">'._('Seleccione un buque').'</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_buque .= '<option value="'.$registro['buque_egreso'].':'.$registro['codigo_agencia'].'">'.$registro['buque_egreso']. ' - '. $registro['fecha_embarque'] . ' - ['. $registro['cantidad'] .' '._('despachos').']</option>';
    }
}

$embarques = '<select id="buque" name="buque">'.$options_buque.'</select>';

preg_match_all('/^(.*)\:(.*)$/i', @$_GET['buque'],$buque);
$embarque = (empty($_GET['buque']) ? '' : 'OR t1.fechatiempo_egreso IS NOT NULL AND t1.tipo_salida="embarque"  AND t1.buque_egreso="'.db_codex($buque[1][0]).'" AND t1.codigo_agencia="'.db_codex($buque[2][0]).'"');
$c = 'SELECT CONCAT(\'<a href="#" rel="\',codigo_contenedor,\'" class="ejecutar_busqueda_codigo_contenedor">\',`codigo_contenedor`,\'</a>\') AS "'._('Contenedor').'", tipo_contenedor AS "'._('Tipo').'", clase AS "'._('Clase').'", CONCAT( x2,  "-", y2,  "-", nivel ) AS "'._('Posición').'", DATE(  `fechatiempo_ingreso` ) AS  "'._('Ingreso').'", DATEDIFF( COALESCE(fechatiempo_egreso,NOW()) ,  `fechatiempo_ingreso` ) AS  "<acronym title=\''._('Días en patio').'\'>'._('DEA').'</acronym>", IF (`fechatiempo_egreso` IS NULL, "N/A" , DATE(  `fechatiempo_egreso` )) AS  "'._('Salida').'", arivu_referencia AS "# ARIVU", CONCAT( `arivu_ingreso`, " - " , ( `arivu_ingreso` + INTERVAL 89 DAY)) AS "ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, COALESCE(fechatiempo_egreso,NOW()) ) AS  "<acronym title=\''._('Días para expiración de ARIVU').'\'>'._('DPEA').'</acronym>", `transportista_egreso` AS "'._('Transportista').'", `buque_egreso` AS "'._('Buque').'", tipo_salida AS "'._('Tipo salida').'", (SELECT COUNT(*) FROM opsal_movimientos AS st0 WHERE motivo="remocion" AND st0.codigo_orden = t1.codigo_orden ) AS "'._('Rem').'", IF (buque_ingreso <> "" AND t1.cliente_ingreso = "", "SI", "NO") AS "DT", `observaciones_egreso` AS "'._('Observaciones').'" FROM `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE 0 '.$embarque.' ORDER BY `codigo_contenedor` ASC';
$r = db_consultar($c);
?>
<div class="noimprimir">
  <h1><?php echo _('Control de embarques'); ?></h1>
    <form action="" method="get">
        <?php echo $embarques; ?>&nbsp;
        <input type="submit" value="<?php echo _('Filtrar'); ?>" />
    </form>
    <hr />
    <br />
</div>
<?php
if (empty($_GET['buque'])) return;
$titulo = sprintf(_('Reporte de embarque de <b>%s</b> contenedores para el buque <b>%s</b>'), mysqli_num_rows($r), $_GET['buque']) ;

echo '<div class="exportable" rel="'.strip_tags($titulo).'">';
echo '<h1>'.$titulo.'</h1>';
echo '<div style="overflow-x:auto;">';
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-una-linea"');
echo '</div>';

echo '<br />';
echo '<div class="exportable">';
echo _('<h2>Deglose por tamaño de contenedor</h2>');
$c = 'SELECT tipo_contenedor AS "'._('Tipo').'", COUNT(*) AS "'._('Cantidad').'" FROM `opsal_ordenes` AS t1 WHERE t1.fechatiempo_egreso IS NOT NULL AND t1.tipo_salida="embarque" AND t1.buque_egreso="'.db_codex($buque[1][0]).'" AND t1.codigo_agencia="'.db_codex($buque[2][0]).'" GROUP BY tipo_contenedor' ;
$r = db_consultar($c);
echo db_ui_tabla($r,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro"');
echo '</div>';

?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>