<?php
if (_F_usuario_cache('modulo_facturar') == '0')
    protegerme();
?>
<?php
/****************** generar_factura *****************/
if (isset($_POST['generar_factura']))
{    
    $facturas = array();
    $UNIQID = uniqid('',true);
   
    foreach($_POST['detalle'] as $categoria => $datos)
    {        
        if (isset($datos['utilizar']))
        {
            $facturas[] = CrearFactura($UNIQID, $_POST['codigo_agencia'], $categoria, $datos);
        }
    }
        
    header('Location: /control.facturas.html?grupo='.$UNIQID);
    
    return;
}

/****************** FIN guardar_factura *************/
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">naviera</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<div class="noimprimir">
<h1 class="opsal_titulo">Asistente de facturación</h1>
<form action="/facturacion.html" method="get" id="controles_facturacion">
    <hr />
    <div style="font-size:0.8em;padding:4px;">
        <p><b>Facturar para operación </b></p>
        <select id="modo_facturacion" name="modo_facturacion">
            <option rel="modo_1" value="contenedores">Almacenaje y movimientos</option>
            <option rel="modo_2" value="condiciones">Elaboración de condición</option>
            <option rel="modo_3" value="reparaciones">Reparaciones</option>
        </select>
        &nbsp;
        <input type="submit" id="filtrar" name="filtrar" value="Realizar filtrado" />
    </div>
    <hr />
    <div style="font-size:0.8em;padding:4px;">
        De&nbsp;
        <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select>
        <span id="modo_3" style="display:none;">
            &nbsp;facturar servicios prestados para el rubro de <b>reparación de contenedores</b>
        </span>
        <span id="modo_2" style="display:none;">
            &nbsp;facturar servicios prestados para el rubro de <b>elaboración de condiciones</b>
        </span>
        <span id="modo_1">
            &nbsp;los contenedores que&nbsp;
            <select name="tipo_salida" id="tipo_salida" style="width:150px;">
                <option value="estadia">no fueron despachados</option>
                <option value="estibas">Solo estibas de no despachados</option>
                <option value="terrestre">fueron despachados vía terrestre</option>
                <option value="terrestre2">fueron despachados vía terrestre [APL]</option>
                <option value="embarque">fueron despachados en el buque</option>
                <option value="embarque_primitivo">fueron embarcados</option>
                <option value="remociones_simples">tuvieron remociones pero no tienen despacho</option>
                <option value="remociones">tuvieron remociones y fueron despachados</option>
                <option value="remociones_embarque">tuvieron remociones y despachado via buque</option>
                <option value="remociones_terrestre">tuvieron remociones y despachado via terrestre</option>
                <option value="dt">tuvieron doble transferencia</option>
                <option value="dm">tuvieron doble movimiento</option>
            </select>        
    
            <span id="seleccion_buque" style="display:none;">
                &nbsp;en&nbsp;
                <span id="seleccion_buque_load"></span>
            </span>       
    
            <span id="seleccion_solo_despacho" style="display:none;">
            &nbsp;cobrar almacenaje&nbsp;
            <select name="tipo_cobro" id="tipo_cobro">
                <option value="periodo">solo del periodo</option>
                <option value="completo">desde la recepción</option>
            </select>
            </span>
            
            <span id="texto_si_terrestre" style="display: none;">
                si el despacho fue
            </span>        
        </span>
        <span id="seleccion_periodo">
            &nbsp;entre el&nbsp;
            <input type="text" class="calendario" name="periodo_inicio" value="" style="width:60px;" />
            &nbsp;al&nbsp;
            <input type="text" class="calendario" name="periodo_final" value="" style="width:60px;" />
        </span>
        <span id="codigo_agencia_en_favor" style="display:none;">
        &nbsp;en favor de&nbsp;
        <select id="codigo_agencia_2" name="codigo_agencia_2"><?php echo $options_agencia; ?></select>
        </span>
    </div>
