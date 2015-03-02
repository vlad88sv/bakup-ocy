<?php
$options_agencia = '';

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Mostrar todas</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<form id="frm_patio" action="/contenedores.html?modo=cotizacion" method="post" autocomplete="off">
<h1>Opciones de refinado</h1>
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
        <tr><td>Agencia</td><td><select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select></td></tr>
        <tr><td>Clase(s)</td><td>
        <div id="clase_contenedor" style="text-align:center;">
            <input type="checkbox" name="clase[]" id="clase_a" value="A"/><label for="clase_a">A</label>&nbsp;
            <input type="checkbox" name="clase[]" id="clase_b" value="B" /><label for="clase_b">B</label>&nbsp;
            <input type="checkbox" name="clase[]" id="clase_c" value="C" /><label for="clase_c">C</label>
        </div>
        </td></tr>
        <tr><td>Tipo</td><td>
        <div id="tamano_contenedor" style="text-align: center;line-height: 30px;">
        <div id="tamano_contenedor" style="text-align: center;line-height: 30px;">
        <input rel="" type="radio" name="tamano_contenedor" id="clase_cualquiera" checked="checked" value=""/>
        <label for="clase_cualquiera">Cualquier tipo</label>&nbsp;
        
        <br />
        <input rel="20" type="radio" name="tamano_contenedor" id="clase_20" checked="checked" value="20"/>
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
        <tr>
            <td>
                Nivel
            </td>
            <td>
                <div id="nivel" style="text-align: center;line-height: 30px;">
                    <input type="radio" name="nivel" id="nivel_1" value="1"/>
                    <label for="nivel_1">1</label>&nbsp;
                    
                    <input type="radio" name="nivel" id="nivel_2" value="2"/>
                    <label for="nivel_2">2</label>&nbsp;
            
                    <input type="radio" name="nivel" id="nivel_3" value="3"/>
                    <label for="nivel_3">3</label>&nbsp;
                    
                    <input type="radio" name="nivel" id="nivel_4" value="4"/>
                    <label for="nivel_4">4</label>&nbsp;
                    
                    <input type="radio" name="nivel" id="nivel_5" value="5"/>
                    <label for="nivel_5">5</label>&nbsp;
            
                    <input type="radio" name="nivel" id="nivel_todos" checked="checked" value=""/>
                    <label for="nivel_todos">Todos</label>&nbsp;        
                </div>
            </td>
        </tr>
        </tr>
    </tbody>
</table>
<hr />
<h1>Información</h1>
<p>Afectados izq: <span id="afectadosIzq"></span></p>
<p>Afectados der: <span id="afectadosDer"></span></p>

<input type="hidden" name="filtrar" value="filtrar" />
<input type="submit" id="filtrar" value="Filtrar" /> <span id="resultados"></span>
</form>
<script type="text/javascript">
    afectados = {};
    
    function propagar_virus(base,direccion)
    {
        if (direccion == 'izq')
        {
            objetos = base.prevUntil('td[nivel="0"]');
        } else {
            objetos = base.nextUntil('td[nivel="0"]');
        }

        $.each(objetos, function () {
            if ($(this).attr('nivel') != '0')
            {
                if ($.inArray($(this).attr('grupo'),afectados[direccion]) == -1)
                {
                    afectados[direccion].push($(this).attr('grupo'));
                    afectados[direccion+'Cant'] += parseInt($(this).attr('nivel'));
                }
                    
                var grupo = $('div#contenedor_mapa table tbody tr td[grupo="'+$(this).attr('grupo')+'"]');   
                grupo.addClass('contenedor_movimiento_afectado_'+direccion);
                propagar_virus(grupo,direccion);
            }
        });
    }
    
    function ejecutar_busqueda(columna, fila, nivel) {
        
                
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
           
            
            afectados.izq = [];
            afectados.izqCant = 0;
            afectados.der = [];
            afectados.derCant = 0;
            
            propagar_virus(grupo,'izq');
            propagar_virus(grupo,'der');
            
            $("#afectadosIzq").html(afectados.izqCant);
            $("#afectadosDer").html(afectados.derCant);       
            
        
        }
    }

    $(function(){
        color = 'black';
        setInterval(function() {
            var elementos = $('.contenedor_filtrado');
            elementos.css('background-color',color);
            color = (color == 'black' ? '#CCC' : 'black');
            elementos.css('color',color);
        }, 500);

        $("#contenedor_mapa").bind('mapa_iniciado',function(event, data){});
        
        $( "#frm_patio").submit(function(event){
            event.preventDefault();
            iniciar_mapa($(this).serialize());
        });
        
        $( "#clase_prioridad" ).buttonset();
        $( "#nivel" ).buttonset();
        
        $( "#tamano_contenedor input[type='radio']" ).button();
        $( "#tipo_contenedor input[type='radio']" ).button();
        
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
                       
            ejecutar_busqueda( $(this).attr('col'), $(this).attr('fila'), parseInt($(this).attr('nivel')) );
        });
        
    });
</script>
