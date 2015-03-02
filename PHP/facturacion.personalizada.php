<h1>Módulo de facturación personalizada</h1>
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

if (isset($_POST['guardar_documento']))
{    
    $datos = $_POST;
    unset($datos['guardar_documento']);
    
    $datos['modo_facturacion'] = 'otros';
    $datos['tipo_salida'] = 'otra';
    $datos['sin_iva'] = numero2($datos['sin_iva']);
    $datos['iva'] = numero2( ( $datos['sin_iva']  * 1.13) - $datos['sin_iva'] );
    $datos['total'] = numero2( $datos['sin_iva'] * 1.13 );
    
    $UNIQID = uniqid('',true);
    CrearFactura($UNIQID, $_POST['codigo_agencia'], $_POST['categoria'], $datos);
    
    echo '<p>Documento creado exitosamente.</p>';
}

?>
<form action="" method="post">
    <table class="tabla-estandar opsal_tabla_ancha opsal_tabla_borde_oscuro horizontal">
        <tr><td style="width:25%;">Cobrar a</td><td><select name="codigo_agencia"><?php echo $options_agencia; ?></select></td></tr>
        <tr><td>Periodo</td><td>Desde <input type="text" class="calendario" name="periodo_inicio" value="<?php echo mysql_date(); ?>"/> hasta <input type="text" class="calendario" name="periodo_final" value="<?php echo mysql_date(); ?>"/></td></tr>
        <tr><td>Concepto para estado de cuenta</td><td><input name="grupo" type="text" value=""/></td></tr>
        <tr><td>Columna</td><td><select name="categoria"><option value="fact_estibas">Estibas</option><option value="fact_desestibas">Desestibas</option><option value="fact_almacenaje">Almacenaje</option><option value="fact_movimientos">Movimientos</option><option value="fact_remociones">Remociones</option><option value="fact_elaboracion_condicion">Elaboración de condición</option><option value="fact_otros">Otros</option></select></td></tr>
        <tr><td>Cantidad</td><td><input name="cantidad" type="text" value="1"/></td></tr>
        <tr><td>Servicio (detalle en documento legal)</td><td><input name="detalle" style="width:98%;" type="text" value=""/></td></tr>
        <tr><td>Total sin IVA</td><td><input type="text" name="sin_iva" value="0.00"/></td></tr>
        
    </table>
    <input type="submit" name="guardar_documento" value="Guardar" />
</form>

<script>
    $(function(){
        $(".calendario").datepicker({dateFormat: 'yy-mm-dd', constrainInput: true, defaultDate: +0}).datepicker('setDate', new Date());
    });
</script>