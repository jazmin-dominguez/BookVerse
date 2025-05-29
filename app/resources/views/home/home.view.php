<?php
session_start();
require_once __DIR__ . '/../../functions/books_functions.php';

// Obtener libros recomendados (por ejemplo, los últimos 6 libros)
$recommendedBooks = getAllBooks(10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BookVerse - Inicio</title> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <nav>
        <div class="brand"><strong>BookVerse</strong></div>
        <div class="nav-links">
            <a href="/BookVerse/">Inicio</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/BookVerse/profile">
                    <i class="bi bi-person-circle"></i> Mi Perfil
                </a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="/BookVerse/admin/books">
                        <i class="bi bi-gear"></i> Administrar
                    </a>
                <?php endif; ?>
                <a href="/BookVerse/auth/logout">
                    <button class="btn btn-outline">Cerrar Sesión</button>
                </a>
            <?php else: ?>
                <a href="/BookVerse/auth/login">
                    <button class="btn btn-outline">Iniciar Sesión</button>
                </a>
                <a href="/BookVerse/auth/login">
                    <button class="btn">Registrarse</button>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <h2>Libros recomendados</h2>
        <div class="grid grid-3">
            <?php foreach ($recommendedBooks as $book): ?>
            <div class="card">
                <img src="<?= htmlspecialchars($book['cover_image'] ?? '/BookVerse/public/images/default-book.jpg') ?>" 
                     alt="<?= htmlspecialchars($book['title']) ?>">
                <div class="card-title" onclick="window.location.href='/BookVerse/books/view/<?= $book['id'] ?>'" style="cursor:pointer;">
                    <?= htmlspecialchars($book['title']) ?>
                    <div class="card-info">
                        <p class="author"><?= htmlspecialchars($book['author']) ?></p>
                        <p class="genre"><?= htmlspecialchars($book['genre']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($recommendedBooks)): ?>
        <p class="no-books">No hay libros disponibles en este momento.</p>
        <?php endif; ?>
    </main>

    <!-- Modal -->
    <div id="bookModal" class="modal" style="display: none;">
        <div class="modal-content">
            <!-- Contenido del modal se cargará aquí -->
        </div>
    </div> 

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc; /* Fondo claro moderno */
            color: #1a1a1a;
        }

        header, main, footer {
            max-width: 1200px;
            margin: auto;
            padding: 1rem;
        }

        /* NAV */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #d0e2f2;
            background-color: #c300ff; /* Azul principal (coherente con login) */
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #fff; /* Blanco para contraste con fondo azul */
            font-size: 0.95rem;
        }

        .btn {
            padding: 0.4rem 0.8rem;
            border: 1px solid #fff;
            background: transparent;
            color: #fff;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #0096c7;
        }

        /* SECTIONS */
        h2 {
            font-size: 1.2rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-3 {
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        }

        .card {
            border: none;
            padding: 0.75rem;
            text-align: center;
            background-color: #ffffff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        .card img {
            width: 100%;
            aspect-ratio: 3 / 4;
            background-color: #e0e0e0;
            object-fit: cover;
            margin-bottom: 0.5rem;
            border-radius: 4px;
        }

        .card-title {
            font-weight: bold;
            font-size: 0.95rem;
            margin-bottom: 0.2rem;
        }

        .card-author {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.4rem;
        }

        .card-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #777;
        }

        .filters {
            display: flex;
            align-items: flex-end;
            gap: 1rem;
            margin: 1rem 0 2rem;
        }

        .filters select {
            padding: 0.4rem;
            font-size: 0.85rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* FOOTER */
        footer {
            border-top: 1px solid #d0e2f2;
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #555;
            padding: 2rem 1rem;
            margin-top: 3rem;
        }

        footer div {
            width: 45%;
        }

        footer h2 {
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        footer a {
            color: #0077b6;
            text-decoration: none;
        }

        /* Tarjetas horizontales (solo para catálogo) */
        .horizontal-card {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            text-align: left;
            border: none;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
            padding: 0.75rem;
            border-radius: 8px;
        }

        .horizontal-card img {
            width: 120px;
            height: 160px;
            object-fit: cover;
            background-color: #eee;
            flex-shrink: 0;
            border-radius: 4px;
        }

        .card-info {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

    </style>

    <script>
    function viewBook(id) {
        fetch(`/BookVerse/books/view/${id}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('bookModal').innerHTML = html;
                document.getElementById('bookModal').style.display = 'block';
            })
            .catch(err => {
                console.error('Error cargando modal:', err);
            });
    }

    // Cerrar modal cuando se hace clic fuera de él
    window.onclick = function(event) {
        const modal = document.getElementById('bookModal');
        if (event.target === modal) {
            closeModal();
        }
    };

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    function closeModal() {
        const modal = document.getElementById('bookModal');
        modal.style.display = 'none';
        modal.querySelector('.modal-content').innerHTML = '';
    }
    </script>
</body>
</html>
