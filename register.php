<?php
session_start();
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar CAPTCHA (simulado; integra Google reCAPTCHA)
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        echo "Completa el CAPTCHA.";
        exit;
    }
    // Procesar registro
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $ci = $_POST['ci'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $edad = $_POST['edad'];
    $genero = $_POST['genero'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $foto = '';
    if (isset($_FILES['foto'])) {
        $foto = 'uploads/' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
    }
    $rol = $_POST['rol'];  // 'usuario' o 'administrador'
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, ci, telefono, direccion, edad, genero, email, password, foto, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssisssss", $nombre, $apellido, $ci, $telefono, $direccion, $edad, $genero, $email, $password, $foto, $rol);
    if ($stmt->execute()) {
        echo "Registro exitoso.";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<form method="POST" enctype="multipart/form-data"> <link rel="stylesheet" href="style.css">
    Nombre: <input type="text" name="nombre" required><br>
    Apellido: <input type="text" name="apellido" required><br>
    CI: <input type="text" name="ci" required><br>
    Teléfono: <input type="text" name="telefono"><br>
    Dirección: <textarea name="direccion"></textarea><br>
    Edad: <input type="number" name="edad"><br>
    Género: <select name="genero"><option value="masculino">Masculino</option><option value="femenino">Femenino</option><option value="otro">Otro</option></select><br>
    Email: <input type="email" name="email" required><br>
    Contraseña: <input type="password" name="password" required><br>
    Foto: <input type="file" name="foto"><br>
    Rol: <select name="rol"><option value="usuario">Usuario</option><option value="administrador">Administrador</option></select><br>
    <div class="g-recaptcha" data-sitekey="tu_site_key"></div>  <!-- Integra Google reCAPTCHA -->
    <button type="submit">Registrarse</button>
</form>
<script src="https://www.google.com/recaptcha/api.js"></script>