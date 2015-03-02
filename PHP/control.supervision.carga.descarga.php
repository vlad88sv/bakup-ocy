<?php
function control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, $tipo, $titulo)
{
    $c = '
    SELECT
    t1.`codigo_agencia`,
    t1.`fecha_ingreso`,
    t1.`ID_buque`,
    t1.`ID_carga_descarga`,
    t1.`ingresado_por`,
    t1.`supervisor`,
    t1.`marchamador`,
    t1.`inicio_operacion`,
    t1.`final_operacion`,
    TIMESTAMPDIFF(HOUR, t1.`inicio_operacion`,t1.`final_operacion`) AS totalhrs,
    t2.`usuario` AS "nombre_operador",
    t3.`usuario` AS "nombre_agencia"
    FROM `opsal`.`opsal_carga_descarga` AS t1
    LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
    LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
    WHERE ID_carga_descarga="'.$ID_carga_descarga.'"
    ';
    
    $resultado = db_consultar($c);
    
    $f = db_fetch($resultado);
    
    $costo_por_hora_supervision = db_obtener('opsal_tarifas',$tipo, 'codigo_usuario="'.$f['codigo_agencia'].'"');
    
    //echo nl2br(print_r($f,true));
    
    echo '<div class="exportable" rel="'.$titulo.'">';
    echo '<br />';
    echo '<p style="color:black;font-size:1.2em;text-align:center;font-weight:bold;">'.$f['nombre_agencia'].' - '.$f['ID_buque'].'</p>';
    echo '<p style="color:black;font-size:1.1em;text-align:center;">'.$titulo.'</p>';
    echo '<br /><br />';
    echo '<table><tr>';
    echo '<td>';
        echo '<table id="abc" style="width:400px;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
        echo '<tr><th colspan="2">Inicio de Ops</th><th colspan="2">Fin de Ops</th></tr>';
        echo '<tr><th>Fecha</th><th>Hora</th><th>Fecha</th><th>Hora</th></tr>';
        echo '<tr><td>'.date('d-M-y',strtotime($f['inicio_operacion'])).'</td><td>'.date('H:i',strtotime($f['inicio_operacion'])).'</td><td>'.date('d-M-y',strtotime($f['final_operacion'])).'</td><td>'.date('H:i',strtotime($f['final_operacion'])).'</td></tr>';
        echo '</table>';
    echo '</td>';
    echo '<td style="vertical-align:middle;">&nbsp;=&nbsp;</td>';
    echo '<td>';
        echo '<table id="sbc" style="width:600px;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
        echo '<tr><th>Total horas</th><th>Costo por hora</th><th>Subtotal</th><th>IVA 13%</th><th>Total</th></tr>';
        echo '<tr><td>'.$f['totalhrs'].'h</td><td>$'.$costo_por_hora_supervision.'</td><td>'.dinero($f['totalhrs']*$costo_por_hora_supervision).'</td><td>'.dinero(($f['totalhrs']*$costo_por_hora_supervision)*0.13).'</td><td>'.dinero(($f['totalhrs']*$costo_por_hora_supervision)*1.13).'</td></tr>';
        echo '</table>';
    echo '</td>';
    echo '</tr></table>';
    
    //***** DETALLE ******//
    $c = 'SELECT `categoria`, `ID_carga_descarga`, SUM(`cantidad`) AS sum_cantidad, `tipo_contenedor`, `patio` FROM `detalle_carga_descarga` WHERE ID_carga_descarga='.$ID_carga_descarga. ' GROUP BY `categoria`,`tipo_contenedor`';
    $r = db_consultar($c);
    
    while ($detalle = db_fetch($r))
    {
        $detalles[$detalle['categoria']][] = $detalle['sum_cantidad'].' x '.$detalle['tipo_contenedor'];
    }
    
    echo '<p style="color:black;font-size:1.1em;text-align:center;margin-top:10px;">Detalle</p>';
    
    echo '<table style="width:100%;table-layout:fixed;" class="opsal_tabla_borde_oscuro tabla-estandar tabla-centrada">';
    echo '<tr><th colspan="2">Import</th><th colspan="2">Export</th></tr>';
    echo '<tr><th>Vacios</th><th>Llenos</th><th>Vacios</th><th>Llenos</th></tr>';
    echo '<tr><td><div>'.@join('</div><div>',@$detalles['importacion_vacios']).'</div></td><td><div>'.@join('</div><div>',@$detalles['importacion_llenos']).'</div></td><td><div>'.@join('</div><div>',@$detalles['exportacion_vacios']).'</div></td><td><div>'.@join('</div><div>',@$detalles['exportacion_llenos']).'</div></td></tr>';
    echo '<tr>';
    
    echo '</tr>';
    echo '</table>';
    //***** DETALLE ******//
    
    echo '</div>';
    
    return array( 'datos' => $f, 'detalles' => $detalles, 'total' => ($f['totalhrs']*$costo_por_hora_supervision) );
}


echo '<h1>Detalle de supervisión de operaciones de carga/descarga</h1>';

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar']))
{
    db_consultar('DELETE FROM opsal_carga_descarga WHERE ID_carga_descarga="'.$_GET['eliminar'].'" LIMIT 1');
    db_consultar('DELETE FROM detalle_carga_descarga WHERE ID_carga_descarga="'.$_GET['eliminar'].'"');
}

