<?php
protegerme(false);
require_once(__BASE_cePOSa__.'PHP/ssl.comun.php');
$arrJS[] = 'tiny_mce/jquery.tinymce';
$arrHEAD[] = <<< HTML
<script type="text/javascript">
$(function() {
    $('#mensaje').tinymce({
        script_url : 'JS/tiny_mce/tiny_mce.js',
        language : "es",
        theme : "advanced",
        mode : "exact",
        entity_encoding: "raw",
        relative_urls : false,
        plugins : "safari,style,layer,table,advhr,advimage,advlink,media,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,template",
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect,cleanup,code",
        theme_advanced_buttons2 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,advhr,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        button_tile_map : true,
        convert_urls : false
    });
});
</script>
HTML;

$GLOBAL_TIDY_BREAKS = true;

list($factura,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);

$buffer = '<p>Sr./Sra. '.$f['nombre_t_credito'].',</p>';

switch(@$_GET['plantilla'])
{
case 'facturacion_incorrecta':
    $buffer .=
    '
    <p>Gracias por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>., sin embargo no hemos podido realizar el cargo a su tarjeta de crédito/débito. Por favor comuniquese con nosotros lo antes posible para aclarar el método de pago ya que de lo contrario no podremos procesar su orden a tiempo.</p>
    ';
    $titulo = 'Error al realizar cargo de su compra [#'.$f['codigo_compra'].$f['salt'].']';
break;
case 'facturacion_correcta':
    $buffer =
    '
    <p>Gracias por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>. Este correo es para notificarle que su pedido fue correctamente facturado</p>
    <p><strong>¡Gracias por su compra!, su pedido esta programado para ser entregado el día '.date('d/m/Y',strtotime($f['fecha_entrega'])).'.</strong></p>
    ';
    $titulo = 'Datos de facturacion de su compra [#'.$f['codigo_compra'].$f['salt'].']';
break;
case 'pedido_aclarar':
    $buffer .=
    '
    <p>Gracias por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>. Se le notifica que hay datos faltantes o incorrectos en su pedido, favor comuniquese con nosotros lo antes posible para aclarar los siguientes datos solicitados, ya que de lo contrario no podremos procesar su orden a tiempo.</p>
    ';
    $titulo = 'Error al procesar los datos de su compra [#'.$f['codigo_compra'].$f['salt'].']';
break;
case 'enviado':
    $buffer =
    '
    <p>Gracias por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>. Se le notifica que su pedido ha sido entregado.</p>
    <p><strong>¡Esperamos atendenderle nuevamente!</strong></p>
    ';
    $titulo = 'Su arreglo natural de flores ha sido entregado [#'.$f['codigo_compra'].$f['salt'].']';
break;
case 'datos_basicos':
    $buffer =
    '
    <p>Gracias por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>. Se le reenvian los datos básicos de su compra.</p>
    <p>Normalmente este correo se envía si Ud. solicitó algún cambio, por lo que se le pide amablemente que corrobore los datos nuevamente</p>
    <p><strong>¡Esperamos atendenderle nuevamente!</strong></p>
    ';
    $titulo = 'Datos básicos de su compra [#'.$f['codigo_compra'].$f['salt'].']';
break;
case 'error_entrega':
    list($factura,$f) = SSL_COMPRA_FACTURA($_GET['transaccion']);
    $buffer =
    '
    <p>Este correo se le envia por su compra en <strong>'.PROY_NOMBRE_CORTO.'</strong>. Este correo es para informarle que hubo un error la entrega de su pedido.</p>
    ';
    $titulo = 'Error en su pedido [#'.$f['codigo_compra'].$f['salt'].']';
break;
}

$buffer .= '
<hr />
'.$factura.'
<hr />
<p style="color:#555;text-align:center;">
<i>
Atención al cliente '.PROY_NOMBRE_CORTO.'<br />
Teléfono '.PROY_TELEFONO_PRINCIPAL.'<br />
</i>
<img src="http://flor360.com/IMG/portada/logo.png" />
</p>
';

if(isset($_POST['enviar']) || isset($_GET['envio_rapido']))
{
    $contenido = (isset($_GET['envio_rapido']) ? $buffer : $_POST['mensaje']);
    $titulo = (isset($_GET['envio_rapido']) ? $titulo : $_POST['titulo']);
    registrar($f['codigo_compra'], 'correo.'.@$_GET['plantilla'], $titulo, $contenido);
    correo($f['correo_contacto'] .', '.PROY_MAIL_BROADCAST, $titulo, $contenido);
    echo '<p>Enviado</p>';
    return;
}

$textarea = $buffer;

echo '<form action="'.PROY_URL_ACTUAL_DINAMICA.'" method="post" >';
echo '<p><strong>Destinatario:</strong> '.$f['correo_contacto'].ui_input('enviar','Enviar','submit').'</p>';
echo '<p><strong>Asunto:</strong> <input style="width:800px;" name="titulo" value="'.$titulo.'" /></p>';
echo '<textarea id="mensaje" name="mensaje" style="width:100%;height:50em;">'.$textarea.'</textarea>';
echo '</form>';
?>
