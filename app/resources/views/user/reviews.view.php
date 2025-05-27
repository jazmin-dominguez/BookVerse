<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reseñas - BookVerse</title>
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

    <main class="user-reviews">
        <h2>Mis Reseñas</h2>

        <?php if (!empty($reviews)): ?>
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="book-info">
                    <img src="<?= htmlspecialchars($review['cover_image'] ?? '/BookVerse/public/images/default-book.jpg') ?>" 
                         alt="<?= htmlspecialchars($review['title']) ?>">
                    <div>
                        <h3><?= htmlspecialchars($review['title']) ?></h3>
                        <p class="author"><?= htmlspecialchars($review['author']) ?></p>
                <th>Título</th>
                <th>Autor</th>
                <th>Género</th>
                <th>Reviews</th>
                
            </tr>
        </thead>
        <tbody id="book-table-body" style="text-align: center;">
            <!-- Aquí van los libros con JavaScript -->
            <tr>
                <td>1</td>
                <td><img src="images/profile-1.jpg" alt="portada" style="width: 50px; height: auto; border-radius: 4px; display: block; margin: 0 auto;"></td>
                <td>El Principito</td>
                <td>Antoine de Saint-Exupéry</td>
                <td>Ficción</td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <button title="Ver" style="background: none; color: #2196F3; border: none; padding: 6px;">
                        <i class="bi bi-check-square" style="font-size: 1.2rem;"></i>
                        </button>
                        <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 6px;">
                        <i class="bi bi-x-lg" style="font-size: 1.2rem;"></i>
                        </button>
                    </div>
                </td>
                
            </tr>
            <tr>
                <td>2</td>
                <td><img src="images/profile-1.jpg" alt="portada" style="width: 50px; height: auto; border-radius: 4px; display: block; margin: 0 auto;"></td>
                <td>El Principito</td>
                <td>Antoine de Saint-Exupéry</td>
                <td>Ficción</td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <button title="Ver" style="background: none; color: #2196F3; border: none; padding: 6px;">
                        <i class="bi bi-check-square"></i>
                        </button>
                        <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 6px;">
                        <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </td>
                
            </tr>
        </tbody>
    </table>
</div>