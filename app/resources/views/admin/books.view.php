<?php
    require_once __DIR__ . '/../../../config.php';
    require_once __DIR__ . '/../../functions/session_functions.php';
    require_once __DIR__ . '/../../../models/books.php';

    requireAdmin();

    $booksModel = new \app\models\books();
    $books = $booksModel->getAllBooks();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Libros - BookVerse Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BookVerse/app/resources/views/admin/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
/* Modal flotante */
#book-form-modal {
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
    width: 450px;
    max-height: 90vh;
    overflow-y: auto;
}

/* Estilos para modo oscuro del modal */
.dark-mode-variables #book-form-modal {
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

input, select, textarea, button {
    width: 100%;
    margin-bottom: 10px;
    padding: 12px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: var(--color-white);
    color: var(--color-dark);
}

textarea {
    height: 80px;
    resize: vertical;
}

.dark-mode-variables input,
.dark-mode-variables select,
.dark-mode-variables textarea {
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
    width: 100px;
    height: 130px;
    border: 2px dashed #ccc;
    border-radius: 8px;
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
    text-align: center;
    font-size: 12px;
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
    min-width: 900px;
}

.book-cover {
    width: 50px;
    height: 65px;
    border-radius: 4px;
    object-fit: cover;
}

.bi {
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
    #book-form-modal {
        width: 90%;
        max-width: 400px;
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
                <a href="/BookVerse/admin/users">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Users</h3>
                </a>
                <a href="/BookVerse/admin/books" class="active">
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
            
            <h1>Gestión de Libros</h1>
            
            <div class="books">
                <div class="recent-orders">
                    <h2>Lista de Libros</h2>
                    <div style="margin-bottom: 20px;">
                        <button onclick="agregarLibro()" style="background: #2196F3; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;">
                            <i class="bi bi-plus-circle"></i> Agregar Libro
                        </button>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Portada</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Género</th>
                                <th>ISBN</th>
                                <th>Año Publicación</th>
                                <th>Páginas</th>
                                <th>Stock</th>
                                <th>Fecha Creación</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="book-table-body">
                            <?php foreach ($books as $book): ?>
                            <tr data-book-id="<?= htmlspecialchars($book['id']) ?>">
                                <td>
                                    <img src="<?= htmlspecialchars($book['cover_image'] ?? '/BookVerse/app/public/images/default-book.png') ?>" 
                                         alt="Book Cover" class="book-cover">
                                </td>
                                <td><?= htmlspecialchars($book['title']) ?></td>
                                <td><?= htmlspecialchars($book['author']) ?></td>
                                <td><?= htmlspecialchars($book['genre']) ?></td>
                                <td><?= htmlspecialchars($book['isbn']) ?></td>
                                <td><?= htmlspecialchars($book['publication_year']) ?></td>
                                <td><?= htmlspecialchars($book['pages'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($book['stock'] ?? '1') ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($book['created_at']))) ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($book['updated_at']))) ?></td>
                                <td>
                                    <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 8px;" onclick="editarLibro(this.parentElement.parentElement)">
                                        <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
                                    </button>
                                    <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 8px;" onclick="eliminarLibro(this.parentElement.parentElement)">
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
    <div id="book-form-modal">
        <span class="close" onclick="cerrarFormulario()">&times;</span>
        <h3>Agregar Libro</h3>
        <form id="bookForm" onsubmit="guardarLibro(event)">
        <div class="form-group">
            <label for="cover-input">Portada del libro:</label>
            <input type="file" id="cover-input" name="cover_image" accept="image/*">
            <div id="image-preview" class="image-preview empty">
                <span>Seleccionar portada</span>
            </div>
        </div>
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="author">Autor:</label>
                <input type="text" id="author" name="author" required>
            </div>
            <div class="form-group">
                <label for="genre">Género:</label>
                <input type="text" id="genre" name="genre" required>
            </div>
            <div class="form-group">
                <label for="isbn">ISBN:</label>
                <input type="text" id="isbn" name="isbn" required>
            </div>
            <div class="form-group">
                <label for="publication_year">Año de Publicación:</label>
                <input type="number" id="publication_year" name="publication_year" min="1000" max="2024" required>
            </div>
            <div class="form-group">
                <label for="pages">Número de Páginas:</label>
                <input type="number" id="pages" name="pages" min="1">
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" min="1" value="1">
            </div>
            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea id="description" name="description" placeholder="Descripción opcional del libro..."></textarea>
            </div>
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarFormulario()">Cancelar</button>
        </form>
    </div>

    <script src="/BookVerse/app/resources/views/admin/JS/index.js"></script>
    <script src="/BookVerse/app/resources/views/admin/JS/libros.js"></script>
</body>
</html>