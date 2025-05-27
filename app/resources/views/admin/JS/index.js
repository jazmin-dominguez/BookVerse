document.addEventListener('DOMContentLoaded', () => {
    const menuLinks = document.querySelectorAll('.sidebar a');

    menuLinks.forEach(link => {
        link.addEventListener('click', function (e) { 
            const section = this.querySelector('h3')?.textContent.trim();
            
            if (section === 'Dashboard') {
                e.preventDefault(); 
                menuLinks.forEach(l => l.classList.remove('active')); 
                this.classList.add('active'); 
                window.location.reload();
            } else { 
                menuLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

}); 

function loadDashboard() {
    window.location.reload();
}

function loadUsers() {
    const main = document.getElementById('dynamic-content');

    fetch('user.html')
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar user.html');
            return response.text();
        })
        .then(html => {
            main.innerHTML = html;
            if (typeof renderUsuariosEjemplo === 'function') {
                renderUsuariosEjemplo();
            }
        })
        .catch(error => {
            main.innerHTML = `<p style="color: red;">Error al cargar usuarios: ${error.message}</p>`;
            console.error(error);
        });
}

function loadBooks() {
    const main = document.getElementById('dynamic-content');

    fetch('books.html')
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar books.html');
            return response.text();
        })
        .then(html => {
            main.innerHTML = html; 
            if (typeof renderBooks === 'function') renderBooks();
        })
        .catch(error => {
            main.innerHTML = `<p style="color: red;">Error al cargar libros: ${error.message}</p>`;
            console.error(error);
        });
}

function logout() { 
    window.location.href = "../home/index.html";
}

// Manejo del menú móvil
const sideMenu = document.querySelector('aside');
const menuBtn = document.getElementById('menu-btn');
const closeBtn = document.getElementById('close-btn');

if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        sideMenu.style.display = 'block';
    });
}

if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        sideMenu.style.display = 'none';
    });
}

// Manejo del modo oscuro
const darkMode = document.querySelector('.dark-mode');

if (darkMode) {
    darkMode.addEventListener('click', () => {
        const isDarkMode = document.body.classList.contains('dark-mode-variables');
        
        if (isDarkMode) {
            // Cambiar a modo claro
            document.body.classList.remove('dark-mode-variables');
            // localStorage.setItem('darkMode', 'disabled'); // Uncomment in production
            updateDarkModeToggle(false);
        } else {
            // Cambiar a modo oscuro
            document.body.classList.add('dark-mode-variables');
            // localStorage.setItem('darkMode', 'enabled'); // Uncomment in production
            updateDarkModeToggle(true);
        }
    });
}

function updateDarkModeToggle(isDark) {
    const lightIcon = darkMode.querySelector('span:nth-child(1)');
    const darkIcon = darkMode.querySelector('span:nth-child(2)');
    
    if (isDark) {
        lightIcon.classList.remove('active');
        darkIcon.classList.add('active');
    } else {
        lightIcon.classList.add('active');
        darkIcon.classList.remove('active');
    }
}

// Funciones para manejo de usuarios
function agregarUsuario() { 
    resetForm();
    const modal = document.getElementById('user-form-modal');
    const modalTitle = document.querySelector('#user-form-modal h3');
    const submitBtn = document.querySelector('#userForm button[type="submit"]');
    
    if (modal) modal.style.display = 'block';
    if (modalTitle) modalTitle.textContent = 'Agregar Usuario';
    if (submitBtn) submitBtn.textContent = 'Guardar';
    
    // Hacer contraseña requerida para nuevo usuario
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (passwordInput) passwordInput.required = true;
    if (confirmPasswordInput) confirmPasswordInput.required = true;
}

function editarUsuario(fila) {
    const userId = fila.dataset.userId;
    const celdas = fila.getElementsByTagName('td');
    
    // Extraer datos de la fila (basado en la estructura de tu tabla)
    const imagen = celdas[0] ? celdas[0].querySelector('img')?.src : '';
    const username = celdas[1] ? celdas[1].textContent.trim() : '';
    const fullName = celdas[2] ? celdas[2].textContent.trim() : '';
    const email = celdas[3] ? celdas[3].textContent.trim() : '';
    const role = celdas[4] ? celdas[4].textContent.trim().toLowerCase() : '';

    // Poblar formulario con datos existentes
    const usernameInput = document.getElementById('username');
    const fullNameInput = document.getElementById('full_name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    if (usernameInput) usernameInput.value = username;
    if (fullNameInput) fullNameInput.value = fullName;
    if (emailInput) emailInput.value = email;
    if (roleSelect) roleSelect.value = role;
    
    // Para edición, la contraseña no es requerida
    if (passwordInput) {
        passwordInput.value = '';
        passwordInput.required = false;
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.value = '';
        confirmPasswordInput.required = false;
    }

    // Mostrar imagen actual si existe
    if (imagen && imagen !== '/BookVerse/app/public/images/default-avatar.png') {
        const imagePreview = document.getElementById('image-preview');
        if (imagePreview) {
            imagePreview.innerHTML = `<img src="${imagen}" alt="Imagen actual">`;
            imagePreview.classList.remove('empty');
        }
    }

    // Configurar modal para edición
    const modal = document.getElementById('user-form-modal');
    const modalTitle = document.querySelector('#user-form-modal h3');
    const submitBtn = document.querySelector('#userForm button[type="submit"]');
    const userForm = document.getElementById('userForm');

    if (modal) modal.style.display = 'block';
    if (modalTitle) modalTitle.textContent = 'Editar Usuario';
    if (submitBtn) submitBtn.textContent = 'Actualizar';
    if (userForm) userForm.dataset.editingUserId = userId;
}

function eliminarUsuario(fila) {
    const userId = fila.dataset.userId;
    const username = fila.getElementsByTagName('td')[1]?.textContent.trim();

    // Using SweetAlert2 for confirmation
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar al usuario "${username}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí harías la petición AJAX para eliminar
                deleteUserFromServer(userId)
                    .then(() => {
                        fila.remove();
                        Swal.fire('Eliminado', 'El usuario ha sido eliminado correctamente.', 'success');
                    })
                    .catch(error => {
                        Swal.fire('Error', 'No se pudo eliminar el usuario.', 'error');
                        console.error('Error al eliminar usuario:', error);
                    });
            }
        });
    } else {
        // Fallback si SweetAlert2 no está disponible
        if (confirm(`¿Estás seguro de que deseas eliminar al usuario "${username}"?`)) {
            deleteUserFromServer(userId)
                .then(() => {
                    fila.remove();
                    alert('Usuario eliminado correctamente.');
                })
                .catch(error => {
                    alert('Error al eliminar el usuario.');
                    console.error('Error al eliminar usuario:', error);
                });
        }
    }
}

