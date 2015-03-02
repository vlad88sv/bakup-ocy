<?php
// Aquí es más seguro irnos por el codigo de orden porque el número de contenedor podria referirse a cualquier recepcion en la historia de el!.

if (empty($_GET['ID_movimiento']) || !is_numeric($_GET['ID_movimiento']))
{
    echo '<p>Debe utilizar un código de movimiento interno</p>';
    return;
}

if ( _F_usuario_cache('nivel') != 'tecnico' && _F_usuario_cache('nivel') != 'jefatura' )
{
    echo '<p>Fallo de autorización. Evento ha sido reportado.</p>';
    return;
}

if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('cobrar_a', 'cheque', 'observacion', 'flag_traslado')));
    
    db_actualizar_datos('opsal_movimientos',$DATOS,'ID_movimiento="'.$_POST['ID_movimiento'].'"');
    
    registrar('Se ha editado un movimiento (ID: <b>'.$_POST['ID_movimiento'].'</b>)','edicion.movimiento',$_POST['codigo_orden']);
}


$c = 'SELECT t1.`ID_movimiento`, t1.`codigo_posicion`, t1.`codigo_orden`, t1.`nivel`, t1.`codigo_usuario`, t1.`cobrar_a`, t1.`cheque`, t1.`fechatiempo`, t1.`motivo`, t1.`observacion`, t1.`flag_traslado`, t1.`opsal_movimientoscol`, t2.`codigo_contenedor` FROM `opsal_movimientos` AS t1 LEFT JOIN `opsal_ordenes` AS t2 USING (codigo_orden) WHERE `ID_movimiento` = "'.db_codex($_GET['ID_movimiento']).'"';
$r = db_consultar($c);

if (mysqli_num_rows($r) == 0)
{
    echo '<h1>Edición de Movimientos</h1>';
    echo '<p>No se ha encontrado el movimiento solicitado</p>';
    return;
}

$f = mysqli_fetch_assoc($r);

echo '<h1>Edición de movimientos - editando movimiento <b>#'.$f['ID_movimiento'].'</b></h1>';

echo '<form method="POST" action="">';
echo '<input type="hidden" value="'.$f['ID_movimiento'].'" name="ID_movimiento" />';
echo '<input type="hidden" value="'.$f['codigo_orden'].'" name="codigo_orden" />';

echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro horizontal input100">';
echo '<tr><th>Código de contenedor</th><td>'.$f['codigo_contenedor'].'</td></tr>';
echo '<tr><th style="width:200px;">Número de movimiento</th><td>'.$f['ID_movimiento'].'</td></tr>';
echo '<tr><th>Motivo</th><td>'.ucfirst(strtolower($f['motivo'])).'</td></tr>';
echo '<tr><th>Cheque</th><td><input type="text" name="cheque" value="'.$f['cheque'].'" /></td></tr>';
echo '<tr><th>Cobrar a</th><td><select name="cobrar_a">'.'<option value="10">Interno</option>'.db_ui_opciones('codigo_usuario','usuario','opsal_usuarios','WHERE nivel="agencia"','','',$f['cobrar_a']).'</select></td></tr>';
echo '<tr><th>Doble movimiento</th><td>'.ui_combobox('flag_traslado',ui_array_a_opciones(array('0' => 'No', '1' => 'Si')), $f['flag_traslado']).'</td></tr>';
echo '<tr><th>Observación</th><td><input type="text" name="observacion" value="'.$f['observacion'].'" /></td></tr>';
echo '</table>';

echo '<input type="submit" name="guardar" value="Guardar" />';
echo '</form>';
?>
<script type="text/javascript">
$(function(){
    $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0});
});
</script>