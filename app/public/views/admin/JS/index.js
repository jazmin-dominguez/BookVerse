document.addEventListener('DOMContentLoaded', () => {
    const menuLinks = document.querySelectorAll('.sidebar a');

    menuLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Quitar 'active' a todos
            menuLinks.forEach(l => l.classList.remove('active'));

            // Activar el actual
            this.classList.add('active');

            // Obtener el texto del menú
            const section = this.querySelector('h3')?.textContent.trim();

            // Cargar contenido dinámico según la opción
            switch (section) {
                case 'Dashboard':
                    loadDashboard();
                    break;
                case 'Users':
                    loadUsers();
                    break;
                case 'Books':
                    loadBooks();
                    break;
                case 'Sale List':
                    loadSaleList();
                    break;
                case 'Reports':
                    loadReports();
                    break;
                case 'Settings':
                    loadSettings();
                    break;
                case 'New Login':
                    loadNewLogin();
                    break;
                case 'Logout':
                    logout();
                    break;
                default:
                    console.warn('Sección no reconocida:', section);
            }
        });
    });
});
// Aquí ya tienes el código del manejo del menú con addEventListener...

// Pega esto después:
function loadDashboard() {
    window.location.reload(); // recarga la página
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
            renderUsuariosEjemplo(); // se llama aquí
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

            // Ocultar user-profile
            //const userProfile = document.querySelector('.user-profile');
            //if (userProfile) userProfile.style.display = 'none';
            // Si books.html necesita ejecutar scripts, llámalos aquí:
            if (typeof renderBooks === 'function') renderBooks();
        })
        .catch(error => {
            main.innerHTML = `<p style="color: red;">Error al cargar libros: ${error.message}</p>`;
            console.error(error);
        });
}

function logout() {
    window.location.href = "login.html";
}

const sideMenu = document.querySelector('aside');
const menuBtn = document.getElementById('menu-btn');
const closeBtn = document.getElementById('close-btn');

const darkMode = document.querySelector('.dark-mode');

menuBtn.addEventListener('click', () => {
    sideMenu.style.display = 'block';
});

closeBtn.addEventListener('click', () => {
    sideMenu.style.display = 'none';
});

darkMode.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode-variables');
    darkMode.querySelector('span:nth-child(1)').classList.toggle('active');
    darkMode.querySelector('span:nth-child(2)').classList.toggle('active');
})


Orders.forEach(order => {
    const tr = document.createElement('tr');
    const trContent = `
        <td>${order.productName}</td>
        <td>${order.productNumber}</td>
        <td>${order.likeStatus}</td>
        <td class="${order.dislikes === 'Declined' ? 'danger' : order.dislikes === 'Pending' ? 'warning' : 'primary'}">${order.status}</td>
        <td class="primary">Details</td>
    `;
    tr.innerHTML = trContent;
    document.querySelector('table tbody').appendChild(tr);
});

function loadScript(url) {
    return new Promise((resolve, reject) => {
        let script = document.createElement('script');
        script.src = url;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Error cargando script ${url}`));
        document.body.appendChild(script);
    });
}

document.getElementById('profile-photo-clickable').addEventListener('click', () => {
    fetch('config/config.html')
        .then(res => {
            if (!res.ok) throw new Error('Error cargando config.html');
            return res.text();
        })
        .then(html => {
            document.getElementById('dynamic-content').innerHTML = html;
            return loadScript('config/config.js');
        })
        .catch(err => console.error(err));
});


function agregarUsuario() {
        // Resetear formulario y modo agregar
        cerrarFormulario();
        document.getElementById('user-form-modal').style.display = 'block';
    }

    function cerrarFormulario() {
        document.getElementById('user-form-modal').style.display = 'none';
        document.getElementById('userForm').reset();

        // Resetear título y botón a modo agregar
        document.querySelector('#user-form-modal h3').innerText = 'Agregar Usuario';
        document.querySelector('#userForm button[type="submit"]').innerText = 'Guardar';

        // Limpiar estado de edición
        delete document.getElementById('userForm').dataset.editingRowIndex;
    }

    function editarUsuario(fila) {
        // Obtener las celdas de la fila seleccionada
        const celdas = fila.getElementsByTagName('td');

        // Cargar datos en el formulario
        document.getElementById('nombre').value = celdas[0].innerText;
        document.getElementById('email').value = celdas[1].innerText;
        document.getElementById('rol').value = celdas[2].innerText;

        // Guardar índice fila para actualizar
        document.getElementById('userForm').dataset.editingRowIndex = Array.from(fila.parentNode.children).indexOf(fila);

        // Cambiar título y botón para modo editar
        document.querySelector('#user-form-modal h3').innerText = 'Editar Usuario';
        document.querySelector('#userForm button[type="submit"]').innerText = 'Actualizar';

        // Mostrar modal
        document.getElementById('user-form-modal').style.display = 'block';
    }

    function guardarUsuario(event) {
        event.preventDefault();

        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        const rol = document.getElementById('rol').value;

        const tbody = document.getElementById('user-table-body');
        const editingIndex = document.getElementById('userForm').dataset.editingRowIndex;

        if (editingIndex !== undefined) {
            // Actualizar fila existente
            const fila = tbody.children[editingIndex];
            fila.innerHTML = `
                <td>${nombre}</td>
                <td>${email}</td>
                <td>${rol}</td>
                <td>
                    <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 8px;" onclick="editarUsuario(this.parentElement.parentElement)">
                        <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
                    </button>
                    <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 8px;" onclick="eliminarUsuario(this.parentElement.parentElement)">
                        <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
                    </button>
                </td>
            `;

            Swal.fire({
                icon: 'success',
                title: 'Usuario actualizado',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            // Crear nueva fila
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${nombre}</td>
                <td>${email}</td>
                <td>${rol}</td>
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

            Swal.fire({
                icon: 'success',
                title: 'Usuario agregado',
                showConfirmButton: false,
                timer: 1500
            });
        }

        // Limpiar estado edición y cerrar modal
        delete document.getElementById('userForm').dataset.editingRowIndex;
        cerrarFormulario();

        return false;
    }

    function eliminarUsuario(fila) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fila.remove();
                Swal.fire({
                    icon: 'success',
                    title: 'Usuario eliminado',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    }


