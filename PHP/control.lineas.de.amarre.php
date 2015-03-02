<?php
if (isset($_POST['facturar']))
{
    $datos = $_POST;
    unset($datos['facturar']);
    
    $datos['modo_facturacion'] = 'lineas';
    $datos['tipo_salida'] = 'otra';
    $datos['sin_iva'] = numero2($datos['sin_iva']);
    $datos['iva'] = numero2( ( $datos['sin_iva']  * 1.13) - $datos['sin_iva'] );
    $datos['total'] = numero2( $datos['sin_iva'] * 1.13 );
    $datos['grupo'] = ucfirst($_POST['tipo_operacion']).' líneas de amarre '.$datos['buque'];
    
    $UNIQID = uniqid('',true);
    CrearFactura($UNIQID, $_POST['codigo_agencia'], 'fact_lineas', $datos);
    
    echo '<p>Documento creado exitosamente.</p>';
    return;
}

if (isset($_GET['prefacturar']))
{
    $c = 'SELECT t1.codigo_agencia, t4.usuario AS "agencia", t5.usuario AS "agencia_tarifas", `dia_operacion`, `tipo_operacion`, `ID_buque`, `num_lineas`, `concepto`, `modificador`, `precio_grabado`, `duracion` FROM `opsal_lineas_amarre` AS t1 LEFT JOIN `opsal_lineas_amarre_detalle` USING(ID_linea_amarre) LEFT JOIN `la_conceptos` USING(codigo_concepto) LEFT JOIN `opsal`.`opsal_usuarios` AS t4 ON (t1.`codigo_agencia` = t4.`codigo_usuario`) LEFT JOIN `opsal`.`opsal_usuarios` AS t5 ON (t1.`tarifas_de` = t5.`codigo_usuario`) WHERE t1.`ID_buque`="'.db_codex($_GET['prefacturar']).'" ORDER BY tipo_operacion DESC, dia_operacion ASC, codigo_concepto ASC';
    $r = db_consultar($c);
    
    while ($f = db_fetch($r))
    {
        $operaciones[$f['tipo_operacion']][] = $f;
    }
    
    
    foreach ($operaciones as $operacion)
    {
        $subtotal = 0;
        $min_dia_operacion = 0;
        $max_dia_operacion = 0;
        
        echo '<h1>' . ucfirst($operacion[0]['tipo_operacion']) .'</h1>';
        echo '<div class="opsal_burbuja exportable" style="overflow-x:auto;" rel="Lineas de amarre">';
        echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
        echo '<tr><th>Día de operación</th><th>Concepto</th><th>Tiempo</th><th>Duración</th><th>Total</th></tr>';
        
        foreach($operacion as $evento)
        {
            if ($min_dia_operacion == 0 || (strtotime($evento['dia_operacion']) < strtotime($min_dia_operacion)) )
                $min_dia_operacion = $evento['dia_operacion'];
                
            if ($max_dia_operacion == 0 || (strtotime($evento['dia_operacion']) > strtotime($max_dia_operacion)) )
                $max_dia_operacion = $evento['dia_operacion'];                
            
            $subtotal += $evento['precio_grabado'];
            echo '<tr><td>'.$evento['dia_operacion'].'</td><td>'.$evento['concepto'].'</td><td>'.$evento['modificador'].'</td><td>'.$evento['duracion'].'</td><td>'.dinero($evento['precio_grabado']).'</td></tr>';
        }
        echo '<tr style="font-weight:bold;"><td colspan="4" style="text-align:right;">Total:</td><td>'.dinero($subtotal).'</td></tr>';
        echo '</table>';
        echo '</div>';
        
        $periodo = ($max_dia_operacion == $min_dia_operacion ? 'en ' . $max_dia_operacion : 'del '.$min_dia_operacion . ' al ' . $max_dia_operacion);
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="periodo_inicio" value="'.$min_dia_operacion.'" />';
        echo '<input type="hidden" name="periodo_final" value="'.$max_dia_operacion.'" />';
        echo '<input type="hidden" name="sin_iva" value="'.$subtotal.'" />';
        echo '<input type="hidden" name="buque" value="'.$operacion[0]['ID_buque'].'" />';
        echo '<input type="hidden" name="tipo_operacion" value="'.$operacion[0]['tipo_operacion'].'" />';
        echo '<input type="hidden" name="codigo_agencia" value="'.$operacion[0]['codigo_agencia'].'" />';
        echo '<input type="text" name="detalle" style="width:700px;" value="Servicio de '.$operacion[0]['agencia'].' de '.$operacion[0]['tipo_operacion'].' de líneas de amarre de '.$operacion[0]['agencia_tarifas'].' '.$periodo.' para buque '.$operacion[0]['ID_buque'].'" />&nbsp;';
        echo '<input type="submit" name="facturar" value="Facturar" />&nbsp;';
        echo 'por '.dinero($subtotal);
        echo '</form>';
        echo '<hr />';
    }
    
    
    return;
}

/********DETALLES**/

$c = 'SELECT
MIN(t1.`dia_operacion`) AS min_dia_operacion,
MAX(t1.`dia_operacion`) AS max_dia_operacion,
t1.`ID_buque`,
t1.`ID_linea_amarre`,
t1.`ingresado_por`,
t1.num_lineas,
t1.tipo_operacion,
t1.modificador,
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_lineas_amarre` AS t1
LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
GROUP BY ID_buque
';
$resultado = db_consultar($c);

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $accion = '<a href="/control.lineas.de.amarre.html?prefacturar='.$f['ID_buque'].'">Detalles</a>';
        $accion .= '&nbsp;|&nbsp;<a href="/control.lineas.de.amarre.html?eliminar='.$f['ID_buque'].'">Eliminar</a>';
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $f['ID_buque'],$f['nombre_operador'],$f['nombre_agencia'],'de ' . $f['min_dia_operacion'] . ' al '. $f['max_dia_operacion'],$f['num_lineas'], $accion);
    }
    $ultimos_ingresos .= '<thead><tr><th>ID buque</th><th>Ingresó</th><th>Agencia</th><th>Fecha</th><th>No. líneas</th><th>Acción</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}
?>
<h2>Ingresos de lineas de amarre</h2>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
