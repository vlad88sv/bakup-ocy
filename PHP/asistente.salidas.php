<h1 class="opsal_titulo">Asistente de salidas</h1>
<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'nueva.solicitud';

$menu[] = array('url' => '/asistente_de_salida.html','modo' => 'nueva.solicitud','titulo' => 'NUEVA SOLICITUD DE SALIDA');
$menu[] = array('url' => '/asistente_de_salida.html','modo' => 'solicitudes.enviadas','titulo' => 'SOLICITUDES ENVIADAS');
$menu[] = array('url' => '/asistente_de_salida.html','modo' => 'salidas.programadas','titulo' => 'SALIDAS PROGRAMADAS');

foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}

switch ($_GET['modo'])
{
    case 'salidas.programadas':
        echo '<img src="/paja/asistente de salidas/programadas.jpg" />';
        break;
    case 'solicitudes.enviadas':
        echo '<img src="/paja/asistente de salidas/enviadas.jpg" />';
        break;
    case 'nueva.solicitud':
        echo '<img src="/paja/asistente de salidas/nueva.jpg" />';    
        break;
    default:
        echo '<p>No implementado</p>';
}