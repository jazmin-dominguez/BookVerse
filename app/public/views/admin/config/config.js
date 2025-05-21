// Escuchar cambio de archivo para vista previa
document.getElementById('profilePicInput').addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (evt) {
            document.getElementById('profilePreview').src = evt.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Escuchar submit del formulario
document.getElementById('config-form').addEventListener('submit', function (e) {
    e.preventDefault();
    alert('Cambios guardados (simulado)');
    // Aquí pondrías código para enviar al backend con fetch/AJAX
});
