<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

$x2 = $_POST['columna'];
$y2 = $_POST['fila'];
$nivel = $_POST['nivel'];


$c_ordenes = "
SELECT t4.`usuario` AS 'nombre_agencia', t3.`x` , t3.`y` , `codigo_orden` , `codigo_contenedor` , `tipo_contenedor` , t2.`visual` , t2.`cobro` , t2.`afinidad`, t2.`nombre`, `codigo_agencia` , `codigo_posicion` , t1.`nivel` , `clase` , `tara` , `chasis` , `transportista_ingreso` , `transportista_egreso` , `buque_ingreso` , `buque_egreso` , `cheque_ingreso` , `cheque_egreso` , `cepa_salida` , `arivu_ingreso` , `observaciones_egreso` , `observaciones_ingreso` , `destino` , `estado` , `fechatiempo_ingreso` , `fechatiempo_egreso` , `ingresado_por`
FROM `opsal_ordenes` AS t1
LEFT JOIN `opsal_tipo_contenedores` AS t2
USING ( tipo_contenedor )
LEFT JOIN `opsal_posicion` AS t3
USING ( codigo_posicion )
LEFT JOIN `opsal_usuarios` AS t4
ON t4.`codigo_usuario` = t1.`codigo_agencia`
WHERE `estado` = 'dentro' AND t3.x2='$x2' AND t3.y2='$y2' AND t1.nivel='$nivel'
";
$r_ordenes = db_consultar($c_ordenes);
$buffer = '';
if (mysqli_num_rows($r_ordenes) > 0)
{
    $f = mysqli_fetch_assoc($r_ordenes);
    
    $buffer .= 'Naviera: <b>'.$f['nombre_agencia'].'</b><br />';
    $buffer .= 'Tipo: <b>'.$f['nombre'].'</b><br />';
    $buffer .= 'Clase: <b>'.$f['clase'].'</b><br />';
    $buffer .= 'Serial: <b>'.$f['codigo_contenedor'].'</b><br />';
    $buffer .= 'Ingreso: <b>'.$f['fechatiempo_ingreso'].'</b><br />';
} else {
    
    $f['codigo_orden'] = 0;
}

$json['resultados'] = $buffer;
$json['codigo_orden'] = $f['codigo_orden'];

echo json_encode($json);
?>