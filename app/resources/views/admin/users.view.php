<?php
    require_once __DIR__ . '/../../../models/user.php';
    $usersModel = new \app\models\User();
    $users = $usersModel->getAllUsers(100);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - BookVerse Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BookVerse/app/resources/views/admin/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
/* Modal flotante */
#user-form-modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: var(--color-white);
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    z-index: 1000;
    width: 400px;
    max-height: 80vh;
    overflow-y: auto;
}

/* Estilos para modo oscuro del modal */
.dark-mode-variables #user-form-modal {
    background-color: var(--color-dark);
    color: var(--color-white);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: var(--color-dark);
}

.dark-mode-variables .form-group label {
    color: var(--color-white);
}

input, select, button {
    width: 100%;
    margin-bottom: 10px;
    padding: 12px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: var(--color-white);
    color: var(--color-dark);
}

.dark-mode-variables input,
.dark-mode-variables select {
    background-color: var(--color-dark);
    border-color: var(--color-info-dark);
    color: var(--color-white);
}

button[type="submit"] {
    background-color: #2196F3;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: #1976D2;
}

button[type="button"] {
    background-color: #f44336;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

button[type="button"]:hover {
    background-color: #d32f2f;
}

.close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: var(--color-dark);
}

.dark-mode-variables .close {
    color: var(--color-white);
}

.image-preview {
    width: 80px;
    height: 80px;
    border: 2px dashed #ccc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 10px auto;
    overflow: hidden;
    cursor: pointer;
    transition: border-color 0.3s;
}

.image-preview:hover {
    border-color: #2196F3;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview.empty {
    background-color: #f8f9fa;
    color: #6c757d;
}

.dark-mode-variables .image-preview.empty {
    background-color: var(--color-info-dark);
    color: var(--color-white);
}

/* Tabla responsive */
.recent-orders {
    overflow-x: auto;
}

table {
    width: 100%;
    min-width: 800px;
}

.profile-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.bi {
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
    #user-form-modal {
        width: 90%;
        max-width: 350px;
    }
}
</style>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside>
            <div class="sidebar">
                <a href="/BookVerse/admin">
                    <span class="material-icons-sharp">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="/BookVerse/admin/users" class="active">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Users</h3>
                </a>
                <a href="/BookVerse/admin/books">
                    <span class="material-icons-sharp">library_books</span>
                    <h3>Books</h3>
                </a>
                <a href="/BookVerse/auth/logout">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Cerrar Sesión</h3>
                </a>
            </div>
        </aside>

        <main>
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">menu</span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active">light_mode</span>
                    <span class="material-icons-sharp">dark_mode</span>
                </div>

                <div class="profile">
                    <div class="info">
                        <p>Hey, <b><?= $_SESSION['full_name'] ?? 'Admin' ?></b></p>
                        <small class="text-muted">Administrador</small>
                    </div>
                    <div class="profile-photo" id="profile-photo-clickable" style="cursor:pointer;">
                        <img src="/BookVerse/app/public/images/profile-1.jpg" alt="Profile Photo">
                    </div>
                </div>
            </div>
            
            <h1>Gestión de Usuarios</h1>
            
            <div class="users">
                <div class="recent-orders">
                    <h2>Lista de Usuarios</h2>
                    <div style="margin-bottom: 20px;">
                        <button onclick="agregarUsuario()" style="background: #2196F3; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                            <i class="bi bi-plus-circle"></i> Agregar Usuario
                        </button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha Creación</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="user-table-body">
                            <?php foreach ($users as $user): ?>
                            <tr data-user-id="<?= htmlspecialchars($user['id']) ?>">
                                <td>
                                    <img src="<?= htmlspecialchars($user['imagen'] ?? '/BookVerse/app/public/images/default-avatar.png') ?>" 
                                         alt="Profile Photo" class="profile-img">
                                </td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($user['created_at']))) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($user['updated_at']))) ?></td>
                                <td>
                                    <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 8px;" onclick="editarUsuario(this.parentElement.parentElement)">
                                        <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
                                    </button>
                                    <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 8px;" onclick="eliminarUsuario(this.parentElement.parentElement)">
                                        <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="user-form-modal">
        <span class="close" onclick="cerrarFormulario()">&times;</span>
        <h3>Agregar Usuario</h3>
        <form id="userForm" onsubmit="guardarUsuario(event)">
            <div class="form-group">
                <label for="imagen-input">Imagen de Perfil:</label>
                <div class="image-preview empty" id="image-preview" onclick="document.getElementById('imagen-input').click()">
                    <span>Seleccionar imagen</span>
                </div>
                <input type="file" id="imagen-input" accept="image/*" style="display: none;" onchange="previewImage(this)">
                <input type="hidden" id="imagen-base64" name="imagen">
            </div>
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="full_name">Nombre Completo:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <small style="color: #666; font-size: 12px;">Debe coincidir con la contraseña ingresada</small>
            </div>
            <div class="form-group">
                <label for="role">Rol:</label>
                <select id="role" name="role" required>
                    <option value="">Seleccione</option>
                    <option value="user">Usuario</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarFormulario()">Cancelar</button>
        </form>
    </div>

    <script src="/BookVerse/app/resources/views/admin/JS/index.js"></script>
    <script src="/BookVerse/app/resources/views/admin/JS/usuarios.js"></script>
</body>
</html>
