<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option value="10">Interno</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}

$c = 'SELECT nombre FROM cheques WHERE flag_activo=1 ORDER BY nombre ASC';
$r = db_consultar($c);
$options_cheques = '<option selected="selected" value="">Seleccione uno</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_cheques .= '<option value="'.$registro['nombre'].'">'.$registro['nombre'].'</option>';
    }
}
?>
<form id="frm_movimiento_contenedores" action="/contenedores.html?modo=remociones" method="post" autocomplete="off">
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr><td>Cobrar a</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td></tr>
        
        <tr><td>Cheque</td><td><select id="cheque" name="cheque"><?php echo $options_cheques; ?></select></td></tr>
        
        <tr><td>Fecha</td><td><input type="text" class="calendariocontiempo" value="" id="fechatiempo" name="fechatiempo" /></td></tr>

        <tr><td>Doble Mov.</td><td>
        <div id="traslado" style="text-align: center;line-height: 30px;">
            <input type="radio" name="traslado" id="traslado_no" checked="checked" value="0"/>
            <label for="traslado_no">No</label>&nbsp;
            
            <input type="radio" name="traslado" id="traslado_si" value="1"/>
            <label for="traslado_si">Si</label>&nbsp;
        </div>
        </td></tr>
        
        <tr>
            <td>Posición de contenedor</td><td>
            <table id="drop_target" class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th><th>Nivel</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" name="posicion_columna" id="posicion_columna" class="posicion" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_fila" id="posicion_fila"  class="posicion" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_nivel" id="posicion_nivel"  class="posicion" /></td>
                </tr>
            </tbody>
            </table>
            </td>
        </tr>
        
        <tr><td>Datos encontrados</td><td style="text-align: center;"><div id="datos_encontrados" style="height:100px;width:200px;margin:auto;overflow-y: auto;background-color:white;color:gray;text-align: left;font-size:10px;"</td></tr>

        <tr>
            <td>Posición de destino</td><td>
            <table class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th><th>Nivel</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" name="posicion_columna_2" id="posicion_columna_2" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_fila_2" id="posicion_fila_2" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_nivel_2" id="posicion_nivel_2" /></td>
                </tr>
            </tbody>
            </table>
            </td>
        </tr>
                
        <tr><td>Observaciones</td><td><textarea name="observaciones"></textarea></td></tr>

    </tbody>
