<?php
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
<form id="frm_salida" action="/contenedores.html?modo=salida" method="post" autocomplete="off">
<input type="hidden" name="codigo_orden" id="codigo_orden" value="0" />
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr>
            <td>Posición de contenedor</td><td>
            <table id="drop_target" class="opsal_tabla_ancha tabla-estandar tabla-centrada">
            <tbody>
            <tr><th>Col.</th><th>Fila</th><th>Nivel</th></tr>
            <tr>
                <td><input style="width:20px;" type="text" value="" class="posicion" name="posicion_columna" id="posicion_columna" /></td>
                <td><input style="width:20px;" type="text" value="" class="posicion" name="posicion_fila" id="posicion_fila" /></td>
                <td><input style="width:20px;" type="text" value="" class="posicion" name="posicion_nivel" id="posicion_nivel" /></td>
                </tr>
            </tbody>
            </table>
            </td>
        </tr>
        
        <tr>
            <td>Datos encontrados</td>
            <td style="text-align: center;"><div id="datos_encontrados" style="height:100px;width:200px;margin:auto;overflow-y: auto;background-color:white;color:gray;text-align: left;font-size:10px;"</td>
        </tr>
        
        <tr>
            <td>Tipo de salida</td>
            <td>
                <select id="tipo_salida" name="tipo_salida">
                    <option value="terrestre">Terrestre</option>
                    <option value="embarque">Embarque</option>
                </select>
            </td>
        </tr>
        
        <tr><td>Fecha salida</td><td><input type="text" name="fechatiempo_egreso" id="fechatiempo_egreso" class="calendario" /></td></tr>
        
        <tr>
            <td>No. EIR</td>
            <td><input type="text" value="" id="eir_egreso" name="eir_egreso" /></td>
        </tr>
        
        <!--
        <tr>
            <td>Dirección salida</td>
            <td style="text-align:center;">
                <div id="direccion">
                <input type="radio" name="direccion" value="izquierda" id="direccion_izquierda" /> <label for="direccion_izquierda">Izquierda</label>
                <input type="radio" name="direccion" value="derecha" id="direccion_derecha" /> <label for="direccion_derecha">Derecha</label>
                </div>
            </td>
        </tr>
        !-->
        
        <tr><td>Cheque</td><td><select id="cheque" name="cheque_egreso"><?php echo $options_cheques; ?></select></td></tr>
        <tr><td>Marchamo</td><td><input type="text" value="" id="egreso_marchamo" name="egreso_marchamo" /></td></tr>
        <tr><td>Transporte</td><td><input type="text" id="transportista" name="transportista_egreso" /></td></tr>
        <tr><td>Chofer</td><td><input type="text" value="" id="chofer_egreso" name="chofer_egreso" /></td></tr>
        
        <tr><td>Chasis</td><td><input type="text" value="" id="chasis" name="chasis_egreso" /></td></tr>
        <tr><td>Buque</td><td><input type="text" name="buque_egreso" id="buque_egreso" /></td></tr>
        <tr><td>Destino</td><td><input type="text" name="destino" id="destino" /></td></tr>
        <tr><td>Booking</td><td><input type="text" value="" id="booking_number" name="booking_number" /></td></tr>
        <tr><td>Observaciones</td><td><textarea name="observaciones_egreso"></textarea></td></tr>

    </tbody>
</table>
<hr />
<p>Información: <span id="informacion"></span></p>
<hr />


<input type="hidden" name="guardar" value="guardar" />
<input type="submit" id="realizar_salida" value="Realizar salida de contenedor" /> <span id="indicador_de_envio"></span>
</form>
<?php
$r = db_consultar('SELECT buque_egreso FROM `opsal_ordenes` WHERE buque_egreso<>"" GROUP BY buque_egreso');
$tagsBuque = array();
while ($f = db_fetch($r)) $tagsBuque[] = $f['buque_egreso'];

$r = db_consultar('SELECT cheque_egreso FROM `opsal_ordenes` WHERE cheque_egreso<>"" GROUP BY cheque_egreso');
$tagsCheque = array();
while ($f = db_fetch($r)) $tagsCheque[] = $f['cheque_egreso'];

$r = db_consultar('SELECT transportista_egreso FROM `opsal_ordenes` WHERE transportista_ingreso<>"" GROUP BY transportista_egreso');
$tagsTransportista = array();
while ($f = db_fetch($r)) $tagsTransportista[] = $f['transportista_egreso'];

