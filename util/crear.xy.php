<?php
$abc = 'abcdefghijklmnopqrstuvwxyz';

for ($x=1; $x < 61; $x++)
{
    for ($y=1; $y < 37; $y++)
    {
        
        if ($x < 27)
            $x2 = strtoupper($abc[floor((($x-1)%26))]);
        else
            $x2 = strtoupper($abc[floor((($x-26)/26))].$abc[(($x-1)%26)]);
            
        $y2 = (($y*2)-1);
        //echo 'X: '.$x2.', Y: '.$y2.' <br />';
        echo sprintf('INSERT INTO opsal_posicion (x,y,x2,y2,tipo) VALUES(%s,%s,"%s","%s","%s");',$x,$y,$x2,$y2,'calle').'<br />';
    }
}
?>