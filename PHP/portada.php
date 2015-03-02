<?php
switch (_F_usuario_cache('nivel'))
{
    case 'externo':
        require_once('contenedores.myr.php');
        break;
    case 'agencia':
        require_once('portada.agencia.php');
        break;
    default:
        require_once('portada.opsal.php');
        break;
}
?>