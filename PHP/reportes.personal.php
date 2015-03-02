<?php
echo '<h2>Operadores</h2>';
$c = 'SELECT `contexto` AS "Contexto", `usuario` AS "Usuario", COUNT(*) AS "Cantidad" FROM `opsal_bitacora` LEFT JOIN `opsal_usuarios` USING(codigo_usuario) GROUP BY `codigo_usuario`, `contexto` ORDER BY `contexto`, COUNT(*) DESC, usuario';
$r = db_consultar($c);

echo db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro"');

echo '<br /><hr /><br />';

echo '<h2>Cheques en recepción</h2>';
echo '<p>Eliminación de ruido: menos de 10 ocurrencias</p>';
$c = 'SELECT IF (TRIM(cheque_ingreso) = "", "Sin cheque", TRIM(cheque_ingreso)) AS "Cheque", COUNT(*) AS "Cantidad" FROM `opsal_ordenes` GROUP BY cheque_ingreso HAVING Cantidad > 10 ORDER BY Cantidad DESC';
$r = db_consultar($c);

echo db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro"');

echo '<br /><hr /><br />';

echo '<h2>Cheques en remocion</h2>';
echo '<p>Eliminación de ruido: menos de 5 ocurrencias</p>';
$c = 'SELECT IF (TRIM(cheque) = "", "Sin cheque", TRIM(cheque)) AS "Cheque", COUNT(*) AS "Cantidad" FROM `opsal_movimientos` WHERE motivo="remocion" GROUP BY cheque HAVING Cantidad > 5 ORDER BY Cantidad DESC';
$r = db_consultar($c);

echo db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro"');


echo '<br /><hr /><br />';

echo '<h2>Cheques en despacho</h2>';
echo '<p>Eliminación de ruido: menos de 10 ocurrencias</p>';
$c = 'SELECT IF (TRIM(cheque_egreso) = "", "Sin cheque", TRIM(cheque_egreso)) AS "Cheque", COUNT(*) AS "Cantidad" FROM `opsal_ordenes` GROUP BY cheque_egreso HAVING Cantidad > 10 ORDER BY Cantidad DESC';
$r = db_consultar($c);

echo db_ui_tabla($r,'class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro"');

?>