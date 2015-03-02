<?php
if ( empty($_GET['EDI']) || !is_numeric($_GET['EDI']) )
    return;

// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=".$_GET['EDI'].".edi");
header("Content-Type: application/edi");
header("Content-Transfer-Encoding: binary");

echo crear_EDI($_GET['EDI']);

//echo nl2br(crear_EDI($_GET['EDI']));

//echo '<br ></hr>';

//echo 'Resultado de la transferencia a directorio de prueba: ';
//echo enviar_edi($_GET['EDI'], true) ? 'Exitosa' : 'Fallida';
?>