//**** impresión ****//
if (isset($_GET['facturar']) && is_numeric($_GET['facturar']) && (_F_usuario_cache('nivel') == 'jefatura'))
{
    
    echo '<p class="noimprimir">';
    echo '<a href="/control.supervision.carga.descarga.html?facturar='.$_GET['facturar'].'&crear_factura">Crear factura para esta supervisón de carga y descarga con su respectivo reporte de marchamos</a>';
    echo '</p>';
    
    
    $ID_carga_descarga = db_codex($_GET['facturar']);
    
    ob_start();
    $supervision = control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, 'p_supervision_carga_descarga','Supervisión de Operaciones de carga y descarga');
    $total_supervision = $supervision['total'];
    $anexo_supervision = ob_get_clean();
    
    echo '<br /><hr /><br />';
    
    ob_start();
    $marchamos = control_supervision_carga_descarga__crear_impresion($ID_carga_descarga, 'p_revision_marchamos','Revisión de marchamos');
    $total_marchamos = $marchamos['total'];
    $anexo_marchamos = ob_get_clean();

    
    if (isset($_GET['crear_factura']))
    {
        $codigo_agencia = db_obtener('opsal_carga_descarga','codigo_agencia','ID_carga_descarga="'.$ID_carga_descarga .'"');
        
        
        
        $datos = array(
            'detalle' => 'Servicio de supervisión de operaciones de carga y descarga',
            'sin_iva' => $total_supervision,
            'iva' => '0',
            'total' => ($total_supervision * __IVA__ ),
            'cantidad' => '1',
            'anexo' => $anexo_supervision,
            'periodo_inicio' => $supervision['inicio_operacion'],
            'periodo_final' => $supervision['final_operacion'],
            'grupo' => 'Sup. de OPS'
            );
        
        $UNIQID = uniqid('',true);
        CrearFactura($UNIQID, $codigo_agencia, 'supervision', $datos);

        $datos = array(
            'detalle' => 'Servicio de revisión de marchamos',
            'sin_iva' => $total_marchamos,
            'iva' => '0',
            'total' => ($total_marchamos * __IVA__ ),
            'cantidad' => '1',
            'anexo' => $anexo_marchamos,
            'periodo_inicio' => $marchamos['inicio_operacion'],
            'periodo_final' => $marchamos['final_operacion'],
            'grupo' => 'Rev. de marchamos'
            );
        
        $UNIQID = uniqid('',true);
        CrearFactura($UNIQID, $codigo_agencia, 'marchamos', $datos);

        header('Location: /control.facturas.html');
    } else {
        echo $anexo_supervision;
        echo $anexo_marchamos;
    }
    
    return;
}
//******************//

if (empty($_GET['fecha_inicio']) || empty($_GET['fecha_final']))
{
  $fecha_inicio = $fecha_final = mysql_date();
} else {
  $fecha_inicio = $_GET['fecha_inicio'];
  $fecha_final = $_GET['fecha_final'];
}

$c = '
SELECT
t1.`fecha_ingreso`,
t1.`ID_buque`,
t1.`ID_carga_descarga`,
t1.`ingresado_por`,
t1.`supervisor`,
t1.`marchamador`,
DATE_FORMAT(t1.`inicio_operacion`,"%e-%b-%y %H:%i") AS "inicio_operacion_fmt",
DATE_FORMAT(t1.`final_operacion`,"%e-%b-%y %H:%i") AS "final_operacion_fmt",
t2.`usuario` AS "nombre_operador",
t3.`usuario` AS "nombre_agencia"
FROM `opsal`.`opsal_carga_descarga` AS t1
LEFT JOIN `opsal`.`opsal_usuarios` AS t2 ON (t1.`ingresado_por` = t2.`codigo_usuario`)
LEFT JOIN `opsal`.`opsal_usuarios` AS t3 ON (t1.`codigo_agencia` = t3.`codigo_usuario`)
ORDER BY t1.`fecha_ingreso` DESC
';
$resultado = db_consultar($c);


$ultimos_ingresos = '';

if (mysqli_num_rows($resultado) == 0)
{
    $ultimos_ingresos .= '<p>No se encontraron ingresos</p>';
} else {
    $ultimos_ingresos .= '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
    while ($f = mysqli_fetch_assoc($resultado))
    {
        $ultimos_ingresos .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',$f['nombre_agencia'],$f['ID_buque'],$f['nombre_operador'],ellipsis($f['supervisor'],20),ellipsis($f['marchamador'],20),$f['inicio_operacion_fmt'],$f['final_operacion_fmt'],'<a href="/control.supervision.carga.descarga.html?facturar='.$f['ID_carga_descarga'].'">Facturar</a>','<a class="eliminar_supervision_carga_descarga" href="/control.supervision.carga.descarga.html?eliminar='.$f['ID_carga_descarga'].'">Eliminar</a>');
    }
    $ultimos_ingresos .= '<thead><tr><th>Naviera</th><th>Buque</th><th>Ingresó</th><th>Supervisó</th><th>Marchamó</th><th>Inicio operación</th><th>Final operación</th><th>Facturar</th><th>Eliminar</th></tr></thead>';
    $ultimos_ingresos .= '</table>';
}

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$agencias = array('' => 'todas las agencias');
$options_agencia = '<option selected="selected" value="">Mostrar todas</option>';
if (mysqli_num_rows($r) > 0)
{
  while ($registro = mysqli_fetch_assoc($r))
  {
    $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    $agencias[$registro['codigo_usuario']] = $registro['usuario'];
  }
}
?>
<div class="noimprimir">
  <form action="" method="GET">
      Con fecha de inicio de operación entre <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> y <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="Filtrar" />
  </form>
  <hr />
  <br />
</div>
<div class="opsal_burbuja">
    <?php echo $ultimos_ingresos; ?>
</div>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
        $(".eliminar_supervision_carga_descarga").click(function(){
            return confirm('¿Esta seguro que desea eliminar esa supervisión de carga y descarga?');
        });
    });
</script>