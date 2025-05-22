<style>
/* Modal flotante */
        #user-form-modal {
            display: none;
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -20%);
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 1000;
            width: 300px;
        }

        input, select, button {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button[type="submit"] {
            background-color: #2196F3;
            color: white;
            border: none;
        }

        button[type="button"] {
            background-color: #f44336;
            color: white;
            border: none;
        }

        .bi {
            vertical-align: middle;
        }
</style>

<h1>Usuarios</h1>

<div class="recent-orders">
    <h2>Lista de Usuarios</h2>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="user-table-body">
            <!-- Aquí se llenará con JavaScript -->
        </tbody>
    </table>
    <a href="#" onclick="agregarUsuario()">Agregar Usuario</a>
</div>

<!-- Modal (Formulario flotante oculto) -->
<div id="user-form-modal" style="
    display: none;
    position: fixed;
    top: 20%;
    left: 50%;
    transform: translate(-50%, -20%);
    background-color: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    z-index: 1000;
    width: 300px;
">
    <h3>Agregar Usuario</h3>
    <form id="userForm" onsubmit="return guardarUsuario(event)">
        <label>Nombre</label>
        <input type="text" id="nombre" required style="width: 100%; margin-bottom: 10px;">

        <label>Email</label>
        <input type="email" id="email" required style="width: 100%; margin-bottom: 10px;">

        <label>Rol</label>
        <select id="rol" required style="width: 100%; margin-bottom: 15px;">
            <option value="">Seleccione</option>
            <option value="Administrador">Administrador</option>
            <option value="Usuario">Usuario</option>
        </select>

        <button type="submit">Guardar</button>
        <button type="button" onclick="cerrarFormulario()" style="margin-left: 10px;">Cancelar</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="index.js"></script>

