// Renombrada para evitar conflictos
function renderUsuariosEjemplo() {
    const tbody = document.querySelector("#user-table-body");
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td>Juan Pérez</td>
            <td>juan@example.com</td>
            <td>Admin</td>
            <td>
                <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 6px;">
                    <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
                </button>
                <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 6px;">
                    <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
                </button>
            </td>

        </tr>
    `;
}
