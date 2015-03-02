<?php

if (empty($_REQUEST['id']) || !is_numeric($_REQUEST['id']) )
{
    echo '<p>Error: se esperaban mas parametros.</p>';
    return;
}

$cReparaciones = sprintf('SELECT o.codigo_contenedor, r.`codigo_reparacion`, r.`clase_anterior`, r.`clase_nueva`, r.`codigo_orden`, r.`codigo_digitador`, r.`fechahora_digitado`, r.`flag_eliminado`, r.`tecnico_responsable`, r.`costo_reparacion`, r.`duracion_reparacion`, r.`detalle_reparacion`, r.`fechahora_reparacion`, u.usuario AS "nombre_digitador" FROM `opsal_reparaciones` AS r LEFT JOIN opsal_ordenes AS o ON r.codigo_orden = o.codigo_orden LEFT JOIN opsal_usuarios AS u ON r.codigo_digitador = u.codigo_usuario WHERE flag_eliminado=0 AND codigo_reparacion="%s"', $_REQUEST['id']);

$rReparaciones = db_consultar($cReparaciones);

echo mysqli_error($db_link);

if (! mysqli_num_rows($rReparaciones))
{
    echo '<p>No hay reparaciones registradas con dicho ID.</p>';
    return;
}

$fRep = mysqli_fetch_assoc($rReparaciones);

echo '<h1>Datos reparación</h1>';
echo '<table class="tabla-estandar opsal_tabla_borde_oscuro horizontal">';
echo '<tr><th>Contenedor</th><td>'.$fRep['codigo_contenedor'].'</td></tr>';
echo '<tr><th>Reparación digitada por</th><td>'.$fRep['nombre_digitador'].'</td></tr>';
echo '<tr><th>Fecha y hora digitado</th><td>'.$fRep['fechahora_digitado'].'</td></tr>';
echo '<tr><th>Clase antes de reparación</th><td>'.strtoupper($fRep['clase_anterior']).'</td></tr>';
echo '<tr><th>Clase después de reparación</th><td>'.strtoupper($fRep['clase_nueva']).'</td></tr>';
echo '<tr><th>Costo de reparación (USD $)</th><td>'.$fRep['costo_reparacion'].'</td></tr>';
echo '<tr><th>Técnico responsable</th><td>'.$fRep['tecnico_responsable'].'</td></tr>';
echo '<tr><th>Fecha y hora de reparación</th><td>'.$fRep['fechahora_reparacion'].'</td></tr>';
echo '<tr><th>Duración aprox. de reparación</th><td>'.$fRep['duracion_reparacion'].'</td></tr>';
echo '<tr><th>Detalle de reparación</th><td>'.$fRep['detalle_reparacion'].'</td></tr>';
echo '</table>';


echo '<h1>Fotografías y documentos de reparación</h1>';
$cRepArchivos = 'SELECT * FROM `opsal_reparaciones_archivos` WHERE codigo_reparacion='.$fRep['codigo_reparacion'];
$rRepArchivos = db_consultar($cRepArchivos);

if ( !mysqli_num_rows($rRepArchivos) )
{
    echo '<p>Ninguna registrada</p>';
    return;
}

while ($fRepArchivo = mysqli_fetch_array($rRepArchivos))
{
    echo "<h3>{$fRepArchivo['nombre_archivo']}";
    echo '<div><a href="IMG/REP/'.$fRepArchivo['nombre_archivo'].'" target="_blank"><img style="width:300px;300px;" src="IMG/REP/'.$fRepArchivo['nombre_archivo'].'" /></a></div>';
}