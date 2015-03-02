<?php
if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
    $fecha_inicio = $_GET['fecha_inicio'];
    $fecha_final = $_GET['fecha_final'];
}

$c = 'SELECT codigo_salida, fechatiempo, COUNT(*) AS numero_contenedores, usuario FROM salida_bloque AS t1 LEFT JOIN opsal_usuarios USING(codigo_usuario) LEFT JOIN detalle_salida_bloque USING(codigo_salida) WHERE DATE(t1.fechatiempo) BETWEEN "'.$fecha_inicio.'" AND "'.$fecha_final.'" GROUP BY codigo_salida';
$r = db_consultar($c);
?>
<h1>Historial de salidas en bloque</h1>
<div class="noimprimir">
    <form action="/control.salidas.bloque.html" method="get">
        Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="" /> <input type="submit" value="Filtrar" />
    </form>
    <hr />
    <br />
</div>
<?php
echo '<div class="exportable">';
echo '<h1>Mostrando  <b>'.mysqli_num_rows($r).'</b> despachos en bloque de <b>'.$fecha_inicio.'</b> a <b>'.$fecha_final.'</b> para <b>'.$agencias[@$_GET['codigo_agencia']].'</b></h1>';
if (mysqli_num_rows($r) > 0)
{
    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($r))
    {
        echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href="/detalle.salida.bloque.html?cs='.$f['codigo_salida'].'">Ver</a></td></tr>',$f['codigo_salida'],$f['fechatiempo'],$f['numero_contenedores'],$f['usuario']);
    }
    echo '<thead>';
    echo '<tr><th>Código salida</th><th>Fecha</th><th>No. Contenedores</th><th>Usuario</th><th>Ver</th></tr>';
    echo '</thead>';
    echo '</table>';
} else {
    echo '<p>No hay datos</p>';
}
echo '</div>';
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>