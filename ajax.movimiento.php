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

    
    // Obtengamos el codigo_posicion de origen
    $c_posicion = 'SELECT codigo_posicion FROM opsal_posicion WHERE x2="'.$_POST['posicion_columna'].'" AND y2="'.$_POST['posicion_fila'].'"';
    $r_posicion = db_consultar($c_posicion);
    if (mysqli_num_rows($r_posicion) > 0)
    {
        $b_posicion = mysqli_fetch_assoc($r_posicion);
        $codigo_posicion_origen = $b_posicion['codigo_posicion'];
    }
    
    // Obtengamos el codigo_orden para ese codigo de posicion y ese nivel
    $codigo_orden = db_obtener('opsal_ordenes','codigo_orden','estado="dentro" AND codigo_posicion="'.$codigo_posicion_origen.'" AND nivel="'.$_POST['posicion_nivel'].'"');

    // Obtengamos el codigo_posicion de destino
    $c_posicion = 'SELECT codigo_posicion FROM opsal_posicion WHERE x2="'.$_POST['posicion_columna_2'].'" AND y2="'.$_POST['posicion_fila_2'].'"';
    $r_posicion = db_consultar($c_posicion);
    if (mysqli_num_rows($r_posicion) > 0)
    {
        $b_posicion = mysqli_fetch_assoc($r_posicion);
        $codigo_posicion_destino = $b_posicion['codigo_posicion'];
    }
    
    // Actualizamos la posicion en la orden
    $DATOS['nivel'] = $_POST['posicion_nivel_2'];
    $DATOS['codigo_posicion'] = $codigo_posicion_destino;    
    $ractualizado = db_actualizar_datos ('opsal_ordenes',$DATOS, 'codigo_orden="'.$codigo_orden.'"');
    
    // Chequeamos si se pudo actualizar
    if ($ractualizado > 0)
    {
        unset($DATOS);
        $DATOS['codigo_posicion'] = $codigo_posicion_destino;
        $DATOS['cobrar_a'] = $codigo_agencia;
        $DATOS['codigo_orden'] = $codigo_orden;
        $DATOS['cheque'] = $_POST['cheque'];
        $DATOS['fechatiempo'] = $_POST['fechatiempo'];
        $DATOS['nivel'] = $_POST['posicion_nivel'];
        $DATOS['codigo_usuario'] = _F_usuario_cache('codigo_usuario');
        $DATOS['motivo'] = 'remocion';
        $DATOS['flag_traslado'] = $_POST['traslado'];
        $DATOS['observacion'] = $_POST['observaciones'];
        
        db_agregar_datos('opsal_movimientos',$DATOS);
        
        registrar('Contenedor (ID: <b>'.$codigo_orden.'</b>) cambió de <b>'.$_POST['posicion_columna'].$_POST['posicion_fila'].'-'.$_POST['posicion_nivel'].'</b> a <b>'.$_POST['posicion_columna_2'].$_POST['posicion_fila_2'].'-'.$_POST['posicion_nivel_2'].'</b>','movimiento',$codigo_orden);
        
        echo 'ok';
    }
}
?>