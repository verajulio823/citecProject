<?php
if(isset($_POST['email'])) {

// Debes editar las próximas dos líneas de código de acuerdo con tus preferencias
$email_to = "verajulio823@gmail.com";
$email_subject = "Contacto CITIE AREQUIPA 2016";

// Aquí se deberían validar los datos ingresados por el usuario
if(!isset($_POST['nombre']) ||
!isset($_POST['apellido']) ||
!isset($_POST['departamento']) ||
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

$email_message = "Detalles del formulario de contacto:\n\n";
$email_message .= "Nombre: " . $_POST['nombre'] . "\n";
$email_message .= "Apellido: " . $_POST['apellido'] . "\n";
$email_message .= "Departamento: " . $_POST['departamento'] . "\n";
$email_message .= "Institucion: " . $_POST['institucion'] . "\n";
$email_message .= "Direccion: " . $_POST['direccion'] . "\n\n";
$email_message .= "Ciudad: " . $_POST['ciudad'] . "\n\n";
$email_message .= "CodigoPostal: " . $_POST['codigoPostal'] . "\n\n";
$email_message .= "Pais: " . $_POST['pais'] . "\n\n";
$email_message .= "Telefono: " . $_POST['telefono'] . "\n\n";
$email_message .= "Email: " . $_POST['email'] . "\n\n";



// Ahora se envía el e-mail usando la función mail() de PHP

$headers = "From: verajulio823@gmail.com"."\r\n".
"CC: verajulio823@gmail.com";

$respuesta=@mail($email_to, $email_subject, $email_message, $headers);
if ( $respuesta == true) {
            echo 'El email se envió exitosamente';
        }
        else {
            echo 'Hubo un problema en el envío del mensaje';
        }

}
?>