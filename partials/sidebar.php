<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
<link rel="stylesheet" href="/oficialiadepartes/css/globals/style-sidebar.css">

<!-- partials/sidebar.php -->
<div id="themeLoading" class="theme-loading"></div>
<!-- Sidebar -->
<aside class="sidebar">

    <div class="sidebar-header">
        <div class="user-avatar"><?php echo substr($_SESSION['nombre'], 0, 1); ?></div>
        <button class="toggler sidebar-toggler">
            <span class="material-symbols-rounded">chevron_left</span>
        </button>
        <button class="toggler menu-toggler">
            <span class="material-symbols-rounded">menu</span>
        </button>
    </div>
    
    <nav class="sidebar-nav">
    <ul class="nav-list primary-nav">

        <?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Usuario'): ?>
        <li class="nav-item">
            <a class="nav-link" href="index.php?action=homedash">
                <span class="nav icon material-symbols-rounded">Home</span>
                <span class="nav-label">Inicio</span>
            </a>
            <span class="nav-tooltip">Inicio</span>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <li class="nav-item">
                <a class="nav-link" href="index.php?action=areasadmin">
                    <span class="nav icon material-symbols-rounded">Apartment</span>
                    <span class="nav-label">Áreas</span>
                </a>
                <span class="nav-tooltip">Áreas</span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?action=usersadmin">
                    <span class="nav icon material-symbols-rounded">Group</span>
                    <span class="nav-label">Usuarios</span>
                </a>
                <span class="nav-tooltip">Usuarios</span>
            </li>
        <?php endif; ?>

        <?php if ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Usuario'): ?>
        <li class="nav-item">
            <a class="nav-link" href="index.php?action=expedientes">
                <span class="nav icon material-symbols-rounded">Folder</span>
                <span class="nav-label">Expedientes</span>
            </a>
            <span class="nav-tooltip">Expedientes</span>
        </li>
        <?php endif; ?>

        <?php if ($_SESSION['tipo_usuario'] === 'Guardia'): ?>
            <li class="nav-item">
            <a class="nav-link" href="index.php?action=registrar">
                <span class="nav icon material-symbols-rounded">edit_document</span>
                <span class="nav-label">Registrar</span>
            </a>
            <span class="nav-tooltip">Registrar</span>
          </li>
        <?php endif; ?>

    </ul>

    <ul class="nav-list secondary-nav">

        <li class="nav-item mt-4">
            <a class="nav-link" href="index.php?action=config">
                <span class="nav icon material-symbols-rounded">Settings</span>
                <span class="nav-label">Configuración</span>
            </a>
            <span class="nav-tooltip">Configuración</span>
        </li>

        <li class="nav-item">
            <a class="nav-link" id="themeToggle">
                <span class="nav icon material-symbols-rounded">dark_mode</span>
                <span class="nav-label">Cambiar tema</span>
            </a>
            <span class="nav-tooltip">Cambiar tema</span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="index.php?action=logout">
                <span class="nav icon material-symbols-rounded">Logout</span>
                <span class="nav-label">Cerrar Sesión</span>
            </a>
            <span class="nav-tooltip">Cerrar Sesión</span>
        </li>

    </ul>
    </nav>

</aside>
