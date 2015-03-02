<?php
$id_condicion = 0;

if (!empty($_GET['ID']) && is_numeric($_GET['ID']))
{
    $id_condicion = $_GET['ID'];
    
    $c = 'SELECT `codigo_agencia`, `codigo_contenedor`, `tipo_contenedor`, `referencia_papel`, `codigo_cheque`, `fecha_ingreso`, `estado` FROM `opsal_condiciones` WHERE `ID_condicion` = ' . $id_condicion;
    $r = db_consultar($c);
    $f = db_fetch($r);   
}

$c = 'SELECT nombre FROM cheques WHERE flag_activo=1 ORDER BY nombre ASC';
$r = db_consultar($c);
$options_cheques = '<option selected="selected" value="">Seleccione uno</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_cheques .= '<option '.(@$f['codigo_cheque'] == $registro['nombre'] ? 'selected="selected"' : '' ).' value="'.$registro['nombre'].'">'.$registro['nombre'].'</option>';
    }
}

?>
<h1 class="opsal_titulo">Elaboración de condición</h1>
<?php

if (isset($_POST['eliminar']) && isset($_POST['ID_condicion']) && is_numeric($_POST['ID_condicion']))
{
    $c = 'DELTE FROM opsal_condiciones WHERE ID_condicion='.$_POST['ID_condicion'].' LIMIT 1';
    $r = db_consultar($c);
    
    echo '<hr /><p class="opsal_notificacion">Condición eliminada exitosamente.</p><hr />';
    registrar('Condición #'.$_POST['ID_condicion'].' eliminada para contenedor <b>'.$_POST['codigo_contenedor'].'</b>','condicion');
}

if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('ID_condicion', 'codigo_agencia', 'codigo_contenedor', 'codigo_cheque', 'referencia_papel','fecha_ingreso','tipo_contenedor','estado')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    if (db_reemplazar_datos('opsal_condiciones',$DATOS) > 0)
    {
        echo '<hr /><p class="opsal_notificacion">Condición ingresada exitosamente.</p><hr />';
        registrar('Condición elaborada para contenedor <b>'.$_POST['codigo_contenedor'].'</b>','condicion');
    }
}
?>
<div class="opsal_burbuja">
<?php if ($id_condicion > 0) echo '<p style="color:red;">MODO DE EDICIÓN</p>'; ?>
<form id="form_condicion" action="/elaboracion.de.condicion.html" method="post" autocomplete="off">
    <input type="hidden" id="ID_condicion" name="ID_condicion" value="<?php echo $id_condicion; ?>" />
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Agencia</td><td><select name="codigo_agencia"><?php echo db_ui_opciones('codigo_usuario','usuario','opsal_usuarios','WHERE nivel="agencia"','','',(isset($_POST['codigo_agencia']) ? $_POST['codigo_agencia'] : @$f['codigo_agencia']) ); ?></select></td>
        </tr>
        <tr>
            <td>Fecha</td><td><input type="text" class="calendario" name="fecha_ingreso" value="<?php echo @$f['fecha_ingreso']; ?>" /></td>
        </tr>
        <tr>
            <td>Cheque</td><td><select id="codigo_cheque" name="codigo_cheque"><?php echo $options_cheques; ?></select></td>
        </tr>
        <tr>
            <td>Contenedor</td><td><input type="text" id="codigo_contenedor" name="codigo_contenedor" value="<?php echo @$f['codigo_contenedor']; ?>" /></td>
        </tr>
        <tr>
            <td>Tipo</td><td><select name="tipo_contenedor"><?php echo db_ui_opciones('tipo_contenedor','nombre','opsal_tipo_contenedores','','','',@$f['tipo_contenedor']); ?></select></td>
        </tr>
        <tr>
            <td>Número EIR</td><td><input type="text" id="referencia_papel" name="referencia_papel" value="<?php echo @$f['referencia_papel']; ?>" /></td>
        </tr>
        <tr>
            <td>Estado</td><td><input type="radio" name="estado" value="vacio" <?php echo (@$f['estado'] != 'lleno' ? 'checked="checked"' : ''); ?> /> Vacio <input type="radio" name="estado" value="lleno" <?php echo (@$f['estado'] == 'lleno' ? 'checked="checked"' : ''); ?> /> Lleno</td>
        </tr>
    </tbody>
</table>

<input type="submit" name="guardar" id="guardar" value="Guardar" />
<?php if ($id_condicion > 0) echo '<input type="submit" name="eliminar" id="eliminar" value="Eliminar" />'; ?>
</form>
</div>
<hr />
<?php
$ultimos_ingresos = '';

$c = '
SELECT 
t1.`ID_condicion`,
t1.`codigo_agencia`,
t1.`fecha_ingreso`,
t1.`codigo_cheque`,
t1.`codigo_contenedor`,
t1.`tipo_contenedor`,
t1.`referencia_papel`,
t1.`estado`,
t2.`usuario` AS "nombre_agencia",
t4.`usuario` AS "nombre_operador"
FROM `opsal`.`opsal_condiciones` AS t1 LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.codigo_agencia = t2.codigo_usuario) LEFT JOIN `opsal`.`opsal_usuarios` AS t4 ON (t1.ingresado_por = t4.codigo_usuario) 
ORDER BY t1.`fecha_ingreso` DESC LIMIT 50
';
$resultado = db_consultar($c);

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $ultimos_ingresos .= sprintf('<tr><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>','/elaboracion.de.condicion.html?ID='.$f['ID_condicion'],$f['fecha_ingreso'],$f['nombre_operador'],$f['nombre_agencia'],$f['codigo_cheque'],$f['codigo_contenedor'],$f['tipo_contenedor'],$f['estado'],$f['referencia_papel']);
    }
    $ultimos_ingresos .= '<thead><tr><th>Fecha</th><th>Ingresó</th><th>Agencia</th><th>Cheque</th><th>Contenedor</th><th>Tipo</th><th>Estado</th><th>EIR</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 50 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        
        $('#form_condicion').submit(function () {        
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
            
            if ($("select#codigo_cheque option:selected").val() == "")
            {
                alert ("Seleccione un cheque.");
                return false;
            }
            
            if ($("#codigo_contenedor").val() == "")
            {
                alert ("Ingrese un identificador de contenedor.");
                return false;
            }
            
            if ($("#referencia_papel").val() == "")
            {
                alert ("Ingrese el número de serie de la hoja física utilizada.");
                return false;
            }
            
            if ($("#ID_condicion").val() == 0) {
                var valido = true;
                
                $.ajax({
                    type: 'POST',
                    url: 'ajax.seguro.php',
                    data: {accion : 'verificar_doble_ingreso_condicion', contenedor : $("#codigo_contenedor").val(), EIR : $("#referencia_papel").val()},
                    success: function(data){if (data.cantidad > 0) valido = false;},
                    dataType: 'json',
                    async:false
                });
                
                if (!valido)
                {
                    alert ("ERROR: parece que esta condición ya esta ingresada.\nSe comparo número de contenedor y número de EIR.");
                    return false;
                }
            }
            
            
            return true;
        });
        
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
        $(".calendariocontiempo").datetimepicker({dateFormat: 'yy-mm-dd', constrainInput: true, timeFormat: 'hh:mm:ss', defaultDate: +0}).datetimepicker('setDate', new Date());
    });
</script>