<nav>
    <div class="brand"><strong>BookVerse</strong></div>
    <div class="nav-links">
        <a href="/BookVerse/user">Mi Biblioteca</a>
        <a href="/BookVerse/user/books">Explorar</a>
        <a href="/BookVerse/user/reviews">Mis Reseñas</a>
        <div class="user-menu">
            <img src="<?php echo $_SESSION['user']['profile_image'] ?? '/BookVerse/app/resources/images/default-avatar.png'; ?>" 
                 alt="Usuario" 
                 class="avatar">
            <div class="dropdown-menu">
                <a href="/BookVerse/user/profile">Mi Perfil</a>
                <a href="/BookVerse/user/config">Configuración</a>
                <a href="/BookVerse/auth/logout">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</nav>
