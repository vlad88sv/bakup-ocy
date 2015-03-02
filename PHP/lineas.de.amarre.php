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

if (isset($_POST['guardar']))
{
    $DATOS = array_intersect_key($_POST,array_flip(array('ID_buque', 'tipo_operacion', 'dia_operacion', 'num_lineas', 'modificador', 'codigo_agencia', 'notas', 'tarifas_de')));
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    
    $ID_linea_amarre = db_agregar_datos('opsal_lineas_amarre',$DATOS);
    $modificador = $_POST['modificador'];
    
    unset ($DATOS);
    
    $codigo_agencia = $_POST['codigo_agencia'];
    $num_lineas = $_POST['num_lineas'];
    
    if ( $ID_linea_amarre > 0)
    {
        
        for ($i = 0; $i < 10; $i++)
        {
            unset($DATOS);
            $tarifa = 0;
            
            if (!isset($_POST[(string)$i]) || !is_numeric($_POST[(string)$i]))
                continue;
            
            $DATOS['duracion'] = (float)$_POST[(string)$i];
            
            if ($DATOS['duracion'] == 0)
                continue;
            
            $tarifa = numero2($_POST['p'.$i]);
            
            $total = numero2($_POST['c'.$i] * $DATOS['duracion'] * $tarifa);
                        
            $DATOS['ID_linea_amarre'] = $ID_linea_amarre;
            $DATOS['codigo_concepto'] = $i;
            $DATOS['precio_grabado'] = numero2($total);
            
            db_agregar_datos('opsal_lineas_amarre_detalle' ,$DATOS);
        }
        
                
        // Procedemos a agregar el detalle
        echo '<hr /><p class="opsal_notificacion">Estado de lineas de amarre ingresadas exitosamente.</p><hr />';
        registrar('Ingresado detalle de lineas de amarre para buque <b>'.$_POST['ID_buque'].'</b>','lineas');
    }
}
?>

<h1 class="opsal_titulo">Servicio de líneas de amarre</h1>
<div class="opsal_burbuja">
<p>Nota: puede agregar los siguientes servicios a un mismo buque múltiples veces.</p>
<form id="form_lineas_amarre" action="/lineas.de.amarre.html" method="post" autocomplete="off">
<table>
    
<tr><td style="vertical-align: top;">
<table id="opsal_ims">
    <tbody>
        <tr>
            <td>Cobro a</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td>
        </tr>
        
        <tr>
            <td>Tarifas de</td><td><select id="tarifas_de" name="tarifas_de"><?php echo $options_agencia; ?></select></td>
        </tr>

        <tr>
            <td>Buque</td><td><input type="text" id="ID_buque" name="ID_buque"/></td>
        </tr>
        
        <tr>
            <td>Día de operación</td>
            <td><input class="timepicker" type="text" id="dia_operacion" name="dia_operacion"/></td>
        </tr>
        
        <tr>
            <td>Número de lineas</td>
            <td>
                <select id="num_lineas" name="num_lineas">
                    <option value="1-3">1-3</option>
                    <option value="4-6">4-6</option>
                    <option value="7-9">7-9</option>
                    <option value="10-12">10-12</option>
                    <option value="13-15">13-15</option>
                    <option value="16-18">16-18</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>Tipo de operación</td>
            <td>
                <select name="tipo_operacion">
                    <option value="carga">Carga</option>
                    <option value="descarga">Descarga</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>Modificador</td>
            <td>
                <select name="modificador">
                    <option value="vivo">Tiempo Normal</option>
                    <option value="muerto">Tiempo muerto</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>Notas</td><td><textarea name="notas"></textarea></td>
        </tr>
    </tbody>
</table>

<br />

</td><td style="vertical-align: top;">

