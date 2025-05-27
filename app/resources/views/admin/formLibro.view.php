<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../functions/session_functions.php';
require_once __DIR__ . '/../../../models/books.php';

requireAdmin();

$bookId = isset($_GET['id']) ? $_GET['id'] : null;
$book = null;
$isEdit = false;

if ($bookId) {
    $booksModel = new \app\models\books();
    $book = $booksModel->getBookById($bookId);
    $isEdit = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BookVerse/app/resources/views/admin/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title><?= $isEdit ? 'Editar' : 'Agregar' ?> Libro</title>
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--color-white);
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--color-info-light);
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .button-group button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn {
            background-color: var(--color-primary);
            color: var(--color-white);
        }

        .cancel-btn {
            background-color: var(--color-light);
            color: var(--color-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar Section -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <h2>Book<span style="color: #8000ff;">Verse</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>

            <div class="sidebar">
                <a href="/BookVerse/admin">
                    <span class="material-icons-sharp">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="/BookVerse/admin/users">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Usuarios</h3>
                </a>
                <a href="/BookVerse/admin/books" class="active">
                    <span class="material-icons-sharp">library_books</span>
                    <h3>Libros</h3>
                </a>
                <a href="/BookVerse/auth/logout">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Cerrar Sesión</h3>
                </a>
            </div>
        </aside>

        <main>
            <div class="form-container">
                <h2><?= $isEdit ? 'Editar' : 'Agregar Nuevo' ?> Libro</h2>
                <form id="bookForm" onsubmit="saveBook(event)">
                    <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($book['id']) ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="title">Título</label>
                        <input type="text" id="title" name="title" required
                            value="<?= $isEdit ? htmlspecialchars($book['title']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="author">Autor</label>
                        <input type="text" id="author" name="author" required
                            value="<?= $isEdit ? htmlspecialchars($book['author']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="genre">Género</label>
                        <select id="genre" name="genre" required>
                            <option value="">Seleccionar género</option>
                            <?php
                            $genres = ['Ficción', 'No ficción', 'Misterio', 'Romance', 'Ciencia ficción', 'Fantasía', 'Drama', 'Terror'];
                            foreach ($genres as $genre) {
                                $selected = $isEdit && $book['genre'] === $genre ? 'selected' : '';
                                echo "<option value=\"$genre\" $selected>$genre</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" required
                            value="<?= $isEdit ? htmlspecialchars($book['isbn']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="publication_year">Año de publicación</label>
                        <input type="number" id="publication_year" name="publication_year" required
                            min="1800" max="<?= date('Y') ?>"
                            value="<?= $isEdit ? htmlspecialchars($book['publication_year']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" required><?= $isEdit ? htmlspecialchars($book['description']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cover_image">URL de la imagen de portada</label>
                        <input type="url" id="cover_image" name="cover_image" required
                            value="<?= $isEdit ? htmlspecialchars($book['cover_image']) : '' ?>">
                    </div>

                    <div class="button-group">
                        <button type="button" class="cancel-btn" onclick="window.location.href='/BookVerse/admin/books'">
                            Cancelar
                        </button>
                        <button type="submit" class="save-btn">
                            <?= $isEdit ? 'Guardar cambios' : 'Crear libro' ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="/BookVerse/app/resources/views/admin/JS/index.js"></script>
    <script>
    function saveBook(event) {
        event.preventDefault();
        const formData = new FormData(document.getElementById('bookForm'));
        const bookData = Object.fromEntries(formData.entries());

        const url = bookData.id ?
            `/BookVerse/admin/books/update/${bookData.id}` :
            '/BookVerse/admin/books/create';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: bookData.id ? 'Libro actualizado correctamente' : 'Libro creado correctamente',
                    icon: 'success',
                    confirmButtonColor: '#8000ff'
                }).then(() => {
                    window.location.href = '/BookVerse/admin/books';
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message || 'Ha ocurrido un error',
                    icon: 'error',
                    confirmButtonColor: '#8000ff'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Ha ocurrido un error en la comunicación con el servidor',
                icon: 'error',
                confirmButtonColor: '#8000ff'
            });
        });
    }
    </script>
</body>
</html>
