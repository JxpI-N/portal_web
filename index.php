<?php
session_start();
include 'db.php';
?>
<!DOCTYPE html>
<html lang="es">
<head> <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal La Fortuna - Inicio</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <h1>Comunidad La Fortuna</h1>
        <p>Portal Interactivo Comunitario</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> | <a href="logout.php">Cerrar Sesión</a></p>
        <?php else: ?>
            <a href="login.php">Iniciar Sesión</a> | <a href="register.php">Registrarse</a>
        <?php endif; ?>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="actividades.php">Actividades</a></li>
            <li><a href="noticias.php">Noticias</a></li>
            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'administrador'): ?>
                <li><a href="admin_panel.php">Panel Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main>
        <section id="bienvenida">
            <h2>Bienvenido a La Fortuna</h2>
            <p>Tu portal para participar, informarte y conectar con la comunidad.</p>
            <!-- Asistente IA -->
            <div id="chatbot">
                <h3>Asistente IA</h3>
                <div id="chat-messages"></div>
                <input type="text" id="user-input" placeholder="Pregunta sobre la página...">
                <button onclick="sendMessage()">Enviar</button>
            </div>
        </section>
        <section id="eventos">
            <h2>Próximos Eventos</h2>
            <ul>
                <?php
                $sql = "SELECT titulo, descripcion, fecha FROM actividad ORDER BY fecha DESC LIMIT 5";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><strong>" . htmlspecialchars($row['titulo']) . ":</strong> " . htmlspecialchars($row['descripcion']) . " Fecha: " . htmlspecialchars($row['fecha']) . "</li>";
                    }
                } else {
                    echo "<li>No hay eventos próximos.</li>";
                }
                ?>
            </ul>
            <a href="actividades.php"><button>Ver Todas</button></a>
        </section>
        <section id="noticias">
            <h2>Últimas Noticias</h2>
            <ul>
                <?php
                $sql = "SELECT titulo, contenido, fecha FROM noticia ORDER BY fecha DESC LIMIT 5";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><strong>" . htmlspecialchars($row['titulo']) . ":</strong> " . htmlspecialchars(substr($row['contenido'], 0, 100)) . "... Fecha: " . htmlspecialchars($row['fecha']) . "</li>";
                    }
                } else {
                    echo "<li>No hay noticias.</li>";
                }
                ?>
            </ul>
            <a href="noticias.php"><button>Ver Todas</button></a>
        </section>
        <!-- Chat Anónimo (solo para usuarios) -->
        <section id="chat-anonimo">
            <h2>Chat Anónimo</h2>
            <div id="chat-messages-anonimo"></div>
            <input type="text" id="anon-message" placeholder="Mensaje anónimo...">
            <button onclick="sendAnonMessage()">Enviar</button>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Comunidad La Fortuna.</p>
    </footer>
    <script>
        // Asistente IA simple
        function sendMessage() {
            const input = document.getElementById('user-input').value;
            const messages = document.getElementById('chat-messages');
            messages.innerHTML += '<p>Tú: ' + input + '</p>';
            // Simula respuesta IA
            setTimeout(() => {
                let response = 'Esta página es un portal comunitario donde puedes ver actividades, noticias y contactar a la comunidad. ¿Qué más quieres saber?';
                messages.innerHTML += '<p>IA: ' + response + '</p>';
            }, 1000);
            document.getElementById('user-input').value = '';
        }

        // Chat Anónimo
        function sendAnonMessage() {
            const message = document.getElementById('anon-message').value;
            $.post('send_chat.php', { message: message }, function() {
                loadChat();
            });
            document.getElementById('anon-message').value = '';
        }

        function loadChat() {
            $.get('get_chat.php', function(data) {
                $('#chat-messages-anonimo').html(data);
            });
        }
        setInterval(loadChat, 2000);  // Actualiza cada 2 segundos
    </script>
</body>
</html>
<?php $conn->close(); ?>