<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$is_admin = ($_SESSION['rol'] == 'administrador');
?>
<!DOCTYPE html>
<html lang="es">
<head> <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividades - Portal La Fortuna</title>
    <link rel="stylesheet" href="style.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
</head>
<body>
    <header>
        <h1>Actividades Comunitarias</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> | <a href="logout.php">Cerrar Sesión</a></p>
    </header>
    <nav>
        <ul>
            <li><a href="index.php">Inicio</a></li>
            <li><a href="actividades.php">Actividades</a></li>
            <li><a href="noticias.php">Noticias</a></li>
            <li><a href="contacto.php">Contacto</a></li>
            <?php if ($is_admin): ?>
                <li><a href="admin_panel.php">Panel Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main>
        <section id="lista-actividades">
            <h2>Lista de Actividades</h2>
            <ul>
                <?php
                $sql = "SELECT id_actividad, titulo, descripcion, fecha, foto FROM actividad ORDER BY fecha DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><strong>" . htmlspecialchars($row['titulo']) . ":</strong> " . htmlspecialchars($row['descripcion']) . " Fecha: " . htmlspecialchars($row['fecha']);
                        if ($row['foto']) echo " <img src='" . htmlspecialchars($row['foto']) . "' alt='Foto' width='100'>";
                        if ($is_admin) echo " <a href='edit_actividad.php?id=" . $row['id_actividad'] . "'>Editar</a>";
                        echo "</li>";
                    }
                } else {
                    echo "<li>No hay actividades.</li>";
                }
                ?>
            </ul>
            <?php if ($is_admin): ?>
                <a href="add_actividad.php"><button>Agregar Actividad</button></a>
            <?php endif; ?>
        </section>
        <section id="calendario">
            <h2>Calendario de Actividades</h2>
            <div id='calendar'></div>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Comunidad La Fortuna.</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php
                    $sql = "SELECT titulo, fecha FROM actividad";
                    $result = $conn->query($sql);
                    $events = [];
                    while ($row = $result->fetch_assoc()) {
                        $events[] = "{ title: '" . addslashes($row['titulo']) . "', start: '" . $row['fecha'] . "' }";
                    }
                    echo implode(',', $events);
                    ?>
                ]
            });
            calendar.render();
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>