</form>
<hr />
</div>
<?php
if (isset($_GET['filtrar']))
{
    echo '<p>Compartir estos datos: <input readonly="readonly" onclick="this.focus();this.select();" style="width:850px;" value="'.curPageURL().'"></p>';
    echo '<hr />';
    
    $factura = FacturarPeriodo($_GET);
    echo '<form action="/facturacion.html" method="POST" target="_blank">';
    
    echo '<div class="noimprimir" style="border-radius:5px; border: 1px solid grey; padding: 5px;">';
        echo '<h1>Totales</h1>';
        echo $factura['cuadro'];
        echo '<div style="text-align:right;"><input type="submit" value="Generar factura" id="generar_factura" name="generar_factura" /></div>';
    echo '</div>';
    
    echo '<br />';
    
    echo '<div style="border-radius:5px; border: 1px solid grey; padding: 5px;">';
        echo '<h1>Anexos</h1>';
        echo $factura['anexo'];
    echo '</div>';
    
    echo '<div class="noimprimir">';
        echo '<br /><hr />';
        echo '<input type="hidden" name="periodo_inicio" value="'.$_GET['periodo_inicio'].'" />';
        echo '<input type="hidden" name="periodo_final" value="'.$_GET['periodo_final'].'" />';
        echo '<input type="hidden" name="codigo_agencia" value="'.$_GET['codigo_agencia'].'" />';
    echo '</div>';
    
    echo '</form>';
}
?>
<script type="text/javascript">
    $(function(){
        
        $("#generar_factura").click(function(event){
            if ($('.chkconcepto:checked').length == 0)
            {
                alert ("Debe seleccionar un concepto por lo menos.");
                event.preventDefault();
                return;
            }
        });
        
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
        
        $('#tipo_de_facturacion').change(function(){
            //?
        });
        
        $('#tipo_cobro').change(function(){
            
            $('#texto_si_terrestre').hide();
            $('#seleccion_periodo').show();
            
            if ($('#tipo_salida option:selected').val() == 'embarque' && $('#tipo_cobro option:selected').val() == 'completo')
            {
                $('#seleccion_periodo').hide();    
            }
            
            if ( ($('#tipo_salida option:selected').val() == 'terrestre' || $('#tipo_salida option:selected').val() == 'terrestre2') && $('#tipo_cobro option:selected').val() == 'completo' )
            {
                $('#texto_si_terrestre').show();
            }
        });
        
        $('#tipo_salida, #codigo_agencia').change(function(){
            if ($('#tipo_salida option:selected').val() == 'embarque')
            {
                $('#tipo_cobro').val('periodo');
                
                $('#seleccion_solo_despacho').show();
                $('#seleccion_buque').show();
                $('#texto_si_terrestre').hide();
                
                $('#seleccion_buque_load').html('[cargando]').load('ajax.seguro.php',{accion : 'obtener_ultimos_buques', codigo_agencia : $("#codigo_agencia").val()});
            }
            
            if (($('#tipo_salida option:selected').val() == 'terrestre' || $('#tipo_salida option:selected').val() == 'terrestre2'))
            {
                if ($('#tipo_cobro option:selected').val() == 'completo')
                {
                    $('#texto_si_terrestre').show();
                } else {
                    $('#texto_si_terrestre').hide();
                }
                
                $('#seleccion_solo_despacho').show();
                $('#seleccion_periodo').show();
                $('#seleccion_buque').hide();
                $('#seleccion_buque_load').empty();
            }
            
            if ($('#tipo_salida option:selected').val() == 'patio' || $('#tipo_salida option:selected').val() == 'remociones')
            {
                $('#texto_si_terrestre').hide();
                $('#seleccion_solo_despacho').hide();
                $('#seleccion_periodo').show();
                $('#seleccion_buque').hide();
                $('#seleccion_buque_load').empty();
            }
            
            if ($('#tipo_salida option:selected').val() == 'embarque_primitivo')
            {
                $('#tipo_cobro').val('completo');
                
                $('#seleccion_solo_despacho').hide();
                $('#seleccion_buque').hide();
                $('#texto_si_terrestre').hide();
                $('#seleccion_periodo').show();
            }
            
            if ($('#tipo_salida option:selected').val() == 'remociones' || $('#tipo_salida option:selected').val() == 'remociones_terrestre' || $('#tipo_salida option:selected').val() == 'remociones_embarque'  || $('#tipo_salida option:selected').val() == 'remociones_simples')    
            {
               $("#codigo_agencia_en_favor").show();
            } else {
                $("#codigo_agencia_en_favor").hide();
            }       
            
        });
        
        $('#modo_facturacion').change(function(){
            $('#modo_1').hide();    
            $('#modo_2').hide();
            $('#modo_3').hide();
            
            $('#' + $('#modo_facturacion option:selected').attr('rel')).show();
        });
        
        $("#filtrar").click(function(event){
            
            if ($('#tipo_salida option:selected').val() == 'patio' && $('#tipo_cobro option:selected').val() == 'completo')
            {
                event.preventDefault();
                alert('Selección inválida.\nNo puede facturar contenedores sin despacho desde la recepción.');
            }
            
            if ($("#codigo_agencia").val() == "")
            {
                event.preventDefault();
                alert('Debe especificar una agencia.');
            }
        });
    });
</script>