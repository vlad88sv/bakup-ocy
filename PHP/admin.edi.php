<?php
if (empty($_GET['objetivo']) || !is_numeric($_GET['objetivo']))
    return;

$_GET['objetivo'] = db_codex($_GET['objetivo']);

if (isset($_POST['guardar_edi']))
{    
    unset($_POST['guardar_edi']);
    
    $_POST['activo'] = ( isset($_POST['activo']) ? '1' : '0' );
    db_reemplazar_datos('edi', $_POST);   
}


$c = 'SELECT `codigo_usuario`, `activo`, `unb_ver`, `sender_id`, `receiver_id`, `loc165`, `metodo`, `usuario`, `contrasena`, `host`, `dir_work`, `dir_out`, `dir_in` FROM `edi` WHERE codigo_usuario = ' . $_GET['objetivo'];
$r = db_consultar($c);
$f = db_fetch($r);
?>
<h1>Ajustes EDI de #<?php echo $_GET['objetivo']; ?></h1>
<form action="" method="post">
    <input type="hidden" name="codigo_usuario" value="<?php echo $_GET['objetivo']; ?>" />
<table>
    <tr><td>Activo</td><td><input name="activo" type="checkbox" <?php echo (@$f['activo'] == '1' ? 'checked="checked"' : ''); ?> value="1" /></tr>
    <tr><td>UNB Ver.</td><td><input name="unb_ver" type="text" value="<?php echo ( isset($f['unb_ver']) ? $f['unb_ver'] : '2' ); ?>" /></tr>
    <tr><td>UNG</td><td><input name="ung" type="text" value="<?php echo ( isset($f['ung']) ? $f['ung'] : '0' ); ?>" /></tr>
    <tr><td>SENDER ID</td><td><input name="sender_id" type="text" value="<?php echo @$f['sender_id']; ?>" /></tr>
    <tr><td>RECEIVER ID</td><td><input name="receiver_id" type="text" value="<?php echo @$f['receiver_id']; ?>" /></tr>
    <tr><td>LOC 165</td><td><input name="loc165" type="text" value="<?php echo @$f['loc165']; ?>" /></tr>
    <tr><td>Metodo</td><td><select name="metodo"><option value="ftp">FTP</option><option <?php echo ( @$f['metodo'] == 'sftp' ? 'selected="selected"' : '' ); ?>value="sftp">SFTP</option></select></tr>
    <tr><td>Usuario</td><td><input name="usuario" type="text" value="<?php echo @$f['usuario']; ?>" /></td></tr>
    <tr><td>Constraseña</td><td><input name="contrasena" type="text" value="<?php echo @$f['contrasena']; ?>" /></td></tr>
    <tr><td>IP|HOST</td><td><input name="host" type="text" value="<?php echo @$f['host']; ?>" /></td></tr>
    <tr><td>WORK DIR</td><td><input name="dir_work" type="text" value="<?php echo ( isset($f['dir_work']) ? $f['dir_work'] : '/WORK/' ); ?>" /></td></tr>
    <tr><td>OUT DIR</td><td><input name="dir_out" type="text" value="<?php echo ( isset($f['dir_out']) ? $f['dir_out'] : '/OUT/' ); ?>" /></td></tr>
    <tr><td>IN DIR</td><td><input name="dir_in" type="text" value="<?php echo ( isset($f['dir_in']) ? $f['dir_in'] : '/IN/' ); ?>" /></td></tr>
</table>

<input type="submit" name="guardar_edi" value="Guardar" />
</form>
<br /><hr />
<h2>Notas</h2>
<p>1. Versión utilizada por este sistema EDIFACT D95B.</p>
<p>2. Elementos enviados: <span style="font-weight: bold; font-style: italic;">received empty</span> (gate out) y <span style="font-weight: bold; font-style: italic;">dispatched empty for export</span> (gate out).</p>
<p>3. Los tamaños estan expresados según ISO 6346</p>