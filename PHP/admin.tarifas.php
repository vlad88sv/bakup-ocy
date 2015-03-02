<?php
if (empty($_GET['objetivo']) || !is_numeric($_GET['objetivo']))
    return;

$_GET['objetivo'] = db_codex($_GET['objetivo']);



if (isset($_POST['guardar_tarifas']))
{    
    unset($_POST['guardar_tarifas']);
    db_reemplazar_datos('opsal_tarifas', $_POST);   
}

$c = 'SELECT `codigo_usuario`, remociones_gratis_mes, dias_libres_2040, `p_elaboracion_condiciones`, `p_almacenaje_20`, `p_almacenaje_40`, `p_estiba_20`, `p_estiba_40`, `p_embarque_desestiba_20`, `p_embarque_desestiba_40`, `p_terrestre_desestiba_20`, `p_terrestre_desestiba_40`, `p_remocion`, `p_doble_transferencia`, `multiplicador_remociones`, `remocion_como_doble_movimiento`, `la_supervisor`, `la_muellero`, `la_estibador`, `la_operador`, `la_montacarga`, `la_estiba`, `la_desestiba`, `la_combustible`, `la_transporte` FROM `opsal_tarifas` WHERE codigo_usuario = ' . $_GET['objetivo'];
$r = db_consultar($c);
$f = db_fetch($r);
?>
<h1>Tarifas de cliente</h1>
<form action="" method="post">
    <input type="hidden" name="codigo_usuario" value="<?php echo $_GET['objetivo']; ?>" />
    
    <table>
    <tr><td>p_almacenaje_20</td><td><input name="p_almacenaje_20" type="text" value="<?php echo $f['p_almacenaje_20']; ?>" /></td></tr>
    <tr><td>p_almacenaje_40</td><td><input name="p_almacenaje_40" type="text" value="<?php echo $f['p_almacenaje_40']; ?>" /></td></tr>
    <tr><td>p_estiba_20</td><td><input name="p_estiba_20" type="text" value="<?php echo $f['p_estiba_20']; ?>" /></td></tr>
    <tr><td>p_estiba_40</td><td><input name="p_estiba_40" type="text" value="<?php echo $f['p_estiba_40']; ?>" /></td></tr>
    <tr><td>p_embarque_desestiba_20</td><td><input name="p_embarque_desestiba_20" type="text" value="<?php echo $f['p_embarque_desestiba_20']; ?>" /></td></tr>
    <tr><td>p_embarque_desestiba_40</td><td><input name="p_embarque_desestiba_40" type="text" value="<?php echo $f['p_embarque_desestiba_40']; ?>" /></td></tr>
    <tr><td>p_terrestre_desestiba_20</td><td><input name="p_terrestre_desestiba_20" type="text" value="<?php echo $f['p_terrestre_desestiba_20']; ?>" /></td></tr>
    <tr><td>p_terrestre_desestiba_40</td><td><input name="p_terrestre_desestiba_40" type="text" value="<?php echo $f['p_terrestre_desestiba_40']; ?>" /></td></tr>
    <tr><td>p_doble_transferencia</td><td><input name="p_doble_transferencia" type="text" value="<?php echo $f['p_doble_transferencia']; ?>" /></td></tr>
    <tr><td>p_remocion</td><td><input name="p_remocion" type="text" value="<?php echo $f['p_remocion']; ?>" /></td></tr>
    <tr><td>p_elaboracion_condiciones</td><td><input name="p_elaboracion_condiciones" type="text" value="<?php echo $f['p_elaboracion_condiciones']; ?>" /></td></tr>
    <tr><td>dias_libres_2040</td><td><input name="dias_libres_2040" type="text" value="<?php echo $f['dias_libres_2040']; ?>" /></td></tr>
    <tr><td>remociones_gratis_mes</td><td><input name="remociones_gratis_mes" type="text" value="<?php echo $f['remociones_gratis_mes']; ?>" /></td></tr>
    <tr><td>multiplicador_remociones</td><td><input name="multiplicador_remociones" type="text" value="<?php echo $f['multiplicador_remociones']; ?>" /></td></tr>
    <tr><td>remocion_como_doble_movimiento</td><td><input name="remocion_como_doble_movimiento" type="text" value="<?php echo $f['remocion_como_doble_movimiento']; ?>" /></td></tr>
    <tr><td>la_supervisor</td><td><input name="la_supervisor" type="text" value="<?php echo $f['la_supervisor']; ?>" /></td></tr>
    <tr><td>la_muellero</td><td><input name="la_muellero" type="text" value="<?php echo $f['la_muellero']; ?>" /></td></tr>
    <tr><td>la_estibador</td><td><input name="la_estibador" type="text" value="<?php echo $f['la_estibador']; ?>" /></td></tr>
    <tr><td>la_operador</td><td><input name="la_operador" type="text" value="<?php echo $f['la_operador']; ?>" /></td></tr>
    <tr><td>la_montacarga</td><td><input name="la_montacarga" type="text" value="<?php echo $f['la_montacarga']; ?>" /></td></tr>
    <tr><td>la_estiba</td><td><input name="la_estiba" type="text" value="<?php echo $f['la_estiba']; ?>" /></td></tr>
    <tr><td>la_desestiba</td><td><input name="la_desestiba" type="text" value="<?php echo $f['la_desestiba']; ?>" /></td></tr>
    <tr><td>la_combustible</td><td><input name="la_combustible" type="text" value="<?php echo $f['la_combustible']; ?>" /></td></tr>
    <tr><td>la_transporte</td><td><input name="la_transporte" type="text" value="<?php echo $f['la_transporte']; ?>" /></td></tr>
    </table>

<input type="submit" name="guardar_tarifas" value="Guardar" />
</form>