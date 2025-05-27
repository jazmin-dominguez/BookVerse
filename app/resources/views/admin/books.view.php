<?php
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../functions/session_functions.php';
require_once __DIR__ . '/../../../models/books.php';

requireAdmin();

$booksModel = new \app\models\books();
$books = $booksModel->getAllBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="app/resources/views/admin/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Administrar Libros</title>
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
                <a href="Bookverse/admin">
                    <span class="material-icons-sharp">dashboard</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="Bookverse/admin/users">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Usuarios</h3>
                </a>
                <a href="Bookverse/admin/books" class="active">
                    <span class="material-icons-sharp">library_books</span>
                    <h3>Libros</h3>
                </a>
                <a href="Bookverse/auth/logout">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Cerrar Sesión</h3>
                </a>
            </div>
        </aside>

        <main>
            <div class="filters" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; align-items: center;">
                <input type="text" id="searchInput" placeholder="Buscar libro..." style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc; flex: 1;">

                <select id="authorFilter" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="">Todos los autores</option>
                    <?php
                    $authors = $booksModel->getUniqueAuthors();
                    foreach ($authors as $author) {
                        echo "<option value=\"" . htmlspecialchars($author) . "\">" . htmlspecialchars($author) . "</option>";
                    }
                    ?>
                </select>

                <select id="genreFilter" style="padding: 0.5rem; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="">Todos los géneros</option>
                    <?php
                    $genres = $booksModel->getUniqueGenres();
                    foreach ($genres as $genre) {
                        echo "<option value=\"" . htmlspecialchars($genre) . "\">" . htmlspecialchars($genre) . "</option>";
                    }
                    ?>
                </select>

                <button onclick="window.location.href='index.php?uri=admin/books/add'" style="padding: 0.5rem 1rem; border: none; background-color: #8000ff; color: white; border-radius: 8px; cursor: pointer;">
                    <span class="material-icons-sharp">add</span> Nuevo libro
                </button>
            </div>

            <!-- Books Table -->
            <div class="recent-orders">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Género</th>
                            <th>ISBN</th>
                            <th>Año</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody">
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['genre']) ?></td>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= htmlspecialchars($book['publication_year']) ?></td>
                            <td>
                                <button onclick="editBook(<?= $book['id'] ?>)" class="edit-btn">
                                    <span class="material-icons-sharp">edit</span>
                                </button>
                                <button onclick="deleteBook(<?= $book['id'] ?>)" class="delete-btn">
                                    <span class="material-icons-sharp">delete</span>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="app/resources/views/admin/JS/index.js"></script>
    <script>
    function editBook(id) {
        window.location.href = `index.php?uri=admin/books/edit/${id}`;
    }

    function deleteBook(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8000ff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`index.php?uri=admin/books/delete/${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            '¡Eliminado!',
                            'El libro ha sido eliminado.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el libro.',
                            'error'
                        );
                    }
                });
            }
        });
    }

    // Filtrado en tiempo real
    document.getElementById('searchInput').addEventListener('input', filterBooks);
    document.getElementById('authorFilter').addEventListener('change', filterBooks);
    document.getElementById('genreFilter').addEventListener('change', filterBooks);

    function filterBooks() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const authorFilter = document.getElementById('authorFilter').value;
        const genreFilter = document.getElementById('genreFilter').value;
        const rows = document.getElementById('booksTableBody').getElementsByTagName('tr');

        for (let row of rows) {
            const title = row.cells[0].textContent.toLowerCase();
            const author = row.cells[1].textContent.toLowerCase();
            const isbn = row.cells[3].textContent.toLowerCase();

            const matchesSearch = title.includes(searchTerm) || author.includes(searchTerm) || isbn.includes(searchTerm);
            const matchesAuthor = !authorFilter || row.cells[1].textContent === authorFilter;
            const matchesGenre = !genreFilter || row.cells[2].textContent === genreFilter;

            row.style.display = matchesSearch && matchesAuthor && matchesGenre ? '' : 'none';
        }
    }
    </script>
</body>
</html>