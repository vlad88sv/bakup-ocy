<?php
    // Comprobación básica: cancelar si no es valido el codigo del contenedor o no existe
    if (!isset($_GET['codigo_contenedor']) || !is_numeric($_GET['codigo_contenedor']))
    {
        header('Location: ' . PROY_URL);
        echo '<p>El código de producto es inválido, redirigiendo a '.ui_href('',PROY_URL,PROY_URL).'</p>';
        return;
    }

    // Primero obtenemos toda la información del contenedor
    $cContenedor = sprintf('SELECT `codigo_producto`, `titulo`, `descripcion`, `vistas`, `descontinuado` FROM `%s` WHERE codigo_producto=%s LIMIT 1',db_prefijo.'producto_contenedor',db_codex($_GET['codigo_contenedor']));
    $rContenedor = db_consultar($cContenedor);

    // Comprobación extendida: cancelar si no se encontró...
    if (!mysqli_num_rows($rContenedor))
    {
        header('Location: ' . PROY_URL);
        echo '<p>El código de producto no fue encontrado, redirigiendo a '.ui_href('',PROY_URL,PROY_URL).'</p>';
        return;
    }
    
    // Existe, entonces obtengamos todos los datos
    $contenedor = mysqli_fetch_assoc($rContenedor);

    // Si es admin procesar cualquier cambio
    if (_F_usuario_cache('nivel') == _N_administrador)
    {
        // Si cancelo, anulemos la salida
        if (isset($_POST['PME_sys_canceladd']) || isset($_POST['PME_sys_cancelchange']))
            unset($_POST['referencia']);

        PROCESAR_CATEGORIAS();
        PROCESAR_VARIEDADES();
        PROCESAR_CONTENEDOR();
    }

    // actualizamos la información del contenedor por si PROCESAR_CONTENEDOR() hizo algo...
    $contenedor = mysqli_fetch_assoc(db_consultar($cContenedor));

    // Revisamos si la URL es correcta - por los bromistas
    $titulo_SEO = SEO($contenedor['titulo']);
    if ($titulo_SEO != $_GET['titulo'].'.html')
    {
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: '. PROY_URL.URL_SUFIJO_VITRINA.SEO($contenedor['titulo'].'-'.$contenedor['codigo_producto']));
        ob_end_clean();
        exit;
    }

    // Titulo de la pagina
    $HEAD_titulo = $contenedor['titulo'] . ' - Arreglo #' . $contenedor['codigo_producto'] . ' - ' . PROY_NOMBRE;
    $HEAD_descripcion = strip_tags($contenedor['descripcion']);
    $modoHorizontal = false;
    
    /********************** bCategoria***************************************/
    $bCategoria= '';

    // Obtengamos las categorias del producto!!!
    $c = sprintf('SELECT b.codigo_menu, a.codigo_categoria, b.titulo, b.descripcion FROM %s AS a LEFT JOIN %s AS b ON a.codigo_categoria = b.codigo_categoria WHERE a.codigo_producto="%s" ORDER BY b.titulo ASC', db_prefijo.'productos_categoria', db_prefijo.'categorias',$contenedor['codigo_producto']);
    $rCategoria = db_consultar($c);
    $bCategoria.= '<div style="text-align:center;margin:5px;">';
    while ($f = mysqli_fetch_assoc($rCategoria))
    {
        switch ($f['codigo_menu'])
        {
            case '5':
                $modoHorizontal = true;
                $arrCSS[] = 'CSS/estilo.formal';
                $arrCSS[] = 'CSS/estilo.horizontal';
                break;
            
            case '6':
                $arrCSS[] = 'CSS/estilo.alegre';
                break;
            
            case '7':
                $modoHorizontal = false;
                $arrCSS[] = 'CSS/estilo.formal';
                break;
        }
        $bCategoria.= '<span class="etiqueta-categoria">'.$f['titulo'].SI_ADMIN(' <form style="display:inline;" action="'.PROY_URL_ACTUAL.'" method="POST">'.ui_input('codigo_categoria',$f['codigo_categoria'],'hidden').ui_input('btn_eliminar_categoria','x','submit','btnlnk').'</form>').'</span> ';
    }
    $bCategoria.= '</div>';
    //$bCategoria.= SI_ADMIN(BR.flores_db_ui_obtener_categorias_cmb('cmb_agregar_categoria',$contenedor['codigo_producto']).ui_input('btn_agregar_categoria','Agregar','submit'));
    $bCategoria.= SI_ADMIN(BR.'<form action="'.PROY_URL_ACTUAL.'" method="POST">'.flores_db_ui_obtener_categorias_chkbox('chk_agregar_categoria',$contenedor['codigo_producto']).ui_input('btn_agregar_categoria_v2','Agregar','submit','btnlnk').'</form>');


    /*************** variedades ********************************************/
    // Luego obtenemos toda la información de sus variedades
    $c = sprintf('SELECT `codigo_variedad`, `codigo_producto`, `foto`, `descripcion`, `precio`, `precio_oferta`, `deshabilitado` FROM `%s` WHERE codigo_producto="%s" ORDER BY precio DESC, descripcion ASC',db_prefijo.'producto_variedad',$contenedor['codigo_producto']);
    $variedad = db_consultar($c);

    $precargar_img = array();
    
    $VARIEDADES_ADMIN = '<h2>Administración de variedades</h2>';
    $VARIEDADES = '';
    $VARIEDADES .= '<table style="width:100%;border-collapse:collapse;" id="contenedor_variedades">';
    $PRECIO = 0;
    for ($i=0; $i<mysqli_num_rows($variedad); $i++) {
        $f = mysqli_fetch_assoc($variedad);
        if ($f['deshabilitado'] == 0)
        {
        $VARIEDADES .=  '<tr class="variedades">';
        $VARIEDADES .= '<td style="width:30px;"><input type="radio" id="variedad_'.$f['codigo_variedad'].'" class="variedad" name="variedad"';
        
        if ($modoHorizontal) {
            if (isset($_GET['fb']))
            {
                $IMG = imagen_URL($f['foto'],500,333,'img0.');
                $class = 'fb-horizontal';
            } else {
                $IMG = imagen_URL($f['foto'],600,400,'img0.');
                $class = 'horizontal';
            }
        } else {
            $IMG = imagen_URL($f['foto'],400,600,'img0.');
            $class = 'vertical';
        }

        if (empty($flag_selected) || (!empty($_GET['variedad']) && $_GET['variedad'] == $f['codigo_variedad']) )
        {
            $HEAD_ogimage = imagen_URL($f['foto'],130,110,'img0.');
            $PRECIO = $f['precio']; // Para buscar similares
            $VARIEDADES .= ' checked="checked"';
            $IMG_CONTENEDOR = '<img alt="Imagen del producto" id="imagen_contenedor" class="'.$class.'" style="" src="'.$IMG.'" />';
        }
        
        $precargar_img[] = $IMG;
        
        $precio_etiqueta = ($f['precio_oferta'] > 0 ? '<span style="text-decoration:line-through;">$'.$f['precio'].'</span> - Oferta: <span style="color:red;">$' . $f['precio_oferta'] . '</span>' : '$'.$f['precio']);
        
        $VARIEDADES .= ' src="'.$IMG.'"';
        $VARIEDADES .= ' id="'.$f['foto'].'"';
        $VARIEDADES .= ' value="'.$f['codigo_variedad'].'" /></td>';
        $VARIEDADES .= '<td><label for="variedad_'.$f['codigo_variedad'].'">' . htmlentities($f['descripcion'],ENT_QUOTES,'UTF-8'). '&nbsp;&nbsp;#'. $contenedor['codigo_producto'].'</label></td>'.
        '<td style="text-align:right"><label for="variedad_'.$f['codigo_variedad'].'">'.$precio_etiqueta.'</label></td>';
        $VARIEDADES .= '</tr>';
        }
        $VARIEDADES_ADMIN .= '<form action="'.PROY_URL_ACTUAL.'" method="POST"><p style="white-space:nowrap;clear:both;display:block;"><span style="float:left">' . $f['descripcion'] .'</span> <span style="float:right">'. ui_input('codigo_variedad',$f['codigo_variedad'],'hidden').' '.ui_input('btn_editar_variedad','Editar','submit','btnlnk btnlnk-mini').ui_input('btn_eliminar_variedad','Eliminar','submit','btnlnk btnlnk-mini').ui_input('btn_clonar_foto_variedad','Clonar Foto','submit','btnlnk btnlnk-mini').ui_input('btn_clonar_receta_variedad','Clonar prep.','submit','btnlnk btnlnk-mini').'</span></p></form>';
        $flag_selected=true;
    }
    $VARIEDADES .= '</table>';
    $VARIEDADES_ADMIN = '<div style="display:block;clear:both">'.
    $VARIEDADES_ADMIN . '</div><form action="'.PROY_URL_ACTUAL.'" method="POST">'.BR . ui_input('btn_agregar_variedad','Agregar variedad', 'submit', 'btnlnk btnlnk-mini').'</form>';


    /********************** PRODUCTOS SIMILARES ***************************************/
    $cProducto_similar = sprintf('SELECT codigo_producto, titulo, foto FROM '.db_prefijo.'producto_variedad LEFT JOIN '.db_prefijo.'producto_contenedor USING (codigo_producto) LEFT JOIN '.db_prefijo.'productos_categoria USING (codigo_producto) WHERE codigo_categoria IN (SELECT codigo_categoria FROM '.db_prefijo.'productos_categoria WHERE codigo_producto = '.$contenedor['codigo_producto'].') AND foto <> "" AND descontinuado="no" AND '.db_prefijo.'producto_variedad.codigo_producto <> %s AND precio BETWEEN (%s)*0.60 AND (%s)*1.40 GROUP BY '.db_prefijo.'producto_variedad.codigo_producto ORDER BY RAND() LIMIT %s',$contenedor['codigo_producto'],$PRECIO,$PRECIO,($modoHorizontal ? 4 : 6));
    $bProducto_similar = '<div id="productos_similares">';
    $rProducto_similar = db_consultar($cProducto_similar);
    if (mysqli_num_rows($rProducto_similar))
        while ($fsimilar = mysqli_fetch_assoc($rProducto_similar))
        {
            $IMG_similar = $modoHorizontal ? imagen_URL($fsimilar['foto'],231,143,'img0.') : imagen_URL($fsimilar['foto'],143,231,'img0.');
            $bProducto_similar .= sprintf('<a href="%s"><img class="producto_similar" src="'.$IMG_similar.'" alt="Producto similar: %s" /></a> ',PROY_URL.URL_SUFIJO_VITRINA.SEO($fsimilar['titulo'].'-'.$fsimilar['codigo_producto']),$fsimilar['titulo']);
        }
    $bProducto_similar .= '</div>';
    /**************************************************************************************************************************/
        
    /* Desplegar lo que conseguimos */
    if( $contenedor['descontinuado'] == "si" )
        echo '<p class="error">Lo sentimos, este producto esta descontinuado y no se encuentra disponible.</p>';

    // Tabla
    echo '<table style="table-layout:fixed;width:100%;">';
    echo '<tr>';
    echo '<td id="vitrina_imagen" class="'.$class.'">';

    // Mostrar los datos del contenedor
    if (!isset($IMG_CONTENEDOR))
        $IMG_CONTENEDOR = '<img src="IMG/stock/sin_imagen.jpg" title="Sin Imagen" />';

    echo '<table style="width:100%;"></tr>';
    
    $consulta = sprintf('SELECT codigo_producto, titulo, foto FROM '.db_prefijo.'producto_variedad LEFT JOIN '.db_prefijo.'producto_contenedor USING (codigo_producto) LEFT JOIN '.db_prefijo.'productos_categoria USING (codigo_producto) WHERE codigo_categoria IN (SELECT codigo_categoria FROM '.db_prefijo.'productos_categoria WHERE codigo_producto = '.$contenedor['codigo_producto'].') AND foto <> "" AND descontinuado="no" AND '.db_prefijo.'producto_variedad.codigo_producto <> %s AND precio BETWEEN (%s)*0.60 AND (%s)*1.40 GROUP BY '.db_prefijo.'producto_variedad.codigo_producto ORDER BY RAND() LIMIT 2',$contenedor['codigo_producto'],$PRECIO,$PRECIO);
    $resultado = db_consultar($consulta);
    $fsimilar = mysqli_fetch_assoc($resultado);
    //echo '<td><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($fsimilar['titulo'].'-'.$fsimilar['codigo_producto']).'"><img src="'.PROY_URL_ESTATICA.'IMG/stock/flecha.izq.gif" /></a></td>';
    
    echo '<td>';
    echo '<h1 style="font-family: helvetica;color:grey;background-color:white;font-size:16px;border:1px solid grey;">Nombre: <span style="font-style: italic;color:black;">'.$contenedor['titulo'], '</span> <span style="display:inline-block;float:right;">Código: <span style="color:black;">#', $contenedor['codigo_producto'], '</span></span></h1><hr />';
    echo '<div style="text-align:center">'.$IMG_CONTENEDOR.'</div>';
    echo '</td>';
    
    $fsimilar = mysqli_fetch_assoc($resultado);
    //echo '<td><a href="'.PROY_URL.URL_SUFIJO_VITRINA.SEO($fsimilar['titulo'].'-'.$fsimilar['codigo_producto']).'"><img src="'.PROY_URL_ESTATICA.'IMG/stock/flecha.derecha.gif" /></a></td>';
    echo '</tr></table>';
    
    echo '</td>';
    echo '<td id="vitrina_info" style="vertical-align:top">';

    $bInfoCompra  = $bInfoAdicional = '';
