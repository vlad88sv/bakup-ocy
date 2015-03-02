<?php
if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_final = $_GET['fecha_final'];
}
?>
<h1><?php echo _('Reporte de reparaciones   '); ?></h1>
<div class="noimprimir">
    <form action="/control.reparaciones.html" method="get">
        <?php echo _('Fecha inicio:'); ?> <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> <?php echo _('Fecha final:'); ?> <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> <input type="submit" value="<?php echo _('Filtrar'); ?>" />

    </form>
  <hr />
  <br />
</div>
<?php
$agencia = (!empty($_GET['codigo_agencia']) ? ' AND t0.cobrar_a="'.$_GET['codigo_agencia'] .'"' : '');

$cReparaciones = sprintf('SELECT `codigo_reparacion`, `clase_anterior`, `clase_nueva`, `codigo_contenedor`, `codigo_digitador`, `fechahora_digitado`, `flag_eliminado`, `tecnico_responsable`, `costo_reparacion`, `duracion_reparacion`, `detalle_reparacion`, `fechahora_reparacion`, u.usuario AS "nombre_digitador" FROM `opsal_reparaciones` AS r  LEFT JOIN opsal_ordenes AS o ON o.codigo_orden=r.codigo_orden LEFT JOIN opsal_usuarios AS u ON r.codigo_digitador = u.codigo_usuario WHERE DATE(fechahora_reparacion) BETWEEN "%s" AND "%s"', $fecha_inicio, $fecha_final);
$rReparaciones = db_consultar($cReparaciones);

echo mysqli_error($db_link);
echo '<div class="exportable">';
echo sprintf( _('<h1>Mostrando <b>%s</b> reparaciones de <b>%s</b> a <b>%s</b></h1>'), mysqli_num_rows($r), $fecha_inicio, $fecha_final);
if (! mysqli_num_rows($rReparaciones))
{
    echo '<p>No hay reparaciones registradas para este contenedor.</p>';
} else {
    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    echo '<tr>'.'<th>ID</th><th>Codigo contenedor</th><th>Nombre digitador</th><th>Fecha de digitación</th><th>Clase anterior</th>'.'<th>Clase nueva</th>'.'<th>Costo reparacion</th><th>Fecha de reparacion</th><th>Duración</th><th>Detalle</th><th>Acciones</th></tr>';
    while ($fRep = mysqli_fetch_assoc($rReparaciones))
    {
        echo '<tr>'.'<td>' . $fRep['codigo_reparacion'].'</td>'.'<td>' . $fRep['codigo_contenedor'].'</td>'.'<td>' . $fRep['nombre_digitador'].'</td>'.'<td>' . $fRep['fechahora_digitado'].'</td>'.'<td>' . $fRep['clase_anterior'].'</td>'.'<td>' . $fRep['clase_nueva'].'</td>'.'<td>$' . $fRep['costo_reparacion'].'</td>'.'<td>' . $fRep['fechahora_reparacion'].'</td>'.'<td>' . $fRep['duracion_reparacion'].'</td>'.'<td>' . $fRep['detalle_reparacion'].'</td>' . '<td><a href="/reparacion.html?id='.$fRep['codigo_reparacion'].'">Ver</a> - <a href="">Eliminar</a></td>'.'</tr>';
    }
    echo '</table>';
}
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>