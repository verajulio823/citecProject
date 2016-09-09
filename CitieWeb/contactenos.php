<?php 


$email_message = "Detalles del formulario de contacto:\n\n";
$email_message .= "Nombre: " . $_POST['name'] . "\n";
$email_message .= "Apellido: " . $_POST['surname'] . "\n";
$email_message .= "Correo: " . $_POST['email'] . "\n";
$email_message .= "Telefono: " . $_POST['phone'] . "\n";
$email_message .= "Mensaje: " . $_POST['message'] . "\n\n";

 
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
$mail->FromName = "Contactenos";   
$mail->Subject = "Contactenos";   
$mail->AddAddress("citiearequipa@gmail.com");   

   
$mail->Body = $email_message;   
   
if( !$mail->Send() ) 
{  
   echo "<script language='javascript'>
alert('Mensaje enviado, muchas gracias.');
window.location.href = 'http://TUSITIOWEB.COM';
</script>";  
} 
else 
{   
	echo '<script language="javascript">alert("No se pudo enviar su mensaje");</script>'; 
    //echo "Mensaje enviado";   
}   
   


?>
