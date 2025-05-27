<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?> - BookVerse</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/BookVerse/profile">
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
            <?php else: ?>
                <a href="/BookVerse/auth/login">
                    <button class="btn btn-outline">Iniciar Sesión</button>
                </a>
                <a href="/BookVerse/auth/register">
                    <button class="btn">Registrarse</button>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="book-details">
        <div class="book-container">
            <div class="book-image">
                <img src="<?= htmlspecialchars($book['cover_image'] ?? '/BookVerse/public/images/default-book.jpg') ?>" 
                     alt="<?= htmlspecialchars($book['title']) ?>">
            </div>
            <div class="book-info">
                <h1><?= htmlspecialchars($book['title']) ?></h1>
                <p class="author">Por <?= htmlspecialchars($book['author']) ?></p>
                <p class="genre">Género: <?= htmlspecialchars($book['genre']) ?></p>
                <p class="year">Año de publicación: <?= htmlspecialchars($book['publication_year']) ?></p>
                <p class="isbn">ISBN: <?= htmlspecialchars($book['isbn']) ?></p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="actions">
                    <button class="btn" onclick="addToFavorites(<?= $book['id'] ?>)">
                        <i class="bi bi-heart"></i> Añadir a favoritos
                    </button>
                    <button class="btn" onclick="addToReadingList(<?= $book['id'] ?>)">
                        <i class="bi bi-bookmark"></i> Añadir a lista de lectura
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
        .book-details {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .book-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .book-image img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .book-info h1 {
            margin: 0 0 1rem;
            color: #333;
            font-size: 2rem;
        }

        .book-info p {
            margin: 0.5rem 0;
            color: #666;
        }

        .book-info .author {
            font-size: 1.2rem;
            color: #444;
        }

        .actions {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
        }

        .actions .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #c300ff;
            color: white;
            transition: background-color 0.3s ease;
        }

        .actions .btn:hover {
            background-color: #9f00cc;
        }

        @media (max-width: 768px) {
            .book-container {
                grid-template-columns: 1fr;
            }

            .book-image {
                max-width: 300px;
                margin: 0 auto;
            }
        }
    </style>

    <script>
    function addToFavorites(bookId) {
        fetch(`/BookVerse/api/favorites/add/${bookId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Libro añadido a favoritos');
            } else {
                alert('Error al añadir a favoritos');
            }
        });
    }

    function addToReadingList(bookId) {
        fetch(`/BookVerse/api/reading-list/add/${bookId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Libro añadido a la lista de lectura');
            } else {
                alert('Error al añadir a la lista de lectura');
            }
        });
    }
    </script>
</body>
</html>
