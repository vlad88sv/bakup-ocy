<?php
$options_agencia = '';

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Cualquiera</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<form id="frm_cotizacion" action="/contenedores.html?modo=cotizacion" method="post" autocomplete="off">
<h1>Opciones para filtrado</h1>
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr><td>Naviera</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td></tr>
        <tr><td>Clase(s)</td><td>
        <div id="clase_contenedor" style="text-align:center;">
            <input type="checkbox" name="clase[]" id="clase_a" value="A"/><label for="clase_a">A</label>&nbsp;
            <input type="checkbox" name="clase[]" id="clase_b" value="B" /><label for="clase_b">B</label>&nbsp;
            <input type="checkbox" name="clase[]" id="clase_c" value="C" /><label for="clase_c">C</label>
        </div>
        </td></tr>
        <tr><td>Tipo</td><td>
        <div id="tamano_contenedor" style="text-align: center;line-height: 30px;">
        <input rel="" type="radio" name="tamano_contenedor" id="clase_cualquiera" checked="checked" value=""/>
        <label for="clase_cualquiera">Cualquier tipo</label>&nbsp;
        
        <br />
        
        <input rel="20" type="radio" name="tamano_contenedor" id="clase_20" value="20"/>
        <label for="clase_20">20</label>&nbsp;
        
        <input rel="40" type="radio" name="tamano_contenedor" id="clase_40" value="40"/>
        <label for="clase_40">40</label>&nbsp;
        
        <input rel="60" type="radio" name="tamano_contenedor" id="clase_45" value="45"/>
        <label for="clase_45">45</label>&nbsp;
        
        <input rel="60" type="radio" name="tamano_contenedor" id="clase_48" value="48"/>
        <label for="clase_48">48</label><br />
        </div>
        
        <div id="tipo_contenedor" style="text-align: center;line-height: 30px;">
        <input type="radio" name="tipo_contenedor" id="clase_dc" checked="checked" value="DC"/>
        <label for="clase_dc">DC</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_hc" value="HC"/>
        <label for="clase_hc">HC</label>&nbsp;

        <input type="radio" name="tipo_contenedor" id="clase_ot" value="OT"/>
        <label for="clase_ot">OT</label>&nbsp;

        <input type="radio" name="tipo_contenedor" id="clase_ho" value="HO"/>
        <label for="clase_ho">OTHC</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_rf" value="RF"/>
        <label for="clase_rf">RF</label>&nbsp;
        
        <input type="radio" name="tipo_contenedor" id="clase_fr" value="FR"/>
        <label for="clase_fr">FR</label>&nbsp;

        <input type="radio" name="tipo_contenedor" id="clase_tq" value="FR"/>
        <label for="clase_tq">TQ</label>&nbsp;        
        </div>
        </td></tr>
        <tr>
            <td>
                Rango
            </td>
            <td>
                <table class="opsal_tabla_ancha tabla-centrada" style="border-collapse:collapse;">
                <tr><th style="text-align:center;">Inicio</th><th style="text-align:center;">Final</th></tr>
                <tr>
                    <td>
                        <table class="opsal_tabla_ancha tabla-centrada" style="border-collapse:collapse;">
                        <tr><th>Col.</th><th>Fila</th></tr>
                        <tr>
                            <td><input style="width:20px;" type="text" value="" name="rango_inicio_col" id="rango_inicio_col" /></td>
                            <td><input style="width:20px;" type="text" value="" name="rango_inicio_fila" id="rango_inicio_fila" /></td>
                        </tr>
                        </table>
                    </td>
                    <td>
                        <table class="opsal_tabla_ancha tabla-centrada" style="border-collapse:collapse;">
                        <tr><th>Col.</th><th>Fila</th></tr>
                        <tr>
                            <td><input style="width:20px;" type="text" value="" name="rango_final_col" id="rango_final_col" /></td>
                            <td><input style="width:20px;" type="text" value="" name="rango_final_fila" id="rango_final_fila" /></td>
                        </tr>
                        </table>
                    </td>
                </tr>
                </table>
            </td>
        </tr>    
        
        <tr>
            <td>Dirección de salida</td>
            <td style="text-align:center;">
                <div id="direccion">
                <input type="radio" name="direccion" value="izquierda" id="direccion_izquierda" /> <label for="direccion_izquierda">Izquierda</label>
                <input type="radio" name="direccion" value="derecha" id="direccion_derecha" checked="checked" /> <label for="direccion_derecha">Derecha</label>
                </div>
            </td>
        </tr>
        <tr>
            <td>Orden de salida</td>
            <td style="text-align:center;">
                <div id="orden_salida">
                <input type="radio" name="orden_salida" value="bloque" id="orden_salida_columna" /> <label for="orden_salida_columna">Columna</label>
                <input type="radio" name="orden_salida" value="fila" id="orden_salida_fila" checked="checked" /> <label for="orden_salida_fila">Fila</label>
                </div>
            </td>
        </tr>
        
        <tr><td>Límite  <acronym title="número máximo de contenedores a búscar, 0 indica ilimitado. Se escogen los mas antiguos." style="font-size:8px;">?</acronym></td><td><input type="text" name="limite" /></td></tr>
        
        <tr>
            <td>Forzar <acronym title="Asegurese de usar un limite si fuerza la búsqueda por vencimiento de ARIVU o por antiguedad en patio." style="font-size:8px;">?</acronym></td>
            <td style="text-align:center;">
                <div id="forzar">
                <input type="radio" name="forzar" value="arivu" id="forzar_arivu" /> <label for="forzar_arivu">ARIVU</label>
                <input type="radio" name="forzar" value="no" id="forzar_no" checked="checked" /> <label for="forzar_no">No</label>
                <input type="radio" name="forzar" value="antiguedad" id="forzar_antiguedad" /> <label for="forzar_antiguedad">Antiguedad</label>
                </div>
            </td>
        </tr>

    </tbody>
