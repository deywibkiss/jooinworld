<?php 
// INGRESE EL EMAIL DONDE RECIBIRÁ LOS DATOS DEL FOMULARIO

if(isset($_POST["nombre"]) && isset($_POST["email"])  ){
$mycorreo = "robinson.perdomo@sainetingenieria.com";
$desde = "Escribieron desde tu sitio web";
$sucorreo = $_POST["email"];
$contenido = "Señor ".$_POST["nombre"]."\n";
$contenido .= "Correo : ".$_POST["email"]."\n";
$contenido .= "Telefono : ".$_POST["telefono"]."\n";
$contenido .= "Ciudad : ".$_POST["ciudad"]."\n";
$contenido .= "Mensaje : ".$_POST["mensaje"];

$header = "From:".$_POST["email"]."\nReply-To:".$_POST["email"]."\n";
$header .= "X-Mailer:PHP/".phpversion()."\n";
$header .= "Mime-Version: 1.0\n";
$header .= "Content-Type: text/plain";

mail($mycorreo, $desde, utf8_encode($contenido) ,$header);
	header( 'Location:gracias.html' );
}



?>