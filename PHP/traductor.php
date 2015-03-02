<?php
$traduccion = '';
if (empty($_GET['peticion']))
    $_GET['peticion'] = 'portada';
    
$traduccion = preg_replace('/\s/','+',$_GET['peticion']);

if (!file_exists(__PHPDIR__.$traduccion.'.php'))
    $traduccion = '404';

if (!S_iniciado())
    $traduccion = 'iniciar.sesion';

require_once(__PHPDIR__.$traduccion.'.php');
?>
