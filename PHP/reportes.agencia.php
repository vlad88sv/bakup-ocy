<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Seleccione una</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}

if (!empty($_POST['codigo_agencia']))
{

    // REPORTE de tipo de contenedores por patio
    $c = '
    SELECT t3.nombre AS  "Tipo de contenedor", COUNT(*) AS  "Cantidad"
    FROM  `opsal_ordenes` AS t1
    LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
    LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
    WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.db_codex($_POST['codigo_agencia']).'" AND
    t1.fechatiempo_ingreso >= "'.$_POST['fecha_inicio'].'" AND
    t1.fechatiempo_ingreso <= "'.$_POST['fecha_final'].'"
    GROUP BY t1.tipo_contenedor
    ';
    
    $rTipos = db_consultar($c);
    
    $tTipos = 0;
    
    while ($f = mysqli_fetch_array($rTipos))
    {
        $tTipos += $f[1];
    }
    mysqli_data_seek($rTipos,0);
    
    // REPORTE de clase de contenedores por patio
    $c = '
    SELECT CONCAT("[", t1.clase, "] ", t3.nombre) AS  "Clase", COUNT(*) AS  "Cantidad"
    FROM  `opsal_ordenes` AS t1
    LEFT JOIN  `opsal_usuarios` AS t2 ON t1.codigo_agencia = t2.codigo_usuario
    LEFT JOIN  `opsal_tipo_contenedores` AS t3 ON t3.tipo_contenedor = t1.tipo_contenedor
    WHERE  `estado` =  "dentro" AND t1.codigo_agencia="'.$_POST['codigo_agencia'].'"
    GROUP BY t3.tipo_contenedor, t1.clase
    ';
    
    $rClases = db_consultar($c);

} //--$_POST['codigo_agencia']
?>
<h1>Reporte por agencia</h1>
<form action="" method="post">
    <select id="codigo_agencia" name="codigo_agencia"><?php echo $options_agencia; ?></select>
    <input type="submit" value="Filtrar" />
</form>
<?php
if (empty($_POST['codigo_agencia']))
{
    echo '<p>Favor seleccione una agencia y presione filtrar</p>';
    return false;
}
?>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v1"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rTipos,'class="opsal_tabla_ancha  tabla-estandar"'); ?><br /><p>Total de contenedores: <b><?php echo $tTipos; ?></b></p></td></tr>
</table>
<table class="opsal_tabla_ancha opsal_tabla_borde_oscuro">
    <tr><td style="width:650px;text-align:center;"><div style="width:600px;height:400px;" id="v2"></div></td>
    <td style="vertical-align: top;"><?php echo db_ui_tabla($rClases,'class="opsal_tabla_ancha  tabla-estandar"'); ?></td></tr>
</table>
<br /><hr />
<h1>Contenedores activos (dentro del patio)</h1>
<?php
$c = 'SELECT CONCAT( x2,  "-", y2,  "-", nivel ) AS posicion, DATE(  `fechatiempo_ingreso` ) AS  "Fecha Ingreso", DATEDIFF( NOW( ) ,  `fechatiempo_ingreso` ) AS  "Días en patio", DATE(  `arivu_ingreso` + INTERVAL 89 DAY ) AS "Vencimiento ARIVU", DATEDIFF(  `arivu_ingreso` + INTERVAL 89 DAY , NOW( ) ) AS  "Días para vencimiento ARIVU", `codigo_contenedor` FROM  `opsal_ordenes` AS t1 LEFT JOIN  `opsal_posicion` AS t2 USING ( codigo_posicion ) WHERE estado =  "dentro" AND codigo_agencia="'.$_POST['codigo_agencia'].'" ORDER BY  `fechatiempo_ingreso` ASC';
$resultado = db_consultar($c);
echo db_ui_tabla($resultado,'class="opsal_tabla_ancha tabla-estandar"');
?>
<script type="text/javascript">
  function drawVisualization() {
<?php echo gpie($rTipos,"Tipos de contenedores",'v1','Tipo de contenedor','TEU'); ?>
<?php echo gpie($rClases,"Clase de Contenedores",'v2','Clase de Contenedores','Cantidad'); ?>
  }
  google.setOnLoadCallback(drawVisualization);
</script>