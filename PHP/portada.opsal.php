<h1 class="opsal_titulo">OPSAL - Contenedores con atención</h1>
<p>Catalogan como contenedores con atención aquellos que tengan menos de 30 días restantes de ARIVU o aquellos con mas de 60 días en patio.</p>
<?php
$agencia = (!empty($_GET['codigo_agencia']) ? ' AND codigo_agencia="'.$_GET['codigo_agencia'] .'"' : '');

echo '<form action="" method="get">Mostrar alertas para <select name="codigo_agencia"><option value="">Todas las agencias</option>'.db_ui_opciones('codigo_usuario','usuario','opsal_usuarios','WHERE nivel="agencia"','','',@$_GET['codigo_agencia']).'</select> <input type="submit" value="Filtrar"></form><br /><hr />';
$c = 'SELECT t1.clase, usuario AS "naviera", CONCAT( x2,  "-", y2,  "-", t1.nivel ) AS "posicion", `codigo_contenedor` AS "contenedor", tipo_contenedor, DATE(  `fechatiempo_ingreso` ) AS  "fecha_ingreso", (DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) +1) AS  "dias_en_patio", `arivu_ingreso`, ( `arivu_ingreso` + INTERVAL 89 DAY) AS "expiracion_arivu", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY, NOW( ) ) AS  "dias_expiracion_arivu", observaciones_ingreso FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE estado =  "dentro" '.$agencia.' AND (DATEDIFF( NOW( ) , `arivu_ingreso`) > 60 OR DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) > 60) ORDER BY `codigo_agencia`, `fechatiempo_ingreso` ASC';
$resultado = db_consultar($c);

echo '<div class="exportable" rel="Contenedores con atención al '.date('Y-m-d Hi').'">';
echo '<table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro">';
echo '<tr><th>Naviera</th><th>Contenedor</th><th>Tipo</th><th>Clase</th><th>Posición</th><th>Recepción</th><th><acronym title="Días en patio">DEP</acronym></th><th>Exp. ARIVU</th><th><acronym title="Días para expiración de ARIVU">DPEA</acronym></th><th style="width:500px;">Observaciones</th></tr>';
while ($f = mysqli_fetch_assoc($resultado))
{
    echo '<tr>';
    echo '<td>'.$f['naviera'].'</td>';
    echo '<td><a href="#" rel="'.$f['contenedor'].'" class="ejecutar_busqueda_codigo_contenedor">'.$f['contenedor'].'</a></td>';
    echo '<td>'.$f['tipo_contenedor'].'</td>';
    echo '<td>'.$f['clase'].'</td>';
    echo '<td>'.$f['posicion'].'</td>';
    echo '<td>'.$f['fecha_ingreso'].'</td>';
    echo '<td>'.($f['dias_en_patio'] >= 60 ? '<b style="color:red">'.$f['dias_en_patio'].'</b>' : $f['dias_en_patio']) .'</td>';
    echo '<td>'.($f['expiracion_arivu'] != '0000-00-00' ? $f['arivu_ingreso'] . ' - ' . $f['expiracion_arivu'] : 'No disponible').'</td>';
    echo '<td>'.($f['dias_expiracion_arivu'] && $f['dias_expiracion_arivu'] <= 30 && $f['dias_expiracion_arivu'] >= 0  ? ' <b style="color:red">'.$f['dias_expiracion_arivu'].'</b>' : $f['dias_expiracion_arivu']) . ($f['dias_expiracion_arivu'] < 0 ? ' <b style="color:red">¿ERROR?</b>' : '') .'</td>';
    echo '<td>'. ellipsis ($f['observaciones_ingreso'], 70) .'</td>';
    echo '</tr>';
}
echo '</div>';
?>