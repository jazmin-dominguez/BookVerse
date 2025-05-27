<?php
session_start();
require_once __DIR__ . '/../../classes/DB.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /BookVerse/auth/login');
    exit;
}

// Obtener los libros suscritos del usuario
$db = new \app\classes\DB();
$subscribedBooks = $db->setTable('user_books')
    ->select(['books.*'])
    ->join('INNER JOIN books ON user_books.book_id = books.id')
    ->where([['user_books.user_id', '=', $_SESSION['user_id']]])
    ->get();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Libros - BookVerse</title>
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

    <main class="user-books">
        <div class="recent-orders">
            <h2>Mis Libros Suscritos</h2>
            <?php if (!empty($subscribedBooks)): ?>
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Género</th>
                        <th>Fecha de publicación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribedBooks as $book): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($book['cover_image'] ?? '/BookVerse/public/images/default-book.jpg') ?>" 
                                 alt="<?= htmlspecialchars($book['title']) ?>" 
                                 style="width: 50px; height: auto; border-radius: 4px; display: block; margin: 0 auto;">
                        </td>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td><?= htmlspecialchars($book['genre']) ?></td>
                        <td><?= htmlspecialchars($book['publication_year']) ?></td>
                        <td>
                            <div class="actions">
                                <button onclick="window.location.href='/BookVerse/books/<?= $book['id'] ?>'" 
                                        title="Ver detalles" 
                                        class="action-btn view-btn">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="removeBook(<?= $book['id'] ?>)" 
                                        title="Eliminar de mi lista" 
                                        class="action-btn delete-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-books">
                <i class="bi bi-book" style="font-size: 3rem; color: #ccc;"></i>
                <p>No tienes libros suscritos todavía</p>
                <a href="/BookVerse/" class="btn">Explorar libros</a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <style>
        .user-books {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 0.5rem;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .view-btn {
            color: #c300ff;
        }

        .delete-btn {
            color: #ff3333;
        }

        .no-books {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .no-books p {
            margin: 1rem 0;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
        }
    </style>

    <script>
    function removeBook(bookId) {
        if (confirm('¿Estás seguro de que quieres eliminar este libro de tu lista?')) {
            fetch(`/BookVerse/api/books/unsubscribe/${bookId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error al eliminar el libro de tu lista');
                }
            });
        }
    }
    </script>
</body>
</html>
            </tr>
            <tr>
                <td>2</td>
                <td><img src="images/profile-1.jpg" alt="portada" style="width: 50px; height: auto; border-radius: 4px; display: block; margin: 0 auto;"></td>
                <td>El Principito</td>
                <td>Antoine de Saint-Exupéry</td>
                <td>Ficción</td>
                <td>06/04/1943</td>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <button title="Ver" style="background: none; color: #2196F3; border: none; padding: 6px;">
                        <i class="bi bi-hand-thumbs-up" style="font-size: 1.2rem;"></i>
                        </button>
                        <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 6px;">
                        <i class="bi bi-hand-thumbs-down" style="font-size: 1.2rem;"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>