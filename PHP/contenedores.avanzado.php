<h1 class="opsal_titulo">Inventario y Facturación</h1>
<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'patio';

$menu[] = array('url' => '/contenedores.avanzado.html','modo' => 'patio','titulo' => 'EN PATIO');
$menu[] = array('url' => '/contenedores.avanzado.html','modo' => 'facturado','titulo' => 'FACTURADO');
$menu[] = array('url' => '/contenedores.avanzado.html','modo' => 'pendiente','titulo' => 'POR FACTURAR');

foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}

switch ($_GET['modo'])
{
    case 'patio':
        echo '<img src="/paja/iyf/patio.jpg" />';
        break;
    case 'facturado':
        echo '<img src="/paja/iyf/facturado.jpg" />';
        break;
    case 'pendiente':
        echo '<img src="/paja/iyf/por facturar.jpg" />';
        break;
    default:
        echo '<p>No implementado</p>';
}
?>
