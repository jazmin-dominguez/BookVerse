function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const base64Input = document.getElementById('imagen-base64');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            preview.classList.remove('empty');
            base64Input.value = e.target.result;
        };

        reader.readAsDataURL(input.files[0]);
    }
}

function resetImagePreview() {
    const preview = document.getElementById('image-preview');
    const base64Input = document.getElementById('imagen-base64');

    preview.innerHTML = '<span>Seleccionar imagen</span>';
    preview.classList.add('empty');
    base64Input.value = '';
    document.getElementById('imagen-input').value = '';
}

async function guardarUsuario(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    const isEditing = form.dataset.isEditing === 'true';
    
    try {
        // Campos requeridos según el contexto
        const requiredFields = isEditing
            ? ['username', 'full_name', 'email', 'role'] // Sin password al editar
            : ['username', 'full_name', 'email', 'role', 'password']; // Con password al crear

        // Validar campos requeridos
        const missingFields = requiredFields.filter(field => {
            const value = formData.get(field);
            return !value || value.trim() === '';
        });
        
        if (missingFields.length > 0) {
            throw new Error(`Los siguientes campos son requeridos: ${missingFields.join(', ')}`);
        }

        // Manejar validación de contraseñas
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');
        
        if (isEditing) {
            // Al editar: solo validar si se proporcionó una nueva contraseña
            if (password && password.trim() !== '') {
                if (password !== confirmPassword) {
                    throw new Error('Las contraseñas no coinciden');
                }
                // Solo incluir password si se proporcionó uno nuevo
                data.password = password;
            }
            // Si no se proporcionó password, no lo incluimos en data
        } else {
            // Al crear: siempre validar contraseñas
            if (password !== confirmPassword) {
                throw new Error('Las contraseñas no coinciden');
            }
            data.password = password;
        }

        // Agregar campos básicos
        data.username = formData.get('username');
        data.full_name = formData.get('full_name');
        data.email = formData.get('email');
        data.role = formData.get('role');

        // Agregar imagen si existe
        const imageInput = document.getElementById('imagen-base64');
        if (imageInput && imageInput.value && !imageInput.value.includes('default-avatar.png')) {
            data.imagen = imageInput.value;
        }

        // Determinar URL
        const userId = form.dataset.userId;
        const url = userId
            ? `/BookVerse/admin/users/${userId}/update` 
            : '/BookVerse/admin/users/create';

        console.log('Enviando petición a:', url);
        console.log('Datos a enviar:', data);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`Error del servidor (${response.status})`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const responseText = await response.text();
            console.error('Non-JSON response:', responseText);
            throw new Error('El servidor devolvió una respuesta no válida');
        }

        const result = await response.json();
        console.log('Response result:', result);
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente'
            });
            
            if (isEditing) {
                const rowIndex = form.dataset.editingRowIndex;
                if (rowIndex !== undefined) {
                    actualizarFilaUsuario(rowIndex, result.user);
                }
            } else {
                agregarFilaUsuario(result.user);
            }
            
            cerrarFormulario();
        } else {
            throw new Error(result.error || 'Error al procesar la solicitud');
        }

    } catch (error) {
        console.error('Error completo:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    }
}

function editarUsuario(fila) {
    const celdas = fila.cells;
    const userId = fila.dataset.userId;
    const imagenSrc = celdas[0].querySelector('img').src;
    const username = celdas[1].textContent;
    const nombre = celdas[2].textContent;
    const email = celdas[3].textContent;
    const rol = celdas[4].textContent.toLowerCase();

    // Guardar el ID del usuario en el formulario
    const form = document.getElementById('userForm');
    form.dataset.userId = userId;
    form.dataset.isEditing = 'true';
    
    // IMPORTANTE: Quitar el required de los campos de contraseña para edición
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    passwordField.required = false;
    confirmPasswordField.required = false;
    
    // Agregar placeholder para indicar que la contraseña es opcional
    passwordField.placeholder = 'Dejar vacío para mantener la actual';
    confirmPasswordField.placeholder = 'Confirmar nueva contraseña (opcional)';

    // Cargar datos en el formulario
    document.getElementById('username').value = username;
    document.getElementById('full_name').value = nombre;
    document.getElementById('email').value = email;
    document.getElementById('role').value = rol;
    
    // IMPORTANTE: Limpiar campos de contraseña
    passwordField.value = '';
    confirmPasswordField.value = '';

    // Cargar imagen actual
    const preview = document.getElementById('image-preview');
    const base64Input = document.getElementById('imagen-base64');

    if (imagenSrc && !imagenSrc.includes('default-avatar.png')) {
        preview.innerHTML = `<img src="${imagenSrc}" alt="Current Image">`;
        preview.classList.remove('empty');
        base64Input.value = imagenSrc;
    } else {
        resetImagePreview();
    }

    // Configurar modal para edición
    document.querySelector('#user-form-modal h3').innerText = 'Editar Usuario';
    document.querySelector('#userForm button[type="submit"]').innerText = 'Actualizar';

    const rowIndex = Array.from(fila.parentNode.children).indexOf(fila);
    document.getElementById('userForm').dataset.editingRowIndex = rowIndex;

    document.getElementById('user-form-modal').style.display = 'block';
}


async function eliminarUsuario(fila) {
    if (!fila || !fila.dataset.userId) {
        console.error('Fila o ID de usuario no válido');
        return;
    }

    try {
        const userId = fila.dataset.userId;
        console.log('Intentando eliminar usuario:', userId);
        
        // Confirmar eliminación
        const confirmResult = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción desactivará el usuario y no podrá acceder al sistema",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmResult.isConfirmed) {
            return;
        }

        const response = await fetch(`/BookVerse/admin/users/${userId}/delete`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Error al eliminar usuario');
        }

        const result = await response.json();
        if (result.success) {
            fila.remove();
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Usuario eliminado correctamente'
            });
        } else {
            throw new Error(result.error || 'Error al eliminar usuario');
        }

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al eliminar usuario'
        });
    }
}