</table>
<input type="hidden" name="guardar" value="guardar" />
<input type="submit" id="guardar" value="Guardar movimiento" /> <input type="button" id="cancelar_movimiento" value="Cancelar movimiento" /> <span id="indicador_de_envio"></span>
</form>
<?php
$r = db_consultar('SELECT cheque FROM `opsal_movimientos` WHERE cheque<>"" GROUP BY cheque');
$tagsCheque = array();
while ($f = db_fetch($r)) $tagsCheque[] = $f['cheque'];
?>
<script type="text/javascript">
    cubicaje = 0;
    afinidad = 20;
    modo = 'recoger';
    flag_posicion = false;
    nivel_deseado = 0;
        
    $(function () {
        
        $("#cancelar_movimiento").click(function(){
            $('#frm_movimiento_contenedores')[0].reset();
            
            $("#datos_encontrados").html('');
            
            $('.contenedor_movimiento_origen').removeClass('contenedor_movimiento_origen');
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
        });
        
        $('#frm_movimiento_contenedores').submit(function(event){
            event.preventDefault();
        
            
            if ( !Date.parseExact($("#fechatiempo").val(), "yyy-MM-dd HH:mm:ss") )
            {
                alert ("El formato de la fecha parece incorrecto.");
                $("#fechatiempo").focus();
                return false;   
            }
            
            if ($("select#cheque option:selected").val() == "")
            {
                alert ("Seleccione un cheque.");
                return false;
            }

            if ($("#posicion_columna").val() == "" || $("#posicion_fila").val() == "" || $("#posicion_nivel").val() == "")
            {
                alert ("Verifique la posición inicial.");
                return false;
            }

            if ($("#posicion_columna_2").val() == "" || $("#posicion_fila_2").val() == "" || $("#posicion_nivel_2").val() == "")
            {
                alert ("Verifique la posición de destino.");
                return false;
            }
            
            $("#indicador_de_envio").html('<img src="/IMG/general/cargando.gif" />');
            
            $("#guardar").attr('disabled','disabled');
            
            //$("#contenedor_mapa").html('<p>Guardando datos...</p><br /><img src="/IMG/general/cargando.gif" />');
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
            
            $.post('ajax.movimiento.php',$('#frm_movimiento_contenedores').serialize(),function (datos){
                if (datos == 'ok') {
                    iniciar_mapa();
                    $('#frm_movimiento_contenedores')[0].reset();
                } else {
                    alert('ERROR: ' + datos);
                }
            });
        });
        
        $("#contenedor_mapa").bind('mapa_iniciado',function(){
            $("#indicador_de_envio").empty();
            $("#codigo_contenedor").focus();
            $("#guardar").removeAttr('disabled');
        });

        $('.posicion').change(function(){
            if (/\w{2}/.test ($('#posicion_columna').val()) == true && /\d+/.test ($('#posicion_fila').val()) && /\d+/.test ($('#posicion_nivel').val())  )
            {
                nivel_deseado = $('#posicion_nivel').val();
                flag_posicion = true;
                modo = 'recoger';
                $('#opsal_mapa #contenedor_mapa table td[col="'+$('#posicion_columna').val()+'"][fila="'+$('#posicion_fila').val()+'"]').trigger('click');
            }
        });
        
        $('#opsal_mapa #contenedor_mapa table td').live('click',function(){
            
            if (modo == 'recoger')
            {
                if ($(this).attr('afinidad') == 'libre')
                {
                    alert('No existen contenedores en esta ubicación.');
                    return false;
                }
                
                if ($(this).hasClass('contenedor_zona_muerta'))
                {
                    alert('Seleccionar el contenedor desde el punto de origen.');
                    return false;
                }
 
                $("#posicion_columna_2").val('');
                $("#posicion_fila_2").val('');
                $("#posicion_nivel_2").val('');                
                $("#cobrado_como").val('');
                $("#datos_encontrados").html('');
                
                $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
                
                columna = $(this).attr('col');
                fila = $(this).attr('fila');
                nivel = parseInt($(this).attr('nivel'));
                cubicaje = parseInt($(this).attr('visual'));
                
                $("#posicion_columna").val(columna);
                $("#posicion_fila").val(fila);
                $("#posicion_nivel").val(nivel);

                // Eliminamos los anteriores
                $('.contenedor_movimiento_origen').removeClass('contenedor_movimiento_origen');
                
                if (columna == "" || fila == "" || nivel == "")
                {
                    $("#datos_encontrados").html('Faltan datos para ubicar contenedor');
                    return false;
                }
                
                ubicacion = $('div#contenedor_mapa table tbody tr td[col="'+columna+'"][fila="'+fila+'"]');
    
                if (ubicacion.length > 0)
                {
                    ubicacion_nivel = parseInt(ubicacion.attr('nivel'));
                    if ( ubicacion_nivel < nivel )
                    {
                        $("#datos_encontrados").html('No hay contenedores en ese nivel.');
                        return false;
                    }
                    
                    if ( ubicacion_nivel == 0 || nivel == 0)
                    {
                        $("#datos_encontrados").html('No hay contenedores en esa ubicación.');
                        return false;
                    }
                    
                    // Exito
                    var grupo = $('div#contenedor_mapa table tbody tr td[grupo="'+ubicacion.attr('grupo')+'"]');
                    grupo.addClass('contenedor_movimiento_origen');
                    
                    $.post('ajax.buscar_contenedor.php',{columna:columna, fila:fila, nivel:nivel},function(data){
                        $("#datos_encontrados").html(data.resultados);
                    },'json');
                    
                    afinidad = ubicacion.attr('afinidad');
                    modo = 'depositar';
                
                    // Si ingresó la posición manualmente o vía búsqueda, asegurarnos que es el de mas arriba o alertar
                    if (flag_posicion && parseInt(nivel_deseado) != parseInt($(this).attr('nivel')))
                    {
                        alert('El contenedor deseado no puede ser procesado debido a que hay otro contenedor encima. Deberá liberar el paso primero para realizar la operación deseada.');
                        flag_posicion = false;
                        return false;
                    }
                
                } else {
                
                    $("#datos_encontrados").html('Tal ubicación no existe.');
                }
                
            } else {
                
                if ($(this).hasClass('contenedor_movimiento_origen'))
                {
                    alert('Este es el punto de origen del movimiento. No puede moverlo al mismo lugar.');
                    return false;
                }
                
                if (parseInt($(this).attr('nivel')) > 3)
                {
                    alert('No se puede ubicar un contenedor en 5 nivel. Modo estricto esta activado.');
                    return false;
                }
                
                if ($(this).attr('afinidad') != 'libre' && $(this).attr('afinidad') !=  afinidad)
                {
                    alert('No se puede ubicar un contenedor de ' + afinidad + ' pies sobre uno de ' + $(this).attr('afinidad') + ' pies. Modo estricto esta activado.');
                    return false;
                }
    
                if ($(this).hasClass('contenedor_zona_muerta'))
                {
                    alert('No se puede ubicar el contenedor en este punto. Modo estricto esta activado.');
                    return false;
                }
                
                var x = $(this).attr('x');
                var y = $(this).attr('y');
                
                $("#posicion_columna_2").val($(this).attr('col'));
                $("#posicion_fila_2").val($(this).attr('fila'));
                $("#posicion_nivel_2").val((parseInt($(this).attr('nivel')) + 1));
                
                referencia = $('#'+x+'_'+y);
                
                $("#contenedor_visual").css('left',(referencia.position().left)).css('top',(referencia.position().top)).css('height',((19*(cubicaje/20))+1) + 'px').css('width','11px');
                
                modo = 'recoger';
                
                nivel_1 = parseInt($("#posicion_nivel").val());
                nivel_2 = parseInt($("#posicion_nivel_2").val());
                
                if (nivel_1 > 1 && nivel_2 == 1)
                {
                    tipo_cobro = 'desestiba';
                } else if (nivel_1 > 1 && nivel_2 > 1) {
                    tipo_cobro = 'estibaYdesestiba';
                } else {
                    tipo_cobro = 'estiba';
                }
                
                $("#cobrado_como").val(tipo_cobro);

                $('#opsal_mapa #contenedor_mapa table').css('cursor','auto');
            }
        });
        
        $("#traslado").buttonset();
    });
</script>