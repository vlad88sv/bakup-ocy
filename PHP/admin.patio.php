<table id="opsal_ims" class="opsal_tabla_ancha">
<tbody>
<tr>
<td style="width:340px;vertical-align:top;">
<form id="frm_patio" method="post" action="/administracion.html?modo=patio">
<table class="tabla-estandar opsal_tabla_ancha">
    <tbody>
    <tr>
        <td>Tipo</td>
        <td>
            <select name="tipo">
                <option value="calle">Calle</option>
                <option value="normal">Normal</option>
                <option value="extra">Extra</option>
                <option value="restringido">Restringido</option>
                <option value="taller">Taller</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            Rango
        </td>
        <td>
            <table class="tabla-estandar opsal_tabla_ancha tabla-centrada">
            <tr><th style="text-align:center;">Inicio</th><th style="text-align:center;">Final</th></tr>
            <tr>
                <td>
                    <table class="tabla-estandar tabla-centrada opsal_tabla_ancha">
                    <tbody>
                    <tr><th>Col.</th><th>Fila</th></tr>
                    <tr>
                        <td><input style="width:20px;" type="text" value="" name="rango_inicio_col" id="rango_inicio_col" /></td>
                        <td><input style="width:20px;" type="text" value="" name="rango_inicio_fila" id="rango_inicio_fila" /></td>
                    </tr>
                    </tbody>
                    </table>
                </td><td>
                    <table class="tabla-estandar tabla-centrada opsal_tabla_ancha">
                    <tbody>
                    <tr><th>Col.</th><th>Fila</th></tr>
                    <tr>
                        <td><input style="width:20px;" type="text" value="" name="rango_final_col" id="rango_final_col" /></td>
                        <td><input style="width:20px;" type="text" value="" name="rango_final_fila" id="rango_final_fila" /></td>
                    </tr>
                    </tbody>
                    </table>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<input type="hidden" name="guardar" value="guardar" />
<input type="submit" id="guardar" value="Guardar" />
</form>
</td>
<td id="opsal_mapa" style="width:744px;background-color:white;text-align:left;vertical-align: top;">
    <table id="opsal_regla_superior" style="width:732px !important;height:10px;left: 10px;position: relative;">
        <tr>
            <?php
            $c = 'SELECT x2 FROM opsal_posicion GROUP BY x2 ORDER BY x DESC';
            $r = db_consultar($c);
            
            while ($f = mysqli_fetch_assoc($r))
            {
                $x2 = $f['x2'][0].(isset($f['x2'][1]) ? '<br />'.$f['x2'][1] : '');
                echo '<td>'.$x2.'</td>';
            }
            ?>
        </tr>
    </table>
    <table id="opsal_regla_lateral" style="float:left;height:640px;width:10px;">
        <?php
        $c = 'SELECT y2 FROM opsal_posicion GROUP BY y2 ORDER BY y DESC';
        $r = db_consultar($c);
        
        while ($f = mysqli_fetch_assoc($r))
        {
            echo '<tr><td>'.$f['y2'].'</td></tr>';
        }
        ?>    
    </table>
    <div style="position: relative;float:left;width:732px;">
        <div id="contenedor_mapa" style="height:705px;text-align:center;">
                <p>Cargando mapa...</p><br />
                <img src="/IMG/general/cargando.gif" />
        </div>
        <div id="contenedor_visual" style="position:absolute;background-color:#00FAFF;z-index:99;"></div>
    </div>
</td>
</tr>
</tbody>
</table>
<script type="text/javascript">
    modo = 'inicio';
    inicio = final = null;
    
    // Iniciemos el mapa
    function iniciar_mapa(opciones) {
        opciones = typeof opciones !== 'undefined' ? opciones : {};
        $.post('ajax.mapa.php',opciones, function (data){
            var tiempo_inicio = new Date().getTime();
            $('#contenedor_mapa').html(data.mapa);
            $("#contenedor_mapa").trigger('mapa_iniciado',data);
            // Select all elements that are to share the same tooltip
            var elems = $('#contenedor_mapa td');
     
            $('<div />').qtip(
            {
                    content: ' ', // Can use any content here :)
                    position: {
                            my: 'bottom right',
                            at: 'top left',
                            target: 'event', // Use the triggering element as the positioning target
                            effect: false	// Disable default 'slide' positioning animation
                    },
                    show: {
                            target: elems,
                            delay: 0,
                            solo: true
                    },
                    hide: {
                            target: elems,
                            delay: 0,
                    },
                    events: {
                            show: function(event, api) {
                                    // Update the content of the tooltip on each show
                                    var target = $(event.originalEvent.target);
     
                                    if(target.length) {
                                            api.set('content.text', target.attr('tooltip'));
                                    }
                            }
                    }
            });
        
            console.log('Mapa renderizado en ' + (new Date().getTime() - tiempo_inicio) + 'us');
        }, 'json');
    }
    
    $(function(){
        iniciar_mapa();
    
        $("#contenedor_visual").css('opacity','0.2');
                
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
                console.log($(this).attr('col') + ' > ' + inicio.attr('col'));
                console.log($(this).attr('fila') + ' > ' + inicio.attr('fila'));
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
        
        $('#frm_patio').submit(function(event){
            event.preventDefault();
            
            $.post('ajax.patio.php',$('#frm_patio').serialize(),function (){
                iniciar_mapa();
                $('#frm_patio')[0].reset();
            });

        });
    });
</script>