<?php
function gpie($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.PieChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}

function gCol($resultado, $titulo, $divID, $columna, $fila)
{
    
    $buffer = "
    var data = google.visualization.arrayToDataTable([
      ['$columna', '$fila'],
    ";
    
    while ($f = mysqli_fetch_array($resultado))
    {
        $tbuffer[] = "['".$f[0]."', ".$f[1]."]";
    }
    
    $buffer .= join(', ', $tbuffer);
    
    $buffer .= "]);";
  
    $buffer .= "new google.visualization.ColumnChart(document.getElementById('$divID')).draw(data, {title:'$titulo'});";
    
    return $buffer;
}

function numero($numero)
{
    return number_format($numero,2,'.',',');
}

function numero2($numero)
{
    return number_format($numero,2,'.','');
}

function dinero($numero)
{
    return '$'.numero($numero,2,'.',',');
}

function CrearFactura($uniqid, $codigo_agencia, $categoria, $datos)
{
    
    $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $DATOS['codigo_agencia'] = $codigo_agencia;
    $DATOS['categoria'] = $categoria;
    $DATOS['fecha_creada'] = mysql_datetime();
    $DATOS['uniqid'] = $uniqid;
    $DATOS['cantidad'] = $datos['cantidad'];
    $DATOS['cu'] = $datos['cu'];
    $DATOS['servicio'] = $datos['detalle'];
    $DATOS['total_sin_iva'] = $datos['sin_iva'];
    $DATOS['iva'] = $datos['iva'];
    $DATOS['total'] = $datos['total'];
    $DATOS['periodo_inicio'] = $datos['periodo_inicio'];
    $DATOS['periodo_final'] = $datos['periodo_final'];
    $DATOS['modo_facturacion'] = $datos['modo_facturacion'];
    $DATOS['tipo_salida'] = $datos['tipo_salida'];
    $DATOS['grupo'] = $datos['grupo'];
    
    $codigo_factura = db_agregar_datos('facturas',$DATOS);
    
    // Guardamos el anexo
    unset($DATOS);
    $DATOS['codigo_factura'] = $codigo_factura;
    $DATOS['anexo'] = $datos['anexo'];
    db_agregar_datos('facturas_anexos',$DATOS);
    
    return $codigo_factura;
}

function FacturarPeriodo(array $op)
{
    $anexo = '';
    $cuadro = array();
    
    $contenedores = array();
    
    $periodo_inicio = $op['periodo_inicio'];
    $periodo_final = $op['periodo_final'];
    
    
    $periodo = ($periodo_inicio == $periodo_final ? date('Y-M-d',strtotime($periodo_inicio))  : 'de ' . date('Y-M-d',strtotime($periodo_inicio)) . ' a ' . date('Y-M-d',strtotime($periodo_final)));
    
    $codigo_agencia = $op['codigo_agencia'];
    $tipo_salida = $op['tipo_salida'];
    
    $flags = @$op['flag'];
    $quirks = @$op['quirks'];
    
    $where = $where_movimientos = '';
    
    // Info
    $agencia = db_obtener('opsal_usuarios','usuario',"codigo_usuario='$codigo_agencia'");
    
    if ( isset($_GET['codigo_agencia_2']) && is_numeric($_GET['codigo_agencia_2']) )
    {
        $agencia_2 = db_obtener('opsal_usuarios','usuario',"codigo_usuario='".$_GET['codigo_agencia_2']."'");
    }
    
    if ($op['modo_facturacion'] == 'contenedores' && $op['tipo_cobro'] == 'completo')
    {
        $inicio_cobro = 'DATE(`fechatiempo_ingreso`)';
        $final_cobro = 'DATE( COALESCE( `fechatiempo_egreso`, NOW()) )';
    } else {
        $inicio_cobro = 'GREATEST (DATE(`fechatiempo_ingreso`), "'.$periodo_inicio.'")';
        $final_cobro = 'LEAST ( DATE(COALESCE( `fechatiempo_egreso`, NOW())), "'.$periodo_final.'" ) ';
    }
    
    // TRADUCTOR
    
    $grupo = ''; // Este grupo define la asociación para el estado de resultados
    
    if ($op['modo_facturacion'] == 'contenedores') {
        switch($op['tipo_salida'])
        {
            // Caso 1 - por despacho terrestre
            case 'terrestre':
                $grupo = 'DESPACHO TERRESTRE';
                
                $where = 'AND t1.estado="fuera" AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="terrestre" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                $servicio_almacenaje = 'Almacenaje de contenedores vacios ' . $agencia . ', despachados vía terrestre durante el periodo ' .  $periodo;
                $servicio_movimiento = 'Movimientos de contenedores vacios ' . $agencia . ', despachados vía terrestre durante el periodo ' .  $periodo;
                break;

            // Caso 1.2 - por despacho terrestre
            case 'terrestre2':
                $grupo = 'DESPACHO TERRESTRE';
                
                $where = 'AND t1.estado="fuera" AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="terrestre" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                $where_movimientos = 'AND DATE(t0.fechatiempo) >= "'.$periodo_inicio.'"';
                $servicio_almacenaje = 'Almacenaje de contenedores vacios ' . $agencia . ', despachados vía terrestre durante el periodo ' .  $periodo;
                $servicio_movimiento = 'Movimientos de contenedores vacios ' . $agencia . ', despachados vía terrestre durante el periodo ' .  $periodo;
                break;
            
            // Caso 2 - por despacho búque
            case 'embarque':
                $grupo = $op['buque'];
                
                // Encontramos el periodo de este buque
                $periodo_buque = db_fetch(db_consultar('SELECT MIN(fechatiempo_egreso) AS fmin_SQL, MAX(fechatiempo_egreso) AS fmax_SQL, DATE_FORMAT(MIN(fechatiempo_egreso),"%e.%b.%y") AS fmin, DATE_FORMAT(MAX(fechatiempo_egreso),"%e.%b.%y") AS fmax FROM opsal_ordenes WHERE buque_egreso="'.db_codex($op['buque']).'" GROUP BY buque_egreso'));
                
                if ($periodo_buque['fmin'] == $periodo_buque['fmax'])
                    $periodo = $periodo_buque['fmin'];
                else
                    $periodo = 'de '.$periodo_buque['fmin'] .' a '. $periodo_buque['fmax'];
                
                $periodo_inicio = $periodo_buque['fmin_SQL'];
                $periodo_final = $periodo_buque['fmax_SQL'];
    
                $where = 'AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="embarque" AND t1.estado="fuera" AND t1.buque_egreso="'.$op['buque'].'"';
                $servicio_almacenaje = 'Almacenaje de contenedores vacios '.$agencia.' embarcados en el buque '.$op['buque'].', '.$periodo;
                $servicio_movimiento = 'Movimientos de contenedores vacios '.$agencia.' embarcados en el buque '.$op['buque'].', '.$periodo;
                break;
            
            // Caso 3 - por estadía
            case 'patio':
            case 'estadia':
                $grupo = 'ESTADIA';
                $op['modo_facturacion'] = 'almacenaje';
                $where = 'AND codigo_agencia="'.$codigo_agencia.'" AND DATE(fechatiempo_ingreso) <= "'.$periodo_final.'" AND (t1.fechatiempo_egreso IS NULL || DATE(t1.fechatiempo_egreso) > "'.$periodo_final.'")';
                $op['tipo_cobro'] = 'periodo';
                $servicio_almacenaje = 'Estadía de contenedores no despachados '. $agencia .'';
                $servicio_movimiento = 'Estibas de contenedores no despachados '. $agencia .'';
                break;

            // Caso 4 - solo estibas de no despachados estilo APL
            case 'estibas':
                $grupo = 'INGRESOS';
                $op['modo_facturacion'] = 'movimientos';
                $where = 'AND codigo_agencia="'.$codigo_agencia.'" AND DATE(fechatiempo_ingreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND (t1.fechatiempo_egreso IS NULL || DATE(t1.fechatiempo_egreso) > "'.$periodo_final.'")';
                $where_movimientos = 'AND t0.motivo="estiba"';
                $op['tipo_cobro'] = 'periodo';
                $servicio_almacenaje = 'Estadía de contenedores vacios '. $agencia .', no despachados';
                $servicio_movimiento = 'Estibas de contenedores vacios '. $agencia .', no despachados';
                break;
            
            // Caso 4 - por embarque primitivo (no por buque sino que por periodo)
            case 'embarque_primitivo':
                $grupo = "EMBARQUE";
                $where = 'AND t1.estado="fuera"  AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="embarque" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                $where_movimientos = 'AND DATE(t0.fechatiempo) >= "'.$periodo_inicio.'"';
                $op['tipo_cobro'] = 'completo';
                $servicio_almacenacaje = 'Almacenamiento de contenedores, despachados vía embarque';
                $servicio_movimiento = 'Movimientos de contenedores, despachados vía embarque';
                break;
            
            // Caso 5 - remociones
            case 'remociones':
                $grupo = "REMOCIONES";
                $op['modo_facturacion'] = 'remociones';
                $where = 'AND t1.estado="fuera" AND t0.motivo="remocion" AND DATE(t1.fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                if (isset($_GET['codigo_agencia_2']) && is_numeric($_GET['codigo_agencia_2']))
                {
                    $where_movimientos = 'AND t1.codigo_agencia="'.db_codex($_GET['codigo_agencia_2']).'"';
                }
                $servicio_movimiento = 'Remociones de contenedores vacios despachados terrestre o embarcados, del cliente '.$agencia_2.' durante el periodo '. $periodo;
                break; 
            
            // Caso 5.1 - remociones via embarque
            case 'remociones_embarque':
                $grupo = "REMOCIONES";
                $op['modo_facturacion'] = 'remociones';
                $where = 'AND t1.tipo_salida="embarque" AND t1.estado="fuera" AND t0.motivo="remocion" AND DATE(t1.fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                if (isset($_GET['codigo_agencia_2']) && is_numeric($_GET['codigo_agencia_2']))
                {
                    $where_movimientos = 'AND t1.codigo_agencia="'.db_codex($_GET['codigo_agencia_2']).'"';
                }
                $servicio_movimiento = 'Remociones de contenedores vacios despachados embarque, del cliente '.$agencia_2.' durante el periodo '. $periodo;
                break; 
            
            // Caso 5.2 - remociones via terrestre
            case 'remociones_terrestre':
                $grupo = "REMOCIONES";
                $op['modo_facturacion'] = 'remociones';
                $where = 'AND t1.tipo_salida="terrestre" AND t1.estado="fuera" AND t0.motivo="remocion" AND DATE(t1.fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                if (isset($_GET['codigo_agencia_2']) && is_numeric($_GET['codigo_agencia_2']))
                {
                    $where_movimientos = 'AND t1.codigo_agencia="'.db_codex($_GET['codigo_agencia_2']).'"';
                }
                $servicio_movimiento = 'Remociones de contenedores vacios despachados terrestre, del cliente '.$agencia_2.' durante el periodo '. $periodo;
                break; 
            
            // Caso 5 - remociones
            case 'remociones_simples':
                $grupo = "REMOCIONES";
                $op['modo_facturacion'] = 'remociones';
                $where = 'AND t1.estado="dentro" AND t0.motivo="remocion" AND DATE(t0.fechatiempo) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                if (isset($_GET['codigo_agencia_2']) && is_numeric($_GET['codigo_agencia_2']))
                {
                    $where_movimientos = 'AND t1.codigo_agencia="'.db_codex($_GET['codigo_agencia_2']).'"';
                }
                $servicio_movimiento = 'Remociones de contenedores vacios, del cliente '.$agencia_2.' durante el periodo '. $periodo;
                break;
            
            // Case 6 - DT - doble transferencia
            case 'dt':
                $grupo = "DT";
                $op['modo_facturacion'] = 'dt';
                $where = 'AND t1.estado="fuera"  AND codigo_agencia="'.$codigo_agencia.'" AND t1.tipo_salida="embarque" AND t1.buque_ingreso <> "" AND t1.cliente_ingreso = "" AND t1.fechatiempo_egreso IS NOT NULL AND DATE(fechatiempo_egreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                $servicio_dt = 'Doble transferencia de contenedores '. $agencia . ', periodo ' . $periodo;
                $servicio_movimiento = 'Doble transferencia de contenedores '. $agencia . ', periodo ' . $periodo;
                break;
            
            // Caso 7 - doble movimientos - con descuento
            case 'dm':
                $grupo = 'Doble Movimientos';
                $op['modo_facturacion'] = 'movimientos';
                $op['flag_no_detalle_estibas'] = true;
                $op['flag_no_detalle_desestibas'] = true;
                $op['flag_no_detalle_remociones'] = true;
                $where = 'AND t0.motivo="remocion" AND t0.flag_traslado=1 AND DATE(t0.fechatiempo) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'"';
                $servicio_movimiento = 'Doble movimientos de contenedores del cliente '.$agencia.' durante el periodo '. $periodo;
                break; 

        }
    }
        
    // ALMACENAJE - contenedores ingresados entre fecha_inicio y fecha_final
    if ($op['modo_facturacion'] == 'contenedores' || $op['modo_facturacion'] == 'almacenaje')
    {
        $b_anexo = '<h2>Almacenaje</h2>';

        $dias_en_patio = '(DATEDIFF(DATE( COALESCE( `fechatiempo_egreso`, NOW()) ), DATE(`fechatiempo_ingreso`)) + 1)';
        
        $c = '
        SELECT t3.cobro, t4.`dias_libres_2040`, `codigo_contenedor`, tipo_contenedor, DATE( `arivu_ingreso` + INTERVAL 89 DAY) AS "vencimiento_arivu", DATEDIFF( `arivu_ingreso` + INTERVAL 89 DAY , COALESCE(DATE( `fechatiempo_egreso` ),NOW( )) ) AS "dias_para_vencimiento_arivu", DATE( `fechatiempo_ingreso` ) AS "fecha_ingreso_fmt", COALESCE(DATE( `fechatiempo_egreso` ), "N/A") AS "fecha_salida_fmt", @inicio_cobro := '.$inicio_cobro.' AS "inicio_cobro", '.$final_cobro.' AS "final_cobro", '.$dias_en_patio.' AS "dias_en_patio",  @dias_tomados := GREATEST( (DATEDIFF( '.$final_cobro.' , '.$inicio_cobro.') + 1) , 0) AS "dias_tomados", @dias_libres := LEAST(@dias_tomados, GREATEST(0, (t4.`dias_libres_2040` - DATEDIFF(@inicio_cobro, DATE( `fechatiempo_ingreso` ))))) AS "dias_libres_aplicables", @dias_cobrados := GREATEST( @dias_tomados -  @dias_libres , 0 ) AS "dias_cobrados", @precio_dia := IF( t3.cobro =20, t4.`p_almacenaje_20` , t4.`p_almacenaje_40` ) AS "precio_por_dia", @precio_dia * @dias_cobrados AS "subtotal"
        FROM `opsal_ordenes` AS t1
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4 ON t1.`codigo_agencia` = t4.`codigo_usuario`
        WHERE 1 '.$where.'
        ORDER BY `codigo_contenedor` ASC';
        $r = db_consultar($c);

        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_almacenaje.'">';
        
        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        
        $servicio_almacenaje_fmt = '<div style="text-align:center;">'.join('</div><div style="text-align:center;">',explode("\n", wordwrap($servicio_almacenaje, 40, "\n"))).'</div>';
        $periodo_fmt = '<div style="text-align:center;">'.join('</div><div style="text-align:center;">',explode("\n", wordwrap($periodo, 15, "\n"))).'</div>';
        
        $b_anexo .= '<thead>';
        $b_anexo .= '<tr><th>Línea</th><th colspan="2">Período</th><th colspan="7">Servicio</th><th>Cant.</th></tr>';
        $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="2">'.$periodo_fmt.'</td><td colspan="7">'.$servicio_almacenaje_fmt.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><th colspan="11"></th></tr>';
        $b_anexo .= '</thead>';
        
        $b_anexo .= '</table>';

        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';

        $b_anexo .= '<thead><tr>';
        $b_anexo .= '<th>No.</th>';
        $b_anexo .= '<th>N° Contenedor</th>';
        $b_anexo .= '<th>Tipo</th>';
        $b_anexo .= '<th>Tam</th>';
        //$b_anexo .= '<th><div>Vencimiento</div><div>ARIVU</div></acronym></th>';
        $b_anexo .= '<th>Recepción</th>';
        //$b_anexo .= '<th>Inicio cobro</th>';
        //$b_anexo .= '<th>Final cobro</th>';
        $b_anexo .= '<th>Despacho</th>';
        //$b_anexo .= '<th><div>Días</div><div>patio</div></th>';
        //$b_anexo .= '<th><div>Días</div><div>libres</div></th>';
        $b_anexo .= '<th><div>Días</div><div>cobrados</div></th>';
        $b_anexo .= '<th><div>Precio</div><div>día</div></th>';
        $b_anexo .= '<th>Subtotal</th>';
        $b_anexo .= '<th>IVA</th>';
        $b_anexo .= '<th>Total</th>';
        $b_anexo .= '</tr></thead>';
        
        $total_siniva = 0;
        $total_dias = 0;
        
        $total_sin_iva = array();
        $total_dias_cobrados = array();
        
        $i = 1;
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.$i.'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            //$b_anexo .= '<td>'.$f['vencimiento_arivu'].' ['.$f['dias_para_vencimiento_arivu'].'d]</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso_fmt'].'</td>';
            //$b_anexo .= '<td>'.$f['inicio_cobro'].'</td>';
            //$b_anexo .= '<td>'.$f['final_cobro'].'</td>';
            $b_anexo .= '<td>'.$f['fecha_salida_fmt'].'</td>';
            //$b_anexo .= '<td>'.$f['dias_en_patio'].'</td>';
            //$b_anexo .= '<td>'.$f['dias_libres_aplicables'].'/'.$f['dias_libres_2040'].'</td>';
            $b_anexo .= '<td>'.$f['dias_cobrados'].'</td>';
            $b_anexo .= '<td>'.dinero($f['precio_por_dia']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
        
            $total_siniva += $f['subtotal'];
            $total_dias += $f['dias_cobrados'];
            
            @$total_sin_iva[$f['cobro']] += $f['subtotal'];
            @$total_dias_cobrados[$f['cobro']] += $f['dias_cobrados'];
            @$precio_dia[$f['cobro']] = $f['precio_por_dia'];
            
            $i++;
            
            $contenedores[$f['codigo_contenedor']] = $f;
        }
        $b_anexo .= '<tr><th colspan="6"></th><th>'.$total_dias.'</th><th></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        
        $b_anexo .= '</table>';
        
        $totales['fact_almacenaje_sin_iva'] = $total_siniva;
        $totales['fact_almacenaje'] = ($total_siniva * 1.13);
    
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;
        
        $cuadro[] = array('nombre' => 'Almacenaje', 'sin_iva' => $totales['fact_almacenaje_sin_iva'], 'sugerido' => $totales['fact_almacenaje'], 'categoria' => 'fact_almacenaje', 'detalle' => $servicio_almacenaje, 'cantidad' => '1', 'anexo' => $b_anexo);
        
        $servicio_almacenaje_tmp = str_replace('contenedores vacios', "contenedores 1 TEU vacios", $servicio_almacenaje);
        $cuadro[] = array('cu' => @$precio_dia['20'], 'nombre' => 'Almacenaje 20', 'sin_iva' => $total_sin_iva['20'], 'sugerido' => ($total_sin_iva['20']*1.13), 'categoria' => 'fact_almacenaje_20', 'detalle' => $servicio_almacenaje_tmp, 'cantidad' => $total_dias_cobrados['20'], 'anexo' => '');
        $servicio_almacenaje_tmp = str_replace('contenedores vacios', "contenedores 2 TEU vacios", $servicio_almacenaje);
        $cuadro[] = array('cu' => @$precio_dia['40'], 'nombre' => 'Almacenaje 40', 'sin_iva' => $total_sin_iva['40'], 'sugerido' => ($total_sin_iva['40']*1.13), 'categoria' => 'fact_almacenaje_40', 'detalle' => $servicio_almacenaje_tmp, 'cantidad' => $total_dias_cobrados['40'], 'anexo' => '');
    } // Almacenaje
    
    // TAGS: MOVIMIENTOS - ESTIBAS - DESESTIBAS
    if ( $op['modo_facturacion'] == 'contenedores' || $op['modo_facturacion'] == 'movimientos' )
    {        
        $estibas = ' COALESCE(SUM(CASE WHEN ( (t0.flag_traslado = 1 OR t4.`remocion_como_doble_movimiento` = 1) AND  t0.motivo = "remocion") THEN 1 WHEN  t0.motivo = "remocion" THEN 0 WHEN  t0.motivo = "estiba" THEN 1 WHEN t0.motivo = "desestiba" THEN 0 END),0) ';
        $desestibas = ' COALESCE(SUM(CASE WHEN ( (t0.flag_traslado = 1 OR t4.`remocion_como_doble_movimiento` = 1) AND  t0.motivo = "remocion") THEN 1 WHEN t0.motivo = "remocion" THEN 0 WHEN t0.motivo = "estiba" THEN 0 WHEN t0.motivo = "desestiba" THEN 1 END),0) ';
        $remociones = ' COALESCE(SUM(CASE t0.motivo WHEN "remocion" THEN (1*t4.`multiplicador_remociones`) END),0) ';
        
        $valor_desestiba = 'IF( t3.cobro =20, IF (t1.tipo_salida = "terrestre", t4.`p_terrestre_desestiba_20`, t4.`p_embarque_desestiba_20`) , IF (t1.tipo_salida = "terrestre", t4.`p_terrestre_desestiba_40`, t4.`p_embarque_desestiba_40`) )';
        $precio_desestiba = 'IF ( t0.flag_traslado = 0, '.$valor_desestiba.', ( ('.$valor_desestiba.') / 2 ) )';
        
        $valor_estiba = 'IF( t3.cobro =20, t4.`p_estiba_20` , t4.`p_estiba_40` )';
        $precio_estiba = 'IF ( t0.flag_traslado = 0, '.$valor_estiba.' , ( ('.$valor_estiba.') / 2 ) )';
        
        $c = 'SELECT IF(tipo_salida = "terrestre", transportista_egreso, buque_egreso) AS via_egreso, DATE(`fechatiempo_ingreso`) AS "fecha_ingreso", DATE(`fechatiempo_egreso`) AS "fecha_egreso", IF (tipo_salida IS NULL, "N/A", tipo_salida) AS tipo_salida, t5.usuario AS agencia, '.$precio_desestiba.' AS "precio_desestiba", '.$precio_estiba.' AS "precio_estiba", t4.`p_remocion` AS "precio_remocion", CONCAT( x2, "-", y2, "-", t0.nivel ) AS "posicion", `codigo_contenedor`, t3.`tipo_contenedor`, '.$estibas.' AS "estibas", '.$desestibas.' AS "desestibas", '.$remociones.' AS "remociones", ('.$estibas.' + '.$desestibas.') AS "total_movimientos", ( ('.$estibas.' * COALESCE('.$precio_estiba.',0) ) + ('.$desestibas.' * COALESCE('.$precio_desestiba.',0)) ) AS "subtotal_movimientos"
        FROM `opsal_movimientos` AS t0
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_ordenes` AS t1
        USING (codigo_orden)
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4
        ON t0.`cobrar_a` = t4.`codigo_usuario`
        LEFT JOIN `opsal_usuarios` AS t5
        ON t1.`codigo_agencia` = t5.`codigo_usuario`
        WHERE t0.cobrar_a="'.$codigo_agencia.'" '.$where.' '.$where_movimientos.'
        GROUP BY t1.codigo_orden, cobrar_a
        ORDER BY  `codigo_contenedor` ASC';
        $r = db_consultar($c);
        
        $b_anexo = '<br /><hr /><br /><h2>Movimientos</h2>';      
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento.'">';
        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
        $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="7">Servicio</th><th>Cantidad</th></tr>';
        $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="7">'.$servicio_movimiento.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><td colspan="13"></td></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        $b_anexo .= '<thead><tr>';
        $b_anexo .= '<th>No</th>';
        $b_anexo .= '<th>Agencia</th>';
        //$b_anexo .= '<th>Posición</th>';
        $b_anexo .= '<th>Recepción</th>';
        $b_anexo .= '<th>N° contenedor</th>';
        $b_anexo .= '<th>Tipo</th>';
        $b_anexo .= '<th>Tam</th>';
        $b_anexo .= '<th>Salida</th>';
        $b_anexo .= '<th>Estibas</th>';
        $b_anexo .= '<th>Desestibas</th>';
        //$b_anexo .= '<th>Remociones</th>';
        $b_anexo .= '<th>Cant.</th>';
        $b_anexo .= '<th>Subtotal</th>';
        $b_anexo .= '<th>IVA</th>';
        $b_anexo .= '<th>Total</th>';
        $b_anexo .= '</tr></thead>';
        
        
        $total_movimientos = 0;
        
        $cantidad_remociones = 0;
        $total_remociones = 0;
        $precio_remocion = 0;
        
        $cantidad_estibas = 0;
        $total_estibas = 0;
        $precio_estiba = 0;
        
        $cantidad_desestibas = 0;
        $total_desistibas = 0;
        $precio_desestiba = 0;
        
        $total_siniva = 0;
        $i = 1;
        
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.$i.'</td>';
            $b_anexo .= '<td>'.$f['agencia'].'</td>';
            //$b_anexo .= '<td>'.$f['posicion'].'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
            $b_anexo .= '<td>'.$f['estibas'].' @'.dinero($f['precio_estiba']).'</td>';
            $b_anexo .= '<td>'.$f['desestibas'].' @'.dinero($f['precio_desestiba']).'</td>';
            //$b_anexo .= '<td>'.$f['remociones'].' @'.dinero($f['precio_remocion']).'</td>';
            $b_anexo .= '<td>'.$f['total_movimientos'].'</td><td>'.dinero($f['subtotal_movimientos']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
        
            $total_estibas += ($f['estibas'] * $f['precio_estiba']);
            $cantidad_estibas += $f['estibas'];
            $precio_estiba = $f['precio_estiba'];
            
            @$total_desestibas += ($f['desestibas'] * $f['precio_desestiba']);
            @$cantidad_desestibas += $f['desestibas'];
            @$precio_desestiba = $f['precio_desestiba'];
            
            $total_remociones += ($f['remociones'] * $f['precio_remocion']);
            $cantidad_remociones += $f['remociones'];
            $precio_remociones = $f['precio_remocion'];            
            
            $total_siniva += $f['subtotal_movimientos'];
            $i++;            
            
            $total_movimientos += $f['total_movimientos'];
            
            $contenedores[$f['codigo_contenedor']] = array_merge( $contenedores[$f['codigo_contenedor']], $f );
        } 
        $b_anexo .= '<tr><th colspan="9"></th><th>'.$total_movimientos.'</th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;
        
        $totales['fact_movimientos_sin_iva'] = $total_siniva;
        $totales['fact_movimientos'] = ($total_siniva * 1.13);
        
        /**************** MOVIMIENTOS: SOLO ESTIBAS *************************/
        if (empty($op['flag_no_detalle_estibas']))
        {
            $servicio_movimiento_fmt = str_replace('Movimientos', 'Estibas', $servicio_movimiento);
            $b_anexo = '<br /><hr /><br /><h2>Movimientos: estibas</h2>';      
            $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento_fmt.'">';
            $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
            $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="6">Servicio</th><th>Cantidad</th></tr>';
            $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="6">'.$servicio_movimiento_fmt.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
            $b_anexo .= '<tr><td colspan="12"></td></tr>';
            $b_anexo .= '</table>';
            $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
            $b_anexo .= '<thead><tr>';
            $b_anexo .= '<th>No</th>';
            $b_anexo .= '<th>Agencia</th>';
            $b_anexo .= '<th>Recepción</th>';
            $b_anexo .= '<th>N° contenedor</th>';
            $b_anexo .= '<th>Tipo</th>';
            $b_anexo .= '<th>Tam</th>';
            $b_anexo .= '<th>Salida</th>';
            $b_anexo .= '<th>Estibas</th>';
            $b_anexo .= '<th>Cant.</th>';
            $b_anexo .= '<th>Subtotal</th>';
            $b_anexo .= '<th>IVA</th>';
            $b_anexo .= '<th>Total</th>';
            $b_anexo .= '</tr></thead>';
            
            $i = 1;
            
            mysqli_data_seek($r, 0);
            while ($f = db_fetch($r))
            {
                $b_anexo .= '<tr>';
                $b_anexo .= '<td>'.$i.'</td>';
                $b_anexo .= '<td>'.$f['agencia'].'</td>';
                $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
                $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
                $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
                $b_anexo .= '<td>'.$f['estibas'].' @'.dinero($f['precio_estiba']).'</td>';
                $b_anexo .= '<td>'.$f['estibas'].'</td><td>'.dinero($f['estibas']*$f['precio_estiba']).'</td>';
                $b_anexo .= '<td>'.dinero(($f['estibas']*$f['precio_estiba']) * 0.13).'</td>';
                $b_anexo .= '<td>'.dinero(($f['estibas']*$f['precio_estiba']) * 1.13).'</td>';
                $b_anexo .= '</tr>';
                $i++;
            } 
            $b_anexo .= '<tr><th colspan="8"></th><th>'.$cantidad_estibas.'</th><th>'.dinero($total_estibas).'</th><th>'.dinero($total_estibas * 0.13).'</th><th>'.dinero($total_estibas * 1.13).'</th></tr>';
            
            $b_anexo .= '</table>';
            $b_anexo .= '</div>';
            
            $anexo .= $b_anexo;
        } // Estibas
        
        /**************** MOVIMIENTOS: SOLO DESESTIBAS *************************/
        if (empty($op['flag_no_detalle_desestibas']))
        {
            $servicio_movimiento_fmt = str_replace('Movimientos', 'Desestibas', $servicio_movimiento);
            $b_anexo = '<br /><hr /><br /><h2>Movimientos: desestibas</h2>';      
            $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento_fmt.'">';
            $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
            $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="6">Servicio</th><th>Cantidad</th></tr>';
            $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="6">'.$servicio_movimiento_fmt.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
            $b_anexo .= '<tr><td colspan="12"></td></tr>';
            $b_anexo .= '</table>';
            $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
            $b_anexo .= '<thead><tr>';
            $b_anexo .= '<th>No</th>';
            $b_anexo .= '<th>Agencia</th>';
            $b_anexo .= '<th>Recepción</th>';
            $b_anexo .= '<th>N° contenedor</th>';
            $b_anexo .= '<th>Tipo</th>';
            $b_anexo .= '<th>Tam</th>';
            $b_anexo .= '<th>Salida</th>';
            $b_anexo .= '<th>Desestibas</th>';
            $b_anexo .= '<th>Cant.</th>';
            $b_anexo .= '<th>Subtotal</th>';
            $b_anexo .= '<th>IVA</th>';
            $b_anexo .= '<th>Total</th>';
            $b_anexo .= '</tr></thead>';
            
            $i = 1;
            
            mysqli_data_seek($r, 0);
            while ($f = db_fetch($r))
            {
                $b_anexo .= '<tr>';
                $b_anexo .= '<td>'.$i.'</td>';
                $b_anexo .= '<td>'.$f['agencia'].'</td>';
                $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
                $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
                $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
                $b_anexo .= '<td>'.$f['desestibas'].' @'.dinero($f['precio_desestiba']).'</td>';
                $b_anexo .= '<td>'.$f['desestibas'].'</td><td>'.dinero($f['desestibas']*$f['precio_desestiba']).'</td>';
                $b_anexo .= '<td>'.dinero(($f['desestibas']*$f['precio_desestiba']) * 0.13).'</td>';
                $b_anexo .= '<td>'.dinero(($f['desestibas']*$f['precio_desestiba']) * 1.13).'</td>';
                $b_anexo .= '</tr>';
                $i++;
            } 
            $b_anexo .= '<tr><th colspan="8"></th><th>'.$cantidad_desestibas.'</th><th>'.dinero($total_desestibas).'</th><th>'.dinero($total_desestibas * 0.13).'</th><th>'.dinero($total_desestibas * 1.13).'</th></tr>';
            
            $b_anexo .= '</table>';
            $b_anexo .= '</div>';
            
            $anexo .= $b_anexo;
        } // desestibas
        
        /**************** MOVIMIENTOS: SOLO REMOCIONES *************************/
        if (empty($op['flag_no_detalle_remociones']))
        {
            $b_anexo = '<br /><hr /><br /><h2>Movimientos: remociones</h2>';      
            $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento.'">';
            $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
            $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="6">Servicio</th><th>Cantidad</th></tr>';
            $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="6">'.$servicio_movimiento.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
            $b_anexo .= '<tr><td colspan="12"></td></tr>';
            $b_anexo .= '</table>';
            $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
            $b_anexo .= '<thead><tr>';
            $b_anexo .= '<th>No</th>';
            $b_anexo .= '<th>Agencia</th>';
            $b_anexo .= '<th>Recepción</th>';
            $b_anexo .= '<th>N° contenedor</th>';
            $b_anexo .= '<th>Tipo</th>';
            $b_anexo .= '<th>Tam</th>';
            $b_anexo .= '<th>Salida</th>';
            $b_anexo .= '<th>Remociones</th>';
            $b_anexo .= '<th>Cant.</th>';
            $b_anexo .= '<th>Subtotal</th>';
            $b_anexo .= '<th>IVA</th>';
            $b_anexo .= '<th>Total</th>';
            $b_anexo .= '</tr></thead>';
            
            $i = 1;
            
            mysqli_data_seek($r, 0);
            while ($f = db_fetch($r))
            {
                if ($f['remociones'] == '0') continue;
                
                $b_anexo .= '<tr>';
                $b_anexo .= '<td>'.$i.'</td>';
                $b_anexo .= '<td>'.$f['agencia'].'</td>';
                $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
                $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
                $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
                $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
                $b_anexo .= '<td>'.$f['remociones'].' @'.dinero($f['precio_remocion']).'</td>';
                $b_anexo .= '<td>'.$f['remociones'].'</td><td>'.dinero($f['remociones']*$f['precio_remocion']).'</td>';
                $b_anexo .= '<td>'.dinero(($f['remociones']*$f['precio_remocion']) * 0.13).'</td>';
                $b_anexo .= '<td>'.dinero(($f['remociones']*$f['precio_remocion']) * 1.13).'</td>';
                $b_anexo .= '</tr>';
                $i++;
            } 
            $b_anexo .= '<tr><th colspan="8"></th><th>'.$cantidad_remociones.'</th><th>'.dinero($total_remociones).'</th><th>'.dinero($total_remociones * 0.13).'</th><th>'.dinero($total_remociones * 1.13).'</th></tr>';
            
            $b_anexo .= '</table>';
            $b_anexo .= '</div>';
            
            $anexo .= $b_anexo;
        } // Remociones
        
        $detalle = $servicio_movimiento; 
        $cuadro[] = array('nombre' => 'Movimientos', 'sin_iva' => $totales['fact_movimientos_sin_iva'], 'sugerido' => $totales['fact_movimientos'], 'categoria' => 'fact_movimientos', 'detalle' => $detalle, 'cantidad' => $total_movimientos, 'anexo' => $b_anexo);

        if (empty($op['flag_no_detalle_estibas']))
        {
            $detalle = str_replace('Movimientos', 'Estibas', $servicio_movimiento) ; 
            $cuadro[] = array('cu' => $precio_estiba, 'nombre' => 'Estibas', 'sin_iva' => $total_estibas, 'sugerido' => ($total_estibas * 1.13), 'categoria' => 'fact_estibas', 'detalle' => $detalle, 'cantidad' => $cantidad_estibas, 'anexo' => $b_anexo);
        }
        
        if (empty($op['flag_no_detalle_desestibas']))
        {
            $detalle = str_replace('Movimientos', 'Desestibas', $servicio_movimiento) ;
            $cuadro[] = array('cu' => $precio_desestiba, 'nombre' => 'Desestibas', 'sin_iva' => $total_desestibas, 'sugerido' => ($total_desestibas * 1.13), 'categoria' => 'fact_desestibas', 'detalle' => $detalle, 'cantidad' => $cantidad_desestibas, 'anexo' => $b_anexo);
        }
        
        if (empty($op['flag_no_detalle_remociones']))
        {
            $detalle = str_replace('Movimientos', 'Remociones', $servicio_movimiento) ;
            $cuadro[] = array('cu' => $precio_remocion, 'nombre' => 'Remociones', 'sin_iva' => $total_remociones, 'sugerido' => ($total_remociones * 1.13), 'categoria' => 'fact_remociones', 'detalle' => $detalle, 'cantidad' => $cantidad_remociones, 'anexo' => $b_anexo);
        }
    }

    // TAGS: DT - Doble transferencia
    if ( $op['modo_facturacion'] == 'dt' )
    {        
        
        $c = 'SELECT buque_ingreso, IF(tipo_salida = "terrestre", transportista_egreso, buque_egreso) AS via_egreso, DATE(`fechatiempo_ingreso`) AS "fecha_ingreso", DATE(`fechatiempo_egreso`) AS "fecha_egreso", IF (tipo_salida IS NULL, "N/A", tipo_salida) AS tipo_salida, t5.usuario AS agencia, p_doble_transferencia, CONCAT( x2, "-", y2, "-", t0.nivel ) AS "posicion", `codigo_contenedor`, t3.`tipo_contenedor`, 1 AS "dt", 1 AS "total_movimientos", ( p_doble_transferencia ) AS "subtotal_movimientos"
        FROM `opsal_movimientos` AS t0
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_ordenes` AS t1
        USING (codigo_orden)
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4
        ON t0.`cobrar_a` = t4.`codigo_usuario`
        LEFT JOIN `opsal_usuarios` AS t5
        ON t1.`codigo_agencia` = t5.`codigo_usuario`
        WHERE t0.cobrar_a="'.$codigo_agencia.'" '.$where.' '.$where_movimientos.'
        GROUP BY t1.codigo_orden, cobrar_a
        ORDER BY  `codigo_contenedor` ASC';
        $r = db_consultar($c);
        $b_anexo = '<br /><hr /><br /><h2>Doble transferencias</h2>';
        

        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento.'">';
        
        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
        $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="6">Servicio</th><th>Cantidad</th></tr>';
        $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="6">'. $servicio_dt .'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><td colspan="13"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';

        $b_anexo .= '<thead><tr>';
        $b_anexo .= '<th>No</th>';
        $b_anexo .= '<th>Agencia</th>';
        $b_anexo .= '<th>N° contenedor</th>';
        $b_anexo .= '<th>Tipo</th>';
        $b_anexo .= '<th>Tam</th>';
        $b_anexo .= '<th>Fecha ingreso</th>';
        $b_anexo .= '<th>Buque ingreso</th>';
        $b_anexo .= '<th>Fecha salida</th>';
        $b_anexo .= '<th>Buque salida</th>';
        $b_anexo .= '<th>DT</th>';
        $b_anexo .= '<th>Cant.</th>';
        $b_anexo .= '<th>Subtotal</th>';
        $b_anexo .= '<th>IVA</th>';
        $b_anexo .= '<th>Total</th>';
        $b_anexo .= '</tr></thead>';
        
        
        $total_movimientos = 0;
        $precio_doble_transferencia = 0;
        $total_siniva = 0;
        $i = 1;
        
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.$i.'</td>';
            $b_anexo .= '<td>'.$f['agencia'].'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.$f['buque_ingreso'].'</td>';
            $b_anexo .= '<td>'.$f['fecha_egreso'].'</td>';
            $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
            $b_anexo .= '<td>'.$f['dt'].' @'.dinero($f['p_doble_transferencia']).'</td>';
            $b_anexo .= '<td>'.$f['total_movimientos'].'</td><td>'.dinero($f['subtotal_movimientos']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
        
            $total_siniva += $f['subtotal_movimientos'];
            $i++;            
            
            $total_movimientos += $f['total_movimientos'];
            
            $precio_doble_transferencia = $f['p_doble_transferencia'];
        } 
        $b_anexo .= '<tr><th colspan="10"></th><th>'.$total_movimientos.'</th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;
        
        $totales['fact_movimientos_sin_iva'] = $total_siniva;
        $totales['fact_movimientos'] = ($total_siniva * 1.13);
        
        $detalle = $servicio_dt . ', ' . $periodo; 
        
        $cuadro[] = array('cu' => $precio_doble_transferencia, 'nombre' => 'Doble transferencia', 'sin_iva' => $totales['fact_movimientos_sin_iva'], 'sugerido' => $totales['fact_movimientos'], 'categoria' => 'fact_dt', 'detalle' => $detalle, 'cantidad' => $total_movimientos, 'anexo' => $b_anexo);
                
    }
    
    // TAGS: REMOCIONES
    if ( $op['modo_facturacion'] == 'remociones' )
    {
        $remociones = ' COALESCE(SUM(CASE t0.motivo WHEN "remocion" THEN (1*t4.`multiplicador_remociones`) END),0) ';
        
        $c = 'SELECT IF(tipo_salida = "terrestre", transportista_egreso, buque_egreso) AS via_egreso, DATE(`fechatiempo_ingreso`) AS "fecha_ingreso", DATE(`fechatiempo_egreso`) AS "fecha_egreso", IF (tipo_salida IS NULL, "N/A", tipo_salida) AS tipo_salida, t5.usuario AS agencia, t4.`p_remocion` AS "precio_remocion", CONCAT( x2, "-", y2, "-", t0.nivel ) AS "posicion", `codigo_contenedor`, t3.`tipo_contenedor`, '.$remociones.' AS "remociones", '.$remociones.' AS "total_movimientos",  ('.$remociones.' * p_remocion)  AS "subtotal_movimientos"
        FROM `opsal_movimientos` AS t0
        LEFT JOIN `opsal_posicion` AS t2
        USING ( codigo_posicion )
        LEFT JOIN `opsal_ordenes` AS t1
        USING (codigo_orden)
        LEFT JOIN `opsal_tipo_contenedores` AS t3
        USING ( tipo_contenedor )
        LEFT JOIN `opsal_tarifas` AS t4
        ON t0.`cobrar_a` = t4.`codigo_usuario`
        LEFT JOIN `opsal_usuarios` AS t5
        ON t1.`codigo_agencia` = t5.`codigo_usuario`
        WHERE t0.cobrar_a="'.$codigo_agencia.'" '.$where.' '.$where_movimientos.'
        GROUP BY t1.codigo_orden, cobrar_a
        ORDER BY  `codigo_contenedor` ASC';
        $r = db_consultar($c);
        $b_anexo = '<br /><hr /><br /><h2>Remociones</h2>';
        

        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio_movimiento.'">';
        
        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada tabla-centrada">';
        $b_anexo .= '<tr><th>Línea</th><th colspan="4">Período</th><th colspan="6">Servicio</th><th>Cantidad</th></tr>';
        $b_anexo .= '<tr><td>'.$agencia.'</td><td colspan="4">'.$periodo.'</td><td colspan="6">'.$servicio_movimiento.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><td colspan="12"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';

        $b_anexo .= '<thead><tr>';
        $b_anexo .= '<th>No</th>';
        $b_anexo .= '<th>Agencia</th>';
        $b_anexo .= '<th>Recepción</th>';
        $b_anexo .= '<th>N° contenedor</th>';
        $b_anexo .= '<th>Tipo</th>';
        $b_anexo .= '<th>Tam</th>';
        $b_anexo .= '<th>Salida</th>';
        $b_anexo .= '<th>Remociones</th>';
        $b_anexo .= '<th>Cant.</th>';
        $b_anexo .= '<th>Subtotal</th>';
        $b_anexo .= '<th>IVA</th>';
        $b_anexo .= '<th>Total</th>';
        $b_anexo .= '</tr></thead>';
        
        
        $total_movimientos = 0;
        
        $cantidad_remociones = 0;
        $total_remocion = 0;
        $precio_remocion = 0;
        
        $total_siniva = 0;
        $i = 1;
        
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.$i.'</td>';
            $b_anexo .= '<td>'.$f['agencia'].'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
            $b_anexo .= '<td>'.$f['remociones'].' @'.dinero($f['precio_remocion']).'</td>';
            $b_anexo .= '<td>'.$f['total_movimientos'].'</td><td>'.dinero($f['subtotal_movimientos']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
        
            $total_remocion += ($f['remociones'] * $f['precio_remocion']);
            $cantidad_remociones += $f['remociones'];
            $precio_remocion = $f['precio_remocion'];            
            
            $total_siniva += $f['subtotal_movimientos'];
            $i++;            
            
            $total_movimientos += $f['total_movimientos'];
            
            $contenedores[$f['codigo_contenedor']] = array_merge( $contenedores[$f['codigo_contenedor']], $f );
        } 
        $b_anexo .= '<tr><th colspan="8"></th><th>'.$total_movimientos.'</th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;
        
        $totales['fact_movimientos_sin_iva'] = $total_siniva;
        $totales['fact_movimientos'] = ($total_siniva * 1.13);
        
        $detalle = $servicio_movimiento . ', ' . $periodo; 
        
        $detalle = str_replace('Movimientos', 'Remociones', $servicio_movimiento) ;
        $cuadro[] = array('cu' => $precio_remocion, 'nombre' => 'Remociones', 'sin_iva' => $total_remocion, 'sugerido' => ($total_remocion * 1.13), 'categoria' => 'fact_remociones', 'detalle' => $detalle, 'cantidad' => $cantidad_remociones, 'anexo' => $b_anexo);
    }

    
    // TAGS: CONSOLIDADO
    if ($op['modo_facturacion'] == 'contenedores') {
        
        $i = 1;
        $total_siniva = 0;
        
        $b_anexo = '<br /><hr /><br /><h2>Consolidado</h2>';        
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' - Consolidado" style="overflow-x:auto;">';
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';

        $b_anexo .= '<thead><tr>';
        $b_anexo .= '<th>No.</th>';
        
        $b_anexo .= '<th>N° Contenedor</th>';
        $b_anexo .= '<th>Tipo</th>';
        $b_anexo .= '<th>Tam</th>';
        //$b_anexo .= '<th><div>Vencimiento</div><div>ARIVU</div></acronym></th>';
        $b_anexo .= '<th>Recepción</th>';
        //$b_anexo .= '<th>Inicio cobro</th>';
        //$b_anexo .= '<th>Final cobro</th>';
        $b_anexo .= '<th>Despacho</th>';
        //$b_anexo .= '<th><div>Días</div><div>patio</div></th>';
        //$b_anexo .= '<th><div>Días</div><div>libres</div></th>';
        $b_anexo .= '<th><div>Días</div><div>cobrados</div></th>';
        $b_anexo .= '<th><div>Precio</div><div>día</div></th>';
        $b_anexo .= '<th>Subtotal</th>';
        
        $b_anexo .= '<th>Salida</th>';
        $b_anexo .= '<th>Estibas</th>';
        $b_anexo .= '<th>Desestibas</th>';
        $b_anexo .= '<th>Remociones</th>';
        $b_anexo .= '<th>Cant.</th>';
        $b_anexo .= '<th>Subtotal</th>';


        $b_anexo .= '<th><div>SubTotal</div><div>servicios</div></th>';
        $b_anexo .= '<th>IVA</th>';
        $b_anexo .= '<th>Total</th>';
        $b_anexo .= '</tr></thead>';

        foreach($contenedores as $f)
        {
            $b_anexo .= '<tr>';
            
            $b_anexo .= '<td>'.$i.'</td>';
        
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            //$b_anexo .= '<td>'.$f['vencimiento_arivu'].' ['.$f['dias_para_vencimiento_arivu'].'d]</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso_fmt'].'</td>';
            //$b_anexo .= '<td>'.$f['inicio_cobro'].'</td>';
            //$b_anexo .= '<td>'.$f['final_cobro'].'</td>';
            $b_anexo .= '<td>'.$f['fecha_salida_fmt'].'</td>';
            //$b_anexo .= '<td>'.$f['dias_en_patio'].'</td>';
            //$b_anexo .= '<td>'.$f['dias_libres_aplicables'].'/'.$f['dias_libres_2040'].'</td>';
            $b_anexo .= '<td>'.$f['dias_cobrados'].'</td>';
            $b_anexo .= '<td>'.dinero($f['precio_por_dia']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            
            $b_anexo .= '<td style="text-align:left !important;">'. $f['via_egreso'] . '</td>';
            $b_anexo .= '<td>'.$f['estibas'].' @'.dinero($f['precio_estiba']).'</td>';
            $b_anexo .= '<td>'.$f['desestibas'].' @'.dinero($f['precio_desestiba']).'</td>';
            $b_anexo .= '<td>'.$f['remociones'].' @'.dinero($f['precio_remocion']).'</td>';
            $b_anexo .= '<td>'.$f['total_movimientos'].'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal_movimientos']).'</td>';
            
            $b_anexo .= '<td>'.dinero($f['subtotal'] + $f['subtotal_movimientos']).'</td>';
            $b_anexo .= '<td>'.dinero(($f['subtotal'] + $f['subtotal_movimientos'])/1.13).'</td>';
            $b_anexo .= '<td>'.dinero(($f['subtotal'] + $f['subtotal_movimientos'])*1.13).'</td>';

            $b_anexo .= '</tr>';
            
            $total_siniva += ($f['subtotal'] + $f['subtotal_movimientos']);
            $i++;
            
        }
        $b_anexo .= '<tr><th colspan="8"></th><th>'.dinero($totales['fact_almacenaje_sin_iva']).'</th><th colspan="5"></th><th>'.dinero($totales['fact_movimientos_sin_iva']).'</th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;
        
        $totales['fact_consolidado_sin_iva'] = $total_siniva;
        $totales['fact_consolidado'] = ($total_siniva * 1.13);
        
        $cuadro[] = array('nombre' => 'Consolidado', 'sin_iva' => $totales['fact_consolidado_sin_iva'], 'sugerido' => $totales['fact_consolidado'], 'categoria' => 'fact_consolidado', 'detalle' => 'Consolidado'. ' '.$periodo, 'cantidad' => '1', 'anexo' => $b_anexo);
    }
        
    /****** TAGS: CONDICIONES ***/
    if ($op['modo_facturacion'] == 'condiciones')
    {        
        $servicio = 'Elaboración de condiciones';
        $grupo = 'ELAB. CONDICICIONES';
        
        $c = 'SELECT estado, codigo_contenedor, tipo_contenedor, fecha_ingreso, referencia_papel, t2.p_elaboracion_condiciones AS "subtotal" FROM `opsal_condiciones` AS t1 LEFT JOIN `opsal_tarifas` AS t2 ON t1.codigo_agencia = t2.codigo_usuario WHERE DATE(fecha_ingreso) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY `codigo_contenedor` ASC';
        $r = db_consultar($c);
        $i = 0;
        $total_siniva = 0;
        $precio_unitario = 0;
        
        $arr_total_siniva = array('lleno' => 0, 'vacio' => 0);
        $arr_cantidad = array('todos' => 0, 'lleno' => 0, 'vacio' => 0);
        $cuadro_separado  = array('lleno' => array(), 'vacio' => array());
        
        $b_anexo = '<h2>Elaboración de condiciones</h2>';
        
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio.'">';

        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        $b_anexo .= '<tr><th colspan="2">Línea</th><th colspan="3">Período</th><th colspan="3">Servicio</th><th>Cant.</th></tr>';
        $b_anexo .= '<tr><td colspan="2">'.$agencia.'</td><td colspan="3">'.$periodo.'</td><td colspan="3">'.$servicio.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><td colspan="9"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
        $b_anexo .= "<tr><th>No</th><th>Contenedor</th><th>#</th><th>Tipo</th><th>Tam</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>";
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.($i+1).'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.$f['referencia_papel'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
            $total_siniva += $f['subtotal'];
            $arr_total_siniva[$f['estado']] += $f['subtotal'];
            $arr_cantidad[$f['estado']]++;
            $arr_cantidad['todos']++;
            
            $cuadro_separado[$f['estado']][] = $f;
            
            $i++;
            $precio_unitario = $f['subtotal'];
        }
        $b_anexo .= '<tr><th colspan="6"></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';        
        
        $anexo .= $b_anexo;        
        
        $b_anexo = '<h2>Elaboración de condiciones - Vacios</h2>';
        $i = 0;
        
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - vacios - '.$periodo.' '.$servicio.'">';

        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        $b_anexo .= '<tr><th colspan="2">Línea</th><th colspan="3">Período</th><th colspan="3">Servicio</th><th>Cant.</th></tr>';
        $b_anexo .= '<tr><td colspan="2">'.$agencia.'</td><td colspan="3">'.$periodo.'</td><td colspan="3">'.$servicio.'</td><td>'.$arr_cantidad['vacio'].'</td></tr>';
        $b_anexo .= '<tr><td colspan="9"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
        $b_anexo .= "<tr><th>No</th><th>Contenedor</th><th>#</th><th>Tipo</th><th>Tam</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>";
        foreach ($cuadro_separado['vacio'] as $f)
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.($i+1).'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.$f['referencia_papel'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
            
            $i++;
            $precio_unitario = $f['subtotal'];
        }
        $b_anexo .= '<tr><th colspan="6"></th><th>'.dinero($arr_total_siniva['vacio']).'</th><th>'.dinero($arr_total_siniva['vacio'] * 0.13).'</th><th>'.dinero($arr_total_siniva['vacio'] * 1.13).'</th></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        
        $anexo .= $b_anexo;

        $b_anexo = '<h2>Elaboración de condiciones - Llenos</h2>';
        $i = 0;
        
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - vacios - '.$periodo.' '.$servicio.'">';

        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        $b_anexo .= '<tr><th colspan="2">Línea</th><th colspan="3">Período</th><th colspan="3">Servicio</th><th>Cant.</th></tr>';
        $b_anexo .= '<tr><td colspan="2">'.$agencia.'</td><td colspan="3">'.$periodo.'</td><td colspan="3">'.$servicio.'</td><td>'.$arr_cantidad['lleno'].'</td></tr>';
        $b_anexo .= '<tr><td colspan="9"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
        $b_anexo .= "<tr><th>No</th><th>Contenedor</th><th>#</th><th>Tipo</th><th>Tam</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>";
        foreach ($cuadro_separado['lleno'] as $f)
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.($i+1).'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.$f['referencia_papel'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td>'.$f['fecha_ingreso'].'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
            
            $i++;
            $precio_unitario = $f['subtotal'];
        }
        $b_anexo .= '<tr><th colspan="6"></th><th>'.dinero($arr_total_siniva['lleno']).'</th><th>'.dinero($arr_total_siniva['lleno'] * 0.13).'</th><th>'.dinero($arr_total_siniva['lleno'] * 1.13).'</th></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';
        
        $anexo .= $b_anexo;        
        
        $totales['fact_elaboracion_condicion_sin_iva'] = $total_siniva;
        $totales['fact_elaboracion_condicion'] = ($total_siniva * 1.13);
        
        // Todos
        $cuadro[] = array('cu' => $precio_unitario, 'nombre' => 'Elaboración de condición', 'sin_iva' => $totales['fact_elaboracion_condicion_sin_iva'], 'sugerido' => $totales['fact_elaboracion_condicion'], 'categoria' => 'fact_elaboracion_condicion', 'detalle' => $servicio. ' '.$periodo, 'cantidad' => $arr_cantidad['todos'], 'anexo' => $b_anexo);
        
        // Llenos
        $cuadro[] = array('cu' => $precio_unitario, 'nombre' => 'Elaboración de condición - llenos', 'sin_iva' => $arr_total_siniva['lleno'], 'sugerido' => ($arr_total_siniva['lleno'] * 1.13), 'categoria' => 'fact_elaboracion_condicion_lleno', 'detalle' => $servicio. ' - llenos '.$periodo, 'cantidad' => $arr_cantidad['lleno'], 'anexo' => $b_anexo);
        
        // Vacios
        $cuadro[] = array('cu' => $precio_unitario, 'nombre' => 'Elaboración de condición - vacios', 'sin_iva' => $arr_total_siniva['vacio'], 'sugerido' => ($arr_total_siniva['vacio'] * 1.13), 'categoria' => 'fact_elaboracion_condicion_vacio', 'detalle' => $servicio. ' - vacios '.$periodo, 'cantidad' => $arr_cantidad['vacio'], 'anexo' => $b_anexo);
    }
    
    /****** TAGS: REPARACIONES ***/
    if ($op['modo_facturacion'] == 'reparaciones')
    {        
        $servicio = 'Reparación de contenedores';
        $grupo = 'REP. DE CONTENEDORES';
        
        $c = 'SELECT `codigo_reparacion`, tipo_contenedor, `clase_anterior`, `clase_nueva`, `codigo_contenedor`, `codigo_digitador`, `fechahora_digitado`, `flag_eliminado`, `tecnico_responsable`, `costo_reparacion` AS "subtotal", `duracion_reparacion`, `detalle_reparacion`, `fechahora_reparacion`, u.usuario AS "nombre_digitador" FROM `opsal_reparaciones` AS r  LEFT JOIN opsal_ordenes AS o ON o.codigo_orden=r.codigo_orden LEFT JOIN opsal_usuarios AS u ON r.codigo_digitador = u.codigo_usuario WHERE DATE(fechahora_reparacion) BETWEEN "'.$periodo_inicio.'" AND "'.$periodo_final.'" AND codigo_agencia="'.$codigo_agencia.'" ORDER BY `codigo_contenedor` ASC';
        
        $r = db_consultar($c);
        $i = 0;
        $total_siniva = 0;
        
        $arr_total_siniva = 0;
        
        $b_anexo = '<h2>Reparación de contenedores</h2>';
        
        $b_anexo .= '<div class="exportable" rel="'.$agencia.' - '.$periodo.' '.$servicio.'">';

        $b_anexo .= '<table style="margin-bottom:10px;" class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea tabla-centrada">';
        $b_anexo .= '<tr><th colspan="2">Línea</th><th colspan="3">Período</th><th colspan="3">Servicio</th><th>Cant.</th></tr>';
        $b_anexo .= '<tr><td colspan="2">'.$agencia.'</td><td colspan="3">'.$periodo.'</td><td colspan="3">'.$servicio.'</td><td>'.mysqli_num_rows($r).'</td></tr>';
        $b_anexo .= '<tr><td colspan="9"></td></tr>';
        $b_anexo .= '</table>';
        
        $b_anexo .= '<table class="opsal_tabla_ancha tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
        $b_anexo .= "<tr><th>No</th><th>Contenedor</th><th>Cod. referencia</th><th>Tipo</th><th>Tam</th><th>Fecha</th><th>Subtotal</th><th>IVA</th><th>Total</th></tr>";
        while ($f = db_fetch($r))
        {
            $b_anexo .= '<tr>';
            $b_anexo .= '<td>'.($i+1).'</td>';
            $b_anexo .= '<td>'.$f['codigo_contenedor'].'</td>';
            $b_anexo .= '<td>'.$f['codigo_reparacion'].'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],0,2).'</td>';
            $b_anexo .= '<td>'.substr($f['tipo_contenedor'],2,4).'</td>';
            $b_anexo .= '<td>'.$f['fechahora_reparacion'].'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal']).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 0.13).'</td>';
            $b_anexo .= '<td>'.dinero($f['subtotal'] * 1.13).'</td>';
            $b_anexo .= '</tr>';
            $total_siniva += $f['subtotal'];
            $arr_total_siniva += $f['subtotal'];            
            $i++;
        }
        $b_anexo .= '<tr><th colspan="6"></th><th>'.dinero($total_siniva).'</th><th>'.dinero($total_siniva * 0.13).'</th><th>'.dinero($total_siniva * 1.13).'</th></tr>';
        $b_anexo .= '</table>';
        $b_anexo .= '</div>';        
        
        $anexo .= $b_anexo;        
                
        $totales['fact_reparaciones_sin_iva'] = $total_siniva;
        $totales['fact_reparacion'] = ($total_siniva * 1.13);
        
        // Todos
        $cuadro[] = array('cu' => 0.00, 'nombre' => 'Reparación de contenedores', 'sin_iva' => $totales['fact_reparaciones_sin_iva'], 'sugerido' => $totales['fact_reparacion'], 'categoria' => 'fact_reparacion', 'detalle' => $servicio. ' '.$periodo, 'cantidad' => $i, 'anexo' => $b_anexo);       
    }
    
    // GENERAR CUADRO
    
    $bcuadro = '<table class="opsal_tabla_ancha  tabla-estandar opsal_tabla_borde_oscuro tabla-una-linea">';
    $bcuadro .= '<tr><th></th><th>Categoría</th><th>Cantidad</th><th style="width:700px;">Concepto</th><th>C/U</th><th>SubTotal</th><th>IVA</th><th>Total</th></tr>';
    foreach ($cuadro as $parte)
    {
        $hidden = '';
        $hidden .= '<input type="hidden" name="detalle['.$parte['categoria'].'][periodo_inicio]" value="'.$periodo_inicio.'" />';
        $hidden .= '<input type="hidden" name="detalle['.$parte['categoria'].'][periodo_final]" value="'.$periodo_final.'" />';
        $hidden .= '<input type="hidden" name="detalle['.$parte['categoria'].'][modo_facturacion]" value="'. $_GET['modo_facturacion'].'" />';
        $hidden .= '<input type="hidden" name="detalle['.$parte['categoria'].'][tipo_salida]" value="'. @$op['tipo_salida'] .'" />';
        $hidden .= '<input type="hidden" name="detalle['.$parte['categoria'].'][grupo]" value="'. $grupo.'" />';

        $bcuadro .= '<tr>';
        $bcuadro .= '<td>'.$hidden.'<input type="checkbox" class="chkconcepto" name="detalle['.$parte['categoria'].'][utilizar]" /><input type="hidden" value="' . htmlentities($parte['anexo'],ENT_COMPAT ,'UTF-8').'" name="detalle['.$parte['categoria'].'][anexo]" /></td>';
        $bcuadro .= '<td>'.$parte['nombre'].'</td>';
        $bcuadro .= '<td><input type="text" name="detalle['.$parte['categoria'].'][cantidad]" value="'.$parte['cantidad'].'"/></td>';
        $bcuadro .= '<td><input type="text" name="detalle['.$parte['categoria'].'][detalle]" style="width:100%;" value="' . $parte['detalle'].'" /></td>';
        $bcuadro .= '<td><input type="hidden" name="detalle['.$parte['categoria'].'][cu]" value="'.numero2(@$parte['cu']).'"/>'.dinero(@$parte['cu']).'</td>';
        $bcuadro .= '<td><input type="hidden" name="detalle['.$parte['categoria'].'][sin_iva]" value="'.numero2($parte['sin_iva']).'"/>'.dinero($parte['sin_iva']).'</td>';
        $bcuadro .= '<td><input type="hidden" name="detalle['.$parte['categoria'].'][iva]" value="'.numero2($parte['sin_iva']*0.13).'"/>'.dinero($parte['sin_iva']*0.13).'</td>';
        $bcuadro .= '<td><input type="hidden" name="detalle['.$parte['categoria'].'][total]" value="'.numero2($parte['sugerido']).'"/>'.dinero($parte['sugerido']).'</td>';
        $bcuadro .= '</tr>';
    }
    $bcuadro .= '</table>';
    
    return array('anexo' => $anexo, 'cuadro' => $bcuadro);
}

