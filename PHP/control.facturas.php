<?php protegerme(); ?>
<script>
    function escape(str)
    {
        return str.replace(/([ #;?&,.+*~\':"!^$[\]()=>|\/@])/g,'\\$1');
    }
    
    $(function(){
        
        $(".aut_fiscal").dblclick(function(){
            $.post('ajax.seguro.php', {obtener_ultima_aut_fiscal: true}, function(data){
		    alert(data);
	    },'html');
        });
        
        $(".guardar_datos_fiscales").submit(function(event){
            event.preventDefault();
            $.post('ajax.seguro.php',$(this).serialize(), function(data){
		    alert(data);
	    },'html');
        });
        
        $(".eliminar_factura").click(function(){
            var uniqid = $(this).attr('uniqid');
            if ( confirm('Desea eliminar la factura con código interno #' + uniqid) )
            {
		$.post('ajax.seguro.php',{accion:'eliminar_factura', ID:uniqid}, function(){
		    alert('Se ha eliminado la factura.');
		});
                $("#factura_"+escape(uniqid)).remove();
            }
        });
        

        $(".desanular_factura").click(function(){
            var uniqid = $(this).attr('uniqid');
            if ( confirm('Desea desanular la factura con código interno #' + uniqid) )
            {
		$.post('ajax.seguro.php',{accion:'desanular_factura', ID:uniqid}, function(){
		    alert('Se ha desanulado la factura.');
		});
                $("#factura_"+escape(uniqid)).removeClass('factura_anulada');
            }
        });


        $(".anular_factura").click(function(){
            var uniqid = $(this).attr('uniqid');
            if ( confirm('Desea anular la factura con código interno #' + uniqid) )
            {
		$.post('ajax.seguro.php',{accion:'anular_factura', ID:uniqid}, function(){
		    alert('Se ha anulado la factura.');
		});
                $("#factura_"+escape(uniqid)).addClass('factura_anulada');
            }
        });

        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>
<?php

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Cualquier agencia</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}

$CONDICIONES = '';

echo '<h1>Módulo de control de facturas emitadas</h1>';

echo '<h2>Últimos números utilizados de documentos fiscales por tipo</h1>';

// Notar que no es la cantidad de tipo_fiscal existentes, sino el último correlativo.
$c = 'SELECT aut_fiscal AS "Autorización de tiraje", tipo_fiscal AS "Tipo fiscal", MAX(numero_fiscal) AS "Número" FROM `facturas` GROUP BY aut_fiscal, tipo_fiscal ORDER BY fecha_creada DESC, tipo_fiscal ASC';
$r = db_consultar($c);
echo db_ui_tabla($r, 'class="tabla-estandar opsal_tabla_borde_oscuro"');


$ORDER_BY = 'codigo_factura';

if (isset($_GET['filtrar']))
{
    if (isset($_GET['ver_facturas']) || isset($_GET['ver_cf']) || isset($_GET['ver_desconocidos']))
        $CONDICIONES .= ' AND tipo_fiscal IN ('.(isset($_GET['ver_facturas']) ? '"factura"' : '""').''.(isset($_GET['ver_cf']) ? ', "credito_fiscal"' : '').''.(isset($_GET['ver_desconocidos']) ? ', "desconocido"' : "").')';
    
    if ( isset($_GET['codigo_agencia']) && is_numeric($_GET['codigo_agencia']) )
    {
        $CONDICIONES .= ' AND codigo_agencia = "'.db_codex($_GET['codigo_agencia']).'"';
    }
    
    if ( isset($_GET['ordenar']))
    {
        $ORDER_BY = 'numero_fiscal';
    }
    
}

if (empty($_GET['grupo']))
{
    $fecha_inicio = (@$_GET['fecha_inicio'] ?: date('Y-m-01'));
    $fecha_final = (@$_GET['fecha_final'] ?: date("Y-m-t"));
    
    $PERIODO = "AND periodo_inicio >= '$fecha_inicio' AND periodo_final <= '$fecha_final' " ;
} else {
    $PERIODO = "";
}

if (!empty($_GET['grupo']))
{
    $CONDICIONES = ' AND t1.`uniqid` = "'.db_codex($_GET['grupo']).'"';
}


$c = 'SELECT t1.grupo, aut_fiscal, numero_fiscal, tipo_fiscal, uniqid, t3.`nombre_fiscal`, t3.`tipo_de_documento`, t3.`registro_de_iva`, t3.`nit`, t3.`direccion`, t3.`giro`, t3.departamento, `servicio`, `codigo_factura`, t2.`usuario` AS "operador" , `codigo_agencia`, t3.`usuario` AS "agencia", `fecha_creada`, `fecha_cobrada`, `flag_enviada`, `flag_cobrada`, `flag_anulada`, total_sin_iva, iva, total, periodo_inicio, periodo_final FROM `facturas` AS t1 LEFT JOIN `opsal_usuarios` AS t2 USING(codigo_usuario) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE flag_eliminada=0 ' . $PERIODO . $CONDICIONES . ' ORDER BY '.$ORDER_BY.' DESC';

$r = db_consultar($c);

echo '<br />';
echo '<h2>Facturas generadas</h2>';

echo '<form action="" method="GET" style="border:1px dotted black;margin: 5px 0px;padding: 10px;"><input type="checkbox" id="ver_facturas" checked="checked" name="ver_facturas" /><label for="ver_facturas">Factura</label> <input type="checkbox" checked="checked" id="ver_cf" name="ver_cf" /><label for="ver_cf">Créditos físcales</label> <input type="checkbox" id="ver_desconocidos" checked="checked" name="ver_desconocidos" /><label for="ver_desconocidos">Desconocidos</label> |  Agencia: <select id="codigo_agencia" name="codigo_agencia">'. $options_agencia. '</select> | <input type="checkbox" id="ordenar" name="ordenar" /><label for="ordenar">Orden físcal</label> | Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="'.$fecha_inicio.'" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="'.$fecha_final.'" />| <input type="submit" name="filtrar" value="Filtrar" /></form><br /><hr/><br />';

if (mysqli_num_rows($r) == 0)
{
    echo '<p>No se encontraron facturas que concuerden con sus especificaciones</p>';
    return;
}

while ($f = mysqli_fetch_assoc($r))
{
    $facturas[$f['uniqid']][] = $f;
}

foreach($facturas as $uniqid => $factura)
{
    $detalle = '';
    $subtotal = 0;
    $iva = 0;
    $total = 0;    
 
    // Detalle
    foreach ($factura as $codigo_factura => $bdetalle)
    {
        
        $detalle .= '<p><input readonly="readonly" type="text" style="width:100%;" value="'.$bdetalle['servicio'].'" /></p>';
        $total += $bdetalle['total'];
        $subtotal += $bdetalle['total_sin_iva'];
        $iva += $bdetalle['iva'];
    }

    $periodo = ($factura[0]['periodo_inicio'] == $factura[0]['periodo_final'] ? 'Día ' . $factura[0]['periodo_inicio'] : 'Del ' . $factura[0]['periodo_inicio'] . ' al ' . $factura[0]['periodo_final']);
    
    $datos = '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
    $datos .= '<tr><th>#</th><th>Agencia</th><th>Fecha emisión</th><th>Grupo</th><th>Periodo</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>';
    $datos .= '<tr><td><a href="/control.facturas.html?grupo='.$factura[0]['uniqid'].'">'.$factura[0]['codigo_factura'].'</a></td><td>'.$factura[0]['agencia'].'</td><td>'.$factura[0]['fecha_creada'].'</td><td>'.$factura[0]['grupo'].'</td><td>'. $periodo .'</td><td>'.dinero($subtotal).'</td><td>'.dinero($iva).'</td><td>'.dinero($total).'</td></tr>';
    $datos .= '</table>';
    
    
    $controles = '<div style="margin:10px;padding:20px;border:1px dashed gray;" >';
    $controles .= '<p>Fecha:&nbsp;<input type="text" readonly="readonly" class="calendario" value="" /> NRC:&nbsp;<input readonly="readonly" type="text" value="'.$factura[0]['registro_de_iva'].'" />&nbsp;NIT&nbsp;<input type="text"  readonly="readonly" value="'.$factura[0]['nit'].'" />&nbsp;Cliente:&nbsp;<input type="text" readonly="readonly" value="'.$factura[0]['nombre_fiscal'].'" /></p>';
    $controles .= '<p>Giro:&nbsp;<input type="text" readonly="readonly" style="width:300px;" value="'.$factura[0]['giro'].'" />&nbsp;Dirección:&nbsp;<input type="text" style="width:280px;" readonly="readonly" value="'.$factura[0]['direccion'].'" />&nbsp;Departamento:&nbsp;<input type="text" style="width:120px;" readonly="readonly" value="'.$factura[0]['departamento'].'" /></p>';
    
    
    $controles .= $detalle;
    
    $controles .= '</div>';
    $controles .= '<br /><hr />';
    $controles .= '<table style="width:100%;">';
    $controles .= '<tr>';
    $controles .= '<td>';
    $controles .= '<form action="/impresion.html" target="_blank" method="GET">';
    $controles .= '<input type="hidden" name="uniqid" value="'.$factura[0]['uniqid'].'" />';
    $controles .= '<select name="tipo_impresion" class="imprimir_legal"><option value="consumidor_final">Consumidor final</option><option value="credito_fiscal" '.($factura[0]['tipo_de_documento'] == 'credito_fiscal' ? 'selected="selected"' : '').'>Crédito físcal</option></select>&nbsp;';
    $controles .= '<input type="submit" class="imprimir_legal" value="Impresión" />&nbsp;';
    $controles .= '<input name="impresion_anexo" type="submit" class="imprimir_anexo" value="Anexo" />';
    $controles .= '</form>';   
    $controles .= '</td>';
    $controles .= '<td style="text-align:right;">';
    $controles .= '<form class="guardar_datos_fiscales" action="" method="post">';
    $controles .= '<input type="hidden" name="accion" value="guardar_datos_fiscales" />';
    $controles .= '<input type="hidden" name="uniqid" value="'.$factura[0]['uniqid'].'" />';
    $controles .= 'Documento fiscal utilizado:&nbsp;';
    $controles .= '<select name="tipo_fiscal">';
    $controles .= '<option '.(($factura[0]['tipo_fiscal'] == 'desconocido' || empty($factura[0]['tipo_fiscal']) )  ? 'selected="selected"' : '').' value="desconocido">Desconocido</option>';
    $controles .= '<option '.($factura[0]['tipo_fiscal'] == 'credito_fiscal' ? 'selected="selected"' : '').' value="credito_fiscal">Crédito fiscal</option>';
    $controles .= '<option '.($factura[0]['tipo_fiscal'] == 'factura' ? 'selected="selected"' : '').' value="factura">Factura</option>';
    $controles .= '</select>&nbsp;';
    $controles .= 'Autorización #<input class="aut_fiscal" name="aut_fiscal" type="text" value="'.$factura[0]['aut_fiscal'].'" />&nbsp;';
    $controles .= '#<input name="numero_fiscal" type="text" value="'.$factura[0]['numero_fiscal'].'" />';
    $controles .= '<input type="submit" value="Guardar" />';
    $controles .= '</form>';
    $controles .= '</td>';
    $controles .= '</tr>';
    $controles .= '</table>';
 
    $controles .= '<br /><hr />';   
    
    $controles .= '<div cf="'.$f['codigo_factura'].'" style="text-align:right;">';
    $controles .= '<span style="float:left;"><input type="checkbox" class="documento_verificado" /> Este documento esta validado </span>';
    $controles .= ($factura[0]['flag_anulada'] == '0' ? '<button uniqid="'.$factura[0]['uniqid'].'" class="anular_factura">Anular</button>' : '<button uniqid="'.$factura[0]['uniqid'].'" class="desanular_factura">Desanular</button>');
    $controles .= '&nbsp;|&nbsp;';
    $controles .= '<button uniqid="'.$factura[0]['uniqid'].'" class="eliminar_factura">Eliminar</button>';
    $controles .= '</div>';

    
    echo '<div id="factura_'.$factura[0]['uniqid'].'" style="margin:5px 0;padding:10px;border:3px solid gray;position:relative;" class="'.($factura[0]['flag_anulada'] ? 'factura_anulada' : '').'">';
    echo sprintf('<div>%s</div><div>%s</div>',$datos,$controles);
    echo '</div>';    
    
}
?>