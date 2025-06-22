/// Configuración global de DataTables
$(document).ready(function() {
    // Aplicar configuración global a todas las tablas que no tengan configuración específica
    $('.table').each(function() {
        const tableId = $(this).attr('id');
        
        // Solo aplicar si no es una tabla que ya tiene configuración específica
        if (tableId && !['tablaRoles', 'tablaUsuarios', 'tablaMascotas', 'tablaDispositivos'].includes(tableId)) {
            if (!$.fn.DataTable.isDataTable('#' + tableId)) {
                $('#' + tableId).DataTable({
                    "language": {
                        "url": "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
                    },
                    "responsive": false,
                    "lengthChange": false,
                    "dom": 'ftip'
                });
            }
        }
    });
}); 