function cerrarFormulario() {
    const modal = document.getElementById('user-form-modal');
    if (modal) {
        modal.style.display = 'none';
        resetForm();
    }
}

function resetForm() {
    const form = document.getElementById('userForm');
    if (form) {
        form.reset();
        delete form.dataset.editingUserId;
        
        // Resetear preview de imagen
        const imagePreview = document.getElementById('image-preview');
        if (imagePreview) {
            imagePreview.innerHTML = '<span>Seleccionar imagen</span>';
            imagePreview.classList.add('empty');
        }
        
        // Limpiar campo oculto de imagen base64
        const imagenBase64 = document.getElementById('imagen-base64');
        if (imagenBase64) imagenBase64.value = '';
    }
}

function previewImage(input) {
    const file = input.files[0];
    const preview = document.getElementById('image-preview');
    const imagenBase64Input = document.getElementById('imagen-base64');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            preview.classList.remove('empty');
            
            // Guardar imagen en base64 para envío
            if (imagenBase64Input) {
                imagenBase64Input.value = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    }
}

function guardarUsuario(event) {
    event.preventDefault();
    
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    const isEditing = form.dataset.editingUserId;
    
    // Validar contraseñas si se proporcionaron
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    
    if (password && password !== confirmPassword) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
        } else {
            alert('Las contraseñas no coinciden');
        }
        return;
    }
    
    // Para nuevos usuarios, la contraseña es requerida
    if (!isEditing && !password) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'La contraseña es requerida para nuevos usuarios', 'error');
        } else {
            alert('La contraseña es requerida para nuevos usuarios');
        }
        return;
    }
    
    // Preparar datos para envío
    const userData = {
        username: formData.get('username'),
        full_name: formData.get('nombre'),
        email: formData.get('email'),
        role: formData.get('rol'),
        imagen: formData.get('imagen')
    };
    
    // Solo incluir contraseña si se proporcionó
    if (password) {
        userData.password = password;
    }
    
    // Agregar ID si estamos editando
    if (isEditing) {
        userData.id = form.dataset.editingUserId;
    }
    
    // Aquí harías la petición AJAX al servidor
    saveUserToServer(userData, isEditing)
        .then(response => {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Éxito', isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente', 'success');
            } else {
                alert(isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
            }
            cerrarFormulario();
            // Recargar la tabla o actualizar la fila específica
            if (isEditing) {
                updateUserRow(userData);
            } else {
                addUserRow(response.user);
            }
        })
        .catch(error => {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'No se pudo guardar el usuario', 'error');
            } else {
                alert('No se pudo guardar el usuario');
            }
            console.error('Error al guardar usuario:', error);
        });
}

// Funciones para comunicación con el servidor (implementar según tu backend)
async function saveUserToServer(userData, isEditing) {
    const url = isEditing ? '/BookVerse/admin/user/update' : '/BookVerse/admin/user/create';
    const method = isEditing ? 'PUT' : 'POST';
    
    const response = await fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData)
    });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
}

async function deleteUserFromServer(userId) {
    const response = await fetch(`/BookVerse/admin/user/delete/${userId}`, {
        method: 'DELETE'
    });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return await response.json();
}

function updateUserRow(userData) {
    const row = document.querySelector(`tr[data-user-id="${userData.id}"]`);
    if (row) {
        const cells = row.getElementsByTagName('td');
        if (cells[0]) {
            const img = cells[0].querySelector('img');
            if (img && userData.imagen) {
                img.src = userData.imagen;
            }
        }
        if (cells[1]) cells[1].textContent = userData.username;
        if (cells[2]) cells[2].textContent = userData.full_name;
        if (cells[3]) cells[3].textContent = userData.email;
        if (cells[4]) cells[4].textContent = userData.role;
        if (cells[6]) cells[6].textContent = new Date().toLocaleDateString('es-ES');
    }
}

function addUserRow(userData) {
    const tableBody = document.getElementById('user-table-body');
    if (tableBody) {
        const newRow = document.createElement('tr');
        newRow.dataset.userId = userData.id;
        newRow.innerHTML = `
            <td>
                <img src="${userData.imagen || '/BookVerse/app/public/images/default-avatar.png'}" 
                     alt="Profile Photo" class="profile-img">
            </td>
            <td>${userData.username}</td>
            <td>${userData.full_name}</td>
            <td>${userData.email}</td>
            <td>${userData.role}</td>
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
        tableBody.appendChild(newRow);
    }
}