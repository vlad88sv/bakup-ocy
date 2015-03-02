<?php
if (isset($_GET['impresion_anexo']))
{
    $c = 'SELECT `codigo_factura`, `anexo` FROM `facturas_anexos` WHERE (SELECT codigo_factura FROM facturas WHERE uniqid="'.db_codex($_GET['uniqid']).'") LIMIT 1';
    $r = db_consultar($c);
    $f = db_fetch($r);
    
    echo '<h1>Anexo para '. $_GET['uniqid'].'</h1>';
    echo $f['anexo'];
    return;
}

if (isset($_GET['tipo_impresion']))
{
    switch($_GET['tipo_impresion'])
    {
        case 'credito_fiscal':
            require_once('PHP/impresion.credito_fiscal.php');
            break;
        case 'consumidor_final':
            require_once('PHP/impresion.consumidor_final.php');
            break;
        default:
            echo '???';
    }
}

?>