if( $contenedor['descontinuado'] == "no" || S_iniciado())
{
    echo '<h2>Contenido y descripción</h2>';
    echo '<div class="refinado" style="padding:5px;">';
    echo nl2br($contenedor['descripcion']);
    echo '</div>';
    
    echo SI_ADMIN($VARIEDADES_ADMIN);
    
    if ($modoHorizontal)
        $bInfoCompra .= '<h2>Seleccione la variedad (precio c/u)</h2>';
    else
        $bInfoCompra .= '<h2>Seleccione la variedad</h2>';
        
    $bInfoCompra .= '<div class="refinado" style="padding:5px;">';
    $bInfoCompra .= $VARIEDADES;
    $bInfoCompra .= '</div>';

    if ($modoHorizontal)
    {
        $bInfoCompra .= '
        <table style="width:100%;">
        <tr>
        <td style="vertical-align:middle;text-align:right;width:180px;">
            <input type="submit" id="btn_comprar_ahora" name="btn_comprar_ahora" value="COMPRAR">
        </td>
        <td style="vertical-align:middle;text-align:left;">
            <span style="color:#525451;font-weight:bold; font-style: italic;">Like Us Today!</span><br /><div class="fb-like" data-href="'.PROY_URL_ACTUAL_NOSSL.'" data-send="false" data-layout="button_count" data-width="80" data-show-faces="false"></div>
            
        </td>
        </tr>
        </table>
        <div style="text-align:center;"><img alt="Tarjetas de cŕedito admitidas en floristerias en El Salvador" src="'.PROY_URL.'IMG/stock/credit_card_logos_4.gif"/></div>
        ';
    } else {
        $bInfoCompra .= '
        <table style="width:100%;">
        <tr>
        <td style="vertical-align:middle;text-align:right;width:180px;">
            <input type="submit" id="btn_comprar_ahora" name="btn_comprar_ahora" value="COMPRAR">
        </td>
        <td style="width:179px;height:46px;">
            <div style="text-align:center;"><img alt="Tarjetas de cŕedito admitidas en floristerias en El Salvador" src="'.PROY_URL.'IMG/stock/credit_card_logos_4.gif"/></div>
        </td>
        <td style="vertical-align:middle;text-align:left;">
            <span style="color:#525451;font-weight:bold; font-style: italic;">Like Us Today!</span><br /><div class="fb-like" data-href="'.PROY_URL_ACTUAL_NOSSL.'" data-send="false" data-layout="button_count" data-width="80" data-show-faces="false"></div>
        </td>
        </tr>
        </table>
        ';
    }
    $bInfoCompra .= '
    <div style="color:black;font-weight:normal;font-size:11px;border: 1px solid #CCCCCC;padding:3px;">
    <p style="font-size:13px; font-weight:bold;text-align: justify;">Al presionar el botón "COMPRAR" se te mostrará un formulario en el que podras especificar la fecha de entrega e ingresar todos los demas datos necesarios de tu pedido.<br />Podrás escoger los siguientes metodos de pago:</p>
    <div style="padding-left:30px;">
    <span class="li">Tarjeta de crédito o débito, nacionales o internacionales</span>
    <span class="li">Abono a nuestra cuenta en <i>Banco de America Central</i></span>
    <span class="li">Solicitar cobro a domicilio</span>
    <span class="li">Pagar en nuestra sucursal en CC. La Gran Vía</span>
    </div>
    </div>
    ';
    
    $bInfoAdicional .= '
    <div class="refinado medio-oculto" style="padding:3px;margin-bottom:5px !important;">
    Los colores de las flores dependen de la disponibilidad.    Si deseas un color de flores en especial ¡contáctanos!.<br />
    En '.PROY_NOMBRE_CORTO.' tratamos de que nuestros arreglos sean exactamente iguales al de nuestras fotografías, sin embargo si al momento de tu compra no se encuentra alguno de los elementos de preparacion del arreglo, se le reemplazara por los mas similares disponibles que sean de igual o mejor calidad al exhibido.
    </div>
    <table id="infoAdicionalContacto">
    <tr>
    <td class="medio-oculto" style="text-align:right;">Si tienes alguna consulta no dudes en llamarnos al</td>
    <td style="text-align:left;"><span style="color:#656565;font-size:14px;">(503)</span>&nbsp;<span style="color:#dc448d;font-size:18px;">'.PROY_TELEFONO_PRINCIPAL.'</span></td>
    </tr>
    <tr>
    <td class="medio-oculto" style="text-align:right;">o escribenos a</td>
    <td style="text-align:left;color:#dc448d;">info@flor360.com</td>
    </tr>
    </table>
    ';
}
else
{
    $bInfoCompra .= '<h2>Producto descontinuado</h2>';
    $bInfoCompra .= '<p>La elaboracion de este producto ha sido descontinuada.</p>';
    $bInfoAdicional .= '';
}

