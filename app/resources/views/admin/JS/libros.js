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
            }
        };

        reader.readAsDataURL(file);
    }
}

function resetImagePreview() {
    const preview = document.getElementById('image-preview');
    const imageInput = document.getElementById('cover-input');

    if (preview) {
        preview.innerHTML = '<span>Seleccionar portada</span>';
        preview.classList.add('empty');
    }
    
    if (imageInput) {
        imageInput.value = '';
    }
}

async function guardarLibro(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const isEditing = form.dataset.isEditing === 'true';
    
    try {
        // Campos requeridos para libros
        const requiredFields = ['title', 'author', 'genre', 'isbn', 'publication_year'];

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
        
        // Validar título (mínimo 2 caracteres, máximo 200)
        const title = formData.get('title').trim();
        if (title.length < 2) {
            await Swal.fire({
                icon: 'error',
                title: 'Título Inválido',
                text: 'El título debe tener al menos 2 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (title.length > 200) {
            await Swal.fire({
                icon: 'error',
                title: 'Título Muy Largo',
                text: 'El título no puede exceder 200 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar autor (mínimo 2 caracteres, máximo 100)
        const author = formData.get('author').trim();
        if (author.length < 2) {
            await Swal.fire({
                icon: 'error',
                title: 'Autor Inválido',
                text: 'El nombre del autor debe tener al menos 2 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (author.length > 100) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre de Autor Muy Largo',
                text: 'El nombre del autor no puede exceder 100 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        // Validar que el autor solo contenga letras, espacios y caracteres especiales básicos
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.'-]+$/.test(author)) {
            await Swal.fire({
                icon: 'error',
                title: 'Nombre de Autor Inválido',
                text: 'El nombre del autor solo puede contener letras, espacios y caracteres básicos',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar género (mínimo 2 caracteres, máximo 50)
        const genre = formData.get('genre').trim();
        if (genre.length < 2) {
            await Swal.fire({
                icon: 'error',
                title: 'Género Inválido',
                text: 'El género debe tener al menos 2 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (genre.length > 50) {
            await Swal.fire({
                icon: 'error',
                title: 'Género Muy Largo',
                text: 'El género no puede exceder 50 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar ISBN (formato básico)
        const isbn = formData.get('isbn').trim();
        if (isbn.length < 10) {
            await Swal.fire({
                icon: 'error',
                title: 'ISBN Inválido',
                text: 'El ISBN debe tener al menos 10 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        if (isbn.length > 17) {
            await Swal.fire({
                icon: 'error',
                title: 'ISBN Muy Largo',
                text: 'El ISBN no puede exceder 17 caracteres',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        // Validar que el ISBN solo contenga números, guiones y espacios
        if (!/^[0-9\-\s]+$/.test(isbn)) {
            await Swal.fire({
                icon: 'error',
                title: 'ISBN con Formato Incorrecto',
                text: 'El ISBN solo puede contener números, guiones y espacios',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar año de publicación
        const year = parseInt(formData.get('publication_year'));
        const currentYear = new Date().getFullYear();
        if (isNaN(year) || year < 1000 || year > currentYear) {
            await Swal.fire({
                icon: 'error',
                title: 'Año de Publicación Inválido',
                text: `El año de publicación debe estar entre 1000 y ${currentYear}`,
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validar número de páginas si se proporciona
        const pages = formData.get('pages');
        if (pages && pages.trim() !== '') {
            const pagesNum = parseInt(pages);
            if (isNaN(pagesNum) || pagesNum < 1 || pagesNum > 10000) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Número de Páginas Inválido',
                    text: 'El número de páginas debe estar entre 1 y 10,000',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        }

        // Validar stock si se proporciona
        const stock = formData.get('stock');
        if (stock && stock.trim() !== '') {
            const stockNum = parseInt(stock);
            if (isNaN(stockNum) || stockNum < 0 || stockNum > 1000) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Stock Inválido',
                    text: 'El stock debe estar entre 0 y 1,000',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        }

        // Validar descripción si se proporciona
        const description = formData.get('description');
        if (description && description.trim()) {
            if (description.trim().length > 1000) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Descripción Muy Larga',
                    text: 'La descripción no puede exceder 1,000 caracteres',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        }

        // Validación especial para ISBN duplicado en edición
        if (isEditing) {
            const currentBookId = form.dataset.bookId;
            formData.append('current_book_id', currentBookId);
        }

        // Determinar URL
        const bookId = form.dataset.bookId;
        const url = bookId
            ? `/BookVerse/admin/books/${bookId}/update` 
            : '/BookVerse/admin/books/create';

        console.log('Enviando petición a:', url);
        console.log('Datos del formulario:', Object.fromEntries(formData));

        // Mostrar loading
        const loadingSwal = Swal.fire({
            title: 'Procesando...',
            text: isEditing ? 'Actualizando libro' : 'Creando libro',
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
             
                const errorJson = JSON.parse(errorText);
                throw new Error(errorJson.error || `Error del servidor (${response.status})`); 
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
                text: isEditing ? 'Libro actualizado correctamente' : 'Libro creado correctamente',
                confirmButtonColor: '#28a745'
            });
            
            if (isEditing) {
                const rowIndex = form.dataset.editingRowIndex;
                if (rowIndex !== undefined) {
                    actualizarFilaLibro(rowIndex, result.book);
                }
            } else {
                if (result.book) {
                    agregarFilaLibro(result.book);
                }
            }
            
            cerrarFormulario();
        } else {
            throw new Error(result.error || 'Error al procesar la solicitud');
        }

    } catch (error) {
        console.error('Error completo:', error);
    }
}

function editarLibro(fila) {
    if (!fila || !fila.cells) {
        console.error('Fila inválida');
        return;
    }

    const celdas = fila.cells;
    const bookId = fila.dataset.bookId;
    
    // Verificar que tenemos suficientes celdas
    if (celdas.length < 7) {
        console.error('Estructura de fila inválida');
        return;
    }

    const coverElement = celdas[0].querySelector('img');
    const coverSrc = coverElement ? coverElement.src : '';
    const title = celdas[1].textContent || '';
    const author = celdas[2].textContent || '';
    const genre = celdas[3].textContent || '';
    const isbn = celdas[4].textContent || '';
    const year = celdas[5].textContent || '';
    const pages = celdas[6].textContent || '';

    // Guardar el ID del libro en el formulario
    const form = document.getElementById('bookForm');
    if (!form) {
        console.error('Formulario no encontrado');
        return;
    }

    form.dataset.bookId = bookId;
    form.dataset.isEditing = 'true';

    // Cargar datos en el formulario con validaciones
    const titleField = document.getElementById('title');
    const authorField = document.getElementById('author');
    const genreField = document.getElementById('genre');
    const isbnField = document.getElementById('isbn');
    const yearField = document.getElementById('publication_year');
    const pagesField = document.getElementById('pages');

    if (titleField) titleField.value = title;
    if (authorField) authorField.value = author;
    if (genreField) genreField.value = genre;
    if (isbnField) isbnField.value = isbn;
    if (yearField) yearField.value = year;
    if (pagesField) pagesField.value = pages !== 'N/A' ? pages : '';

    // Cargar imagen actual
    const preview = document.getElementById('image-preview');
    if (preview) {
        if (coverSrc && !coverSrc.includes('default-book.png')) {
            preview.innerHTML = `<img src="${coverSrc}" alt="Current Cover">`;
            preview.classList.remove('empty');
        } else {
            resetImagePreview();
        }
    }

    // Configurar modal para edición
    const modalTitle = document.querySelector('#book-form-modal h3');
    const submitButton = document.querySelector('#bookForm button[type="submit"]');
    
    if (modalTitle) modalTitle.innerText = 'Editar Libro';
    if (submitButton) submitButton.innerText = 'Actualizar';

    const rowIndex = Array.from(fila.parentNode.children).indexOf(fila);
    form.dataset.editingRowIndex = rowIndex;

    const modal = document.getElementById('book-form-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

async function eliminarLibro(fila) {
    if (!fila || !fila.dataset.bookId) {
        console.error('Fila o ID de libro no válido');
        return;
    }

    try {
        const bookId = fila.dataset.bookId;
        const title = fila.cells[1].textContent;
        console.log('Intentando eliminar libro:', bookId);
        
        // Confirmar eliminación
        const confirmResult = await Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el libro "${title}"? Esta acción no se puede deshacer.`,
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

        const response = await fetch(`/BookVerse/admin/books/${bookId}/delete`, {
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
            throw new Error('Error al eliminar libro: ' + (result?.error || 'Error del servidor'));
        }

        if (result.success || response.ok) {
            fila.remove();
            await Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Libro eliminado correctamente',
                confirmButtonColor: '#28a745'
            });
        } else {
            throw new Error(result.error || 'Error al eliminar libro');
        }

    } catch (error) {
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Error al eliminar libro',
            confirmButtonColor: '#dc3545'
        });
    }
}

function actualizarFilaLibro(index, bookData) {
    if (!bookData) {
        console.error('Datos de libro no válidos');
        return;
    }

    const fila = document.querySelector(`#book-table-body tr:nth-child(${parseInt(index) + 1})`);
    if (!fila || !fila.cells) {
        console.error('Fila no encontrada o inválida');
        return;
    }

    const celdas = fila.cells;

    try {
        // Actualizar imagen de portada
        if (celdas[0] && bookData.cover_image) {
            const imgElement = celdas[0].querySelector('img');
            if (imgElement) {
                imgElement.src = bookData.cover_image;
            }
        }

        // Actualizar datos con validaciones
        if (celdas[1] && bookData.title) celdas[1].textContent = bookData.title;
        if (celdas[2] && bookData.author) celdas[2].textContent = bookData.author;
        if (celdas[3] && bookData.genre) celdas[3].textContent = bookData.genre;
        if (celdas[4] && bookData.isbn) celdas[4].textContent = bookData.isbn;
        if (celdas[5] && bookData.publication_year) celdas[5].textContent = bookData.publication_year;
        if (celdas[6]) celdas[6].textContent = bookData.pages || 'N/A';
    } catch (error) {
        console.error('Error al actualizar fila:', error);
    }
}

function agregarFilaLibro(bookData) {
    if (!bookData) {
        console.error('Datos de libro no válidos');
        return;
    }

    const tbody = document.getElementById('book-table-body');
    if (!tbody) {
        console.error('Tabla de libros no encontrada');
        return;
    }

    const tr = document.createElement('tr');
    tr.dataset.bookId = bookData.id || '';

    const coverSrc = bookData.cover_image || '/BookVerse/app/public/images/default-book.png';

    tr.innerHTML = `
        <td>
            <img src="${coverSrc}" alt="Book Cover" class="book-cover">
        </td>
        <td>${bookData.title || ''}</td>
        <td>${bookData.author || ''}</td>
        <td>${bookData.genre || ''}</td>
        <td>${bookData.isbn || ''}</td>
        <td>${bookData.publication_year || ''}</td>
        <td>${bookData.pages || 'N/A'}</td>
        <td>${bookData.stock || '1'}</td>
        <td>${new Date().toLocaleDateString('es-ES')}</td>
        <td>${new Date().toLocaleDateString('es-ES')}</td>
        <td>
            <button title="Editar" style="background: none; color:rgb(74, 210, 16); border: none; padding: 8px;" onclick="editarLibro(this.parentElement.parentElement)">
                <i class="bi bi-pencil-square" style="font-size: 1.5rem;"></i>
            </button>
            <button title="Eliminar" style="background: none; color: #F44336; border: none; padding: 8px;" onclick="eliminarLibro(this.parentElement.parentElement)">
                <i class="bi bi-trash" style="font-size: 1.5rem;"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

function agregarLibro() {
    cerrarFormulario();
    const modal = document.getElementById('book-form-modal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function cerrarFormulario() {
    const modal = document.getElementById('book-form-modal');
    const form = document.getElementById('bookForm');
    
    if (modal) {
        modal.style.display = 'none';
    }
    
    if (form) {
        form.reset();
        
        // Limpiar datos del formulario
        delete form.dataset.bookId;
        delete form.dataset.isEditing;
        delete form.dataset.editingRowIndex;
    }
    
    resetImagePreview();

    // Restablecer títulos y botones
    const modalTitle = document.querySelector('#book-form-modal h3');
    const submitButton = document.querySelector('#bookForm button[type="submit"]');
    
    if (modalTitle) modalTitle.innerText = 'Agregar Libro';
    if (submitButton) submitButton.innerText = 'Guardar';
}

function filterBooks() {
    const searchInput = document.getElementById('searchInput');
    const authorFilter = document.getElementById('authorFilter');
    const genreFilter = document.getElementById('genreFilter');
    const tbody = document.getElementById('book-table-body');
    
    if (!searchInput || !tbody) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const authorFilterValue = authorFilter ? authorFilter.value : '';
    const genreFilterValue = genreFilter ? genreFilter.value : '';
    const rows = tbody.getElementsByTagName('tr');

    for (let row of rows) {
        if (row.cells.length < 5) continue;
        
        const title = row.cells[1].textContent.toLowerCase();
        const author = row.cells[2].textContent;
        const genre = row.cells[3].textContent;
        const isbn = row.cells[4].textContent.toLowerCase();

        const matchesSearch = title.includes(searchTerm) || 
                            author.toLowerCase().includes(searchTerm) || 
                            isbn.includes(searchTerm);
        const matchesAuthor = !authorFilterValue || author === authorFilterValue;
        const matchesGenre = !genreFilterValue || genre === genreFilterValue;

        row.style.display = matchesSearch && matchesAuthor && matchesGenre ? '' : 'none';
    }
}

// Event listener para cerrar modal al hacer click fuera
document.addEventListener('click', function (event) {
    const modal = document.getElementById('book-form-modal');
    if (event.target === modal) {
        cerrarFormulario();
    }
});

// Event listener para escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('book-form-modal');
        if (modal && modal.style.display === 'block') {
            cerrarFormulario();
        }
    }
});

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que el formulario esté configurado correctamente
    const form = document.getElementById('bookForm');
    if (form) {
        form.addEventListener('submit', guardarLibro);
    }
    
    // Configurar el input de archivo
    const imageInput = document.getElementById('cover-input');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            previewImage(this);
        });
    }
});

// Función para validar que todos los elementos necesarios existan
function validarElementosDOM() {
    const elementos = [
        'bookForm',
        'book-form-modal',
        'image-preview',
        'title',
        'author',
        'genre',
        'isbn',
        'publication_year',
        'book-table-body'
    ];
    
    const faltantes = elementos.filter(id => !document.getElementById(id));
    
    if (faltantes.length > 0) {
        console.warn('Elementos DOM faltantes:', faltantes);
    }
    
    return faltantes.length === 0;
}

// Llamar validación al cargar
document.addEventListener('DOMContentLoaded', validarElementosDOM);