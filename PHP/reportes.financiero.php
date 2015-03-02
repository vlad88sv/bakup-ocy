<?php
$fecha = '';
if (isset($_POST['filtrar']))
{
 $fecha = 'AND t1.fechatiempo_ingreso >= "'.$_POST['fecha_inicio'].'" AND t1.fechatiempo_ingreso <= "'.$_POST['fecha_final'].'"';
}
?>
<h1>Reporte financiero</h1>
<div class="noimprimir" style="border-bottom:1px solid gray;">
    <form action="/reportes.html?modo=financiero" method="post">
        Fecha inicio: <input type="text" class="calendario" name="fecha_inicio" value="" /> Fecha final: <input type="text" class="calendario" name="fecha_final" value="" /> <input type="submit" id="filtrar" name="filtrar" value="Filtrar" />
    </div>
</div>
<p>
   <?php
   if (isset($_POST['filtrar']))
   {
    echo 'Mostrando datos del periodo <b>'.$_POST['fecha_inicio'].'</b> a <b>'.$_POST['fecha_final'].'</b>';
   } else {
    echo 'Mostrando datos historicos';
   }
   ?>
</p>
