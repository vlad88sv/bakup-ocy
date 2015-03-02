<?php

if (isset($_POST['procesar_cambio']))
{
    if (empty($_POST['origen']))
    {
        echo '<p>ERROR: buque origen esta vacio</p>';
        return;
    }
    
    if (empty($_POST['destino']))
    {
        echo '<p>ERROR: buque destino esta vacio</p>';
        return;
    }
    
    $c = 'UPDATE opsal_ordenes SET buque_egreso="'.db_codex($_POST['destino']).'" WHERE buque_egreso="'.db_codex($_POST['origen']).'"';
    db_consultar($c);
    
    echo '<p>CAMBIO PROCESADO - SE MODIFICARON ' . db_afectados() . ' CONTENEDORES</p><hr />';
}

$c = 'SELECT buque_egreso, codigo_agencia, COUNT(*) AS "cantidad", CONCAT(DATE_FORMAT(MIN(`fechatiempo_egreso`),"%e/%b/%y"), " al ", DATE_FORMAT(MAX(`fechatiempo_egreso`),"%e/%b/%y")) AS "fecha_embarque" FROM opsal_ordenes LEFT JOIN opsal_usuarios ON codigo_agencia=codigo_usuario WHERE tipo_salida="embarque" '.$agencia.' GROUP BY `buque_egreso` ORDER BY MAX(`fechatiempo_egreso`) DESC LIMIT 30';
$r = db_consultar($c);

$options_buque = '<option selected="selected" value="">Seleccione un buque</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_buque .= '<option value="'.$registro['buque_egreso'].'">'.$registro['buque_egreso']. ' - '. $registro['fecha_embarque'] . ' - ['. $registro['cantidad'] .' despachos]</option>';
    }
}
?>
<h1>Herramienta de cambio de nombre de buque de despacho</h1>
<p style="color:red;font-weight:bold;">ADVERTENCIA: esta utilidad es para combinar o renombrar despachos via embarque por completo. Esta acción no se puede deshacer por lo que debe realizarse con extrema precaución y conocimiento de esta herramienta.</p>

<h2>Opción 1: combinar embarques</h2>
<form action="" method="post">
    <select name="origen"><?php echo $options_buque; ?></select>&nbsp;
    <input type="submit" name="procesar_cambio" value="combinar con" />&nbsp;
    <select name="destino"><?php echo $options_buque; ?></select>&nbsp;
</form>
<hr />
<br />
<h2>Opción 2: renombrar buque</h2>
<form action="" method="post">
    <select name="origen"><?php echo $options_buque; ?></select>&nbsp;
    <input type="submit" name="procesar_cambio" value="renombrar a" />&nbsp;
    <input name="destino" type="text" value="" style="width:385px;" />
</form>
<hr />
<br />