function actualizarFilaUsuario(index, userData) {
    const fila = document.querySelector(`#user-table-body tr:nth-child(${parseInt(index) + 1})`);
    const celdas = fila.cells;

    // Actualizar imagen
    if (userData.imagen) {
        celdas[0].querySelector('img').src = userData.imagen;
    }

    // Actualizar datos
    celdas[1].textContent = userData.username;
    celdas[2].textContent = userData.full_name;
    celdas[3].textContent = userData.email;
    celdas[4].textContent = userData.role;
    celdas[6].textContent = new Date().toLocaleDateString('es-ES'); // Fecha actualización
}

function agregarFilaUsuario(userData) {
    const tbody = document.getElementById('user-table-body');
    const tr = document.createElement('tr');
    tr.dataset.userId = userData.id;

    const imagenSrc = userData.imagen || '/BookVerse/app/public/images/default-avatar.png';
    const fechaCreacion = new Date().toLocaleDateString('es-ES');

    tr.innerHTML = `
        <td>
            <img src="${imagenSrc}" alt="Profile Photo" class="profile-img">
        </td>
        <td>${userData.username}</td>
        <td>${userData.full_name}</td>
        <td>${userData.email}</td>
        <td>${userData.role}</td>
        <td>${fechaCreacion}</td>
        <td>${fechaCreacion}</td>
        <td>
            <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 8px;" onclick="editarUsuario(this.parentElement.parentElement)">
                <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
            </button>
            <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 8px;" onclick="eliminarUsuario(this.parentElement.parentElement)">
                <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function agregarUsuario() {
    cerrarFormulario();
    
    // Para nuevo usuario, los campos de contraseña SÍ son requeridos
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    passwordField.required = true;
    confirmPasswordField.required = true;
    passwordField.placeholder = 'Contraseña';
    confirmPasswordField.placeholder = 'Confirmar contraseña';
    
    document.getElementById('user-form-modal').style.display = 'block';
}

function cerrarFormulario() {
    document.getElementById('user-form-modal').style.display = 'none';
    const form = document.getElementById('userForm');
    form.reset();
    resetImagePreview();

    document.querySelector('#user-form-modal h3').innerText = 'Agregar Usuario';
    document.querySelector('#userForm button[type="submit"]').innerText = 'Guardar';

    // Restablecer campos de contraseña para nuevo usuario (por defecto)
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    passwordField.required = true;
    confirmPasswordField.required = true;
    passwordField.placeholder = 'Contraseña';
    confirmPasswordField.placeholder = 'Confirmar contraseña';

    // Limpiar datos del formulario
    delete form.dataset.userId;
    delete form.dataset.isEditing;
}

// Event listener para cerrar modal al hacer click fuera
document.addEventListener('click', function (event) {
    const modal = document.getElementById('user-form-modal');
    if (event.target === modal) {
        cerrarFormulario();
    }
});



