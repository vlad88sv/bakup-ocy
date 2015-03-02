<form id="frm_movimiento_contenedores_bloque" action="/contenedores.html?modo=remocion.bloque" method="post" autocomplete="off">
<p style="color:red;font-weight:bold;">ADVERTENCIA: esta utilidad es para mover bloques enteros. Solo pueden moverse a un espacio vacio. Estas remociones no cuentan como remocion internas.</p>
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr>
            <td>Posición de bloque a mover</td><td>
            <table id="drop_target" class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" name="posicion_columna" id="posicion_columna" class="posicion" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_fila" id="posicion_fila"  class="posicion" /></td>
            </tr>
            </tbody>
            </table>
            </td>
        </tr>

        <tr>
            <td>Posición vacia de destino</td><td>
            <table class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" name="posicion_columna_2" id="posicion_columna_2" /></td>
                <td><input style="width:20px;" type="text" value="" name="posicion_fila_2" id="posicion_fila_2" /></td>
            </tr>
            </tbody>
            </table>
            </td>
        </tr>

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
    modo = 'recoger';
        
    $(function () {        
        $("#cancelar_movimiento").click(function(){
            $('#frm_movimiento_contenedores_bloque')[0].reset();
            
            $("#datos_encontrados").html('');
            
            $('.contenedor_movimiento_origen').removeClass('contenedor_movimiento_origen');
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
        });
        
        $('#frm_movimiento_contenedores_bloque').submit(function(event){
            event.preventDefault();
    
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
            
            $.post('ajax.movimiento.bloque.php',$('#frm_movimiento_contenedores_bloque').serialize(),function (){
                iniciar_mapa();
                $('#frm_movimiento_contenedores_bloque')[0].reset();
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
                
                $("#posicion_columna").val(columna);
                $("#posicion_fila").val(fila);

                // Eliminamos los anteriores
                $('.contenedor_movimiento_origen').removeClass('contenedor_movimiento_origen');
                
                if (columna == "" || fila == "")
                {
                    $("#datos_encontrados").html('Faltan datos para ubicar contenedor');
                    return false;
                }
                
                ubicacion = $('div#contenedor_mapa table tbody tr td[col="'+columna+'"][fila="'+fila+'"]');
    
                if (ubicacion.length > 0)
                {
                    if ( parseInt(ubicacion.attr('nivel')) == 0)
                    {
                        $("#datos_encontrados").html('No hay contenedores en esa ubicación.');
                        return false;
                    }
                    
                    // Exito
                    var grupo = $('div#contenedor_mapa table tbody tr td[grupo="'+ubicacion.attr('grupo')+'"]');
                    grupo.addClass('contenedor_movimiento_origen');
                    
                    modo = 'depositar';
                
                } else {
                
                    $("#datos_encontrados").html('Tal ubicación no existe.');
                }
                
            } else {
                
                if ($(this).hasClass('contenedor_movimiento_origen'))
                {
                    alert('Este es el punto de origen del movimiento. No puede moverlo al mismo lugar.');
                    return false;
                }
                
                if (parseInt($(this).attr('nivel')) != 0)
                {
                    alert('No se puede mover el bloque a un bloque no vacio.');
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
                
                referencia = $('#'+x+'_'+y);
                
                $("#contenedor_visual").css('left',(referencia.position().left)).css('top',(referencia.position().top)).css('height', 20 + 'px').css('width','11px');
                
                modo = 'recoger';

                $('#opsal_mapa #contenedor_mapa table').css('cursor','auto');
            }
        });
    });
</script>