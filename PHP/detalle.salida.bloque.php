<?php
$codigo_salida = 0;

if (isset($_GET['cs']))
{
    $codigo_salida = $_GET['cs'];
}

if (isset($_POST['ejecutar_salida']))
{
    // Obtengamos un codigo de salida
    unset($DATOS);
    $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $DATOS['fechatiempo'] = mysql_datetime();
    $DATOS['buque'] = $_POST['buque_egreso'];
    $codigo_salida = db_agregar_datos('salida_bloque',$DATOS);
    
    foreach($_POST['codigo_orden'] as $indice => $a_codigo_orden)
    {
        $codigo_orden = $a_codigo_orden[0];
        // detallemos esta orden en su salida
        unset($DATOS);
        $DATOS['codigo_salida'] = $codigo_salida;
        $DATOS['codigo_orden'] = $codigo_orden;
        $DATOS['salida'] = (isset($_POST['salida'][$indice]) ? '1' : '0');
        db_agregar_datos('detalle_salida_bloque',$DATOS);
        
        unset($DATOS);
        if (isset($_POST['salida'][$indice]))
        {
            // Lo va a sacar
            $DATOS = array_intersect_key($_POST,array_flip(array('buque_egreso','observaciones_egreso','fechatiempo_egreso','destino')));
            $DATOS['tipo_salida'] = 'embarque';
            $DATOS['estado'] = 'fuera';
            $DATOS['egresado_por'] = _F_usuario_cache('codigo_usuario');
        } else {
            // Solo fue parte de la operación, marcarlo como sucio
            $DATOS['sucio'] = '1';
        }
        db_actualizar_datos ('opsal_ordenes',$DATOS,'codigo_orden='.$codigo_orden);
        
        // Añadir la desestiba solo si es para ser extraido.
        // Si fue sucio entonces ya lo harán despues.
        if (isset($_POST['salida'][$indice]))
        {
            $codigo_agencia = db_obtener('opsal_ordenes','codigo_agencia','codigo_orden="'.$codigo_orden.'"');
            unset($DATOS);
            $DATOS['codigo_posicion'] = 0;
            $DATOS['nivel'] = 0;
            $DATOS['cobrar_a'] = $codigo_agencia;
            $DATOS['fechatiempo'] = $_POST['fechatiempo_egreso'];
            $DATOS['motivo'] = 'desestiba';
            $DATOS['codigo_orden'] = $codigo_orden;
            $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
            
            db_agregar_datos('opsal_movimientos',$DATOS);
        }
    }
    registrar('Salida de bloque','egreso.bloque');
    echo '<hr /><p class="noimprimir opsal_notificacion">Contenedor egresado exitosamente.</p><hr />';
}
// Mostramos el código de salida definido.

$rsalida = db_consultar('SELECT usuario, fechatiempo, buque FROM salida_bloque LEFT JOIN opsal_usuarios USING(codigo_usuario) WHERE codigo_salida='.$codigo_salida);

if (mysqli_num_rows($rsalida) == 0)
{
    echo '<h1>Despacho en bloque</h1>';
    echo '<p>No existe tal despacho en bloque.</p>';
    return;
}

$salida = mysqli_fetch_assoc($rsalida);

echo '<h1>Despacho en bloque para buque <b>'.$salida['buque'].'</b>, día <b>'.$salida['fechatiempo'].'</b>, creado por <b>'.$salida['usuario'].'</b></h1>';
$c = 'SELECT codigo_contenedor, tipo_contenedor, transportista_egreso, arivu_referencia,arivu_ingreso, DATE(fechatiempo_ingreso) AS "fecha_ingreso", CONCAT(x2,"-",y2,"-",nivel) AS posicion, tara, IF(salida=1, "Si", "No") AS "con_salida" FROM detalle_salida_bloque LEFT JOIN opsal_ordenes USING (codigo_orden) LEFT JOIN opsal_posicion USING (codigo_posicion) WHERE codigo_salida='.$codigo_salida.' ORDER BY y2 ASC, x2 ASC, nivel DESC';
$rdetalleSalida = db_consultar($c);

$nosalida = 1;
echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
while ($detalleSalida = mysqli_fetch_assoc($rdetalleSalida))
{
echo sprintf('<tr><td>'.$nosalida.'</td><td>%s</td><td>%s</td><td>%s KG</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$detalleSalida['codigo_contenedor'],$detalleSalida['tipo_contenedor'],$detalleSalida['tara'],$detalleSalida['fecha_ingreso'],$detalleSalida['arivu_referencia'],$detalleSalida['arivu_ingreso'],$detalleSalida['con_salida'], $detalleSalida['posicion'], ($detalleSalida['con_salida'] == 'Si' ? 'No aplica' : '') );

$nosalida++;
}
echo '<thead>';
echo '<tr><th>No.</th><th>Contenedor</th><th>Tipo</th><th>Tara</th><th>Ingreso</th><th># ARIVU</th><th>ARIVU</th><th>Despachar</th><th>Posicion</th><th>Nueva posición</th></tr>';
echo '</thead>';
echo '</table>';
?>