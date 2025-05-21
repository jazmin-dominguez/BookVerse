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
                case 'Books':
                    loadBooks();
                    break;
                case 'Reviews':
                    loadReseñas();
                    break;
                case 'Settings':
                    loadConfig();
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

function loadReseñas() {
    const main = document.getElementById('dynamic-content');

    fetch('reviews.html') // ✅ Asegúrate que reviews.html existe en la misma carpeta
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar reviews.html');
            return response.text();
        })
        .then(html => {
            main.innerHTML = html;
            // Si necesitas ejecutar código adicional después de cargar
            if (typeof renderReviews === 'function') renderReviews();
        })
        .catch(error => {
            main.innerHTML = `<p style="color: red;">Error al cargar reseñas: ${error.message}</p>`;
            console.error(error);
        });
}

function loadConfig() {
    const main = document.getElementById('dinamic-content');

    fetch('config.html') // ✅ Asegúrate de que config.html esté en la misma carpeta
        .then(response => {
            if (!response.ok) throw new Error('No se pudo cargar config.html');
            return response.text();
        })
        .then(html => {
            main.innerHTML = html;

            // Ejecutar funciones adicionales si existen (por ejemplo, para cargar datos del usuario)
            if (typeof initConfig === 'function') initConfig();
        })
        .catch(error => {
            main.innerHTML = `<p style="color: red;">Error al cargar configuración: ${error.message}</p>`;
            console.error(error);
        });
}


function logout() {
    // Aquí podrías limpiar la sesión si usas una
    window.location.href = "../home/index.html";
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
        

    `;
    tr.innerHTML = trContent;
    document.querySelector('table tbody').appendChild(tr);
});

document.getElementById('profile-photo-clickable').addEventListener('click', () => {
    fetch('config.html')
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



