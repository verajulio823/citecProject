<?php 

if(isset($_POST['email'])) {

// Debes editar las próximas dos líneas de código de acuerdo con tus preferencias


// Aquí se deberían validar los datos ingresados por el usuario
if(!isset($_POST['nombre']) ||
!isset($_POST['apellido']) ||
!isset($_POST['institucion']) ||
!isset($_POST['direccion']) ||
!isset($_POST['ciudad']) ||
!isset($_POST['codigoPostal']) ||
!isset($_POST['pais']) ||
!isset($_POST['telefono']) ||
!isset($_POST['email'])) {

echo "<b>Ocurrió un error y el formulario no ha sido enviado. </b><br />";
echo "Por favor, vuelva atrás y verifique la información ingresada<br />";
die();
}
}

$archivoG = $_FILES['filesgeneral'];
$archivoE = $_FILES['filesestudiantes'];

$email_message = "Detalles del formulario de contacto:\n\n";
$email_message .= "Nombre: " . $_POST['nombre'] . "\n";
$email_message .= "Apellido: " . $_POST['apellido'] . "\n";
$email_message .= "Institucion: " . $_POST['institucion'] . "\n";
$email_message .= "Direccion: " . $_POST['direccion'] . "\n\n";
$email_message .= "Ciudad: " . $_POST['ciudad'] . "\n\n";
$email_message .= "CodigoPostal: " . $_POST['codigoPostal'] . "\n\n";
$email_message .= "Pais: " . $_POST['pais'] . "\n\n";
$email_message .= "Telefono: " . $_POST['telefono'] . "\n\n";
$email_message .= "Email: " . $_POST['email'] . "\n\n";

 
require '/var/www/html/citieoriginal/it-worker/PHPMailer/class.phpmailer.php';
require '/var/www/html/citieoriginal/it-worker/PHPMailer/class.smtp.php';
   
$mail = new PHPMailer();   
    
$mail->IsSMTP();   
$mail->Host = 'smtp.gmail.com'; 
$mail->SMTPDebug  = 0;
$mail->Port = 465; 
$mail->SMTPAuth = true; 
$mail->SMTPSecure="ssl";
$mail->Username = 'citiearequipa@gmail.com'; 
$mail->Password = 'arequipa2016'; 

$mail->From = "citiearequipa@gmail.com";   
$mail->FromName = "Inscripcion";   
$mail->Subject = "Inscripcion";   
$mail->AddAddress("citiearequipa@gmail.com");   

$mail->AddAttachment($archivoG['tmp_name'], $archivoG['name']);
$mail->AddAttachment($archivoE['tmp_name'], $archivoE['name']);
   
//$mail->WordWrap = 50;   
   
//$body  = "Hi, es un…";   
//$body .= "mensaje de prueba exitoso";   
   
$mail->Body = $email_message;   
   
if( !$mail->Send() ) 
{  
    Header( "Location: error.html" );   
} 
else 
{   
	Header( "Location: gracias.html" );
    //echo "Mensaje enviado";   
}   
   


?>
