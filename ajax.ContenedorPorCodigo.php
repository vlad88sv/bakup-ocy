<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

// Detectar si es búsqueda por ubicacion
if (preg_match('/\w-\d-\d/',$_GET['busqueda'],$posicion) !== false) {
    //
}

$codigo_contenedor = strtoupper(preg_replace(array('/[^\w\d]/','/(\d{4}\w{7})/'),array('','$1'),$_GET['busqueda']));

if (preg_match('/\d{4}\w{7}/',$codigo_contenedor) === false)
{
    echo '<p style="color:red;">Parece que se ha equivocado. El dato ingresado no es un número de contenedor.</p>';
    return;
}

$restriccion_agencia = '';

if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia')
{
    $restriccion_agencia = ' AND codigo_agencia = "'.  _F_usuario_cache('codigo_usuario').'"';
}

$c_ordenes = "
SELECT codigo_orden, tipo_salida, eir_ingreso, eir_egreso, chofer_ingreso, cliente_ingreso, transportista_ingreso, transportista_egreso, chofer_ingreso, chofer_egreso, COALESCE(fechatiempo_egreso, 'aún en patio') AS fechatiempo_egreso_2, `arivu_referencia`, (`arivu_ingreso` + INTERVAL 89 DAY) AS 'arivu_vencimiento', DATEDIFF((`arivu_ingreso` + INTERVAL 89 DAY), COALESCE(fechatiempo_egreso,NOW())) AS 'dias_arivu' , DATEDIFF(COALESCE(fechatiempo_egreso,NOW()), `fechatiempo_ingreso`) AS 'dias_ingreso', DATEDIFF(NOW(), `cepa_salida`) AS 'dias_cepa', t4.`usuario` AS 'nombre_agencia', t5.`usuario` AS 'quien_recibio', t6.`usuario` AS 'quien_despacho', t3.`x2` , t3.`y2` , t1.`nivel`, `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , t1.`nivel` , `clase`, `clase_taller` , `tara` , `chasis` ,  `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , `arivu_ingreso` , `observaciones_taller`, `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , `fechatiempo_egreso` , `ingresado_por`, `booking_number`, `booking_number_ingreso`
FROM `opsal_ordenes` AS t1
LEFT JOIN `opsal_tipo_contenedores` AS t2
USING ( tipo_contenedor )
LEFT JOIN `opsal_posicion` AS t3
USING ( codigo_posicion )
LEFT JOIN `opsal_usuarios` AS t4
ON t4.`codigo_usuario` = t1.`codigo_agencia`
LEFT JOIN `opsal_usuarios` AS t5
ON t5.`codigo_usuario` = t1.`ingresado_por`
LEFT JOIN `opsal_usuarios` AS t6
ON t6.`codigo_usuario` = t1.`egresado_por`
WHERE `codigo_contenedor` = '".$codigo_contenedor."' $restriccion_agencia
ORDER BY fechatiempo_ingreso DESC
LIMIT 1
";

$r_ordenes = db_consultar($c_ordenes);

if (mysqli_num_rows($r_ordenes) == 1)
{
    $f = mysqli_fetch_assoc($r_ordenes);
    mysqli_free_result($r_ordenes);
    
    echo '<h1>Mostrando datos según última recepción del contenedor <b>'.$f['codigo_contenedor'].'</b></h1>';
    echo '<hr />';
    echo 'Compartir estos datos: <input readonly="readonly" onclick="this.focus();this.select();" style="width:400px;" value="'.PROY_URL.'historial.html?ID='.$f['codigo_contenedor'].'">';
    echo '<hr />';

    $cepa_salida = ($f['cepa_salida'] != '0000-00-00 00:00:00' ? $f['cepa_salida'].' ['.$f['dias_cepa'].' días desde salida de CEPA]' : 'Sin datos');
    $arivu = ($f['dias_arivu'] != '0000-00-00' ? $f['arivu_ingreso'].' al '. $f['arivu_vencimiento'] .' [faltan '.$f['dias_arivu'].' días]' : 'Sin datos');
    
    echo '<div style="border: 1px solid grey;border-radius:10px; padding:15px;margin:10px;">';

    echo '<div style="text-align:right;"><form action="contenedor.editar.html" method="GET"><input type="hidden" name="ID_orden" value="'.$f['codigo_orden'].'" /><input type="submit" value="Editar esta recepción" /></form></div>';
    
    echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    echo '<tr><th style="width:200px;text-align:right;">Cód. interno</td><td>'.$f['codigo_orden'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Digitó</th><td>'.ucfirst(strtolower($f['quien_recibio'])).'</td></tr>';
    echo '<tr><th style="text-align:right;">Chequeó</th><td>'.ucfirst(strtolower($f['cheque_ingreso'])).'</td></tr>';
    echo '<tr><th style="text-align:right;">Naviera</td><td>'.$f['nombre_agencia'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Cliente ingreso</th><td>'.$f['cliente_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Transportista ingreso</th><td>'.$f['transportista_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Última posición</th><td>'.$f['x2'].'-'.$f['y2'].'-'.$f['nivel'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Tipo</th><td>'.$f['nombre'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Clase</th><td>'.$f['clase'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Clase despues de M&R</th><td>'.$f['clase_taller'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Observaciones M&R</th><td>'.$f['observaciones_taller'].'</td></tr>';
    echo '<tr><th style="text-align:right;">EIR</th><td>'.$f['eir_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Chofer</th><td>'.$f['chofer_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Transportista</th><td>'.$f['transportista_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Booking N.</th><td>'.$f['booking_number_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Buque</th><td>'.$f['buque_ingreso'].'</td></tr>';
    echo '<tr><th style="text-align:right;">Recepción</th><td>'.$f['fechatiempo_ingreso'].' ['.$f['dias_ingreso'].' días en patio]</td></tr>';
    echo '<tr><th style="text-align:right;">CEPA salida</th><td>'.$cepa_salida.'</td></tr>';
    echo '<tr><th style="text-align:right;">ARIVU</th><td>'.$arivu.'</td></tr>';
    echo '<tr><th style="text-align:right;">Observaciones</th><td>'.( $f['observaciones_ingreso'] ? $f['observaciones_ingreso'] : '[ninguna ingresada]' ).'</td></tr>';        
    echo '</table>';
    
    echo '<br />';
    
    echo '<div class="opsal_burbuja">';
    echo '<h3>Despacho</h3>';
    if ($f['fechatiempo_egreso'] != '')
    {
        
        echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
        echo '<tr><th style="text-align:right;">Digitó</th><td>'.$f['quien_despacho'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Chequeó</th><td>'.ucfirst(strtolower($f['cheque_ingreso'])).'</td></tr>';
        echo '<tr><th style="width:200px;text-align:right;">Fecha</th><td>'.$f['fechatiempo_egreso_2'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Tipo despacho</th><td>'.$f['tipo_salida'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Chofer</th><td>'.$f['chofer_egreso'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Transportista</th><td>'.$f['transportista_egreso'].'</td></tr>';
        echo '<tr><th style="text-align:right;">EIR</th><td>'.$f['eir_egreso'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Booking N.</th><td>'.$f['booking_number'].'</td></tr>';
        echo '<tr><th style="text-align:right;">Buque</th><td>'.$f['buque_egreso'].'</td></tr>';
        echo '</table>';
        
        echo '<div style="text-align:right;"><button class="bq_eliminar_despacho" rel="'.$f['codigo_orden'].'" style="border:1px dotted red; background-color:black;color:red;padding: 5px;margin-top:2px;">Eliminar despacho</button></div>';
        
    } else {
        echo '<p>No tiene despacho registrado [aún se encuentra en patio]</p>';
    }
    echo '</div>';
    
    echo '<br />';
    
    echo '<div class="opsal_burbuja">';            
    echo '<h2>Movimientos durante esta recepción</h2>';
    
    $c_movimientos = "SELECT ID_movimiento, t1.codigo_posicion, x2, y2, t1.nivel, t2.usuario, t4.usuario AS 'cobrado_a', cheque, fechatiempo, motivo, flag_traslado, observacion FROM opsal_movimientos AS t1 LEFT JOIN opsal_usuarios AS t2 USING(codigo_usuario) LEFT JOIN opsal_posicion AS t3 USING(codigo_posicion) LEFT JOIN opsal_usuarios AS t4 ON t1.cobrar_a=t4.codigo_usuario WHERE t1.codigo_orden='".$f['codigo_orden']."' ORDER BY ID_movimiento ASC";
    $r_movimientos = db_consultar($c_movimientos);
    
    $pos_anterior = '[RECEPCIÓN]';
    
    if (mysqli_num_rows($r_movimientos) > 0)
    {
        echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
        while ($g = mysqli_fetch_assoc($r_movimientos))
        {
            $pos_actual = ($g['codigo_posicion'] == '0' ? '[DESPACHO]' : $g['x2'].'-'.$g['y2'].'-'.$g['nivel']);
            echo sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $g['ID_movimiento'], $g['usuario'], $g['cheque'], $pos_anterior, $pos_actual,$g['fechatiempo'],$g['cobrado_a'],$g['motivo'],( $g['flag_traslado'] ? 'Si' : 'No'), $g['observacion']);
            $pos_anterior = $pos_actual;
        }
        echo '<thead>';
        echo '<tr><th>ID</th><th>Digitador</th><th>Cheque</th><th>Posición anterior</th><th>Posición nueva</th><th>Fecha</th><th>Cobrado a</th><th>Categoría</th><th>DM</th><th>Observacion</th></tr>';
        echo '</thead>';
        echo '</table>';
    } else {
        echo '<p>Aún no hay remociones para este contenedor.</p>';
    }
    echo '</div>';
    
    echo '</div>'; // contenedor
    
    echo '<br /><hr />';
    if (S_iniciado() && _F_usuario_cache('nivel') != 'agencia') {
        echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' class="bq_usar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Utilizar</button>';
        echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_remover_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Remoción</button>';
        echo '<button '.($f['estado'] == 'fuera' ? 'disabled="disabled"' : '').' id="bq_despachar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">Despacho</button>';
        echo '<button rel="'.$f['codigo_contenedor'].'" id="ver_historial">Historial</button>';
        echo '&nbsp;<a target="_blank" href="/EDI.php?EDI='.$f['codigo_orden'].'">Ver EDI</button>';
     }
    
} else {
    echo '<p>No se encontró el contenedor búscado (<b>'.$_GET['busqueda'].'</b>)</p>';
}
?>