<?php
$abc = 'abcdefghijklmnopqrstuvwxyz';

$codigo = 1;
for ($x=60; $x > 0; $x--)
{
    for ($y=32; $y > 0; $y--)
    {
        //echo 'X: '.$x.', Y: '.$y.' - ';
        $x2 = strtoupper ( $abc[floor((($x-1)/26))].$abc[(($x-1)%26)] );
        $y2 = (($y*2)-1);
        echo sprintf('UPDATE opsal_posicion SET x2="%s", y2="%s" WHERE codigo_posicion=%s;',$x2,$y2,$codigo).'<br />';
        $codigo++;
    }
}
?>