<?php
if (S_iniciado())
    _F_sesion_cerrar();
    
header('Location: '.PROY_URL,true);
?>