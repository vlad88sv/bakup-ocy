<h1>Estado de cuenta</h1>
<?php
$fecha_inicio = (@$_GET['fecha_inicio'] ?: date('Y-m-01'));
$fecha_final = (@$_GET['fecha_final'] ?: date("Y-m-t"));
?>
<form action="" method="get">
Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="<?php echo $fecha_final; ?>" /> <input type="submit" value="Filtrar" />
</form>
<?php

$totales = "SUM((CASE WHEN categoria IN ('fact_almacenaje','fact_almacenaje_20','fact_almacenaje_40') THEN total_sin_iva ELSE 0 END)) AS t_almacenaje, SUM((CASE WHEN categoria IN ('fact_movimientos','fact_estibas') THEN total_sin_iva ELSE 0 END)) AS t_estiba, SUM((CASE WHEN categoria IN ('fact_desestibas') THEN total_sin_iva ELSE 0 END)) AS t_desestiba, SUM((CASE WHEN categoria IN ('fact_remociones') THEN total_sin_iva ELSE 0 END)) AS t_remocion, SUM((CASE WHEN categoria IN ('fact_dt') THEN total_sin_iva ELSE 0 END)) AS t_dt, SUM((CASE WHEN categoria IN ('supervision') THEN total_sin_iva ELSE 0 END)) AS t_supervision, SUM((CASE WHEN categoria IN ('marchamos') THEN total_sin_iva ELSE 0 END)) AS t_revision, SUM((CASE WHEN categoria IN ('fact_elaboracion_condicion','fact_elaboracion_condicion_lleno') THEN total_sin_iva ELSE 0 END)) AS t_condicion, SUM((CASE WHEN categoria IN ('fact_elaboracion_condicion_vacio') THEN total_sin_iva ELSE 0 END)) AS t_condicion_vacio, SUM((CASE WHEN categoria IN ('fact_consolidado','fact_otros','fact_lineas') THEN total_sin_iva ELSE 0 END)) AS t_otros, SUM(t1.total_sin_iva) AS t_total";
$fiscales = "((CASE WHEN categoria IN ('fact_almacenaje','fact_almacenaje_20','fact_almacenaje_40') THEN numero_fiscal ELSE 0 END)) AS f_almacenaje, ((CASE WHEN categoria IN ('fact_movimientos','fact_estibas') THEN numero_fiscal ELSE 0 END)) AS f_estiba, ((CASE WHEN categoria IN ('fact_desestibas') THEN numero_fiscal ELSE 0 END)) AS f_desestiba, ((CASE WHEN categoria IN ('fact_remociones') THEN numero_fiscal ELSE 0 END)) AS f_remocion, ((CASE WHEN categoria IN ('fact_dt') THEN numero_fiscal ELSE 0 END)) AS f_dt, ((CASE WHEN categoria IN ('supervision') THEN numero_fiscal ELSE 0 END)) AS f_supervision, SUM((CASE WHEN categoria IN ('marchamos') THEN numero_fiscal ELSE 0 END)) AS f_revision, SUM((CASE WHEN categoria IN ('fact_elaboracion_condicion','fact_elaboracion_condicion_lleno') THEN numero_fiscal ELSE 0 END)) AS f_condicion, SUM((CASE WHEN categoria IN ('fact_elaboracion_condicion_vacio') THEN numero_fiscal ELSE 0 END)) AS f_condicion_vacio, SUM((CASE WHEN categoria IN ('fact_consolidado','fact_otros') THEN numero_fiscal ELSE 0 END)) AS f_otros";
$c = "SELECT t2.usuario, t1.grupo, t1.periodo_inicio, t1.periodo_final, $totales, $fiscales FROM facturas AS t1 LEFT JOIN opsal_usuarios AS t2 ON t1.codigo_agencia=t2.codigo_usuario WHERE flag_eliminada=0 AND flag_anulada=0 AND t1.periodo_final BETWEEN '$fecha_inicio' AND '$fecha_final' GROUP BY codigo_agencia, t1.grupo,t1.periodo_inicio, t1.periodo_final ORDER BY codigo_agencia, t1.periodo_inicio ASC, t1.periodo_final ASC, t1.grupo";
$r = db_consultar($c);


$t['t_almacenaje'] = 0;
$t['t_estiba'] = 0;
$t['t_desestiba'] = 0;
$t['t_dt'] = 0;
$t['t_remocion'] = 0;
$t['t_supervision'] = 0;
$t['t_revision'] = 0;
$t['t_condicion'] = 0;
$t['t_condicion_vacio'] = 0;
$t['t_otros'] = 0;
$t['t_total'] = 0;

$st['t_almacenaje'] = 0;
$st['t_estiba'] = 0;
$st['t_desestiba'] = 0;
$st['t_dt'] = 0;
$st['t_remocion'] = 0;
$st['t_supervision'] = 0;
$st['t_revision'] = 0;
$st['t_condicion'] = 0;
$st['t_condicion_vacio'] = 0;
$st['t_otros'] = 0;
$st['t_total'] = 0;

$usuario_anterior = '';

echo '<div class="exportable" style="overflow-x:auto;" rel="Estado de cuentas '.$fecha_inicio.' - '.$fecha_final.'">';
echo '<table id="estado_cuenta" class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro tabla-una-linea">';
echo '<tr><th>Agencia</th><th>Concepto</th><th>Periodo</th><th>Almacenaje</th><th>Estiba</th><th>Desestiba</th><th>Remocion</th><th>DT</th><th>Supervision</th><th>Revision</th><th>Cond Ll</th><th>Cond Vc</th><th>Otros</th><th>Total</th><th>Alm</th><th>Est</th><th>Des</th><th>Rem</th><th>DT</th><th>OPS</th><th>March</th><th>Cond Ll</th><th>Cond Vc</th><th>Otros</th></tr>';


