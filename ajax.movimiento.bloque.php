<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (isset($_POST['guardar']))
{
    // Obtengamos el codigo_posicion de origen
    $c_posicion = 'SELECT codigo_posicion FROM opsal_posicion WHERE x2="'.$_POST['posicion_columna'].'" AND y2="'.$_POST['posicion_fila'].'"';
    $r_posicion = db_consultar($c_posicion);
    if (mysqli_num_rows($r_posicion) > 0)
    {
        $b_posicion = mysqli_fetch_assoc($r_posicion);
        $codigo_posicion_origen = $b_posicion['codigo_posicion'];
        
        // Obtengamos el codigo_posicion de destino
        $c_posicion = 'SELECT codigo_posicion FROM opsal_posicion WHERE x2="'.$_POST['posicion_columna_2'].'" AND y2="'.$_POST['posicion_fila_2'].'"';
        $r_posicion = db_consultar($c_posicion);
        if (mysqli_num_rows($r_posicion) > 0)
        {
            $b_posicion = mysqli_fetch_assoc($r_posicion);
            $codigo_posicion_destino = $b_posicion['codigo_posicion'];
            
            // Actualizamos la posicion en la orden
            $DATOS['codigo_posicion'] = $codigo_posicion_destino;    
            $ractualizado = db_actualizar_datos ('opsal_ordenes',$DATOS, 'estado="dentro" AND codigo_posicion="'.$codigo_posicion_origen.'"');
        }        
    }

}
?>