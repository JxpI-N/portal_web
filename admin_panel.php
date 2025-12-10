<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'administrador') {
    header("Location: login.php");
    exit;
}

// Manejar eliminación de usuario
if (isset($_POST['delete_user'])) {
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    if ($user_id) {
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id_usuario = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        // Log auditoría
        $stmt_log = $conn->prepare("INSERT INTO auditoria (accion, id_admin, detalles) VALUES ('Eliminar usuario', ?, 'ID usuario: ?')");
        $stmt_log->bind_param("is", $_SESSION['user_id'], $user_id);
        $stmt_log->execute();
        echo "Usuario eliminado.";
    }
}

// Manejar creación de usuario
if (isset($_POST['create_user'])) {
    $nombre = filter_var($_POST['nombre'], FILTER_SANITIZE_STRING);
    $apellido = filter_var($_POST['apellido'], FILTER_SANITIZE_STRING);
    $ci = filter_var($_POST['ci'], FILTER_SANITIZE_STRING);
    $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_STRING);
    $direccion = filter_var($_POST['direccion'], FILTER_SANITIZE_STRING);
    $edad = filter_var($_POST['edad'], FILTER_VALIDATE_INT);
    $genero = $_POST['genero'];
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2097152) {  // 2MB
            $foto = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $foto);
        }
    }
    if ($nombre && $apellido && $ci && $email && $password) {
        $stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, ci, telefono, direccion, edad, genero, email, password, foto, rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssisssss", $nombre, $apellido, $ci, $telefono, $direccion, $edad, $genero, $email, $password, $foto, $rol);
        $stmt->execute();
        // Log auditoría
        $stmt_log = $conn->prepare("INSERT INTO auditoria (accion, id_admin, detalles) VALUES ('Crear usuario', ?, 'Email: ?')");
        $stmt_log->bind_param("is", $_SESSION['user_id'], $email);
        $stmt_log->execute();
        echo "Usuario creado.";
    } else {
        echo "Datos inválidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head> <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Panel Administrador - Portal La Fortuna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Panel de Administrador</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> | <a href="logout.php">Cerrar Sesión</a></p>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="actividades.php">Actividades</a></li>
            <li><a href="noticias.php">Noticias</a></li>
            <li><a href="contacto.php">Contacto</a></li>
            <li><a href="admin_panel.php">Panel Admin</a></li>
        </ul>
    </nav>
    <main>
        <section id="usuarios">
            <h2>Usuarios Registrados</h2>
            <table>
                <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Acciones</th></tr>
                <?php
                $sql = "SELECT id_usuario, nombre, apellido, email, rol FROM usuario";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>" . $row['id_usuario'] . "</td><td>" . htmlspecialchars($row['nombre'] . ' ' . $row['apellido']) . "</td><td>" . htmlspecialchars($row['email']) . "</td><td>" . $row['rol'] . "</td><td><form method='POST' onsubmit='return confirm(\"¿Eliminar usuario?\")'><input type='hidden' name='user_id' value='" . $row['id_usuario'] . "'><button type='submit' name='delete_user'>Eliminar</button></form></td></tr>";
                }
                ?>
            </table>
        </section>
        <section id="crear-usuario">
            <h2>Crear Nuevo Usuario</h2>
            <form method="POST" enctype="multipart/form-data">
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
                <button type="submit" name="create_user">Crear Usuario</button>
            </form>
        </section>
        <section id="chat-monitor">
            <h2>Chat Anónimo (Monitoreo)</h2>
            <div id="chat-messages-admin"></div>
            <script>
                function loadChatAdmin() {
                    fetch('get_chat.php').then(response => response.text()).then(data => {
                        document.getElementById('chat-messages-admin').innerHTML = data;
                    });
                }
                setInterval(loadChatAdmin, 2000);
            </script>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Comunidad La Fortuna.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>