// http://www.linuxjournal.com/article/9585
function validcorreo($correo)
{
   $isValid = true;
   $atIndex = strrpos($correo, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($correo, $atIndex+1);
      $local = substr($correo, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !checkdnsrr($domain,"A"))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}


function ES_SSL()
{
    return ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443);
}

function SEO($URL){
    $URL = preg_replace("`\[.*\]`U","",$URL);
    $URL = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$URL);
    $URL = htmlentities($URL, ENT_COMPAT, 'utf-8');
    $URL = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $URL );
    $URL = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $URL);
    return strtolower(trim($URL, '-')).".html";
}
// http://www.webcheatsheet.com/PHP/get_current_page_url.php
// Obtiene la URL actual, $stripArgs determina si eliminar la parte dinamica de la URL
function curPageURL($stripArgs=false,$friendly=false,$forzar_ssl=false) {
$pageURL = '';
if (!$friendly)
{
   $pageURL = 'http';

   if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
   $pageURL .= "://";
}

$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

if ($stripArgs) {$pageURL = preg_replace("/\?.*/", "",$pageURL);}

if ($friendly)
{
    $pageURL = preg_replace('/www\./', '',$pageURL);
    $pageURL = "www.$pageURL";
}

return $pageURL;
}

function domain($forzar_ssl = false) {
$pageURL = 'http';
if ((ES_SSL() || $forzar_ssl) && $forzar_ssl != 'nunca') {$pageURL .= "s";}
$pageURL .= "://";
$pageURL .= $_SERVER["SERVER_NAME"];
$pageURL = preg_replace('/www\./', '',$pageURL);
$pageURL .= "/";
return $pageURL;
}