</table>
<input type="hidden" name="filtrar" value="filtrar" />
<input type="submit" id="filtrar" value="Filtrar" /> <span id="resultados"></span>
</form>
<br /><hr />
<form id="frm_salida" action="detalle.salida.bloque.html" method="post" autocomplete="off">
<h1>Detalles de salida</h1>
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>       
        <tr><td>Fecha salida</td><td><input type="text" name="fechatiempo_egreso" id="fechatiempo_egreso" class="calendario" /></td></tr>
        <tr><td>Buque</td><td><input type="text" name="buque_egreso" id="buque_egreso" /></td></tr>
        <tr><td>Destino</td><td><input type="text" name="destino" id="destino" /></td></tr>
    </tbody>
</table>

<table id="detalles_salida" class="opsal_tabla_ancha opsal_tabla_letra_pequena">
    <tbody>
    </tbody>
    <thead><tr><th>Naviera</th></th><th>Contenedor</th><th>Tipo</th><th>Posicion</th><th>Salida</th></tr></thead>
</table>
<input type="submit" id="ejecutar_salida" name="ejecutar_salida" value="Ejecutar salida y continuar" />
</form>
<hr />
<p><b>Nota: </b> al continuar guardara la salida de los contenedores seleccionados y podrá imprimir la lista detallada de contenedores.</p>
<?php
$c = 'SELECT buque_egreso FROM `opsal_ordenes` WHERE tipo_salida="embarque" GROUP BY buque_egreso';
$r = db_consultar($c);
$tagsBuque = array();
while ($f = db_fetch($r))
{
    $tagsBuque[] = $f['buque_egreso'];
}

