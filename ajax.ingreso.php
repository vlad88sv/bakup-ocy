<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (isset($_POST['guardar']))
{
    $codigo_agencia = $_POST['codigo_agencia'];
    
    if ( empty($codigo_agencia) )
    {
        echo 'detalle: no hay codigo de agencia';
        return;
    }
    
    // Obtengamos el codigo_posicion (punto inicial para el caso de los de 40, 45 y 48)
    //$_POST = 'pocision_columna','posicion_fila','posicion_nivel'
    $c_posicion = 'SELECT codigo_posicion FROM opsal_posicion WHERE x2="'.$_POST['posicion_columna'].'" AND y2="'.$_POST['posicion_fila'].'"';
    $r_posicion = db_consultar($c_posicion);
    if (mysqli_num_rows($r_posicion) > 0)
    {
        $b_posicion = mysqli_fetch_assoc($r_posicion);
        $codigo_posicion = $b_posicion['codigo_posicion'];
    }

    // Normalización de datos
    $_POST['codigo_contenedor'] = strtoupper ( $_POST['codigo_contenedor'] );
    $_POST['tipo_contenedor'] = $_POST['tipo_contenedor'].$_POST['tamano_contenedor'];
        
            
    $DATOS = array_intersect_key($_POST,array_flip(array('codigo_contenedor','cheque_ingreso','clase','tipo_contenedor','codigo_agencia','tara','chasis','transportista_ingreso','buque_ingreso','cepa_salida','arivu_ingreso','observaciones_ingreso','arivu_referencia','fechatiempo_ingreso','eir_ingreso','ingreso_con_danos','cliente_ingreso','chofer_ingreso','ano_fabricacion','booking_number_ingreso','ingreso_marchamo','BL','puerto_procedencia','buque_arribo_estimado', 'ship_type','codigo_patio')));
    $DATOS['estado'] = 'dentro';
    $DATOS['nivel'] = $_POST['posicion_nivel'];
    $DATOS['codigo_posicion'] = $codigo_posicion;
    $DATOS['ingresado_por'] = _F_usuario_cache('codigo_usuario');
    $DATOS['codigo_patio'] = 1;
    if (isset($_POST['codigo_patio']) && is_numeric($_POST['codigo_patio']))
    {
        $DATOS['codigo_patio'] = $_POST['codigo_patio'];
    }
    
    $codigo_orden = db_agregar_datos('opsal_ordenes',$DATOS);
    
    if ($codigo_orden > 0)
    {
        // Agregamos una estiba
        unset($DATOS);
        $DATOS['motivo'] = 'estiba';
        $DATOS['cobrar_a'] = $codigo_agencia;
        $DATOS['codigo_posicion'] = $codigo_posicion;
        $DATOS['codigo_orden'] = $codigo_orden;
        $DATOS['fechatiempo'] = $_POST['fechatiempo_ingreso'];
        $DATOS['nivel'] = $_POST['posicion_nivel'];
        $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
        $DATOS['cheque'] = $_POST['cheque_ingreso'];
        
        db_agregar_datos('opsal_movimientos',$DATOS);
        
        enviar_edi($codigo_orden);
        
        registrar('Nuevo contenedor (ID: <b>'.$codigo_orden.'</b>) en <b>'.$_POST['posicion_columna'].'-'.$_POST['posicion_fila'].'-'.$_POST['posicion_nivel'].'</b>','ingreso',$codigo_orden);
        echo 'ok';
    }
    
    
}
?>