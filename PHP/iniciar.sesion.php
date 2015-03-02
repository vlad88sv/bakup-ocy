<?php
$mensaje = '';

if (isset($_POST['iniciar_proceder']))
{
    
    ob_start();
    $ret = _F_usuario_acceder($_POST['iniciar_campo_usuario'],$_POST['iniciar_campo_clave']);
    $buffer = ob_get_clean();
    if ($ret != 1)
    {
        $mensaje .= '<p style="color:red;">Verifique sus credenciales de acceso.</p>';
    }
}

if (S_iniciado())
{
    if (!empty($_POST['iniciar_retornar']))
    {
        header("location: ".$_POST['iniciar_retornar']);
    } else {
        header("location: ./");
    }
    return;
}

$HEAD_titulo = PROY_NOMBRE . ' - '. _('inicio de sesión');

if (isset($_GET['ref']))
    $_POST['iniciar_retornar'] = $_GET['ref'];
$retorno = empty($_POST['iniciar_retornar']) ? PROY_URL : $_POST['iniciar_retornar'];
?>
<form id="opsal_inicio" method="POST" action="<?php echo PROY_URL ;?>" style="border:2px solid #CCC;border-radius:10px;width:500px;margin:10px auto;padding:100px 0;">
    <h1 style="color:#555;text-align:center;font-size:1.5em;"><?php echo NOMBRE_CORTO; ?></h1>
    <?php echo $mensaje; ?>
    <input type="hidden" value="<?php echo $retorno; ?>" style="" class="" name="iniciar_retornar">
    <table id="opsal_tabla_inicio">
        <tbody>
            <tr><td><?php echo _('Usuario'); ?></td><td><input type="text" value="" style="width:280px;" class="text_redondo" name="iniciar_campo_usuario"></td></tr>
            <tr><td><?php echo _('Clave'); ?></td><td><input type="password" value="" style="width:280px;" class="text_redondo" name="iniciar_campo_clave"></td></tr>
        </tbody>
    </table>
    <input type="submit" value="<?php echo _('Iniciar'); ?>" style="" class="" name="iniciar_proceder">
</form>
<hr />