$c = 'SELECT destino FROM `opsal_ordenes` WHERE tipo_salida="embarque" GROUP BY destino';
$r = db_consultar($c);
$tagsDestino = array();
while ($f = db_fetch($r))
{
    $tagsDestino[] = $f['destino'];
}
?>
<script type="text/javascript">
    modo = 'inicio';
    inicio = final = null;
    /*
    color = 'black';
    setInterval(function() {
        var elementos = $('.contenedor_filtrado');
        elementos.css('background-color',color);
        color = (color == 'black' ? '#CCC' : 'black');
        elementos.css('color',color);
    }, 500);
    */
        
    $(function(){
        $("#contenedor_visual").css('opacity','0.2');
        
        $( "#buque_egreso" ).autocomplete({source: ["<?php echo join('","', $tagsBuque) ;?>"]});
        $( "#destino" ).autocomplete({source: ["<?php echo join('","', $tagsDestino) ;?>"]});
        
        $("#contenedor_mapa").bind('mapa_iniciado',function(event, data){
            numero = data.filtro_numero_resultados || 0;
            $("span#resultados").html( numero + ' contenedores encontrados.' );
            $("#detalles_salida tbody").empty();
            
            if (numero > 0)
            {
                $.each(data.ordenes, function(index, data){
                    //console.log(data);
                    $("#detalles_salida tbody").append('<tr><td><input name="codigo_orden['+index+'][]" value="'+data.codigo_orden+'" type="hidden" />'+data.nombre_agencia+'</td><td>'+data.codigo_contenedor+'</td><td>'+data.nombre+'</td><td>'+data.x2+'-'+data.y2+'-'+data.nivel+'</td><td><input type="checkbox" '+ (data.filtrado == '1' ? 'checked="checked"' : '') +' name="salida['+index+'][]" /></td></tr>');
                });
            }
        });
        
        $('#opsal_mapa #contenedor_mapa table td').live('click',function(event){
            
            if (modo == 'inicio')
            {
                $('#opsal_mapa #contenedor_mapa table td').removeClass('contenedor_movimiento_origen');
                
                $('#rango_final_col').val('');
                $('#rango_final_fila').val('');
                $('#rango_inicio_col').val($(this).attr('col'));
                $('#rango_inicio_fila').val($(this).attr('fila'));
                inicio = $(this);
                inicio.addClass('contenedor_movimiento_origen');
                modo = 'final';
                
                $("#contenedor_visual")
                .css('left',0)
                .css('top',0)
                .css('width',0)
                .css('height',0);  
            } else {
                if ($(this).attr('col') > inicio.attr('col') || $(this).attr('fila') > parseInt(inicio.attr('fila')))
                {
                    alert('Favor seleccione hacia la derecha y abajo del punto de inicio');
                    return false;
                }
                
                $('#rango_final_col').val($(this).attr('col'));
                $('#rango_final_fila').val($(this).attr('fila'));
                final = $(this);
                final.addClass('contenedor_movimiento_origen');
                modo = 'inicio';
                
                $("#contenedor_visual")
                .css('left',inicio.position().left)
                .css('top',inicio.position().top)
                .css('width',((final.position().left - inicio.position().left) + 11))
                .css('height',((final.position().top - inicio.position().top) + 19));  
            }
        });
        
        $( "#clase_prioridad" ).buttonset();
        
        $( "#frm_cotizacion").submit(function(event){
            event.preventDefault();
            iniciar_mapa($(this).serialize());
        });

        $( "#tamano_contenedor input[type='radio']" ).button();
        $( "#tipo_contenedor input[type='radio']" ).button();
        $( "#direccion" ).buttonset();
        $( "#orden_salida" ).buttonset();
        $( "#forzar" ).buttonset();
        
        $('#frm_salida').submit(function(event){
            
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

            if ($("#cheque").val() == "")
            {
                alert ("Ingrese el nombre del cheque.");
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

            if ($("#chasis").val() == "")
            {
                alert ("Verifique número de chasis ingresado.");
                return false;
            }          

            if ($("#buque_egreso").val() == "")
            {
                alert ("Ingrese el nombre del buque.");
                return false;
            }
            
            $("#ejecutar_salida").attr('disable','disable');
        });
    });
</script>
