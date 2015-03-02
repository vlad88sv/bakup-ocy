<?php
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
<?php if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia') { 
    $_GET['codigo_agencia'] = _F_usuario_cache('codigo_usuario');
} else { 
?>
<div class="noimprimir">
<h1>Generar reporte de recepciones</h1>
  <form action="" method="get">
      Agencia: <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select> <input type="submit" value="Filtrar" />
  </form>
  <hr />
  <br />
</div>
<?php
}

if (isset($_GET['codigo_agencia']) && is_numeric($_GET['codigo_agencia']))
{
    $ano = '2013';
    $titulo = 'Control de equipos para '.$ano.' - Datos al '.strftime('%A %e de %B');
    echo '<h1>'. $titulo .'</h1>';
    $datos = array();  
    
    $ano_anterior = ($ano-1);
    $mes_anterior = '12';
      
   
    // Luego obtenemos los saldos diarios por cada mes del año
    
    for($mes = 1; $mes < ( date('m') + 1 ); $mes++)
    {
        //$cMes = 'SUM(IF( (fechatiempo_egreso IS NULL OR fechatiempo_egreso > LAST_DAY("'.$ano.'-'.$mes.'-01") ) AND fechatiempo_ingreso <= LAST_DAY("'.$ano.'-'.$mes.'-01"), 1, 0))';
        
        echo '<h1>Mes '.$mes.'</h1>';
        
        unset($datos);
        unset($tipos);
        
                
        // Empezamos por encontrar los saldos del mes anterior   
        $c = 'SELECT LAST_DAY("'.$ano_anterior.'-'.$mes_anterior.'-01") AS fecha, tipo_contenedor, COUNT(*) AS  "cantidad" FROM  `opsal_ordenes` AS t1 WHERE codigo_agencia="'.db_codex($_GET['codigo_agencia']).'" AND (fechatiempo_egreso IS NULL OR fechatiempo_egreso > LAST_DAY("'.$ano_anterior.'-'.$mes_anterior.'-01") ) AND fechatiempo_ingreso <= LAST_DAY("'.$ano_anterior.'-'.$mes_anterior.'-01") GROUP BY (tipo_contenedor)';
        $rIngresosMes = db_consultar($c);
        while ($f = db_fetch($rIngresosMes))
        {
            $datos[$f['fecha']]['INICIO'][$f['tipo_contenedor']] = $f['cantidad'];
            if (!in_array($f['tipo_contenedor'],$tipos)) $tipos[] = $f['tipo_contenedor'];
        }
        
        // Ingreso
        $c = 'SELECT DATE(fechatiempo_ingreso) AS fecha, tipo_contenedor, COUNT(*) AS  "cantidad" FROM `opsal_ordenes` AS t1 WHERE codigo_agencia="'.db_codex($_GET['codigo_agencia']).'" AND DATE(fechatiempo_ingreso) BETWEEN "'.$ano.'-'.$mes.'-01" AND LAST_DAY("'.$ano.'-'.$mes.'-01") GROUP BY fecha, tipo_contenedor ORDER BY fecha';
        $rIngresosMes = db_consultar($c);
        while ($f = db_fetch($rIngresosMes))
        {
            $datos[$f['fecha']]['RECEPCION'][$f['tipo_contenedor']] = $f['cantidad'];
            if (!in_array($f['tipo_contenedor'],$tipos)) $tipos[] = $f['tipo_contenedor'];
        }

        // Egreso
        $c = 'SELECT DATE(fechatiempo_egreso) AS fecha, tipo_contenedor, COUNT(*) AS  "cantidad" FROM `opsal_ordenes` AS t1 WHERE codigo_agencia="'.db_codex($_GET['codigo_agencia']).'" AND estado="fuera" AND DATE(fechatiempo_egreso) BETWEEN "'.$ano.'-'.$mes.'-01" AND LAST_DAY("'.$ano.'-'.$mes.'-01") GROUP BY fecha, tipo_contenedor ORDER BY fecha';
        $rIngresosMes = db_consultar($c);
        while ($f = db_fetch($rIngresosMes))
        {
            $datos[$f['fecha']]['DESPACHO'][$f['tipo_contenedor']] = $f['cantidad'];
            if (!in_array($f['tipo_contenedor'],$tipos)) $tipos[] = $f['tipo_contenedor'];
        }
        
        sort($tipos);
        //print_r($datos);
        //print_r($tipos);
        
        echo '<div class="exportable" rel="'.$titulo.'">';
        echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-centrada">';
        echo '<tr><th colspan="2">Movimiento</th><th>Fecha</th><th>'.join('</th><th>Total</th><th>',$tipos).'</th><th>Total</th></tr>';
        foreach($datos as $fecha => $movimientos)
        {
            foreach($movimientos as $movimiento => $data)
            {
                $datita = '';
                
                foreach($tipos as $tipo)
                {
                    $valor = (isset($data[$tipo]) ? $data[$tipo] : '0');
                    
                    switch ($movimiento)
                    {
                        case 'INICIO':
                            $suma[$tipo] = $valor;
                            break;
                        case 'RECEPCION':
                            $suma[$tipo] = ($suma[$tipo] + $valor);
                            break;
                        case 'DESPACHO':
                            $suma[$tipo] = ($suma[$tipo] - $valor);
                            break;                            
                    }
                    
                    
                    $datita .= ($movimiento == 'INICIO' ? '<td>-</td><td>'.$valor.'</td>' : '<td>'.$valor.'</td><td>'.$suma[$tipo].'</td>');
                }
                
                switch ($movimiento)
                {
                    case 'INICIO':
                        $fmt_movimiento = '<td colspan="2" style="text-align:center;">'.$movimiento.'</td>';
                        break;
                    case 'RECEPCION':
                        $fmt_movimiento = '<td style="text-align:center;">'.$movimiento.'</td><td></td>';
                        break;
                    case 'DESPACHO':
                        $fmt_movimiento = '<td></td><td style="text-align:center;">'.$movimiento.'</td>';
                        break;
                }
                
                echo '<tr>'.$fmt_movimiento.'<td>'.$fecha.'</td>'.$datita.'</tr>';
            }
        }
        echo '</table>';
        echo '</div>';
        
        $mes_anterior = $mes;
        $ano_anterior = $ano;
    }   
} else {
    echo '<p>Seleccione una agencia para comenzar</p>';
}
?>