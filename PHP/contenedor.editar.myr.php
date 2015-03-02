<?php

if (isset($_POST['agregar_reparacion']) && isset($_POST['codigo_reparacion']))
{
    var_dump($_SESSION);
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_reparacion', 'clase_anterior', 'clase_nueva', 'codigo_orden', 'codigo_digitador', 'fechahora_digitado', 'flag_eliminado', 'tecnico_responsable', 'costo_reparacion', 'duracion_reparacion', 'detalle_reparacion', 'fechahora_reparacion')));
    $DATOS['fechahora_digitado'] = mysql_datetime();
    $DATOS['codigo_digitador'] = _F_usuario_cache('codigo_usuario');
    $codigo_reparacion = db_agregar_datos('opsal_reparaciones',$DATOS);
    registrar('Se ha agregado una reparacion para el contenedor (ID: <b>'.$_POST['codigo_orden'].'</b>)','edicion.contenedor',$_POST['codigo_orden']);
    
    // Cargamos los archivos
    // var_dump($_FILES); //opsal_reparaciones_archivos
    
    if (isset($_FILES['fotografia_reparacion']) && is_array($_FILES['fotografia_reparacion']))
    {
        foreach($_FILES['fotografia_reparacion']['tmp_name'] as $index => $file)
        {
            if ($file !== '')
            {
                $pathinfo = pathinfo($_FILES['fotografia_reparacion']['name'][$index]);
                $nombre_archivo = $codigo_reparacion.'_'.$pathinfo['filename'].'.'.@$pathinfo['extension'];
                echo "<p>Copiando $file - $nombre_archivo</p>";
                
                move_uploaded_file($file, 'IMG/REP/'.$nombre_archivo);
                
                $DATOS = array();
                $DATOS['codigo_reparacion'] = $codigo_reparacion;
                $DATOS['nombre_archivo'] = $nombre_archivo;
                db_agregar_datos('opsal_reparaciones_archivos', $DATOS);
                
                echo mysqli_error($db_link);
                
            }
        }
    }
    
    // Actualizamos la clase despues de taller para el registro
    $DATOS = array();
    $DATOS['clase_taller'] = $_POST['clase_nueva'];
    $DATOS['observaciones_taller'] = $_POST['detalle_reparacion'];
    db_actualizar_datos('opsal_ordenes',$DATOS,'codigo_orden="'.$_POST['codigo_orden'].'"');
    echo '<p style="color:blue;font-weight:bold;">La reparación fue agregada exitosamente, puede agregar otra o salir.</p>';
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


$c = 'SELECT `codigo_orden`, `codigo_patio`, `codigo_contenedor`, `tipo_contenedor`, `codigo_agencia`, `codigo_posicion`, `nivel`, `clase`, `clase_taller`, `tara`, `chasis`, `chasis_egreso`, `transportista_ingreso`, `transportista_egreso`, `buque_ingreso`, `buque_egreso`, `cheque_ingreso`, `cheque_egreso`, `cepa_salida`, `arivu_ingreso`, `arivu_referencia`, `observaciones_taller`, `observaciones_egreso`, `observaciones_ingreso`, `destino`, `estado`, `fechatiempo_ingreso`, `fechatiempo_egreso`, `ingresado_por`, `egresado_por`, `sucio`, `tipo_salida`, `eir_ingreso`, `eir_egreso`, `ingreso_con_danos`, `cliente_ingreso`, `ano_fabricacion`, `booking_number`, `booking_number_ingreso` FROM `opsal_ordenes` AS t1 WHERE t1.`codigo_orden` = "'.db_codex($_GET['ID_orden']).'"';
$r = db_consultar($c);

if (mysqli_num_rows($r) == 0)
{
    echo '<h1>Edición de contenedores M&R</h1>';
    echo '<p>No se ha encontrado el contenedor búscado</p>';
    return;
}

$f = mysqli_fetch_assoc($r);

echo '<h1>Edición de contenedores M&R - editando contenedor <b>'.$f['codigo_contenedor'].'</b></h1>';

if ($f['codigo_patio'] == '1')
{
    echo '<p style="color:red;font-weight:bold;">Nota: el contenedor solicitado solo puede ser modificado parcialmente.</p>';
}
// Edicion de datos basicos si no es el patio de OPSAL
if ($f['codigo_patio'] != '1')
{
    echo '<form method="POST" action="">';
    echo '<input type="hidden" value="'.$f['codigo_orden'].'" name="codigo_orden" />';

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
    echo '</form>';
    echo '<hr />';
}


echo '<br />';

echo '<div class="opsal_burbuja">';            
echo '<h2>Reparaciones durante esta recepción</h2>';
$cReparaciones = sprintf('SELECT `codigo_reparacion`, `clase_anterior`, `clase_nueva`, `codigo_orden`, `codigo_digitador`, `fechahora_digitado`, `flag_eliminado`, `tecnico_responsable`, `costo_reparacion`, `duracion_reparacion`, `detalle_reparacion`, `fechahora_reparacion`, u.usuario AS "nombre_digitador" FROM `opsal_reparaciones` AS r LEFT JOIN opsal_usuarios AS u ON r.codigo_digitador = u.codigo_usuario WHERE codigo_orden="%s" AND flag_eliminado=0', $f['codigo_orden']);
$rReparaciones = db_consultar($cReparaciones);

if (! mysqli_num_rows($rReparaciones))
{
    echo '<p>No hay reparaciones registradas para este contenedor.</p>';
} else {
    echo '<table class="tabla-estandar opsal_tabla_borde_oscuro horizontal">';
    echo '<tr>'.'<th>ID</th>'.'<th>Nombre digitador</th><th>Fecha de digitación</th><th>Clase anterior</th>'.'<th>Clase nueva</th>'.'<th>Costo reparacion</th><th>Fecha de reparacion</th><th>Duración</th><th>Detalle</th><th>Acciones</th></tr>';
    while ($fRep = mysqli_fetch_assoc($rReparaciones))
    {
        echo '<tr>'.'<td>' . $fRep['codigo_reparacion'].'</td>'.'<td>' . $fRep['nombre_digitador'].'</td>'.'<td>' . $fRep['fechahora_digitado'].'</td>'.'<td>' . $fRep['clase_anterior'].'</td>'.'<td>' . $fRep['clase_nueva'].'</td>'.'<td>$' . $fRep['costo_reparacion'].'</td>'.'<td>' . $fRep['fechahora_reparacion'].'</td>'.'<td>' . $fRep['duracion_reparacion'].'</td>'.'<td>' . $fRep['detalle_reparacion'].'</td>' . '<td><a href="/reparacion.html?id='.$fRep['codigo_reparacion'].'">Ver</a> - <a href="">Eliminar</a></td>'.'</tr>';
    }
    echo '</table>';
}

echo '</div>'; // reparaciones

// Manejo de estado de reparaciones "avanzado"
echo '<form method="POST" enctype="multipart/form-data" action="">';
echo '<input type="hidden" value="'.$f['codigo_orden'].'" name="codigo_orden" />';
echo '<input type="hidden" value="'.$f['clase'].'" name="clase_anterior" />';
echo '<input type="hidden" value="0" name="codigo_reparacion" />';

echo '<h3>Datos editables de reparación</h3>';
echo '<fieldset>';
echo '<legend>Agregar reparación</legend>';
echo '<table class="tabla-estandar opsal_tabla_borde_oscuro horizontal">';
echo '<tr><th>Clase después de reparación</th><td>'.ui_combobox('clase_nueva',ui_array_a_opciones(array('A' => 'A', 'B' => 'B', 'C' => 'C')), $f['clase']).'</td></tr>';
echo '<tr><th>Costo de reparación (USD $)</th><td><input placeholder="0.00" type="text" name="costo_reparacion" value="" /></td></tr>';
echo '<tr><th>Técnico responsable</th><td><input type="text" placeholder="nombre de técnico responsable" name="tecnico_responsable" value="" style="width:500px" /></td></tr>';
echo '<tr><th>Fecha y hora de reparación</th><td><input type="text" class="calendariocontiempo" name="fechahora_reparacion" value="" /></td></tr>';
echo '<tr><th>Duración aprox. de reparación</th><td><select name="duracion_reparacion"><option>15 minutos</option><option>30 minutos</option><option>45 minutos</option><option>1 hora</option><option>1 hora 15 minutos</option><option>1 hora 30 minutos</option><option>1 hora 45 minutos</option><option>2 horas</option><option>2 horas 30 minutos</option><option>3 horas</option><option>3 horas 30 minutos</option><option>4 horas</option><option>5 horas</option><option>6 horas</option></select></td></tr>';
echo '<tr><th>Documento 1</th><td><input type="file" accept="image/*" name="fotografia_reparacion[]" /></td></tr>';
echo '<tr><th>Documento 2</th><td><input type="file" accept="image/*" name="fotografia_reparacion[]" /></td></tr>';
echo '<tr><th>Documento 3</th><td><input type="file" accept="image/*" name="fotografia_reparacion[]" /></td></tr>';
echo '<tr><th>Documento 4</th><td><input type="file" accept="image/*" name="fotografia_reparacion[]" /></td></tr>';
echo '<tr><th>Documento 5</th><td><input type="file" accept="image/*" name="fotografia_reparacion[]" /></td></tr>';
echo '<tr><th>Detalle de reparación</th><td><textarea name="detalle_reparacion" style="width:500px;height:100px;"></textarea></td></tr>';
echo '</table>';
echo '<input type="submit" name="agregar_reparacion" value="Agregar reparación" />';
echo '</fieldset>';
echo '</form>';
?>
<script type="text/javascript">
$(function(){
    $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0});
});
</script>

