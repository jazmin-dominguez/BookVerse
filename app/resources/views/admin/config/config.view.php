
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .config-container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border: 1px solid #ddd;
        }
        th {
            background-color: var(--color-background, #eee);
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn-save {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .profile-pic {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>

<br>
<br>
<br>
<div class="config-container">
    <h2>Configuración de Usuario</h2>
    <form id="config-form">
        <table>
            <tr>
                <th>Campo</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Nombre</td>
                <td><input type="text" id="firstName" placeholder="Tu nombre"></td>
            </tr>
            <tr>
                <td>Apellido</td>
                <td><input type="text" id="lastName" placeholder="Tu apellido"></td>
            </tr>
            <tr>
                <td>Usuario</td>
                <td><input type="text" id="username" placeholder="Nombre de usuario"></td>
            </tr>
            <tr>
                <td>Contraseña</td>
                <td><input type="password" id="password" placeholder="Nueva contraseña"></td>
            </tr>
            <tr>
                <td>Foto de perfil</td>
                <td>
                    <input type="file" id="profilePicInput" accept="image/*">
                    <div style="margin-top: 10px;">
                        <img id="profilePreview" class="profile-pic" src="images/default-profile.png" alt="Vista previa">
                    </div>
                </td>
            </tr>
        </table>

        <button type="submit" class="btn-save">Guardar Cambios</button>
    </form>
</div>

<script src="config.js"></script>
