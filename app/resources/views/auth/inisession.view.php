<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login / Registro</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container" id="container">
    <div class="form-container sign-up-container">
        <form action="#">
            <h1>Crear cuenta</h1>
            <input type="text" placeholder="Nombre de usuario" />
            <input type="email" placeholder="Correo electrónico" />
            <input type="password" placeholder="Contraseña" />
            <button>Registrarse</button>
        </form>
    </div>

    <div class="form-container sign-in-container">
        <form action="#">
            <h1>Iniciar sesión</h1>
            <input type="text" placeholder="Nombre de usuario" />
            <input type="password" placeholder="Contraseña" />
            <a href="#">¿Olvidaste tu contraseña?</a>
            <button>Ingresar</button>
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
    </script>
</body>
</html>
