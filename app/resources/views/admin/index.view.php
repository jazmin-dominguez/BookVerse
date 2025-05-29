<?php
    require_once __DIR__ . '/../../../config.php';
    require_once __DIR__ . '/../../functions/session_functions.php';

    requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/BookVerse/app/resources/views/admin/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <title>Dashboard</title>
</head>

<body>

    <div class="container">
        <!-- Sidebar Section -->
        <aside>
            <div class="toggle">
                <div class="logo">
                    <!-- <img src="images/logo.png"> -->
                    <h2>Book<span style="color: #8000ff;">Verse</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">
                        close
                    </span>
                </div>
            </div>

            <div class="sidebar">
                <a href="/BookVerse/admin" class="active">
                    <span class="material-icons-sharp">
                        dashboard
                    </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="/BookVerse/admin/users">
                    <span class="material-icons-sharp">
                        person_outline
                    </span>
                    <h3>Usuarios</h3>
                </a>
                <a href="/BookVerse/admin/books">
                    <span class="material-icons-sharp">
                        library_books
                    </span>
                    <h3>Libros</h3>
                </a>
                <a href="/BookVerse/auth/logout">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Cerrar Sesión</h3>
                </a>
            </div>
        </aside>
        <!-- End of Sidebar Section -->

        <!-- Main Content -->
        <main id="main-content">
            <div class="nav">
                <button id="menu-btn">
                    <span class="material-icons-sharp">
                        menu
                    </span>
                </button>
                <div class="dark-mode">
                    <span class="material-icons-sharp active">
                        light_mode
                    </span>
                    <span class="material-icons-sharp">
                        dark_mode
                    </span>
                </div>

                <div class="profile">
                    <div class="info">
                        <p>Hey, <b><?= $_SESSION['full_name'] ?? 'Admin' ?></b></p>
                        <small class="text-muted">Administrador</small>
                    </div>
                    <div class="profile-photo" id="profile-photo-clickable" style="cursor:pointer;">
                        <img src="images/profile-1.jpg" alt="Profile Photo">
                    </div>
                </div>
            </div>

            <div id="dynamic-content">
                <h1>Dashboard</h1>
                <!-- Analyses -->
                <?php
                require_once __DIR__ . '/../../../models/books.php';
                require_once __DIR__ . '/../../../models/user.php';
                require_once __DIR__ . '/../../../models/likes.php';

                $booksModel = new \app\models\Books();
                $usersModel = new \app\models\User();
                $likesModel = new \app\models\likes();

                $totalLikes = $likesModel->getTotalLikes();
                $totalUsers = $usersModel->getTotalUsers();
                $totalBooks = $booksModel->getTotalBooks();
                
                $likedBooksPercentage = $likesModel->getLikedBooksPercentage();
                $usersWithLikesPercentage = $likesModel->getUsersWithLikesPercentage();
                $booksWithLikesPercentage = $likesModel->getBooksWithLikesPercentage();
                ?>
                <div class="analyse">
                    <div class="sales">
                        <div class="status">
                            <div class="info">
                                <h3>Total Likes</h3>
                                <h1><?= number_format($totalLikes) ?></h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36"></circle>
                                </svg>
                                <div class="percentage">
                                    <p><?= $likedBooksPercentage ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="visits">
                        <div class="status">
                            <div class="info">
                                <h3>Usuarios</h3>
                                <h1><?= number_format($totalUsers) ?></h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36"></circle>
                                </svg>
                                <div class="percentage">
                                    <p><?= $usersWithLikesPercentage ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="searches">
                        <div class="status">
                            <div class="info">
                                <h3>Libros</h3>
                                <h1><?= number_format($totalBooks) ?></h1>
                            </div>
                            <div class="progresss">
                                <svg>
                                    <circle cx="38" cy="38" r="36"></circle>
                                </svg>
                                <div class="percentage">
                                    <p><?= $booksWithLikesPercentage ?>%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End of Analyses -->

                <!-- New Users Section -->
                <div class="new-users">
                    <h2>Nuevos usuarios</h2>
                    <div class="user-list">
                        <?php
                        // Ya tenemos $usersModel del código anterior
                        $newUsers = $usersModel->getRecentUsers(3);
                        foreach ($newUsers as $user): ?>
                        <div class="user">
                            <img src="/BookVerse/app/resources/images/profile.png">
                            <h2><?= htmlspecialchars($user['full_name']) ?></h2>
                            <p><?= htmlspecialchars($user['created_at']) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <div class="user" onclick="window.location.href='/BookVerse/admin/users'" style="cursor:pointer;">
                            <img src="/BookVerse/app/resources/images/plus.png">
                            <h2>Ver más</h2>
                            <p>Usuarios</p>
                        </div>
                    </div>
                </div>
                <!-- End of New Users Section -->

                <!-- Recent Orders Table -->
                <div class="recent-orders">
                    <h2>Nuevos libros</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Género</th>
                                <th>Año</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ya tenemos $booksModel del código anterior
                            $recentBooks = $booksModel->getRecentBooks(5);
                            foreach ($recentBooks as $book): ?>
                            <tr>
                                <td><?= htmlspecialchars($book['title']) ?></td>
                                <td><?= htmlspecialchars($book['author']) ?></td>
                                <td><?= htmlspecialchars($book['genre']) ?></td>
                                <td><?= htmlspecialchars($book['publication_year']) ?></td> 
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="/BookVerse/admin/books">Ver todos los libros</a>
                </div>
                <!-- End of Recent Orders -->
            </div>
        </main>
        <!-- End of Main Content -->

            </div>
        </div>
    </div>
 
    <script src="/BookVerse/app/resources/views/admin/JS/index.js"></script>
    <script src="/BookVerse/app/resources/views/admin/JS/usuarios.js"></script>

</body>

</html>