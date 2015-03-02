<?php
require_once('config.php');
header("Content-type: application/vnd.ms-word; name='word'");
header('Content-Disposition: filename="'.urldecode($_POST['archivo']).'.doc"');
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=Generator content="OPSAL">
<style>
<!--
html, body, h1, h2, td {
    font-family: Calibri, Candara, Segoe, "Segoe UI", Optima, Arial, sans-serif;
}

h1 {
    font-size: 18.0pt;
}

h2 {
    font-size: 11.0pt;
}
table
{
    font-family: Calibri, Candara, Segoe, "Segoe UI", Optima, Arial, sans-serif;
    mso-displayed-decimal-separator:"\.";
    mso-displayed-thousand-separator:"\,";
    border: 2px solid black;
}

col
{
    mso-width-source:auto;
}

td
{
    font-size:10pt;
    font-weight:bold;
    border:1px solid black;
    background-color:white;
}

th
{
    font-size:10pt;
    font-weight:bold;
    background-color:white;
}


@page
{
    mso-page-orientation:landscape;

}
-->
</style>
</head>
<body>
<?php
    $html = strip_tags(urldecode(@$_POST['data']),'<div><table><p><tr><th><tbody><thead><td><h1><h2>');
    
    require_once('PHP/cssi/css_to_inline_styles.php');
    
    $cssToInlineStyles = new CSSToInlineStyles($html);
    $cssToInlineStyles->setUseInlineStylesBlock(true);
    $cssToInlineStyles->setCleanup(true);
    echo html_entity_decode(preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', '<div style="text-align:center;"><img src="'.PROY_URL.'IMG/stock/logo_OPSAL_mini.jpg"></div><h1>'.PROY_EMPRESA.'</h1>'.$cssToInlineStyles->convert()));
?>
</body>