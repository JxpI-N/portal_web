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
    <title>Noticias - Portal La Fortuna</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Últimas Noticias</h1>
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
        <section id="lista-noticias">
            <h2>Lista de Noticias</h2>
            <ul>
                <?php
                $sql = "SELECT id_noticia, titulo, contenido, fecha, foto FROM noticia ORDER BY fecha DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<li><strong>" . htmlspecialchars($row['titulo']) . ":</strong> " . htmlspecialchars(substr($row['contenido'], 0, 200)) . "... Fecha: " . htmlspecialchars($row['fecha']);
                        if ($row['foto']) echo " <img src='" . htmlspecialchars($row['foto']) . "' alt='Foto' width='100'>";
                        if ($is_admin) echo " <a href='edit_noticia.php?id=" . $row['id_noticia'] . "'>Editar</a>";
                        echo "</li>";
                    }
                } else {
                    echo "<li>No hay noticias.</li>";
                }
                ?>
            </ul>
            <?php if ($is_admin): ?>
                <a href="add_noticia.php"><button>Agregar Noticia</button></a>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2023 Comunidad La Fortuna.</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>