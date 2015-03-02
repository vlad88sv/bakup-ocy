<?php
$fecha = (empty($_GET['fecha']) ? mysql_date() : $_GET['fecha']);

$c = 'SELECT contexto FROM opsal_bitacora GROUP BY contexto';
$r = db_consultar($c);

$options_contexto = '<option selected="selected" value="">Cualquier contexto</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_contexto .= '<option value="'.$registro['contexto'].'">'.$registro['contexto'].'</option>';
    }
}

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel<>"agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_usuarios = '<option selected="selected" value="">Cualquier usuario</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_usuarios .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<h1 class="opsal_titulo">Bitácora</h1>
<form action="/bitacora.html" method="get">
    Fecha: <input type="text" class="calendario" name="fecha" value="" /> Contexto: <select name="contexto"><?php echo $options_contexto; ?></select> Usuario: <select name="codigo_usuario"><?php echo $options_usuarios; ?></select> <input type="submit" value="filtrar" />
</form>
<hr />
<?php
$codigo_usuario = (empty($_GET['codigo_usuario']) ? '' : ' AND `codigo_usuario`=' . $_GET['codigo_usuario']);
$contexto = (empty($_GET['contexto']) ? '' : ' AND `contexto`="' . $_GET['contexto'].'"');
$c = "SELECT `codigo_bitacora` AS 'Código', `fechatiempo` AS 'Hora', `contenido` AS 'Contenido', `contexto` AS 'Contexto', CONCAT('<a href=\"#\" rel=\"',codigo_contenedor,'\" class=\"ejecutar_busqueda_codigo_contenedor\">',`codigo_contenedor`,'</a>') AS 'Contenedor', `usuario` AS 'Usuario' FROM `opsal_bitacora` LEFT JOIN `opsal_usuarios` USING (codigo_usuario) LEFT JOIN opsal_ordenes ON opsal_bitacora.ID = opsal_ordenes.codigo_orden WHERE DATE(`fechatiempo`) = '$fecha' $codigo_usuario $contexto ORDER BY fechatiempo DESC";
$resultado = db_consultar($c);
echo db_ui_tabla($resultado,'class="opsal_tabla_ancha"');
?>
<script type="text/javascript">
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>