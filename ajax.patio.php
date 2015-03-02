<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (isset($_POST['guardar']))
{
    $x1 = db_obtener('opsal_posicion','x','x2="'.$_POST['rango_final_col'].'"');
    $x2 = db_obtener('opsal_posicion','x','x2="'.$_POST['rango_inicio_col'].'"');
    $y1 = db_obtener('opsal_posicion','y','y2="'.$_POST['rango_final_fila'].'"');
    $y2 = db_obtener('opsal_posicion','y','y2="'.$_POST['rango_inicio_fila'].'"');

    $c = sprintf('UPDATE `opsal_posicion` SET tipo="%s" WHERE x BETWEEN "%s" AND "%s" AND y BETWEEN "%s" AND "%s"', $_POST['tipo'], $x1, $x2, $y1, $y2);
    db_consultar($c);
}
?>