$r = db_consultar('SELECT chofer_egreso FROM `opsal_ordenes` WHERE chofer_ingreso<>"" GROUP BY chofer_egreso');
$tagsChofer = array();
while ($f = db_fetch($r)) $tagsChofer[] = $f['chofer_egreso'];
?>
<script type="text/javascript">
    
    flag_posicion = false;
    nivel_deseado = 0;
        
    function ejecutar_busqueda() {
        
        // Eliminamos los anteriores
        $('.contenedor_movimiento_origen').removeClass('contenedor_movimiento_origen');
        $('.contenedor_movimiento_afectado_der').removeClass('contenedor_movimiento_afectado_der');
        $('.contenedor_movimiento_afectado_izq').removeClass('contenedor_movimiento_afectado_izq');
        
        columna = $("#posicion_columna").val();
        fila = $("#posicion_fila").val();
        nivel = $("#posicion_nivel").val();
        
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

            $('#informacion').html('');
            
            $.post('ajax.buscar_contenedor.php',{columna:columna, fila:fila, nivel:nivel},function(data){
                $("#datos_encontrados").html(data.resultados);
                $("#codigo_orden").val(data.codigo_orden);
            },'json');
            
            flag_posicion = false;
        
        } else {
        
            $("#datos_encontrados").html('Tal ubicación no existe.');
        }
    }
    
    $(function () {
        $( "#direccion" ).buttonset();

        $( "#buque_egreso" ).autocomplete({source: ["<?php echo join('","', $tagsBuque) ;?>"]});
        $( "#transportista" ).autocomplete({source: ["<?php echo join('","', $tagsTransportista) ;?>"]});
        $( "#chofer_egreso" ).autocomplete({source: ["<?php echo join('","', $tagsChofer) ;?>"]});

        
        $('#frm_salida').submit(function(event){
            event.preventDefault();
            
            
            // Si ha llenado el buque de egreso, tiene que ser embarque
            
            if ($("select#tipo_salida").val() == "terrestre" && $("#buque_egreso").val() != "")
            {
                alert("¿Buque para salida terrestre?. Creo que se ha equivocado.");
                return;
            }
            
            if ($("select#tipo_salida").val() == "")
            {
                alert("Seleccione el tipo de salida");
                return false;
            }

            if ($("#codigo_orden").val() == "" || $("#codigo_orden").val() == "0")
            {
                alert ("Falta el código único.");
                return false;
            }
            
            if ($("#posicion_columna").val() == "" || $("#posicion_fila").val() == "" || $("#posicion_nivel").val() == "")
            {
                alert ("Verifique la posición ingresada.");
                return false;
            }

            if ($("select#cheque option:selected").val() == "")
            {
                alert ("Seleccione un cheque.");
                return false;
            }

            if ($("#transportista").val() == "")
            {
                alert ("Ingrese el nombre del transportista.");
                return false;
            }
            
            if ($("#fechatiempo_egreso").val() == "")
            {
                alert ("Ingrese una fecha de despaho.");
                return false;
            }


            if ($("#chasis").val() == "" && !confirm ("El número de chasis esta vacío, continuar?."))
            {
                // Se dió cuenta que faltaba
                return false;
            }
        
            if ($("#chasis").val() != "" && /[\D]{4}\d{6}/.test($("#chasis").val()) == false )
            {
                alert ("Verifique número de chasis ingresado.\n4 letras y 6 números.");
                return false;
            }
            
            if ($("#buque_egreso").val() == "" && !confirm ("El nombre del buque esta vacío, continuar?."))
            {
                return false;
            }
            
            $("#indicador_de_envio").html('<img src="/IMG/general/cargando.gif" />');
            
            $("#realizar_salida").attr('disabled','disabled');
            
            //$("#contenedor_mapa").html('<p>Guardando datos...</p><br /><img src="/IMG/general/cargando.gif" />');
            $("#contenedor_visual").css('left',0).css('top',0).css('height',0).css('width',0);
            
            $.post('ajax.egreso.php',$('#frm_salida').serialize(),function (){
                iniciar_mapa();
                $('#frm_salida')[0].reset();
            });
        });
        
        $("#contenedor_mapa").bind('mapa_iniciado',function(){
            $("#indicador_de_envio").empty();
            $("#realizar_salida").removeAttr('disabled');
        });

        $("input:radio[name='direccion']").click(function(){
            
            direccion_salida = $("input:radio[name='direccion']:checked").val();
            
            posiciones = (direccion_salida == 'izquierda' ? afectados.izq : afectados.der);
            afectadosCant = (direccion_salida == 'izquierda' ? afectados.izqCant : afectados.derCant);

            if (posiciones.length > 0)
            {
                $('#informacion').html('<b style="color:red">Precaución al remover!</b>');
                $('#afectados').html('<b style="color:red">Es necesario mover '+ afectadosCant+' contenedores</b>');
            } else {
                $('#informacion').html('El paso de salida esta libre');
                $('#afectados').html('0');
            }
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
            
            // Si ingresó la posición manualmente o vía búsqueda, asegurarnos que es el de mas arriba o alertar
            if (flag_posicion && parseInt(nivel_deseado) != parseInt($(this).attr('nivel')))
            {
                alert('El contenedor deseado no puede ser procesado debido a que hay otro contenedor encima. Deberá liberar el paso primero para realizar la operación deseada.');
                flag_posicion = false;
                return false;
            }

            
            $("#posicion_columna").val($(this).attr('col'));
            $("#posicion_fila").val($(this).attr('fila'));
            $("#posicion_nivel").val(parseInt($(this).attr('nivel')));
            
            ejecutar_busqueda();
        });
        
        $("fechatiempo_salida").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0, maxDate: +0}).datepicker('setDate', new Date());
    });
</script>