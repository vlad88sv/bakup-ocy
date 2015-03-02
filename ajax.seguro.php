<?php
require_once('config.php');
require_once (__PHPDIR__."vital.php");

//error_log('ajax');

if (!S_iniciado())
    return;

//error_log(print_r($_GET,true));
    
if (!empty($_GET['accion']))
{
    $_POST['accion'] = $_GET['accion'];
}

if (empty($_POST['accion'])) return;

switch ($_POST['accion'])
{
    case 'obtener_info_bloque':
        run_part__obtener_info_bloque();
        break;
    
    case 'eliminar_despacho':
        run_part__eliminar_despacho();
        break;
    
    case 'verificar_doble_ingreso':
        run_part__verificar_doble_ingreso();
        break;
    
    case 'obtener_ultimos_buques':
        run_part__obtener_ultimos_buques();
        break;
    
    case 'verificar_doble_ingreso_condicion':
        run_part__verificar_doble_ingreso_condicion();
        break;
    
    case 'eliminar_factura':
        run_part_eliminar_factura();
        break;

    case 'anular_factura':
        run_part_anular_factura();
        break;

    case 'desanular_factura':
        run_part_desanular_factura();
        break;    
    
    case 'guardar_datos_fiscales':
        run_part_guardar_datos_fiscales();
        break;
    
    case 'obtener_tarifas_lineas_amarre':
        run_part_obtener_tarifas_lineas_amarre();
        break;
    
    case 'obtener_ultima_aut_fiscal':
        run_part_obtener_ultima_aut_fiscal();
        break;
}

function run_part_obtener_ultima_aut_fiscal()
{
    //db_obtener()
}

function run_part_obtener_tarifas_lineas_amarre()
{
    $c = 'SELECT `la_supervisor`, `la_muellero`, `la_estibador`, `la_operador`, `la_montacarga`, `la_estiba`, `la_desestiba`, `la_combustible`, `la_transporte` FROM `opsal_tarifas` WHERE `codigo_usuario`="'.db_codex($_POST['codigo_agencia']).'"';
    $r = db_consultar($c);
    $f = db_fetch($r);
    echo json_encode($f);
    return;
}

function run_part_guardar_datos_fiscales()
{
    unset($DATOS);
    
    $DATOS['tipo_fiscal'] = db_codex($_POST['tipo_fiscal']);
    $DATOS['numero_fiscal'] = db_codex($_POST['numero_fiscal']);
    $DATOS['aut_fiscal'] = db_codex($_POST['aut_fiscal']);    
    
    if ( db_contar('facturas', 'aut_fiscal="'.$DATOS['aut_fiscal'].'" AND tipo_fiscal="'.$DATOS['tipo_fiscal'].'" AND numero_fiscal="'.$DATOS['numero_fiscal'].'" AND flag_anulada="0" AND flag_eliminada="0"') > 0 )
    {
        echo "No se guardó. El número fiscal ya existe para el tipo de documento especificado.";
        return;
    }
    
    db_actualizar_datos('facturas', $DATOS, 'uniqid="'.$_POST['uniqid'].'"');
    
    echo 'Se ha guardado la factura';
}

function run_part_desanular_factura()
{
    db_consultar('UPDATE facturas SET flag_anulada=0 WHERE uniqid="'.db_codex($_POST['ID']).'"');   
}


function run_part_anular_factura()
{
    db_consultar('UPDATE facturas SET flag_anulada=1 WHERE uniqid="'.db_codex($_POST['ID']).'"');   
}

function run_part_eliminar_factura()
{
    db_consultar('UPDATE facturas SET flag_eliminada=1 WHERE uniqid="'.db_codex($_POST['ID']).'"');   
}

function run_part__verificar_doble_ingreso_condicion()
{
    $dato = db_obtener('opsal_condiciones','COUNT(*)', 'codigo_contenedor="'.db_codex($_POST['contenedor']).'" AND referencia_papel="'.db_codex($_POST['EIR']).'"');
    
    echo json_encode(array('cantidad' => $dato));
    
}

