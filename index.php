<?php
// Ruta del archivo de configuracion y personalizacion
require_once('config.php');
require_once (__PHPDIR__."vital.php");

if (isset($_GET['lang']) || (isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'en_US') )
{
    $directory = dirname(__FILE__).'/locale';
    $domain = 'lang';
    $locale ="en_US.utf8";
    
    setlocale( LC_MESSAGES, $locale);
    bindtextdomain($domain, $directory);
    textdomain($domain);
    bind_textdomain_codeset($domain, 'UTF-8');
}

// Auxiliar para HEAD
$arrHEAD = array();
$arrJS = array();
// Inclusiones JS
$arrJS[] = 'jquery-1.7.2';
$arrJS[] = 'jquery.cookie';
$arrJS[] = 'jquery.scrollTo';
//$arrJS[] = 'jquery.jgrowl';
$arrJS[] = 'jquery.qtip2';
$arrJS[] = 'jquery.facebox';
$arrJS[] = 'jquery.ui';
$arrJS[] = 'jquery.ui.widget';
$arrJS[] = 'jquery.ui.autocomplete';
$arrJS[] = 'jquery.ui.widget';
$arrJS[] = 'jquery.ui.mouse';
$arrJS[] = 'jquery.ui.button';
$arrJS[] = 'jquery.ui.position';
$arrJS[] = 'jquery.ui.slider';
$arrJS[] = 'jquery.ui.datepicker';
$arrJS[] = 'jquery.ui.timepicker';
$arrJS[] = 'jquery.ui.datepicker-es';
$arrJS[] = 'date';


// Inclusiones CSS
$arrCSS[] = 'CSS/estilo';
//$arrCSS[] = 'CSS/jquery.jgrowl';
$arrCSS[] = 'CSS/jquery.qtip';
$arrCSS[] = 'CSS/facebox';
$arrCSS[] = 'CSS/jquery.ui/jquery-ui-1.8';
ob_start();

require_once(__PHPDIR__.'traductor.php');
$BODY = ob_get_clean();