echo '<form action="'.PROY_URL_SSL.'comprar-articulo-'.SEO($contenedor['titulo']).'" method="POST">';

if ($modoHorizontal || S_iniciado())
    echo ui_input('con_cantidad','si','hidden');
    
echo $bInfoCompra;

if (!$modoHorizontal)
    echo $bInfoAdicional;

echo '</form>';
echo SI_ADMIN($bCategoria);
echo '</td></tr></table>';

if ($modoHorizontal)
    echo $bInfoAdicional;

if (!isset($_GET['fb']))
{
    echo '<h2>Productos similares</h2>';
    echo $bProducto_similar;
    
    echo '<p style="background-color:#a9d67b;color:black;padding:4px; margin-bottom: 2px;font-size: 12px;">Flor360.com es la mas destacada entre las <span style="font-weight:bold;">Floristerias El Salvador</span> ya que contamos con diseños florales exclusivos por lo que somos su mejor opción a la hora de enviar <span style="font-weight:bold;">Flores a El Salvador</span> y <span style="font-weight:bold;">Regalos a El Salvador</span>. Are you an international costumer looking to <span style="font-weight:bold;">send present for birthday, valentine, christmas or just some roses or other beautiful flowers</span>?, don\'t worry, we accept international orders so you can send flowers to El Salvador. Problems with Spanish?, call us now to <?php echo PROY_TELEFONO_PRINCIPAL; ?> or reach us by e-mail at <a href="mailto:info@flor360.com">info@flor360.com</a>.</p>';
}
/* -------------------------------------------+-------------------------------------------*/    
/* Nuevo contador de visitas JS asincrono (para aprovechar el cache de la página)  */
?>
<script type="text/javascript">
function preload(arrayOfImages) {
   $(arrayOfImages).each(function () {
       $('<img />').attr('src',this).appendTo('body').css('display','none');
   });
}
preload(["<?php echo join('","',$precargar_img); ?>"]);

