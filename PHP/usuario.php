<?php
$tablausuarios = db_prefijo.'usuarios';

function _F_usuario_acceder($correo, $clave,$enlazar=true){
    global $tablausuarios;
    $correo = db_codex (trim($correo));
    $clave =db_codex (trim($clave));

    $c = "SELECT * FROM $tablausuarios WHERE ( LOWER(usuario)=LOWER('$correo') || LOWER(correo)=LOWER('$correo') ) AND clave=SHA1('$clave') LIMIT 1";
    $resultado = db_consultar ($c);
    if ($resultado && mysqli_num_rows($resultado)) {
        $_SESSION['autenticado'] = true;
        $_SESSION['cache_datos_nombre_completo'] = mysqli_fetch_assoc($resultado);
        return 1;
    } else {
        unset ($_SESSION);
        return 0;
    }
}

function _F_usuario_cache($campo){
    return @$_SESSION['cache_datos_nombre_completo'][$campo];
}
?>
