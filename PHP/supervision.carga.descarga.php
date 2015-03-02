<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Seleccione una</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<h1 class="opsal_titulo">Supervisión de carga y descarga de buques</h1>
<?php
if (isset($_POST['guardar']))
{
    
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_agencia', 'codigo_contenedor', 'ID_buque', 'inicio_operacion','final_operacion', 'supervisor', 'marchamador')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    
    $ID = db_agregar_datos('opsal_carga_descarga',$DATOS);
    
    if ($ID > 0)
    {
        registrar('Supervisión de carga/descarga (ID: <b>'.$ID.'</b>)','sup.carga.descarga');
        
        if (is_array($_POST['importacion_vacios']))
        {    
            foreach ($_POST['importacion_vacios'] as $index => $valor)
            {
                if ($valor == 0) continue;
                
                unset($DATOS);
                $DATOS['ID_carga_descarga'] = $ID;
                $DATOS['cantidad'] = $_POST['importacion_vacios'][$index];
                $DATOS['tipo_contenedor'] = $_POST['importacion_vacios_tipo'][$index];
                $DATOS['patio'] = $_POST['importacion_vacios_patio'][$index];
                $DATOS['categoria'] = 'importacion_vacios';
                
                db_agregar_datos('detalle_carga_descarga',$DATOS);
            }
        }
        
        if (is_array($_POST['importacion_llenos']))
        {    
            foreach ($_POST['importacion_llenos'] as $index => $valor)
            {
                if ($valor == 0) continue;
                
                unset($DATOS);
                $DATOS['ID_carga_descarga'] = $ID;
                $DATOS['cantidad'] = $_POST['importacion_llenos'][$index];
                $DATOS['tipo_contenedor'] = $_POST['importacion_llenos_tipo'][$index];
                $DATOS['patio'] = $_POST['importacion_llenos_patio'][$index];
                $DATOS['categoria'] = 'importacion_llenos';
                
                db_agregar_datos('detalle_carga_descarga',$DATOS);
            }
        }
        
        if (is_array($_POST['exportacion_vacios']))
        {    
            foreach ($_POST['exportacion_vacios'] as $index => $valor)
            {
                if ($valor == 0) continue;
                
                unset($DATOS);
                $DATOS['ID_carga_descarga'] = $ID;
                $DATOS['cantidad'] = $_POST['exportacion_vacios'][$index];
                $DATOS['tipo_contenedor'] = $_POST['exportacion_vacios_tipo'][$index];
                $DATOS['patio'] = $_POST['exportacion_vacios_patio'][$index];
                $DATOS['categoria'] = 'exportacion_vacios';
                
                db_agregar_datos('detalle_carga_descarga',$DATOS);
            }
        }

        if (is_array($_POST['exportacion_llenos']))
        {    
            foreach ($_POST['exportacion_llenos'] as $index => $valor)
            {
                if ($valor == 0) continue;
                
                unset($DATOS);
                $DATOS['ID_carga_descarga'] = $ID;
                $DATOS['cantidad'] = $_POST['exportacion_llenos'][$index];
                $DATOS['tipo_contenedor'] = $_POST['exportacion_llenos_tipo'][$index];
                $DATOS['patio'] = $_POST['exportacion_llenos_patio'][$index];
                $DATOS['categoria'] = 'exportacion_llenos';
                
                db_agregar_datos('detalle_carga_descarga',$DATOS);
            }
        }        
        
        echo '<hr /><p class="opsal_notificacion">Registro de supervisión de carga y descarga ingresado exitosamente.</p><hr />';
    }
}
?>
<div class="opsal_burbuja">
<form id="form_carga_descarga" action="/supervision.carga.descarga.html" method="post" autocomplete="off">
<table class="opsal_tabla_ancha">
    <tr>
    <td>
    <table id="opsal_ims">
        <tbody>
            <tr>
                <td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
            </tr>
            <tr>
                <td>Supervisor</td><td><input type="text" name="supervisor" value="" /></td>
            </tr>
            <tr>
                <td>Marchamador</td><td><input type="text" name="marchamador" value="" /></td>
            </tr>
            <tr>
                <td>Buque</td><td><input type="text" id="ID_buque" name="ID_buque"/></td>
            </tr>
            <tr>
                <td>Inicio de operación</td><td><input class="timepicker" type="text" id="inicio_operacion" name="inicio_operacion"/></td>
            </tr>
            <tr>
                <td>Fin de operación</td><td><input class="timepicker" type="text" id="final_operacion" name="final_operacion"/></td>
            </tr>
        </tbody>
    </table>
    </td>
    <td style="vertical-align: top;">
        <table class="opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-estandar">
            <tr><th colspan="2">Import</th><th colspan="2">Export</th></tr>
            <tr><th>Vacios <a class="agregar_nuevo_detalle" rel="detalle_importacion_vacios" style="float:right;">+</a></th><th>Llenos <a class="agregar_nuevo_detalle" rel="detalle_importacion_llenos" style="float:right;">+</a></th> <th>Vacios <a class="agregar_nuevo_detalle" rel="detalle_exportacion_vacios" style="float:right;">+</a></th><th>Llenos <a class="agregar_nuevo_detalle" rel="detalle_exportacion_llenos" style="float:right;">+</a></th></tr>
            <tr>
                <td id = "detalle_importacion_vacios">
                    <div class = "detalle">
                        <input name="importacion_vacios[]" type="text" style="width:10px;" value="0" /> x 
                        <select style="width:60px;" name="importacion_vacios_tipo[]"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores'); ?></select>
                        |&nbsp;<select style="width: 60px;" name="importacion_vacios_patio[]"><?php echo db_ui_opciones('patios','patios','patios'); ?></select><br />                   
                    </div>
                </td>
                <td id = "detalle_importacion_llenos">
                    <div class="detalle">
                        <input name="importacion_llenos[]" type="text" style="width:10px;" value="0" /> x
                        <select style="width:60px;" name="importacion_llenos_tipo[]"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores'); ?></select>
                        |&nbsp;<select style="width: 60px;" name="importacion_llenos_patio[]"><?php echo db_ui_opciones('patios','patios','patios'); ?></select><br />
                    </div>
                </td>
                <td id = "detalle_exportacion_vacios">
                    <div class="detalle">
                        <input name="exportacion_vacios[]" type="text" style="width:10px;" value="0" /> x 
                        <select style="width:60px;" name="exportacion_vacios_tipo[]"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores'); ?></select>
                        |&nbsp;<select style="width: 60px;" name="exportacion_vacios_patio[]"><?php echo db_ui_opciones('patios','patios','patios'); ?></select><br />
                    </div>
                </td>
                <td id = "detalle_exportacion_llenos">
                    <div class="detalle">
                        <input name="exportacion_llenos[]" type="text" style="width:10px;" value="0" /> x 
                        <select style="width:60px;" name="exportacion_llenos_tipo[]"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores'); ?></select>
                        |&nbsp;<select style="width: 60px;" name="exportacion_llenos_patio[]"><?php echo db_ui_opciones('patios','patios','patios'); ?></select><br />
                    </div>
                </td>
            </tr>
        </table>
    </td>
    </tr>
</table>
<br /><hr /><br />
<div style="text-align: center;"><input type="submit" name="guardar" id="guardar" value="Guardar" /></div>
</form>
</div>
<hr />
<script type="text/javascript">
    $(function(){
        
        $('.agregar_nuevo_detalle').click(function(){
            $('#'+$(this).attr('rel')).append($('#'+$(this).attr('rel') + ' div').first().clone());
        });
        
        $('.timepicker').datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
        
        $('#form_carga_descarga').submit(function () {       
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
            
            if ($("#codigo_cheque").val() == "")
            {
                alert ("Ingrese el nombre del cheque.");
                return false;
            }
            
            if ($("#ID_buque").val() == "")
            {
                alert ("Ingrese el nombre del buque.");
                return false;
            }
            
            return true;
        });
    });    
</script>