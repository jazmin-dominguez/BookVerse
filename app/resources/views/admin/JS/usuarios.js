function previewImage(input) {
    const preview = document.getElementById('image-preview');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Tipo de archivo no válido',
                text: 'Solo se permiten archivos JPG, PNG y GIF',
                confirmButtonColor: '#dc3545'
            });
            input.value = ''; // Limpiar el input
            return;
        }

        // Validar tamaño (máximo 5MB)
        if (file.size > 5000000) {
            Swal.fire({
                icon: 'error',
                title: 'Archivo muy grande',
                text: 'El archivo no puede ser mayor a 5MB',
                confirmButtonColor: '#dc3545'
            });
            input.value = ''; // Limpiar el input
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.remove('empty');
                
                // Guardar la imagen en base64 para el envío
                const base64Input = document.getElementById('imagen-base64');
                if (base64Input) {
                    base64Input.value = e.target.result;
                }
            }
        };

        reader.readAsDataURL(file);
    }
}

function resetImagePreview() {
    const preview = document.getElementById('image-preview');
    const imageInput = document.getElementById('imagen-input');
    const base64Input = document.getElementById('imagen-base64');

    if (preview) {
        preview.innerHTML = '<span>Seleccionar imagen</span>';
        preview.classList.add('empty');
    }
    
    if (imageInput) {
        imageInput.value = '';
    }
    
    if (base64Input) {
        base64Input.value = '';
    }
}