// http://www.php.net/manual/en/function.mt-rand.php#106645
function genRandomString($length = 10) {
    $chars = 'fghjkraeou';
    $result = '';
    
    for ($p = 0; $p < $length; $p++)
    {
        $result .= ($p%2) ? $chars[mt_rand(6, 9)] : $chars[mt_rand(0, 5)];
    }
    
    return $result;
}

// Wrapper de envío de correo electrónico. HTML/utf-8
function correo($para, $asunto, $mensaje,$exHeaders=null)
{
    
    if (correoSMTP($para, $asunto, $mensaje))
        return;
    
    $headers = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n" . 'Date: '.date("r") . "\r\n";
    $headers .= 'From: '. PROY_MAIL_POSTMASTER . "\r\n";
    
    if (!empty($exHeaders))
    {
        $headers .= $exHeaders;
    }
    $mensaje = sprintf('<html><head><title>%s</title></head><body>%s</body>',PROY_NOMBRE,$mensaje);
    return mail($para,'=?UTF-8?B?'.base64_encode($asunto).'?=',$mensaje,$headers);
}

/*
  Copyright (c) 2008, reusablecode.blogspot.com; some rights reserved.
 
  This work is licensed under the Creative Commons Attribution License. To view
  a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or
  send a letter to Creative Commons, 559 Nathan Abbott Way, Stanford, California
  94305, USA.
  */
 
