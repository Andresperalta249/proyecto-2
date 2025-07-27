<div class="contenedor-sistema">
    <div class="contenedor-sistema-header">
        <div class="header-content">
            <div class="header-title">
                <i class="fas fa-user-tag"></i>
                Lista de Roles
            </div>
            <div class="header-search">
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar roles...">
            </div>
        </div>
    </div>
    <div class="contenedor-sistema-body">
        <div id="rolesTable">
            <!-- La tabla se cargará aquí mediante AJAX -->
        </div>
    </div>
</div>

<?php if (verificarPermiso('crear_roles')): ?>
<button type="button" id="btnAgregarRol" class="btn-flotante-sistema" title="Agregar Rol">
    <i class="fas fa-plus"></i>
</button>
<?php endif; ?>
<!-- Modal para crear/editar rol -->
<div class="modal fade" id="rolModal" tabindex="-1" aria-labelledby="rolModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div id="modalContent">
                <!-- El contenido del modal se cargará aquí mediante AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar tabla inicial
    cargarTablaRoles();

    // Búsqueda en tiempo real
    document.getElementById('searchInput').addEventListener('input', function() {
        cargarTablaRoles();
    });

    // Abrir modal para crear rol
    document.getElementById('btnAgregarRol')?.addEventListener('click', function() {
        cargarFormularioRol();
    });

    // Delegación de eventos para editar y eliminar
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-editar')) {
            const id = e.target.closest('.btn-editar').dataset.id;
            cargarFormularioRol(id);
        }
        if (e.target.closest('.btn-eliminar')) {
            const id = e.target.closest('.btn-eliminar').dataset.id;
            eliminarRol(id);
        }
    });

    // Delegación de eventos para cambiar estado
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('cambiar-estado-rol')) {
            const id = e.target.dataset.id;
            const nuevoEstado = e.target.checked ? 'activo' : 'inactivo';
            cambiarEstadoRol(id, nuevoEstado);
        }
    });

    // Manejar cierre del modal
    const rolModal = document.getElementById('rolModal');
    if (rolModal) {
        rolModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('modalContent').innerHTML = '';
        });
    }
});

function cargarTablaRoles() {
    const search = document.getElementById('searchInput').value;

    fetch(`<?= APP_URL ?>/roles/tabla?search=${encodeURIComponent(search)}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('rolesTable').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar la tabla de roles'
            });
        });
}

function cargarFormularioRol(id = null) {
    const url = id ? 
        `<?= APP_URL ?>/roles/form?id=${encodeURIComponent(id)}` : 
        '<?= APP_URL ?>/roles/form';

    // Cierra el modal si ya está abierto y limpia el contenido
    const rolModal = document.getElementById('rolModal');
    const modalInstance = bootstrap.Modal.getInstance(rolModal);
    if (modalInstance) {
        modalInstance.hide();
    }
    document.getElementById('modalContent').innerHTML = '';

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            const modal = new bootstrap.Modal(rolModal, {
                backdrop: false,
                keyboard: true,
                focus: true
            });
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar el formulario'
            });
        });
}

function eliminarRol(id) {
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
            const formData = new FormData();
            formData.append('id', id);

            fetch('<?= APP_URL ?>/roles/delete', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Rol eliminado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        cargarTablaRoles();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al eliminar el rol'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar el rol'
                });
            });
        }
    });
}

function cambiarEstadoRol(id, estado) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('estado', estado);

    fetch('<?= APP_URL ?>/roles/cambiarEstado', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Estado actualizado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                cargarTablaRoles();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Error al cambiar el estado'
            });
            // Revertir el switch si hay error
            cargarTablaRoles();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cambiar el estado'
        });
        // Revertir el switch si hay error
        cargarTablaRoles();
    });
}
</script> 