<?php
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'path/to/PHPMailer/src/Exception.php';
require 'path/to/PHPMailer/src/PHPMailer.php';
require 'path/to/PHPMailer/src/SMTP.php';  // Para PHPMailer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        echo "Email inválido.";
        exit;
    }

    // Verificar si el email existe en la base de datos
    $stmt_check = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows == 0) {
        echo "Email no registrado.";
        exit;
    }

    // Generar código y expiración
    $codigo = rand(100000, 999999);
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Insertar en la tabla recuperacion_contrasena
    $stmt = $conn->prepare("INSERT INTO recuperacion_contrasena (email, codigo, expiracion) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $codigo, $expiracion);
    if ($stmt->execute()) {
        // Enviar email con PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP (ajusta con tus credenciales)
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com';  // Cambia por tu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'tu_email@example.com';  // Tu email
            $mail->Password = 'tu_contraseña';  // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatarios
            $mail->setFrom('noreply@fortuna.com', 'Portal La Fortuna');
            $mail->addAddress($email);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Código de Recuperación de Contraseña';
            $mail->Body = "Tu código de recuperación es: <strong>$codigo</strong>. Expira en 1 hora.";

            $mail->send();
            echo "Código enviado a tu email.";
        } catch (Exception $e) {
            echo "Error al enviar email: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error al generar código.";
    }
}

// Formulario para ingresar código y nueva contraseña (si se envía POST con 'codigo')
if (isset($_POST['codigo'])) {
    $codigo_ingresado = $_POST['codigo'];
    $nueva_password = password_hash($_POST['nueva_password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    // Verificar código
    $stmt_verify = $conn->prepare("SELECT * FROM recuperacion_contrasena WHERE email = ? AND codigo = ? AND expiracion > NOW() AND usado = FALSE");
    $stmt_verify->bind_param("ss", $email, $codigo_ingresado);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();
    if ($result_verify->num_rows > 0) {
        // Actualizar contraseña
        $stmt_update = $conn->prepare("UPDATE usuario SET password = ? WHERE email = ?");
        $stmt_update->bind_param("ss", $nueva_password, $email);
        $stmt_update->execute();

        // Marcar código como usado
        $stmt_used = $conn->prepare("UPDATE recuperacion_contrasena SET usado = TRUE WHERE email = ? AND codigo = ?");
        $stmt_used->bind_param("ss", $email, $codigo_ingresado);
        $stmt_used->execute();

        echo "Contraseña actualizada exitosamente.";
    } else {
        echo "Código inválido o expirado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head> <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - Portal La Fortuna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Recuperar Contraseña</h1>
    </header>
    <main>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <button type="submit">Enviar Código</button>
        </form>
        <form method="POST">
            <input type="hidden" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <label for="codigo">Código:</label>
            <input type="text" name="codigo" required>
            <label for="nueva_password">Nueva Contraseña:</label>
            <input type="password" name="nueva_password" required>
            <button type="submit">Cambiar Contraseña</button>
        </form>
    </main>
    <footer>
        <p>&copy; 2023 Comunidad La Fortuna.</p>
    </footer>
</body>
</html>

<?php $conn->close(); ?>