$(function(){
    if ($.cookie("vista", { expires: 1, path:  window.location.pathname }) == null)
        $.post('ajax',{pajax: "arreglo_visto", codigo_producto: "<?php echo $contenedor['codigo_producto']; ?>"});
    $.cookie("vista", "1", { expires: 1, path:  window.location.pathname });
    
    $(".variedad").click(function(){$("#imagen_contenedor").attr("src",$(this).attr("src"));});
});

comprar_estado_titulo = false;

function cambiarTituloBtnComprar()
{
    if (comprar_estado_titulo)
        $("#btn_comprar_ahora").val('COMPRAR');
    else
        $("#btn_comprar_ahora").val('CLIC AQUI');
    
    comprar_estado_titulo = !comprar_estado_titulo;
}

window.setInterval('cambiarTituloBtnComprar()',1000);
</script>
<?php
/* -------------------------------------------+-------------------------------------------*/

function PROCESAR_CONTENEDOR()
{
    if ((isset($_POST['btn_agregar_variedad']) || isset($_POST['btn_editar_variedad']) || isset($_POST['btn_eliminar_variedad'])) || (isset($_POST['referencia']) && $_POST['referencia'] == 'variedades')) return;
    // Si no hay ninguna referencia ó la referencia es explicitamente nuestra
    if (!isset($_POST['referencia']) || $_POST['referencia'] == 'contenedor')
    {
        global $db_link, $contenedor;
        $_POST['PME_sys_sfn[0]']='0';
        $_POST['PME_sys_fl']='0';
        $_POST['PME_sys_qfn']='';
        $_POST['PME_sys_fm']='0';
        $_POST['PME_sys_rec']=$contenedor['codigo_producto'];
        $_POST['PME_sys_operation']='Cambiar';
        $_POST['con_referencia']=true;
        require(__BASE_cePOSa__.'PHP/gestor_contenedores.php');
    }
}