// Luhn (mod 10) algorithm
function luhn($input)
{
    if (strlen($input) < 10)
        return false;
    
    $sum = 0;
    $odd = strlen($input) % 2;
     
    // Remove any non-numeric characters.
    if (!is_numeric($input))
        $input = preg_replace("/[^\d]/", "", $input);
     
    // Calculate sum of digits.
    for($i = 0; $i < strlen($input); $i++)
    {
        $sum += $odd ? $input[$i] : (($input[$i] * 2 > 9) ? $input[$i] * 2 - 9 : $input[$i] * 2);
        $odd = !$odd;
    }
     
    // Check validity.
    return ($sum % 10 == 0) ? true : false;
}

//Wrapper de envío de correo usando PHPMailer
function correoSMTP($para, $asunto, $mensaje,$html=true,$extra=null)
{
    require_once('class.phpmailer.php');
    $Mail               = new PHPMailer();
    $Mail->IsHTML       ($html) ;
    $Mail->SetLanguage  ("es", 'language/');
    $Mail->PluginDir	= 'PHP/';
    $Mail->Mailer	= 'smtp';
    $Mail->Host		= "mail.opsal.net";
    $Mail->Port		= 25;
    $Mail->SMTPAuth	= true;
    $Mail->Username	= smtp_usuario;
    $Mail->Password	= smtp_clave;
    $Mail->CharSet	= "utf-8";
    $Mail->Encoding	= "quoted-printable";
    $Mail->SetFrom	(PROY_MAIL_POSTMASTER, PROY_MAIL_POSTMASTER_NOMBRE);
    $Mail->Subject	= $asunto;
    $Mail->Body		= $mensaje;

    // Veamos si hay que hacer embed de imagenes
    if (is_array($extra))
    {
        foreach ($extra as $archivo)
        {
            $Mail->AddEmbeddedImage($archivo['ruta'], $archivo['cid'], $archivo['alt'], "base64", $archivo['tipo']);
        }
    }
    
    $correos = preg_split('/,/',$para);
    foreach($correos as $correo)
        $Mail->AddAddress ($correo);

    $x = $Mail->Send();
    
    if ($x)
       return $x;
    else
       return 0;
}


