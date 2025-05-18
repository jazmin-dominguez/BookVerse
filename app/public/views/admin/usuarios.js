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
                <button style="background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px;">Editar</button>
                <button style="background-color: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px;">Eliminar</button>
            </td>

        </tr>
    `;
}
