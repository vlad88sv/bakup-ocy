<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel != "agencia"';
$r = db_consultar($c);

$agencia = '<table class="tabla-estandar opsal_tabla_borde_oscuro">';
$agencia .= '<tbody>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $agencia .= sprintf('<tr><td>%s</td><td>%s</td></tr>',$registro['usuario'],'<a href="/administracion.html?modo=agencias&submodo=editar&objetivo='.$registro['codigo_usuario'].'">Modificar</a> | <a href="/administracion.html?modo=agencias&submodo=eliminar&objetivo='.$registro['codigo_usuario'].'">Eliminar</a>');
    }
}
$agencia .= '</tbody>';
$agencia .= '<thead>';
$agencia .= '<tr><th style="width:150px;">Usuario</th><th>Herramientas</th></tr>';
$agencia .= '</thead>';
$agencia .= '<table>';
?>
<h1 class="opsal_titulo">Control de usuarios</h1>
<hr />
<p>Nota: este lugar no es el indicado para agregar agencias. Use la pestañas agencias para ello.</p>
<hr />
<label for="nombre_cheque">Nombre de usuario</label>&nbsp;
<input style="width:500px;" type="text" name="nombre"/>&nbsp;
<input type="submit" name="guardar" value="Guardar y agregar usuario"/>
<?php echo $agencia; ?>