function PROCESAR_VARIEDADES()
{
    global $db_link, $contenedor;
    if (isset($_POST['btn_clonar_foto_variedad']))
    {
        $foto = db_obtener(db_prefijo.'producto_variedad', 'foto', 'codigo_variedad='.$_POST['codigo_variedad']);        
        $c = 'UPDATE '.db_prefijo.'producto_variedad SET foto="'.$foto.'" WHERE codigo_producto="'.$contenedor['codigo_producto'].'" AND codigo_variedad <> "'.$_POST['codigo_variedad'].'"';
        db_consultar($c);
        return;
    }

    if (isset($_POST['btn_clonar_receta_variedad']))
    {
        $receta = db_obtener(db_prefijo.'producto_variedad', 'receta', 'codigo_variedad='.$_POST['codigo_variedad']);
        
        $c = 'UPDATE '.db_prefijo.'producto_variedad SET receta="'.$receta.'"  WHERE codigo_producto="'.$contenedor['codigo_producto'].'" AND codigo_variedad <> "'.$_POST['codigo_variedad'].'"';
        db_consultar($c);
        return;
    }
    
    // Determinar si necesitamos mostrar PME para agregar una variedad
    if ((isset($_POST['btn_agregar_variedad']) || isset($_POST['btn_editar_variedad']) || isset($_POST['btn_eliminar_variedad'])) || (isset($_POST['referencia']) && $_POST['referencia'] == 'variedades'))
    {
        $_POST['con_referencia']=true;
        $_POST['PME_sys_sfn[0]']='0';
        $_POST['PME_sys_fl']='0';
        $_POST['PME_sys_qfn']='';
        $_POST['PME_sys_fm']='0';
        if (isset($_POST['btn_agregar_variedad']))
        {
            $_POST['PME_sys_rec']='1';
            $_POST['PME_sys_operation']='Agregar';
            $_POST['f360_contenedor']=$contenedor['codigo_producto'];
        }

        if (isset($_POST['codigo_variedad']))
        {
            $_POST['PME_sys_rec']=$_POST['codigo_variedad'];
            if (isset($_POST['btn_editar_variedad']))
                $_POST['PME_sys_operation']='Cambiar';
            elseif (isset($_POST['btn_eliminar_variedad']))
                $_POST['PME_sys_operation']='Suprimir';
        }

        if (isset($_POST['PME_sys_saveadd']) || isset($_POST['PME_sys_savechange']) || isset($_POST['PME_sys_savedelete']))
        {
            ob_start();
        }
        require(__BASE_cePOSa__.'PHP/gestor_variedades.php');
        if (isset($_POST['PME_sys_saveadd']) || isset($_POST['PME_sys_savechange']) || isset($_POST['PME_sys_savedelete']))
        {
            ob_end_clean();
            unset($_POST);
        }
    }
}

