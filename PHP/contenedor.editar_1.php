<?php
// Aquí es más seguro irnos por el codigo de orden porque el número de contenedor podria referirse a cualquier recepcion en la historia de el!.

if (empty($_GET['ID_orden']) || !is_numeric($_GET['ID_orden']))
{
    echo '<p>Debe utilizar un código de orden interno</p>';
    return;
}


if (isset($_POST['guardar']))
{
    
    // Si envian codigo de agencia entonces actualizar cobro de movimientos
    
    if ( !empty($_POST['codigo_agencia']) )
    {
        $codigo_agencia_antiguo = db_obtener('opsal_ordenes', 'codigo_agencia','codigo_orden="'.$_POST['codigo_orden'].'"');
        db_actualizar_datos('opsal_movimientos',array('cobrar_a' => $_POST['codigo_agencia']),'cobrar_a="'.$codigo_agencia_antiguo.'" AND codigo_orden="'.$_POST['codigo_orden'].'"');
    }
    
    
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_contenedor', 'tipo_contenedor', 'codigo_agencia', 'codigo_posicion', 'nivel', 'clase', 'tara', 'chasis', 'chasis_egreso', 'transportista_ingreso', 'transportista_egreso', 'buque_ingreso', 'buque_egreso', 'cheque_ingreso', 'cheque_egreso', 'cepa_salida', 'arivu_ingreso', 'arivu_referencia', 'observaciones_egreso', 'observaciones_ingreso', 'destino', 'estado', 'fechatiempo_ingreso', 'fechatiempo_egreso', 'ingresado_por', 'egresado_por', 'sucio', 'tipo_salida', 'eir_ingreso', 'eir_egreso', 'ingreso_con_danos', 'cliente_ingreso','ano_fabricacion','booking_number', 'booking_number_ingreso', 'clase_taller', 'observaciones_taller','ingreso_marchamo','egreso_marchamo')));
    
    //print_r($DATOS);
    
    db_actualizar_datos('opsal_ordenes',$DATOS,'codigo_orden="'.$_POST['codigo_orden'].'"');
    
    registrar('Se ha editado un contenedor (ID: <b>'.$_POST['codigo_orden'].'</b>)','edicion.contenedor',$_POST['codigo_orden']);
}


$c = 'SELECT `codigo_orden`, `codigo_contenedor`, `tipo_contenedor`, `codigo_agencia`, `codigo_posicion`, `nivel`, `clase`, `clase_taller`, `tara`, `chasis`, `chasis_egreso`, `transportista_ingreso`, `transportista_egreso`, `buque_ingreso`, `buque_egreso`, `cheque_ingreso`, `cheque_egreso`, `cepa_salida`, `arivu_ingreso`, `arivu_referencia`, `observaciones_taller`, `observaciones_egreso`, `observaciones_ingreso`, `destino`, `estado`, `fechatiempo_ingreso`, `fechatiempo_egreso`, `ingresado_por`, `egresado_por`, `sucio`, `tipo_salida`, `eir_ingreso`, `eir_egreso`, `ingreso_con_danos`, `cliente_ingreso`, `ano_fabricacion`, `booking_number`, `booking_number_ingreso` FROM `opsal_ordenes` AS t1 WHERE t1.`codigo_orden` = "'.db_codex($_GET['ID_orden']).'"';
$r = db_consultar($c);

if (mysqli_num_rows($r) == 0)
{
    echo '<h1>Edición de contenedores</h1>';
    echo '<p>No se ha encontrado el contenedor búscado</p>';
    return;
}

$f = mysqli_fetch_assoc($r);

echo '<h1>Edición de contenedores - editando contenedor <b>'.$f['codigo_contenedor'].'</b></h1>';

echo '<form method="POST" action="">';
echo '<input type="hidden" value="'.$f['codigo_orden'].'" name="codigo_orden" />';