<table class="tabla-estandar opsal_tabla_borde_oscuro">
    <tr><th>Concepto</th><th>Cantidad</th><th>Horas</th><th>$ (unidad/hora)</th></tr>
    <tr><td>Supervisor</td><td><input name="c1" type="text" value="" /></td><td><input name="1" type="text" value="0.0" /></td><td><input name="p1" type="text" value="" /></td></tr>
    <tr><td>Muellero</td><td><input name="c2" type="text" value="" /></td><td><input name="2" type="text" value="0.0" /></td><td><input name="p2" type="text" value="" /></td></tr>
    <tr><td>Estibador</td><td><input name="c3" type="text" value="" /></td><td><input name="3" type="text" value="0.0" /></td><td><input name="p3" type="text" value="" /></td></tr>
    <tr><td>Operador</td><td><input name="c4" type="text" value="" /></td><td><input name="4" type="text" value="0.0" /></td><td><input name="p4" type="text" value="" /></td></tr>
    <tr><td>Montacarga</td><td><input name="c5" type="text" value="" /></td><td><input name="5" type="text" value="0.0" /></td><td><input name="p5" type="text" value="" /></td></tr>
    <tr><td>Estiba </td><td><input name="c6" type="text" value="" /></td><td><input name="6" type="text" value="0.0" /></td><td><input name="p6" type="text" value="" /></td></tr>
    <tr><td>Desestiba</td><td><input name="c7" type="text" value="" /></td><td><input name="7" type="text" value="0.0" /></td><td><input name="p7" type="text" value="" /></td></tr>
    <tr><td>Combustible</td><td><input name="c8" type="text" value="" /></td><td><input name="8" type="text" value="0.0" /></td><td><input name="p8" type="text" value="" /></td></tr>
    <tr><td>Transporte</td><td><input name="c9" type="text" value="" /></td><td><input name="9" type="text" value="0.0" /></td><td><input name="p9" type="text" value="" /></td></tr>
    <tr><td colspan="3"></td><td><input type="button" id="cargar_tarifas" value="cargar tarifas" /></td></tr>
</table>
    
</td></tr>
</table>
<input type="submit" name="guardar" value="Guardar" />
</form>
</div>
<hr />
<?php
$ultimos_ingresos = '';

$c = 'SELECT
t1.`dia_operacion`,
t1.`ID_buque`,
t1.`ID_linea_amarre`,
t1.`ingresado_por`,
t1.num_lineas,
t1.tipo_operacion,
t1.modificador,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia",
t4.`usuario` AS "agencia_tarifa"
FROM `opsal`.`opsal_lineas_amarre` AS t1
LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t4 ON (t1.`tarifas_de` = t3.`codigo_usuario`)
ORDER BY ID_buque, t1.`dia_operacion` DESC LIMIT 20
';
$resultado = db_consultar($c);

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $modificador = ( $f['modificador'] == 'vivo' ? 'Tiempo normal' : 'Tiempo muerto');
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $f['ID_buque'],$f['nombre_operador'],$f['nombre_agencia'],$f['agencia_tarifa'],$f['dia_operacion'],$f['num_lineas'],ucfirst($f['tipo_operacion']),$modificador);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID buque</th><th>Ingresó</th><th>Agencia</th><th>Tarifas</th><th>Fecha</th><th>No. líneas</th><th>Tipo operación</th><th>Modificador</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Últimos 20 ingresos</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        
        $('#cargar_tarifas').click(function(){
            if ($("select#tarifas_de option:selected").val() == "")
            {
                alert ("Seleccione una agencia para obtener sus tarifas.");
                return false;                
            }
            
            $.post('ajax.seguro.php', {accion:'obtener_tarifas_lineas_amarre', codigo_agencia:$("select#tarifas_de option:selected").val()}, function(data){
                console.log(data);
                $('input[name="p1"]').val(data.la_supervisor);
                $('input[name="p2"]').val(data.la_muellero);
                $('input[name="p3"]').val(data.la_estibador);
                $('input[name="p4"]').val(data.la_operador);
                $('input[name="p5"]').val(data.la_montacarga);
                $('input[name="p6"]').val(data.la_estiba);
                $('input[name="p7"]').val(data.la_desestiba);
                $('input[name="p8"]').val(data.la_combustible);
                $('input[name="p9"]').val(data.la_transporte);
            },'json');

        });
        
        $('#form_lineas_amarre').submit(function () {        
            if ($("select#codigo_agencia option:selected").val() == "")
            {
                alert ("Seleccione una agencia.");
                return false;
            }
            
            if ($("#ID_buque").val() == "")
            {
                alert ("Ingrese el número del buque.");
                return false;
            }
        });
        
        $('.timepicker').datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>