function HEAD_JS()
{
    global $arrJS;
    $buffer = '';
    foreach ($arrJS as $JS)
        $buffer .= '<script type="text/javascript" src="'.PROY_URL_ESTATICA.'JS/'.$JS.'.js"></script>';

    echo $buffer;
}

function HEAD_CSS()
{
    global $arrCSS;
    $buffer = '';
    foreach ($arrCSS as $CSS)
        $buffer .= '<link rel="stylesheet" type="text/css" href="'.PROY_URL_ESTATICA.$CSS.'.css" />';
    
    echo $buffer;
}

function HEAD_EXTRA()
{
    global $arrHEAD;
    echo "\n";
    echo implode("\n",$arrHEAD);
    echo "\n";
}

function SI_ADMIN($texto)
{
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
        return $texto;
    }
}

function protegerme($solo_salir=false,$niveles=array())
{

    if (_F_usuario_cache('nivel') == _N_administrador || in_array(_F_usuario_cache('nivel'),$niveles))
        return;
    
    if (!$solo_salir)
        header('Location: '. PROY_URL.'?ref='.curPageURL());
    ob_end_clean();
    exit;
}

function ellipsis($text, $max=100, $append='&hellip;')
{
    if (strlen($text) <= $max) return $text;
    $out = substr($text,0,$max);
    if (strpos($text,' ') === FALSE) return $out.$append;
    return preg_replace('/\w+$/','',$out).$append;
}