function run_part__obtener_info_bloque()
{
    //error_log('run_part__obtener_info_bloque');
    $x2 = $_GET['x2'];
    $y2 = $_GET['y2'];
    
    $c = "
    SELECT t4.`usuario` AS 'nombre_agencia', t3.`x` , t3.`y` , t3.`x2`, t3.`y2`, t1.`nivel`, `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , `clase` , `tara` , `chasis` , `transportista_ingreso` , `transportista_egreso` , `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , DATEDIFF(NOW(), `cepa_salida`) AS 'dias_cepa', `arivu_ingreso`, DATEDIFF(NOW(), `arivu_ingreso`) AS 'dias_arivu' , `ingresado_por` , `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , (DATEDIFF(NOW(), `fechatiempo_ingreso`)+1) AS 'dias_ingreso', `fechatiempo_egreso`
    FROM `opsal_ordenes` AS t1
    LEFT JOIN `opsal_tipo_contenedores` AS t2
    USING ( tipo_contenedor )
    LEFT JOIN `opsal_posicion` AS t3
    USING ( codigo_posicion )
    LEFT JOIN `opsal_usuarios` AS t4
    ON t4.`codigo_usuario` = t1.`codigo_agencia`
    WHERE `estado` = 'dentro' AND t3.x2='$x2' AND t3.y2='$y2'
    ORDER BY t1.nivel DESC
    ";
    
    $r = db_consultar($c);

    //error_log($c);
    
    $title = '';
    
    while ($f = db_fetch($r))
    {
        $cepa_salida = ($f['cepa_salida'] == "0000-00-00" ? 'Sin datos' : $f['cepa_salida'].' ['.($f['dias_cepa'] ?: 0).' días desde salida de CEPA]');
        $arivu_ingreso = ($f['dias_arivu'] == "0000-00-00" ? 'Sin datos' : $f['arivu_ingreso'].' ['.$f['dias_arivu'].' días desde el ingreso]');
        
        $title .= '<hr /><p>';
        $title .= '<b>#'.$f['nivel'].'</b> - <b>'.$f['nombre'].'</b> Clase <b>'.$f['clase'].'</b><br />';
        $title .= 'Ingreso: <b>'.$f['fechatiempo_ingreso'].'</b> <b>['.$f['dias_ingreso'].' días]</b><br />';
        $title .= 'ARIVU: <b>'.$arivu_ingreso.'</b><br />';
        $title .= 'CEPA: <b>'.$cepa_salida.'</b><br />';
        $title .= 'Agencia: <b>'.$f['nombre_agencia'].'</b><br />';
        $title .= 'Contenedor: <a href="#" rel="'.$f['codigo_contenedor'].'" class="ejecutar_busqueda_codigo_contenedor"><b>'.$f['codigo_contenedor'].'</b></a> <a  href="#" class="bq_usar_contenedor" col="'.$f['x2'].'" fila="'.$f['y2'].'" nivel="'.$f['nivel'].'">[ Utilizar posición en vista acual ]</a>';
        $title .= '</p>';
    }
    
    echo $title;

}

function run_part__obtener_ultimos_buques()
{
    $c = 'SELECT buque_egreso, COUNT(*) AS "cantidad", CONCAT(DATE(MIN(`fechatiempo_egreso`)), "/", DATE(MAX(`fechatiempo_egreso`))) AS "fecha_embarque" FROM opsal_ordenes WHERE codigo_agencia="'.db_codex($_POST['codigo_agencia']).'" AND tipo_salida="embarque" GROUP BY `buque_egreso` ORDER BY MAX(`fechatiempo_egreso`) DESC LIMIT 10';
    $r = db_consultar($c);
    
    $options_buque = '<option selected="selected" value="">Embarque</option>';
    if (mysqli_num_rows($r) > 0)
    {
        while ($registro = mysqli_fetch_assoc($r))
        {
            $options_buque .= '<option value="'.$registro['buque_egreso'].'">'.$registro['buque_egreso']. ' - '. $registro['fecha_embarque'] . ' - ['. $registro['cantidad'] .' despachos]</option>';
        }
    } else {
        echo '<span style="color:blue;">[sin embarques registrados]</span>';
        return;
    }
    
    echo '<select id="buque" name="buque" style="width:100px;">'.$options_buque.'</select>';
}

function run_part__verificar_doble_ingreso()
{
    $c = 'SELECT COUNT(*) AS cantidad FROM opsal_ordenes WHERE codigo_contenedor="'.db_codex($_POST['contenedor']).'" AND estado="dentro"';
    $r = db_consultar($c);
    
    $f = db_fetch($r);
    
    echo json_encode(array('cantidad' => $f['cantidad']));
}

function run_part__eliminar_despacho()
{
    $c = 'DELETE FROM opsal_movimientos WHERE motivo="desestiba" AND codigo_orden="'.db_codex($_POST['ID']).'" LIMIT 1';
    $r = db_consultar($c);
    
    $c = 'UPDATE opsal_ordenes SET eir_egreso="", transportista_egreso="", chofer_egreso="", observaciones_egreso="", destino="", estado="dentro", cheque_egreso="", chasis_egreso="", fechatiempo_egreso=NULL, egresado_por=0 WHERE codigo_orden="'.db_codex($_POST['ID']).'" LIMIT 1';
    $r = db_consultar($c);
    
    registrar('Se ha eliminado el despacho de un contenedor (ID: <b>'.db_codex($_POST['ID']).'</b>).','eliminacion.despacho');
}
?>