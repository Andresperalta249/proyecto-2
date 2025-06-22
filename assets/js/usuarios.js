$(document).ready(function() {
    // Obtener configuración desde el DOM
    const configElement = document.getElementById('usuarios-config');
    if (!configElement) {
        console.error('Elemento de configuración no encontrado');
        return;
    }

    // Verificar que estamos en la página correcta
    if (!document.getElementById('tablaUsuarios')) {
        return;
    }

    // Verificar que la tabla realmente existe y tiene contenido
    const tablaUsuariosElement = document.getElementById('tablaUsuarios');
    if (!tablaUsuariosElement.querySelector('thead') || !tablaUsuariosElement.querySelector('tbody')) {
        console.log('La tabla de usuarios no tiene la estructura correcta - saliendo');
        return;
    }

    // --- CONFIGURACIÓN Y UTILIDADES ---
    const CURRENT_USER_ID = configElement.dataset.userId;
    const PERMISOS = {
        editar: configElement.dataset.permisoEditar === 'true',
        eliminar: configElement.dataset.permisoEliminar === 'true'
    };

    function getDynamicPageLength() {
        const tableWrapper = document.querySelector('.table-responsive');
        if (!tableWrapper) return 10;

        const topOffset = tableWrapper.getBoundingClientRect().top;
        const headerHeight = 56;
        const footerHeight = 50;
        const safetyMargin = 20;

        const availableHeight = window.innerHeight - topOffset - headerHeight - footerHeight - safetyMargin;
        const avgRowHeight = 48;
        const numRows = Math.floor(availableHeight / avgRowHeight);
        
        return Math.max(5, numRows);
    }

    // --- INICIALIZACIÓN DE DATATABLES ---
    const tablaUsuarios = $('#tablaUsuarios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": `${APP_URL}/usuarios/obtenerUsuarios`,
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "nombre" },
            { "data": "email" },
            { "data": "telefono" },
            { "data": "rol" },
            { 
                "data": "estado",
                "render": function(data, type, row) {
                    const isChecked = data === 'activo';
                    const canToggle = PERMISOS.editar;
                    // Proteger al superadmin (rol_id 3) y a uno mismo de ser desactivados
                    const isDisabled = row.rol_id == 3 || row.id == CURRENT_USER_ID;
                    
                    return `
                        <div class="form-check form-switch d-flex justify-content-center">
                            <input class="form-check-input switch-estado" type="checkbox" 
                                   role="switch"
                                   data-id="${row.id}" 
                                   ${isChecked ? 'checked' : ''} 
                                   ${canToggle && !isDisabled ? '' : 'disabled'}
                                   title="${isDisabled ? 'No se puede cambiar el estado de este usuario' : (canToggle ? 'Cambiar estado' : 'No tienes permiso')}">
                        </div>
                    `;
                },
                "className": "text-center",
                "orderable": false
            },
            { 
                "data": "id",
                "render": function(data, type, row) {
                    let botones = '';
                    // El chequeo de permisos se hace ahora en el controlador
                    botones += `<button class="btn btn-sm btn-info btn-editar" data-id="${data}" title="Editar"><i class="fas fa-edit"></i></button>`;
                    
                    if (row.puede_eliminar) {
                        botones += ` <button class="btn btn-sm btn-danger btn-eliminar" data-id="${data}" title="Eliminar"><i class="fas fa-trash-alt"></i></button>`;
                    } else {
                        botones += ` <button class="btn btn-sm btn-secondary" disabled title="Usuario protegido"><i class="fas fa-lock"></i></button>`;
                    }
                    return botones;
                },
                "orderable": false,
                "searchable": false,
                "width": "100px"
            }
        ],
        "language": {
            "url": `${APP_URL}/assets/js/i18n/Spanish.json`
        },
        "responsive": false,
        "autoWidth": false,
        "ordering": true,
        "lengthChange": false,
        "dom": 'ftip'
    });

    // --- MANEJO DE EVENTOS ---
    
    // Cambiar estado de usuario con switch
    $('#tablaUsuarios tbody').on('change', '.switch-estado', function() {
        const userId = $(this).data('id');
        const nuevoEstado = this.checked ? 'activo' : 'inactivo';

        fetch(`${APP_URL}/usuarios/toggleEstado`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                $(this).prop('checked', !this.checked); // Revertir si falla
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de comunicación.' });
            $(this).prop('checked', !this.checked);
        });
    });

    // --- GESTIÓN DE MODALES (Crear y Editar) ---

    const modalElement = document.getElementById('modal-generico');
    const modal = new bootstrap.Modal(modalElement);
    const modalContent = modalElement.querySelector('.modal-content');

    function loadFormInModal(url) {
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar el formulario.');
                return response.text();
            })
            .then(html => {
                modalContent.innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error en fetch:', error);
                Swal.fire('Error', 'No se pudo cargar el contenido del modal.', 'error');
            });
    }

    // Verificar que los elementos existen antes de agregar event listeners
    const btnNuevoUsuario = document.getElementById('btnNuevoUsuario');
    const btnNuevoUsuarioFlotante = document.getElementById('btnNuevoUsuarioFlotante');

    if (btnNuevoUsuario) {
        btnNuevoUsuario.addEventListener('click', () => {
            loadFormInModal(`${APP_URL}/usuarios/cargarFormulario`);
        });
    }

    if (btnNuevoUsuarioFlotante) {
        btnNuevoUsuarioFlotante.addEventListener('click', () => {
            loadFormInModal(`${APP_URL}/usuarios/cargarFormulario`);
        });
    }
    
    $('#tablaUsuarios tbody').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        loadFormInModal(`${APP_URL}/usuarios/cargarFormulario/${id}`);
    });

    // Enviar formulario de crear/editar
    modalElement.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'formUsuario') {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const url = form.action;

            fetch(url, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modal.hide();
                        tablaUsuarios.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Éxito', text: data.message, timer: 2000, showConfirmButton: false});
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error de Validación', html: data.message || 'Ocurrió un error.' });
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    Swal.fire('Error', 'No se pudo procesar la solicitud.', 'error');
                });
        }
    });

    // Eliminar usuario
    $('#tablaUsuarios tbody').on('click', '.btn-eliminar', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡eliminar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`${APP_URL}/usuarios/eliminarUsuario/${id}`, {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        tablaUsuarios.ajax.reload(null, false);
                        Swal.fire('¡Eliminado!', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error'));
            }
        });
    });
}); 