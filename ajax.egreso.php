<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (isset($_POST['guardar']))
{
    $codigo_orden = $_POST['codigo_orden'];
    $codigo_agencia = db_obtener('opsal_ordenes','codigo_agencia','codigo_orden="'.$codigo_orden.'"');
    
    $estado = db_obtener('opsal_ordenes','estado','codigo_orden="'.$codigo_orden.'"');
    
    if ($estado == 'fuera')
    {
        error_log('Se intentó despachar un contenedor que estaba fuera');
        return;
    }
    
    
    $DATOS = array_intersect_key($_POST,array_flip(array('cheque_egreso','chasis_egreso','transportista_egreso','buque_egreso','observaciones_egreso','fechatiempo_egreso','destino','eir_egreso','chofer_egreso','tipo_salida','booking_number','egreso_marchamo')));
    $DATOS['estado'] = 'fuera';
    $DATOS['egresado_por'] = _F_usuario_cache('codigo_usuario');
    
    db_actualizar_datos ('opsal_ordenes',$DATOS,'codigo_orden='.$codigo_orden);
    
    enviar_edi($codigo_orden);
    
    unset($DATOS);
    $DATOS['codigo_posicion'] = 0;
    $DATOS['nivel'] = 0;
    $DATOS['cobrar_a'] = $codigo_agencia;
    $DATOS['motivo'] = 'desestiba';
    $DATOS['fechatiempo'] = $_POST['fechatiempo_egreso'];
    $DATOS['codigo_orden'] = $codigo_orden;
    $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
    $DATOS['cheque'] = $_POST['cheque_egreso'];
    
    db_agregar_datos('opsal_movimientos',$DATOS);
    
    registrar('Salida de contenedor (ID: <b>'.$codigo_orden.'</b>) en <b>'.$_POST['posicion_columna'].'-'.$_POST['posicion_fila'].'-'.$_POST['posicion_nivel'].'</b>','egreso',$codigo_orden);
    echo '<hr /><p class="opsal_notificacion">Contenedor egresado exitosamente.</p><hr />';
}
?>