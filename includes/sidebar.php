<div class="sidebar">
    <div class="sidebar-header">
        <h2><span class="gradient-text">CityFix</span> AI</h2>
        <p class="small">Dashboard Municipal</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Panel Principal</span>
        </a>
        <a href="reportes_activos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reportes_activos.php' ? 'active' : ''; ?>">
            <i class="fas fa-map-marker-alt"></i>
            <span>Reportes Activos</span>
        </a>
        <a href="historial.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'historial.php' ? 'active' : ''; ?>">
            <i class="fas fa-history"></i>
            <span>Historial</span>
        </a>
        <a href="estadisticas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'estadisticas.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Estadísticas</span>
        </a>
        <a href="configuracion.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <div class="user-name">Administrador</div>
                <div class="user-email">admin@cityfix.ai</div>
            </div>
        </div>
    </div>
</div>