$i_objetivo = mysqli_num_rows($r);
for( $i = 0; $i <= $i_objetivo; $i++ )
{
    $f['usuario'] = '';
    
    if ( $i < $i_objetivo )
        $f = mysqli_fetch_assoc($r);
    
    if ( $usuario_anterior && $f['usuario'] != $usuario_anterior )
    {   
        echo '<tr><th colspan="3" style="text-align:right;">Subtotal:&nbsp;</th><th class="numerica">'.dinero($st['t_almacenaje']).'</th><th class="numerica">'.dinero($st['t_estiba']).'</th><th class="numerica">'.dinero($st['t_desestiba']).'</th><th class="numerica">'.dinero($st['t_remocion']).'</th><th class="numerica">'.dinero($st['t_dt']).'</th><th class="numerica">'.dinero($st['t_supervision']).'</th><th class="numerica">'.dinero($st['t_revision']).'</th><th class="numerica">'.dinero($st['t_condicion']).'</th><th class="numerica">'.dinero($st['t_condicion_vacio']).'</th><th class="numerica">'.dinero($st['t_otros']).'</th><th class="numerica">'.dinero($st['t_total']).'</th><th colspan="10"></th></tr>';
        $st['t_almacenaje'] = 0;
        $st['t_estiba'] = 0;
        $st['t_desestiba'] = 0;
        $st['t_dt'] = 0;
        $st['t_remocion'] = 0;
        $st['t_supervision'] = 0;
        $st['t_revision'] = 0;
        $st['t_condicion'] = 0;
        $st['t_condicion_vacio'] = 0;
        $st['t_otros'] = 0;
        $st['t_total'] = 0;
        
        if ($i == $i_objetivo) break;
    }

    $usuario_anterior = $f['usuario'];

    $t['t_almacenaje'] += $f['t_almacenaje'];
    $t['t_estiba'] += $f['t_almacenaje'];
    $t['t_desestiba'] += $f['t_desestiba'];
    $t['t_dt'] += $f['t_dt'];
    $t['t_remocion'] += $f['t_remocion'];
    $t['t_supervision'] += $f['t_supervision'];
    $t['t_revision'] += $f['t_revision'];
    $t['t_condicion'] += $f['t_condicion'];
    $t['t_condicion_vacio'] += $f['t_condicion_vacio'];
    $t['t_otros'] += $f['t_otros'];
    $t['t_total'] += $f['t_total'];

    $st['t_almacenaje'] += $f['t_almacenaje'];
    $st['t_estiba'] += $f['t_almacenaje'];
    $st['t_desestiba'] += $f['t_desestiba'];
    $st['t_dt'] += $f['t_dt'];
    $st['t_remocion'] += $f['t_remocion'];
    $st['t_supervision'] += $f['t_supervision'];
    $st['t_revision'] += $f['t_revision'];
    $st['t_condicion'] += $f['t_condicion'];
    $st['t_condicion_vacio'] += $f['t_condicion_vacio'];
    $st['t_otros'] += $f['t_otros'];
    $st['t_total'] += $f['t_total'];
           
    echo '<tr><td>'.$f['usuario'].'</td><td>'.$f['grupo'].'</td><td>'.$f['periodo_inicio']. ' - ' . $f['periodo_final'].'</td><td class="numerica">'.dinero($f['t_almacenaje']).'</td><td class="numerica">'.dinero($f['t_estiba']).'</td><td class="numerica">'.dinero($f['t_desestiba']).'</td><td class="numerica">'.dinero($f['t_remocion']).'</td><td class="numerica">'.dinero($f['t_dt']).'</td><td class="numerica">'.dinero($f['t_supervision']).'</td><td class="numerica">'.dinero($f['t_revision']).'</td><td class="numerica">'.dinero($f['t_condicion']).'</td><td class="numerica">'.dinero($f['t_condicion_vacio']).'</td><td class="numerica">'.dinero($f['t_otros']).'</td><td class="numerica">'.dinero($f['t_total']).'</td><td>'.$f['f_almacenaje'].'</td><td>'.$f['f_estiba'].'</td><td>'.$f['f_desestiba'].'</td><td>'.$f['f_remocion'].'</td><td>'.$f['f_dt'].'</td><td>'.$f['f_supervision'].'</td><td>'.$f['f_revision'].'</td><td>'.$f['f_condicion'].'</td><td>'.$f['f_condicion_vacio'].'</td><td>'.$f['f_otros'].'</td></tr>';
    
    $usuario_anterior = $f['usuario'];
    
}
echo '<tr><th colspan="3" style="text-align:right;">Totales:&nbsp;</th><th class="numerica">'.dinero($t['t_almacenaje']).'</th><th class="numerica">'.dinero($t['t_estiba']).'</th><th class="numerica">'.dinero($t['t_desestiba']).'</th><th class="numerica">'.dinero($t['t_remocion']).'</th><th class="numerica">'.dinero($t['t_dt']).'</th><th class="numerica">'.dinero($t['t_supervision']).'</th><th class="numerica">'.dinero($t['t_revision']).'</th><th class="numerica">'.dinero($t['t_condicion']).'</th><th class="numerica">'.dinero($t['t_condicion_vacio']).'</th><th class="numerica">'.dinero($t['t_otros']).'</th><th class="numerica">'.dinero($t['t_total']).'</th><th colspan="10"></th></tr>';
echo '</table>';
echo '</div>';

?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0});
    });
</script>