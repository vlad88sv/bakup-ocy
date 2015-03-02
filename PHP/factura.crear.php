<?php
$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">naviera</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
?>
<h1>Facturación de otros servicios</h1>
<p>Factura a nombre de: <select name="codigo_agencia"><?php echo $options_agencia; ?></select></p>
<p>Cant. <input type="text" name="cantidad" style="width:20px;" /> concepto: <input type="text" style="width:400px;" name="concepto" /> p.u.: <input type="text" name="pu" style="width:30px;" /> </p>
