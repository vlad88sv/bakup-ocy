<?php
function benchmark($referencia)
{
    return;
    global $bench_referencia, $bench_ultimo_evento;
    $tiempo_actual = microtime(true);
    error_log('"'. $referencia . '" se ejecuto ' . ($tiempo_actual-$bench_referencia) . 'ms despues de '. $bench_ultimo_evento);
    $bench_ultimo_evento = $referencia;
}
?>