ob_start();
?>
<body>
<div id="fb-root"></div>
<?php if(!isset($GLOBAL_IMPRESION)) { ?>
    <div id="wrapper">
    <div id="header" class="noimprimir"><?php GENERAR_CABEZA(); ?></div>
    <div id="secc_general">
    <?php echo $BODY; ?>
    </div> <!-- secc_general !-->
    </div> <!-- wrapper !-->
<?php } else { ?>
    <style>
    *{background:#FFF !important;color:#000 !important;font-size:10pt;}
    .medio-oculto{font-size:11pt;}
    </style>
    <?php echo $BODY; ?>
<?php } ?>
</body>
</html>
<?php
$BODY = ob_get_clean();
if (!empty($_LOCATION)) header ("Location: $_LOCATION");

/* CAPTURAR <head> */
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
<head>
    <title><?php echo $HEAD_titulo; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Style-type" content="text/css" />
    <meta http-equiv="Content-Script-type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="es" />
    <meta name="description" content="<?php echo $HEAD_descripcion; ?>" />
    <meta name="keywords" content="<?php echo HEAD_KEYWORDS; ?>" />
    <meta name="robots" content="index, follow" />
    <link href="favicon.ico" rel="icon" type="image/x-icon" />
    <link rel="canonical" href="<?php echo PROY_URL_ACTUAL; ?>" />
    <style type='text/css'>
        @media print {
            .noimprimir { display:none; }
            table { page-break-inside:avoid; margin: 10px 0; }
            a {color: black; font-style: normal; text-decoration: none; }
            .exportable_ctrl { display: none; }
 
        }
        @media screen { .soloimpresion { display:none; } }
    </style>
<?php
HEAD_CSS();
HEAD_JS();
HEAD_EXTRA();
?>
<script type="text/javascript">
$(function(){
    setInterval(function(){$.get('ping.php');},60000);

    $('.blink').each(function() {
        var elem = $(this);
        setInterval(function() {
            if (elem.css('visibility') == 'hidden') {
                elem.css('visibility', 'visible');
            } else {
                elem.css('visibility', 'hidden');
            }    
        }, 500);
    });
    
    $(function(){
	jQuery.download = function(url, data, method){
	    //url and data options required
	    if( url && data ){ 
		    //data can be string of parameters or array/object
		    data = typeof data == 'string' ? data : jQuery.param(data);
		    //split params into form inputs
		    var inputs = '';
		    jQuery.each(data.split('&'), function(){ 
			    var pair = this.split('=');
			    inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />'; 
		    });
		    //send request
		    jQuery('<form target="_blank" action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
		    .appendTo('body').submit().remove();
	    };
	};
        
        $('.cambio_lenguaje').click(function(){
            $.cookie("lang", $(this).attr('rel'));
            window.location = window.location;
        });
    });
});
</script>
<script type="text/javascript">
    function ejecutar_busqueda_codigo_contenedor(codigo_contenedor)
    {
	$.facebox({ ajax: '/ajax.ContenedorPorCodigo.php?busqueda=' + codigo_contenedor });
    }
    
    $(function(){    
	$('.exportable').live('mouseenter', function(){
	    $(this).addClass('exportable_vivo');
	    $(this).prepend('<div class="exportable_ctrl"><b style="color:red;"><?php echo _('Exportar este reporte'); ?></b> <button class="exportar_pdf">.pdf</button><button class="exportar_xls">.xls</button><button class="exportar_doc">.doc</button></div>');
	});
	
	$('.exportable').live('mouseleave', function(){
	    $(this).removeClass('exportable_vivo');
	    $(this).find('.exportable_ctrl').remove();
	});
	
	$('.exportable_ctrl .exportar_xls').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.xls.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});

	$('.exportable_ctrl .exportar_doc').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.doc.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});
	
	$('.exportable_ctrl .exportar_pdf').live('click', function() {
	    var exportable = $(this).parents('.exportable');
	    exportable.find('.exportable_ctrl').remove();
	    var data = encodeURIComponent(exportable.html());
	    //console.log(data);
	    $.download('/exportar.pdf.php', 'archivo='+encodeURIComponent(exportable.attr('rel') || 'reporte')+'&data=' + data);
	});
	
	$(document).bind('reveal.facebox', function() {
	    if ($("#drop_target").length == 0)
	    {
		$("button.bq_usar_contenedor").attr('disabled','disabled');
		$("a.bq_usar_contenedor").remove();
	    }
	});
	
	$("#ver_historial").live('click', function(){
	    window.location = '/historial.html?ID='+$(this).attr('rel');
	});
	
        $('.ejecutar_busqueda_codigo_contenedor').live('click',function(event){
	    event.preventDefault();
            ejecutar_busqueda_codigo_contenedor($(this).attr('rel'));
        });
	
	$(".bq_usar_contenedor").live('click', function(event){
	    event.preventDefault();
	    $("#drop_target #posicion_columna").val($(this).attr('col'));
	    $("#drop_target #posicion_fila").val($(this).attr('fila'));
	    $("#drop_target #posicion_nivel").val($(this).attr('nivel'));
	    $(".posicion").trigger('change');
	});
	
	$(".bq_eliminar_despacho").live('click', function(event){
	    if (confirm('¿Realmente desea eliminar este despacho?.\nNo podrá deshacer esta acción.'))
	    {
		$.post('ajax.seguro.php',{accion:'eliminar_despacho', ID:$(this).attr('rel')}, function(){
		    alert('Se ha eliminado el despacho.');
		});
	    }
	});

	$("#bq_edi").live('click', function(event){
	    $.facebox({ ajax: '/EDI.php?ID=' + $(this).attr('codigo_contenedor')});
	});
	
	$('#ejecutar_traduccion').click(function(){
	    $.post('ajax.traducir.php',{traducir:$("#traducir").val()}, function(data){
		$("#traducido").val(data);
	    });
	    
	});

	$('#frm_buscar').submit(function(){    
	    ejecutar_busqueda_codigo_contenedor ($('#busqueda').val());
	    return false;
	});
    });
</script>
<?php if (defined('GOOGLE_ANALYTICS')) : ?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?php echo GOOGLE_ANALYTICS; ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<?php endif; ?>
</head>
<?php
$HEAD = ob_get_clean();

/* MOSTRAR TODO */
if(isset($GLOBAL_TIDY_BREAKS))
    echo $HEAD.$BODY;
else
{
    $tidy_config = array('output-xhtml' => true,'doctype' => 'transitional');
    $tidy = tidy_parse_string($HEAD.$BODY,$tidy_config,'UTF8');
    $tidy->cleanRepair();
    echo  trim($tidy);
}

function GENERAR_CABEZA(){if (empty($_GET['sin_cabeza'])) require_once(__PHPDIR__.'menu_superior.php');}
?>
