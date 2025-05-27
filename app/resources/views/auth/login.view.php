<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si ya está autenticado, redirigir según el rol
if (isset($_SESSION['user_id'])) {
    header('Location: /BookVerse/');
    exit;
}

// Recuperar y limpiar mensajes de error
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login / Registro - BookVerse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container" id="container">
    <?php if ($error): ?>
    <div class="error-message">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="form-container sign-up-container">
        <form action="/BookVerse/auth/register" method="POST">
            <h1>Crear cuenta</h1>
            <input type="text" name="username" placeholder="Nombre de usuario" required minlength="3" maxlength="50" />
            <input type="text" name="full_name" placeholder="Nombre completo" required />
            <input type="email" name="email" placeholder="Correo electrónico" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" />
            <input type="password" name="password" placeholder="Contraseña" required minlength="6" />
            <input type="password" name="password_confirm" placeholder="Confirmar contraseña" required minlength="6" />
            <div class="terms">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">Acepto los términos y condiciones</label>
            </div>
            <button type="submit">Registrarse</button>
        </form>
    </div>

    <div class="form-container sign-in-container">
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <h1>Iniciar sesión</h1>
            <input type="email" name="email" placeholder="Correo electrónico" required />
            <input type="password" name="password" placeholder="Contraseña" required />
            <a href="/BookVerse/auth/reset-password">¿Olvidaste tu contraseña?</a>
            <button type="submit">Ingresar</button>
        </form>
    </div>

        <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
            <h1>¡Bienvenido de nuevo!</h1>
            <p>Para mantenerte conectado con nosotros, por favor inicia sesión</p>
            <button class="ghost" id="signIn">Iniciar sesión</button>
            </div>
            <div class="overlay-panel overlay-right">
            <h1>¡Hola, amigo!</h1>
            <p>Ingresa tus datos personales y empieza tu viaje con nosotros</p>
            <button class="ghost" id="signUp">Registrarse</button>
            </div>
        </div>
        </div>
    </div>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(120deg, #6f00ff, #c300ff);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25),
                        0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in-container {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .sign-up-container {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.right-panel-active .sign-up-container {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
        }

        .container.right-panel-active .sign-in-container {
            transform: translateX(100%);
            opacity: 0;
        }

        form {
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 0 50px;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
        }

        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 20px;
        }

        button {
            border-radius: 20px;
            border: 1px solid #6f00ff;
            background-color: #6f00ff;
            color: #fff;
            font-size: 14px;
            padding: 12px 45px;
            margin-top: 20px;
            transition: transform 80ms ease-in;
        }

        button:hover {
            background-color: #5000cc;
        }

        button.ghost {
            background-color: transparent;
            border-color: #fff;
        }

        .overlay-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: transform 0.6s ease-in-out;
            z-index: 100;
        }

        .container.right-panel-active .overlay-container {
            transform: translateX(-100%);
        }

        .overlay {
            background: linear-gradient(to right, #6f00ff, #c300ff);
            background-repeat: no-repeat;
            background-size: cover;
            color: #ffffff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .container.right-panel-active .overlay {
            transform: translateX(50%);
        }

        .overlay-panel {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 40px;
            text-align: center;
            top: 0;
            height: 100%;
            width: 50%;
            transform: translateX(0);
            transition: transform 0.6s ease-in-out;
        }

        .overlay-left {
            transform: translateX(-20%);
            left: 0;
        }

        .container.right-panel-active .overlay-left {
            transform: translateX(0);
        }

        .overlay-right {
            right: 0;
            transform: translateX(0);
        }

        .container.right-panel-active .overlay-right {
            transform: translateX(20%);
        }

        .terms {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .terms input[type="checkbox"] {
            width: auto;
            margin: 0;
        }

        input:invalid {
            border: 1px solid #ff4444;
        }

        .error-message {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #ff4444;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
    </style>

    <script>
        const signUpButton = document.getElementById('signUp');
        const signInButton = document.getElementById('signIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });

        async function handleLogin(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);

            try {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Verificando credenciales...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                console.log('Enviando solicitud de login...');
                const response = await fetch('/BookVerse/auth/login', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                console.log('Respuesta recibida:', response.status);
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);

                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Respuesta del servidor no es JSON');
                }

                const data = await response.json();
                console.log('Datos recibidos:', data);
                if (!response.ok) {
                    throw new Error(data.message || `Error HTTP: ${response.status}`);
                }

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: data.message || 'Iniciando sesión...',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    if (data.redirect) {
                        console.log('Redirigiendo a:', data.redirect);
                        window.location.href = data.redirect;
                    } else {
                        console.log('Redirigiendo a página principal');
                        window.location.href = '/BookVerse/';
                    }
                } else {
                    throw new Error(data.message || 'Error al iniciar sesión');
                }
            } catch (error) {
                console.error('Error en login:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de autenticación',
                    text: error.message || 'Error al procesar la solicitud',
                });
            }

            return false;
        }
    </script>
</body>
</html>