function PROCESAR_CATEGORIAS()
{
    global $contenedor;
    if (isset($_POST['btn_agregar_categoria']) && isset($_POST['cmb_agregar_categoria']) && is_numeric($_POST['cmb_agregar_categoria']))
    {
        $datos['codigo_producto'] = $contenedor['codigo_producto'];
        $datos['codigo_categoria'] = $_POST['cmb_agregar_categoria'];
        db_agregar_datos(db_prefijo.'productos_categoria',$datos);
        unset($datos);
    }

    if (isset($_POST['btn_agregar_categoria_v2']))
    {
        $join = (join('),('.$contenedor['codigo_producto'].',',@array_values($_POST['chk_agregar_categoria'])));
        $c = sprintf('INSERT INTO %s (codigo_producto, codigo_categoria) VALUES (%s,%s)',db_prefijo.'productos_categoria',$contenedor['codigo_producto'],$join);
        //echo $c;
        db_consultar($c);
    }

    if (isset($_POST['btn_eliminar_categoria']) && isset($_POST['codigo_categoria']))
    {
        $c = sprintf("DELETE FROM %s WHERE codigo_categoria=%s AND codigo_producto=%s",db_prefijo.'productos_categoria',db_codex($_POST['codigo_categoria']), $contenedor['codigo_producto']);
        $r = db_consultar($c);
    }
}
?>
