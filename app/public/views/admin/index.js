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
                case 'History':
                    loadHistory();
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
    const main = document.getElementById('main-content');

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

