/// Configuración global de DataTables
$(document).ready(function() {
    // Verificar si DataTables está disponible
    if (!$.fn.DataTable) {
        console.log('DataTables no está disponible, saltando inicialización');
        return;
    }
    
    // Aplicar configuración global a todas las tablas que no tengan configuración específica
    $('.table').each(function() {
        const tableId = $(this).attr('id');
        
        // Lista de tablas que NO deben usar DataTables (tienen su propia lógica)
        const tablasExcluidas = [
            'tablaRoles', 
            'tablaUsuarios', 
            'tablaMascotas', 
            'tablaDispositivos',
            'tabla-registros' // Tabla del monitor IoT
        ];
        
        // Solo aplicar si no es una tabla que ya tiene configuración específica
        if (tableId && !tablasExcluidas.includes(tableId)) {
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