<?php
session_start();
require_once __DIR__ . '/../../classes/DB.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /BookVerse/auth/login');
    exit;
}

// Obtener la información del usuario
$db = new \app\classes\DB();
$user = $db->setTable('users')
    ->select(['id', 'username', 'email', 'profile_image', 'created_at'])
    ->where([['id', '=', $_SESSION['user_id']]])
    ->get()[0] ?? null;

if (!$user) {
    header('Location: /BookVerse/logout');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - BookVerse</title>
    <link rel="stylesheet" href="/BookVerse/app/public/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <nav>
        <div class="brand"><strong>BookVerse</strong></div>
        <div class="nav-links">
            <a href="/BookVerse/">Inicio</a>
            <a href="/BookVerse/categories">Categorías</a>
            <a href="/BookVerse/popular">Más populares</a>
            <a href="/BookVerse/profile" class="active">
                <i class="bi bi-person-circle"></i> Mi Perfil
            </a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="/BookVerse/admin/books">
                    <i class="bi bi-gear"></i> Administrar
                </a>
            <?php endif; ?>
            <a href="/BookVerse/logout">
                <button class="btn btn-outline">Cerrar Sesión</button>
            </a>
        </div>
    </nav>

    <main class="user-config">
        <h2>Configuración de la cuenta</h2>

        <div class="config-container">
            <div class="profile-section">
                <div class="profile-image">
                    <img src="<?= htmlspecialchars($user['profile_image'] ?? '/BookVerse/public/images/default-avatar.jpg') ?>" 
                         alt="Foto de perfil">
                    <button class="change-photo-btn" onclick="document.getElementById('profile_image').click()">
                        <i class="bi bi-camera"></i>
                    </button>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;"
                           onchange="updateProfileImage(this)">
                </div>

                <div class="profile-info">
                    <h3><?= htmlspecialchars($user['username']) ?></h3>
                    <p>Miembro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>

            <form id="updateProfileForm" class="config-form">
                <div class="form-group">
                    <label for="username">Nombre de usuario</label>
                    <input type="text" id="username" name="username" 
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="current_password">Contraseña actual</label>
                    <input type="password" id="current_password" name="current_password">
                </div>

                <div class="form-group">
                    <label for="new_password">Nueva contraseña</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar nueva contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn save-btn">Guardar cambios</button>
                </div>
            </form>

            <div class="danger-zone">
                <h3>Zona de peligro</h3>
                <p>Una vez que elimines tu cuenta, no hay vuelta atrás. Por favor, esté seguro.</p>
                <button onclick="deleteAccount()" class="btn delete-btn">
                    <i class="bi bi-exclamation-triangle"></i> Eliminar cuenta
                </button>
            </div>
        </div>
    </main>

    <style>
        .user-config {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .config-container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .profile-section {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .profile-image {
            position: relative;
        }

        .profile-image img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }

        .change-photo-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #c300ff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .change-photo-btn:hover {
            transform: scale(1.1);
        }

        .profile-info h3 {
            margin: 0;
            color: #333;
        }

        .profile-info p {
            color: #666;
            margin: 0.5rem 0 0;
        }

        .config-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            color: #444;
            font-size: 0.9rem;
        }

        .form-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group input:focus {
            border-color: #c300ff;
            outline: none;
        }

        .form-actions {
            margin-top: 1rem;
        }

        .save-btn {
            background: #c300ff;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .save-btn:hover {
            background: #a100d1;
        }

        .danger-zone {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .danger-zone h3 {
            color: #dc3545;
            margin: 0 0 1rem;
        }

        .danger-zone p {
            color: #666;
            margin-bottom: 1rem;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s;
        }

        .delete-btn:hover {
            background: #c82333;
        }
    </style>

    <script>
    function updateProfileImage(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('profile_image', input.files[0]);

            fetch('/BookVerse/api/user/update-image', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error al actualizar la imagen de perfil');
                }
            });
        }
    }

    document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('/BookVerse/api/user/update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Perfil actualizado correctamente');
                window.location.reload();
            } else {
                alert(data.message || 'Error al actualizar el perfil');
            }
        });
    });

    function deleteAccount() {
        if (confirm('¿Estás seguro de que quieres eliminar tu cuenta? Esta acción no se puede deshacer.')) {
            fetch('/BookVerse/api/user/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/BookVerse/logout';
                } else {
                    alert('Error al eliminar la cuenta');
                }
            });
        }
    }
    </script>
</body>
</html>
