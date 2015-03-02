<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
</script>
<div class="noimprimir">
<h1 class="opsal_titulo">Estadísticas</h1>
<?php
if (empty($_GET['modo']))
    $_GET['modo'] = 'patio';

//$menu[] = array('url' => '/reportes.html','modo' => 'financiero','titulo' => 'FINANCIERO');
$menu[] = array('url' => '/reportes.html','modo' => 'historico','titulo' => 'HISTORICO');
$menu[] = array('url' => '/reportes.html','modo' => 'patio','titulo' => 'PATIO');
$menu[] = array('url' => '/reportes.html','modo' => 'ingresos','titulo' => 'RECEPCIONES');
$menu[] = array('url' => '/reportes.html','modo' => 'movimientos','titulo' => 'REMOCIONES');
$menu[] = array('url' => '/reportes.html','modo' => 'salidas','titulo' => 'DESPACHOS');
$menu[] = array('url' => '/reportes.html','modo' => 'agencia','titulo' => 'AGENCIA');
$menu[] = array('url' => '/reportes.html','modo' => 'personal','titulo' => 'PERSONAL');

foreach ($menu AS $id => $datos)
{
    echo '<a class="opsal_pestaña '.($datos['modo'] == $_GET['modo'] ? 'opsal_pestaña_seleccionada' : '').'" href="'.$datos['url'].'?modo='.$datos['modo'].'">'.$datos['titulo'].'</a>';
}
?>
</div>
<?php
if (!empty($_GET['modo']))
{
    $archivo = 'PHP/reportes.'.$_GET['modo'].'.php';
    if (file_exists($archivo))
    {
        require_once($archivo);
        return;
    }
}
echo '<p>No implementado</p>';
?>