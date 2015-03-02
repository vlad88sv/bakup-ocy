<?php
$c = 'SELECT `tarjeta_de`, `tarjeta_para`, `nombre_completo`, `codigo_compra`, `salt`, `accion`, DATE_FORMAT(`timestamp`,"%a %e %H:%i") Fecha, valor_anterior FROM `flores_registro` LEFT JOIN `flores_usuarios` USING (codigo_usuario) LEFT JOIN `flores_SSL_compra_contenedor` USING(codigo_compra) WHERE codigo_compra="'.$_GET['cc'].'" ORDER BY timestamp DESC';
$r = db_consultar($c);

if (mysqli_num_rows($r) > 0)
{
    $tabla = '';
    $tabla .= '<table class="tabla-estandar zebra tabla-una-linea">';
    while ($ft = mysqli_fetch_assoc($r))
    {
        $tabla .= sprintf('<tr><td><a title="De: %s | Para: %s" href="ventas?c=%s">%s</a></td><td><acronym title="Por: %s">%s</acronym></td><td>%s</td></tr>',$ft['tarjeta_de'], $ft['tarjeta_para'],$ft['codigo_compra'],$ft['codigo_compra'].$ft['salt'], $ft['nombre_completo'], $ft['Fecha'], $ft['accion']);
    }
    $tabla .= '</table>';
    echo $tabla;
} else {
    echo 'No hay registros';
}
?>