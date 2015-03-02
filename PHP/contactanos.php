<div style="margin:auto;width:790px;text-align:justify;">
<?php
$HEAD_titulo = 'contáctacnos';
if (isset($_POST['enviar']))
{

  if (strlen($_POST['mensaje']) < 5)
  {
    $error[] = 'Su consulta no parece válida.';
  }

  if (strlen($_POST['nombre'])  < 3 || preg_match('/^\w*$/',$_POST['nombre']))
  {
    $error[] = 'El nombre ingresado no parece válido o es muy corto.';
  }

  if(!validcorreo($_POST['email']))
  {
    $error[] = 'Su correo electrónico no parece válido.';
  }

  if (isset($error) && count($error))
  {
    echo '<h1>Su consulta no pudo ser enviada porque se encontraron los siguientes errores</h1>';
    echo '<p style="color:#F00">'.implode ('</p><p style="color:#F00">',$error).'</p>';
  }
  else
  {
    unset($DATOS);
    $DATOS['nombre'] 	= $_POST['nombre'];
    $DATOS['telefono'] 	=  $_POST['tel'];
    $DATOS['correo'] 	= $_POST['email'];
    $DATOS['interes'] 	= $_POST['mensaje'];
    $DATOS['fecha'] 	= mysql_datetime();
    
    $id_consulta = db_agregar_datos(db_prefijo.'consultas',$DATOS);
    unset($DATOS);
    
    $to      = PROY_MAIL_REPLYTO;
    $subject = 'Nueva consulta a '.PROY_NOMBRE_CORTO.' - #' . $id_consulta;
    $message =
	      '<style>li{font-weight:bold;}</style>' .
	      "<p>La siguiente consulta ha sido recibida a travez de ".PROY_URL_ACTUAL."</p>" .
	      '<ul>'.
	      "<li>Teléfono:</li><p>" . $_POST['tel'] . '</p>' .
	      "<li>Correo electrónico:</li><p>" . $_POST['email'] . '</p>' .
	      "<li>IP:</li><p>" . $_SERVER['REMOTE_ADDR'] . '</p>' .
	      "<li>Nombre:</li><p>" . $_POST['nombre'] . '</p>' .
	      "<li>Consulta:</li><p>" . $_POST['mensaje'] . '</p>' .
	      '</ul>';
    $headers = 'Reply-To: '.$_POST['nombre'].' <'.$_POST['email'].'>' . "\r\n";
    @correo($to, $subject, $message, $headers);

    echo '<p>';
    echo '¡Muchas gracias por su consulta!<br />';
    echo 'Lo invitamos a seguir navegando en nuestro sitio web. <a href="'.PROY_URL.'">Ir a la página principal</a>.<br />';
    echo 'Recuerde que nuestro número telefonico es: '.PROY_TELEFONO.'<br />';
    echo '</p>';
    return;
  }
}
?>
<h1>Contacto</h1>
<div>
<?php cargar_editable('contacto'); ?>
</div>
<hr />
<form action="<?php echo PROY_URL_ACTUAL; ?>" method="post">
<table style="margin:auto">
<tr><td><p>Su teléfono</p></td><td><input name="tel" value="<?php echo @$_POST['tel']; ?>" /></td></tr>
<tr><td><p>Su email</p></td><td><input name="email" value="<?php echo @$_POST['email']; ?>" /></td></tr>
<tr><td><p>Su nombre</p></td><td><input name="nombre" value="<?php echo @$_POST['nombre']; ?>" /></td></tr>
</table>
<p>Comentario o pregunta</p>
<textarea cols="100" rows="10" name="mensaje"><?php echo  @$_POST['mensaje']; ?></textarea><br />
<input type="submit" name="enviar" value="Enviar consulta" />
</form>
</div>