function numero_a_letras($xcifra)
{
$xarray = array(0 => "Cero",
1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
"DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
"VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
);
//
$xcifra = trim($xcifra);
$xlength = strlen($xcifra);
$xpos_punto = strpos($xcifra, ".");
$xaux_int = $xcifra;
$xdecimales = "00";
if (!($xpos_punto === false))
   {
   if ($xpos_punto == 0)
      {
      $xcifra = "0".$xcifra;
      $xpos_punto = strpos($xcifra, ".");
      }
   $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
   $xdecimales = substr($xcifra."00", $xpos_punto + 1, 2); // obtengo los valores decimales
   }
 
$XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
$xcadena = "";
for($xz = 0; $xz < 3; $xz++)
   {
   $xaux = substr($XAUX, $xz * 6, 6);
   $xi = 0; $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
   $xexit = true; // bandera para controlar el ciclo del While
   while ($xexit)
      {
      if ($xi == $xlimite) // si ya llegó al límite máximo de enteros
         {
         break; // termina el ciclo
         }
    
      $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
      $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
      for ($xy = 1; $xy < 4; $xy++) // ciclo para revisar centenas, decenas y unidades, en ese orden
         {
         switch ($xy)
            {
            case 1: // checa las centenas
               if (substr($xaux, 0, 3) < 100) // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas
                  {
                  }
               else
                  {
                  $xseek = $xarray[substr($xaux, 0, 3)]; // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                  if ($xseek)
                     {
                     $xsub = subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                     if (substr($xaux, 0, 3) == 100)
                        $xcadena = " ".$xcadena." CIEN ".$xsub;
                     else
                        $xcadena = " ".$xcadena." ".$xseek." ".$xsub;
                     $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                     }
                  else // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                     {
                     $xseek = $xarray[substr($xaux, 0, 1) * 100]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                     $xcadena = " ".$xcadena." ".$xseek;
                     } // ENDIF ($xseek)
                  } // ENDIF (substr($xaux, 0, 3) < 100)
               break;
            case 2: // checa las decenas (con la misma lógica que las centenas)
               if (substr($xaux, 1, 2) < 10)
                  {
                  }
               else
                  {
                  $xseek = $xarray[substr($xaux, 1, 2)];
                  if ($xseek)
                     {
                     $xsub = subfijo($xaux);
                     if (substr($xaux, 1, 2) == 20)
                        $xcadena = " ".$xcadena." VEINTE ".$xsub;
                     else
                        $xcadena = " ".$xcadena." ".$xseek." ".$xsub;
                     $xy = 3;
                     }
                  else
                     {
                     $xseek = $xarray[substr($xaux, 1, 1) * 10];
                     if (substr($xaux, 1, 1) * 10 == 20)
                        $xcadena = " ".$xcadena." ".$xseek;
                     else 
                        $xcadena = " ".$xcadena." ".$xseek." Y ";
                     } // ENDIF ($xseek)
                  } // ENDIF (substr($xaux, 1, 2) < 10)
               break;
            case 3: // checa las unidades
               if (substr($xaux, 2, 1) < 1) // si la unidad es cero, ya no hace nada
                  {
                  }
               else
                  {
                  $xseek = $xarray[substr($xaux, 2, 1)]; // obtengo directamente el valor de la unidad (del uno al nueve)
                  $xsub = subfijo($xaux);
                  $xcadena = " ".$xcadena." ".$xseek." ".$xsub;
                  } // ENDIF (substr($xaux, 2, 1) < 1)
               break;
            } // END SWITCH
         } // END FOR
         $xi = $xi + 3;
      } // ENDDO
 
      if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
         $xcadena.= " DE";
          
      if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
         $xcadena.= " DE";
       
      // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
      if (trim($xaux) != "")
         {
         switch ($xz)
            {
            case 0:
               if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                  $xcadena.= "UN BILLON ";
               else
                  $xcadena.= " BILLONES ";
               break;
            case 1:
               if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                  $xcadena.= "UN MILLON ";
               else
                  $xcadena.= " MILLONES ";
               break;
            case 2:
               if ($xcifra < 1 )
                  {
                  $xcadena = "CERO $xdecimales/100 DÓLARES";
                  }
               if ($xcifra >= 1 && $xcifra < 2)
                  {
                  $xcadena = "UN $xdecimales/100 DÓLAR";
                  }
               if ($xcifra >= 2)
                  {
                  $xcadena.= "$xdecimales/100 DÓLARES"; //
                  }
               break;
            } // endswitch ($xz)
         } // ENDIF (trim($xaux) != "")
      // ------------------      en este caso, para México se usa esta leyenda     ----------------
      $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
      $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
      $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
      $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
      $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
      $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
      $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
   } // ENDFOR ($xz)
   return trim($xcadena);
} // END FUNCTION
 
 
function subfijo($xx)
   { // esta función regresa un subfijo para la cifra
   $xx = trim($xx);
   $xstrlen = strlen($xx);
   if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
      $xsub = "";
   //
   if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
      $xsub = "MIL";
   //
   return $xsub;
   } // END FUNCTION
?>