async function guardarUsuario(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
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
            await Swal.fire({
                icon: 'warning',
                title: 'Campos Requeridos',
                text: `Los siguientes campos son obligatorios: ${missingFields.join(', ')}`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // ===== VALIDACIONES ESPECÍFICAS =====
        
        // Validar nombre de usuario (mínimo 3 caracteres, máximo 20, solo letras, números y guiones bajos)
        const username = formData.get('username').trim();
        if (username.length < 3) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre de Usuario Muy Corto',
                text: 'El nombre de usuario debe tener al menos 3 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (username.length > 20) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre de Usuario Muy Largo',
                text: 'El nombre de usuario no puede exceder 20 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre de Usuario Inválido',
                text: 'El nombre de usuario solo puede contener letras, números y guiones bajos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar nombre completo (mínimo 2 caracteres, máximo 100)
        const fullName = formData.get('full_name').trim();
        if (fullName.length < 2) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre Completo Muy Corto',
                text: 'El nombre completo debe tener al menos 2 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (fullName.length > 100) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre Completo Muy Largo',
                text: 'El nombre completo no puede exceder 100 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        // Validar que el nombre solo contenga letras, espacios y caracteres especiales básicos
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.'-]+$/.test(fullName)) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre Completo Inválido',
                text: 'El nombre completo solo puede contener letras, espacios y caracteres básicos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar email
        const email = formData.get('email').trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            await Swal.fire({
                icon: 'error',
                title: 'Email Inválido',
                text: 'Por favor ingrese un email válido',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (email.length > 100) {
            await Swal.fire({
                icon: 'error',
                title: 'Email Muy Largo',
                text: 'El email no puede exceder 100 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar rol
        const role = formData.get('role');
        if (!['user', 'admin'].includes(role)) {
            await Swal.fire({
                icon: 'error',
                title: 'Rol Inválido',
                text: 'Por favor seleccione un rol válido',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Manejar validación de contraseñas
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');
        
        if (isEditing) {
            // Al editar: solo validar si se proporcionó una nueva contraseña
            if (password && password.trim() !== '') {
                if (password.length < 6) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Contraseña Muy Corta',
                        text: 'La contraseña debe tener al menos 6 caracteres',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }
                if (password.length > 50) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Contraseña Muy Larga',
                        text: 'La contraseña no puede exceder 50 caracteres',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }
                if (password !== confirmPassword) {
                    await Swal.fire({
                        icon: 'error',
                        title: 'Contraseñas No Coinciden',
                        text: 'La contraseña y su confirmación deben ser iguales',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }
            }
        } else {
            // Al crear: siempre validar contraseñas
            if (password.length < 6) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Contraseña Muy Corta',
                    text: 'La contraseña debe tener al menos 6 caracteres',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            if (password.length > 50) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Contraseña Muy Larga',
                    text: 'La contraseña no puede exceder 50 caracteres',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
            if (password !== confirmPassword) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Contraseñas No Coinciden',
                    text: 'La contraseña y su confirmación deben ser iguales',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        }

        // Determinar URL
        const userId = form.dataset.userId;
        const url = userId
            ? `/BookVerse/admin/users/${userId}/update` 
            : '/BookVerse/admin/users/create';

        console.log('Enviando petición a:', url);
        console.log('Datos del formulario:', Object.fromEntries(formData));

        // Mostrar loading
        const loadingSwal = Swal.fire({
            title: 'Procesando...',
            text: isEditing ? 'Actualizando usuario' : 'Creando usuario',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Enviar FormData
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Cerrar loading
        loadingSwal.close();

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            
            try {
                const errorJson = JSON.parse(errorText);
                throw new Error(errorJson.error || `Error del servidor (${response.status})`);
            } catch (jsonError) {
                throw new Error(`Error del servidor (${response.status})`);
            }
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
            await Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente',
                confirmButtonColor: '#28a745'
            });
            
            if (isEditing) {
                const rowIndex = form.dataset.editingRowIndex;
                if (rowIndex !== undefined) {
                    actualizarFilaUsuario(rowIndex, result.user);
                }
            } else {
                if (result.user) {
                    agregarFilaUsuario(result.user);
                }
            }
            
            cerrarFormulario();
        } else {
            throw new Error(result.error || 'Error al procesar la solicitud');
        }

    } catch (error) {
        console.error('Error completo:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            confirmButtonColor: '#dc3545'
        });
    }
}

function editarUsuario(fila) {
    if (!fila || !fila.cells) {
        console.error('Fila inválida');
        return;
    }

    const celdas = fila.cells;
    const userId = fila.dataset.userId;
    
    // Verificar que tenemos suficientes celdas
    if (celdas.length < 7) {
        console.error('Estructura de fila inválida');
        return;
    }

    const imgElement = celdas[0].querySelector('img');
    const imageSrc = imgElement ? imgElement.src : '';
    const username = celdas[1].textContent || '';
    const fullName = celdas[2].textContent || '';
    const email = celdas[3].textContent || '';
    const role = celdas[4].textContent || '';

    // Guardar el ID del usuario en el formulario
    const form = document.getElementById('userForm');
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    form.dataset.userId = userId;
    form.dataset.isEditing = 'true';

    // Cargar datos en el formulario con validaciones
    const usernameField = document.getElementById('username');
    const fullNameField = document.getElementById('full_name');
    const emailField = document.getElementById('email');
    const roleField = document.getElementById('role');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');

    if (usernameField) usernameField.value = username;
    if (fullNameField) fullNameField.value = fullName;
    if (emailField) emailField.value = email;
    if (roleField) roleField.value = role;
    if (passwordField) passwordField.value = '';
    if (confirmPasswordField) confirmPasswordField.value = '';

    // Hacer campos de contraseña opcionales en edición
    if (passwordField) passwordField.required = false;
    if (confirmPasswordField) confirmPasswordField.required = false;

    // Cargar imagen actual
    const preview = document.getElementById('image-preview');
    const base64Input = document.getElementById('imagen-base64');
    if (preview) {
        if (imageSrc && !imageSrc.includes('default-avatar.png')) {
            preview.innerHTML = `<img src="${imageSrc}" alt="Current Image">`;
            preview.classList.remove('empty');
            if (base64Input) base64Input.value = imageSrc;
        } else {
            resetImagePreview();
        }
    }

    // Configurar modal para edición
    const modalTitle = document.querySelector('#user-form-modal h3');
    const submitButton = document.querySelector('#userForm button[type="submit"]');
    const passwordHelp = document.getElementById('password-help');
    
    if (modalTitle) modalTitle.innerText = 'Editar Usuario';
    if (submitButton) submitButton.innerText = 'Actualizar';
    if (passwordHelp) passwordHelp.innerText = 'Dejar vacío para mantener la contraseña actual';

    const rowIndex = Array.from(fila.parentNode.children).indexOf(fila);
    form.dataset.editingRowIndex = rowIndex;

    const modal = document.getElementById('user-form-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

async function eliminarUsuario(fila) {
    if (!fila || !fila.dataset.userId) {
        console.error('Fila o ID de usuario no válido');
        return;
    }

    try {
        const userId = fila.dataset.userId;
        const username = fila.cells[1].textContent;
        console.log('Intentando eliminar usuario:', userId);
        
        // Confirmar eliminación
        const confirmResult = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar al usuario "${username}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmResult.isConfirmed) {
            return;
        }

        // Mostrar loading
        const loadingSwal = Swal.fire({
            title: 'Eliminando...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch(`/BookVerse/admin/users/${userId}/delete`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        // Cerrar loading
        loadingSwal.close();

        let result;
        const responseText = await response.text();
        
        try {
            result = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('Error parsing JSON:', jsonError);
            console.error('Response text:', responseText);
            
            if (response.ok) {
                result = { success: true };
            } else {
                throw new Error('Error del servidor: ' + responseText.substring(0, 100));
            }
        }

        if (!response.ok) {
            throw new Error('Error al eliminar usuario: ' + (result?.error || 'Error del servidor'));
        }

        if (result.success || response.ok) {
            fila.remove();
            await Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Usuario eliminado correctamente',
                confirmButtonColor: '#28a745'
            });
        } else {
            throw new Error(result.error || 'Error al eliminar usuario');
        }

    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al eliminar usuario',
            confirmButtonColor: '#dc3545'
        });
    }
}

function actualizarFilaUsuario(index, userData) {
    if (!userData) {
        console.error('Datos de usuario no válidos');
        return;
    }

    const fila = document.querySelector(`#user-table-body tr:nth-child(${parseInt(index) + 1})`);
    if (!fila || !fila.cells) {
        console.error('Fila no encontrada o inválida');
        return;
    }

    const celdas = fila.cells;

    try {
        // Actualizar imagen de perfil
        if (celdas[0] && userData.imagen) {
            const imgElement = celdas[0].querySelector('img');
            if (imgElement) {
                imgElement.src = userData.imagen;
            }
        }

        // Actualizar datos con validaciones
        if (celdas[1] && userData.username) celdas[1].textContent = userData.username;
        if (celdas[2] && userData.full_name) celdas[2].textContent = userData.full_name;
        if (celdas[3] && userData.email) celdas[3].textContent = userData.email;
        if (celdas[4] && userData.role) celdas[4].textContent = userData.role;
        if (celdas[6]) celdas[6].textContent = new Date().toLocaleDateString('es-ES');
    } catch (error) {
        console.error('Error al actualizar fila:', error);
    }
}

function agregarFilaUsuario(userData) {
    if (!userData) {
        console.error('Datos de usuario no válidos');
        return;
    }

    const tbody = document.getElementById('user-table-body');
    if (!tbody) {
        console.error('Tabla de usuarios no encontrada');
        return;
    }

    const tr = document.createElement('tr');
    tr.dataset.userId = userData.id || '';

    const imageSrc = userData.imagen || '/BookVerse/app/public/images/default-avatar.png';

    tr.innerHTML = `
        <td>
            <img src="${imageSrc}" alt="Profile Photo" class="profile-img">
        </td>
        <td>${userData.username || ''}</td>
        <td>${userData.full_name || ''}</td>
        <td>${userData.email || ''}</td>
        <td>${userData.role || ''}</td>
        <td>${new Date().toLocaleDateString('es-ES')}</td>
        <td>${new Date().toLocaleDateString('es-ES')}</td>
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
    
    const form = document.getElementById('userForm');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');
    const passwordHelp = document.getElementById('password-help');
    
    // Hacer campos de contraseña requeridos en creación
    if (passwordField) passwordField.required = true;
    if (confirmPasswordField) confirmPasswordField.required = true;
    if (passwordHelp) passwordHelp.innerText = 'Requerido para crear usuario';
    
    const modal = document.getElementById('user-form-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function cerrarFormulario() {
    const modal = document.getElementById('user-form-modal');
    const form = document.getElementById('userForm');
    
    if (modal) {
        modal.style.display = 'none';
    }
    
    if (form) {
        form.reset();
        
        // Limpiar datos del formulario
        delete form.dataset.userId;
        delete form.dataset.isEditing;
        delete form.dataset.editingRowIndex;
    }
    
    resetImagePreview();

    // Restablecer títulos y botones
    const modalTitle = document.querySelector('#user-form-modal h3');
    const submitButton = document.querySelector('#userForm button[type="submit"]');
    const passwordHelp = document.getElementById('password-help');
    
    if (modalTitle) modalTitle.innerText = 'Agregar Usuario';
    if (submitButton) submitButton.innerText = 'Guardar';
    if (passwordHelp) passwordHelp.innerText = 'Requerido para crear usuario';
}

function filterUsers() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const tbody = document.getElementById('user-table-body');
    
    if (!searchInput || !tbody) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const roleFilterValue = roleFilter ? roleFilter.value : '';
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        if (row.cells.length < 4) continue;
        
        const username = row.cells[1].textContent.toLowerCase();
        const fullName = row.cells[2].textContent.toLowerCase();
        const email = row.cells[3].textContent.toLowerCase();
        const role = row.cells[4].textContent;

        const matchesSearch = username.includes(searchTerm) || 
                            fullName.includes(searchTerm) || 
                            email.includes(searchTerm);
        const matchesRole = !roleFilterValue || role === roleFilterValue;

        row.style.display = matchesSearch && matchesRole ? '' : 'none';
    }
}

// Event listener para cerrar modal al hacer click fuera
document.addEventListener('click', function (event) {
    const modal = document.getElementById('user-form-modal');
    if (event.target === modal) {
        cerrarFormulario();
    }
});

// Event listener para escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('user-form-modal');
        if (modal && modal.style.display === 'block') {
            cerrarFormulario();
        }
    }
});

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que el formulario esté configurado correctamente
    const form = document.getElementById('userForm');
    if (form) {
        form.addEventListener('submit', guardarUsuario);
    }
    
    // Configurar el input de archivo
    const imageInput = document.getElementById('imagen-input');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
});

// Función para validar que todos los elementos necesarios existan
function validarElementosDOM() {
    const elementos = [
        'userForm',
        'user-form-modal',
        'image-preview',
        'username',
        'full_name',
        'email',
        'role',
        'password',
        'confirm_password',
        'user-table-body'
    ];
    
    const faltantes = elementos.filter(id => !document.getElementById(id));
    
    if (faltantes.length > 0) {
        console.warn('Elementos DOM faltantes:', faltantes);
    }
    
    return faltantes.length === 0;
}

// Llamar validación al cargar
document.addEventListener('DOMContentLoaded', validarElementosDOM);