if ( _F_usuario_cache('nivel') == 'tecnico' || _F_usuario_cache('nivel') == 'jefatura' )
{

    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro horizontal input100">';
    echo '<tr><th style="width:200px;">Número de recepción</th><td>'.$f['codigo_orden'].'</td></tr>';
    echo '<tr><th>Código de contenedor</th><td><input type="text" name="codigo_contenedor" value="'.$f['codigo_contenedor'].'" /></td></tr>';
    echo '<tr><th>Tipo contenedor</th><td><select name="tipo_contenedor">'.db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores','','','',$f['tipo_contenedor']).'</select></td></tr>';
    echo '<tr><th>Clase</th><td>'.ui_combobox('clase',ui_array_a_opciones(array('A' => 'A', 'B' => 'B', 'C' => 'C')), $f['clase']).'</td></tr>';
    echo '<tr><th>Tara</th><td><input type="text" name="tara" value="'.$f['tara'].'" /></td></tr>';
    echo '<tr><th>Año de fab.</th><td><input type="text" name="ano_fabricacion" value="'.$f['ano_fabricacion'].'" /></td></tr>';
    echo '<tr><th>Chasis</th><td><input type="text" name="chasis" value="'.$f['chasis'].'" /></td></tr>';
    echo '<tr><th>Transportista</th><td><input type="text" name="transportista_ingreso" value="'.$f['transportista_ingreso'].'" /></td></tr>';
    echo '<tr><th>Agencia</th><td><select name="codigo_agencia">'.db_ui_opciones('codigo_usuario','usuario','opsal_usuarios','WHERE nivel="agencia"','','',$f['codigo_agencia']).'</select></td></tr>';
    echo '<tr><th>Buque ingreso</th><td><input type="text" name="buque_ingreso" value="'.$f['buque_ingreso'].'" /></td></tr>';
    echo '<tr><th>Booking Number</th><td><input type="text" name="booking_number_ingreso" value="'.$f['booking_number_ingreso'].'" /></td></tr>';
    echo '<tr><th>Marchamo</th><td><input type="text" name="ingreso_marchamo" value="'.$f['ingreso_marchamo'].'" /></td></tr>';
    echo '<tr><th>Fecha CEPA</th><td><input type="text" name="cepa_salida" class="calendariocontiempo" value="'.$f['cepa_salida'].'" /></td></tr>';
    echo '<tr><th>Fecha ARIVU</th><td><input type="text" name="arivu_ingreso" class="calendario" value="'.$f['arivu_ingreso'].'" /></td></tr>';
    echo '<tr><th>Número de ARIVU</th><td><input type="text" name="arivu_referencia" value="'.$f['arivu_referencia'].'" /></td></tr>';
    echo '<tr><th>Fecha ingreso</th><td><input type="text" name="fechatiempo_ingreso" class="calendariocontiempo" value="'.$f['fechatiempo_ingreso'].'" /></td></tr>';
    echo '<tr><th>EIR ingreso</th><td><input type="text" name="eir_ingreso" value="'.$f['eir_ingreso'].'" /></td></tr>';
    echo '<tr><th>Cliente ingreso</th><td><input type="text" name="cliente_ingreso" value="'.$f['cliente_ingreso'].'" /></td></tr>';
    echo '<tr><th>Observaciones ingreso</th><td><input type="text" name="observaciones_ingreso" value="'.$f['observaciones_ingreso'].'" /></td></tr>';
    echo '</table>';
    
    if ($f['estado'] == 'fuera')
    {
        echo '<h3>Datos editables de despacho</h3>';
        echo '<table class="tabla-estandar opsal_tabla_borde_oscuro horizontal">';
        echo '<tr><th>Tipo salida</th><td>'.ui_combobox('tipo_salida',ui_array_a_opciones(array('terrestre' => 'Terrestre','embarque' => 'Embarque')),$f['tipo_salida']).'</td></tr>';
        echo '<tr><th>Fecha egreso</th><td><input type="text" name="fechatiempo_egreso" class="calendariocontiempo" value="'.$f['fechatiempo_egreso'].'" /></td></tr>';
        echo '<tr><th>Booking Number</th><td><input type="text" name="booking_number" value="'.$f['booking_number'].'" /></td></tr>';
        echo '<tr><th>Marchamo</th><td><input type="text" name="egreso_marchamo" value="'.$f['egreso_marchamo'].'" /></td></tr>';
        echo '</table>';
    }
} else {
    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro horizontal input100">';
    echo '<tr><th style="width:200px;">Código de contenedor</th><td>'.$f['codigo_contenedor'].'</td></tr>';
    echo '<tr><th>Clase al ingreso</th><td>'.$f['clase'].'</td></tr>';
    echo '<tr><th>Clase después de reparación</th><td>'.ui_combobox('clase_taller',ui_array_a_opciones(array('0' => 'Sin cambio', 'A' => 'A', 'B' => 'B', 'C' => 'C')), $f['clase_taller']).'</td></tr>';
    echo '<tr><th>Observaciones al ingreso</th><td>'.$f['observaciones_ingreso'].'</td></tr>';
    echo '<tr><th>Observaciones después de reparación</th><td><input type="text" name="observaciones_taller" value="'.$f['observaciones_taller'].'" /></td></tr>';
    echo '</table>';

}
echo '<input type="submit" name="guardar" value="Guardar" />';
echo '</form>';
?>
<script type="text/javascript">
$(function(){
    $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0});
});
</script>