<?php
echo '<form action="" method="GET">';
echo '<p>Año: <input type="text" name="fecha_consolidado" value="'.date('Y').'" /> <input type="submit" value="Actualizar" /></p>';

$ano = db_codex(@$_GET['fecha_consolidado'] ?: date('Y'));
$cMes[] = array();

for($mes = 1; $mes < 13; $mes++)
{
    $cMes[$mes] = 'SUM(IF( DATE("'.$ano.'-'.$mes.'-01") <= DATE(NOW()) AND (fechatiempo_egreso IS NULL OR fechatiempo_egreso > LAST_DAY("'.$ano.'-'.$mes.'-01") ) AND fechatiempo_ingreso <= LAST_DAY("'.$ano.'-'.$mes.'-01"), 1, 0))';
}
$c = 'SELECT IF(codigo_agencia, (SELECT usuario FROM opsal_usuarios WHERE codigo_usuario=codigo_agencia), "<b>Total</b>") AS "Agencia", '.$cMes[1].' AS  "Enero", '.$cMes[2].' AS  "Febrero", '.$cMes[3].' AS  "Marzo", '.$cMes[4].' AS  "Abril", '.$cMes[5].' AS  "Mayo", '.$cMes[6].' AS  "Junio", '.$cMes[7].' AS  "Julio", '.$cMes[8].' AS  "Agosto", '.$cMes[9].' AS  "Septiembre", '.$cMes[10].' AS  "Octubre", '.$cMes[11].' AS  "Noviembre", '.$cMes[12].' AS  "Diciembre" FROM  `opsal_ordenes` AS t1 WHERE YEAR(fechatiempo_ingreso) = "'.$ano.'" GROUP BY (codigo_agencia) WITH ROLLUP';

$rIngresosMes = db_consultar($c);


$titulo = 'Consolidado de equipos por unidad - '.$ano.' - Datos al '.strftime('%A %e de %B');
echo '<h1>'. $titulo .'</h1>';
echo '<div class="exportable" rel="'.$titulo.'">';
echo db_ui_tabla($rIngresosMes,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-centrada"');
echo '</div>';


////////////////
echo '<br /><hr /><br />';

for($mes = 1; $mes < 13; $mes++)
{
    $cMes[$mes] = 'SUM(IF( DATE("'.$ano.'-'.$mes.'-01") <= DATE(NOW()) AND (fechatiempo_egreso IS NULL OR fechatiempo_egreso > LAST_DAY("'.$ano.'-'.$mes.'-01") ) AND fechatiempo_ingreso <= LAST_DAY("'.$ano.'-'.$mes.'-01"), TEU, 0))';
}
$c = 'SELECT IF(codigo_agencia, (SELECT usuario FROM opsal_usuarios WHERE codigo_usuario=codigo_agencia), "<b>Total</b>") AS "Agencia", '.$cMes[1].' AS  "Enero", '.$cMes[2].' AS  "Febrero", '.$cMes[3].' AS  "Marzo", '.$cMes[4].' AS  "Abril", '.$cMes[5].' AS  "Mayo", '.$cMes[6].' AS  "Junio", '.$cMes[7].' AS  "Julio", '.$cMes[8].' AS  "Agosto", '.$cMes[9].' AS  "Septiembre", '.$cMes[10].' AS  "Octubre", '.$cMes[11].' AS  "Noviembre", '.$cMes[12].' AS  "Diciembre" FROM `opsal_ordenes` AS t1 LEFT JOIN opsal_tipo_contenedores AS t2 USING(tipo_contenedor) WHERE YEAR(fechatiempo_ingreso) = "'.$ano.'" GROUP BY (codigo_agencia) WITH ROLLUP';

$rIngresosMes = db_consultar($c);

$titulo = 'Consolidado de equipos por TEU - '.$ano.' - Datos al '.strftime('%A %e de %B');
echo '<h1>'. $titulo .'</h1>';
echo '<div class="exportable" rel="'.$titulo.'">';
echo db_ui_tabla($rIngresosMes,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-centrada"');
echo '</div>';


////////////////
echo '<br /><hr /><br />';

for($mes = 1; $mes < 13; $mes++)
{
    $cMes[$mes] = 'SUM(IF( DATE("'.$ano.'-'.$mes.'-01") <= DATE(NOW()) AND fechatiempo_ingreso <= LAST_DAY("'.$ano.'-'.$mes.'-01") AND (fechatiempo_egreso IS NULL OR fechatiempo_egreso > "'.$ano.'-'.$mes.'-01" ) , DATEDIFF(LEAST(COALESCE(fechatiempo_egreso, DATE(NOW())),LAST_DAY("'.$ano.'-'.$mes.'-01")), GREATEST(fechatiempo_ingreso, DATE("'.$ano.'-'.$mes.'-01") ) ) , 0))';
}
$c = 'SELECT IF(codigo_agencia, (SELECT usuario FROM opsal_usuarios WHERE codigo_usuario=codigo_agencia), "<b>Total</b>") AS "Agencia", '.$cMes[1].' AS  "Enero", '.$cMes[2].' AS  "Febrero", '.$cMes[3].' AS  "Marzo", '.$cMes[4].' AS  "Abril", '.$cMes[5].' AS  "Mayo", '.$cMes[6].' AS  "Junio", '.$cMes[7].' AS  "Julio", '.$cMes[8].' AS  "Agosto", '.$cMes[9].' AS  "Septiembre", '.$cMes[10].' AS  "Octubre", '.$cMes[11].' AS  "Noviembre", '.$cMes[12].' AS  "Diciembre" FROM `opsal_ordenes` AS t1 LEFT JOIN opsal_tipo_contenedores AS t2 USING(tipo_contenedor) WHERE YEAR(fechatiempo_ingreso) = "'.$ano.'" GROUP BY (codigo_agencia) WITH ROLLUP';

$rIngresosMes = db_consultar($c);

$titulo = 'Consolidado de equipos por días en patio - incluyendo días libres - '.$ano.' - Datos al '.strftime('%A %e de %B');
echo '<h1>'. $titulo .'</h1>';
echo '<div class="exportable" rel="'.$titulo.'">';
echo db_ui_tabla($rIngresosMes,'class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-centrada"');
echo '</div>';
?>
