<?php
error_log('hit');
$arrCSS[] = 'CSS/formitable';
include ('PHP/Formitable/Formitable.class.php');
$newForm = new Formitable( db_obtener_link_legado() ,"opsal","opsal_usuarios" );
$newForm->msg_updateSuccess = 'Agencia editada exitosamente.';
$newForm->setPrimaryKey("codigo_usuario"); 
if (isset($_POST['submit'])) $newForm->submitForm(); 
$newForm->openForm('action="" method="post"');
$newForm->hideField("codigo_usuario");
$newForm->hideField("clave"); 
$newForm->hideField("grupo"); 
$newForm->forceTypes(array("usuario","correo","nombre","nombre_fiscal","tipo_de_documento","registro_de_iva","nit","direccion","departamento","giro"),array("text","text","text","text","text","text","text","text","text","text"));
$newForm->printForm();
?>