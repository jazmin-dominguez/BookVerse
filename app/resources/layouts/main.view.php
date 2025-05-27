<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'BookVerse'; ?></title>
    <link rel="stylesheet" href="/BookVerse/app/resources/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <?php 
    // Incluir el header correspondiente según el tipo de usuario
    if (isset($userType)) {
        switch ($userType) {
            case 'admin':
                include __DIR__ . '/partials/admin_header.php';
                break;
            case 'user':
                include __DIR__ . '/partials/user_header.php';
                break;
            default:
                include __DIR__ . '/partials/guest_header.php';
                break;
        }
    }
    ?>

    <main>
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
