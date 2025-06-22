// JS integrado para Monitor IoT con funcionalidad de reportes
$(function() {
  console.log('Monitor.js cargado correctamente');
  
  // Variables globales para reportes
  let map = null;
  let marker = null;
  let markersGroup = null;
  
  // Inicializar solo cuando se active la pestaña de reportes
  $('#reportes-tab').on('shown.bs.tab', function (e) {
    inicializarReportes();
  });
  
  function inicializarReportes() {
    console.log('Inicializando funcionalidad de reportes...');
    
    // Inicializar Select2 para propietario
    if (!$('#propietario').hasClass('select2-hidden-accessible')) {
      $('#propietario').select2({
        ajax: {
          url: MONITOR_URL + 'getPropietarios',
          dataType: 'json',
          delay: 250,
          data: params => ({ q: params.term }),
          processResults: data => data
        },
        placeholder: 'Buscar dueño...',
        allowClear: true,
        width: '100%'
      });
    }

    // Mascota dependiente del propietario
    $('#propietario').off('change.reportes').on('change.reportes', function() {
      const usuario_id = $(this).val();
      if (!usuario_id) {
        $('#mascota').prop('disabled', true).val(null).trigger('change');
        return;
      }
      $('#mascota').prop('disabled', false).val(null).trigger('change');
      $('#mascota').select2('destroy').select2({
        ajax: {
          url: MONITOR_URL + 'getMascotasPorPropietario',
          dataType: 'json',
          delay: 250,
          data: params => ({ usuario_id, q: params.term }),
          processResults: data => data
        },
        placeholder: 'Selecciona mascota...',
        allowClear: true,
        width: '100%'
      });
    });

    // MAC con autocompletado
    if (!$('#mac').hasClass('select2-hidden-accessible')) {
      $('#mac').select2({
        ajax: {
          url: MONITOR_URL + 'getMacs',
          dataType: 'json',
          delay: 250,
          data: params => ({ q: params.term }),
          processResults: data => data
        },
        tags: true,
        placeholder: 'Buscar MAC...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 2
      });
    }

    // Mostrar todas
    $('#mostrar-todo').off('click.reportes').on('click.reportes', function() {
      $('#propietario').val(null).trigger('change');
      $('#mascota').val(null).trigger('change').prop('disabled', true);
      $('#mac').val(null).trigger('change');
      $('#rango-fechas').val('');
      cargarRegistros(1, true); // true = mostrar todos según permisos
    });

    // Cargar tabla al cambiar filtros
    $('#propietario, #mascota, #mac').off('change.reportes').on('change.reportes', function() {
      cargarRegistros();
    });

    // Inicializar mapa
    const tieneMapa = $('#map').length > 0;
    if (tieneMapa && !map) {
      map = L.map('map').setView([19.4326, -99.1332], 5);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);
    }

    // Inicializar date range picker
    if (!$('#rango-fechas').data('daterangepicker')) {
      $('#rango-fechas').daterangepicker({
        autoUpdateInput: false,
        locale: {
          format: 'YYYY-MM-DD',
          separator: ' a ',
          applyLabel: 'Aplicar',
          cancelLabel: 'Limpiar',
          fromLabel: 'Desde',
          toLabel: 'Hasta',
          customRangeLabel: 'Personalizado',
          daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
          monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
          firstDay: 1
        }
      });

      $('#rango-fechas').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' a ' + picker.endDate.format('YYYY-MM-DD'));
        cargarRegistros();
      });

      $('#rango-fechas').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        cargarRegistros();
      });
    }

    // Exportar a CSV
    $('#exportar-excel').off('click.reportes').on('click.reportes', function() {
      const usuario_id = $('#propietario').val();
      const mascota_id = $('#mascota').val();
      const mac = $('#mac').val();
      let url = MONITOR_URL + 'exportarCsv?usuario_id=' + (usuario_id||'') + '&mascota_id=' + (mascota_id||'') + '&mac=' + (mac||'');
      window.open(url, '_blank');
    });

    // Al hacer click en una fila, centrar el mapa principal y mostrar marcador
    $('#tabla-registros').off('click.reportes').on('click.reportes', 'tr', function() {
      if (!map) return;
      const lat = $(this).data('lat');
      const lng = $(this).data('lng');
      const mascota = $(this).find('td').eq(1).text();
      const dueno = $(this).find('td').eq(2).text();
      const mac = $(this).find('td').eq(3).text();
      if (lat && lng) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        map.setView([lat, lng], 16);
        marker.bindPopup(`<b>${mascota}</b><br>Dueño: ${dueno}<br>MAC: <span class='text-monospace'>${mac}</span>`).openPopup();
      }
    });

    // Cargar ubicaciones iniciales en el mapa
    cargarUltimasUbicaciones();
    
    // Cargar datos iniciales
    console.log('Llamando a cargarRegistros inicial...');
    cargarRegistros();
  }

  // Cargar registros y actualizar tabla/mapa
  function cargarRegistros(page = 1, mostrarTodos = false) {
    console.log('Iniciando cargarRegistros con página:', page, 'mostrarTodos:', mostrarTodos);
    const usuario_id = $('#propietario').val();
    const mascota_id = $('#mascota').val();
    const mac = $('#mac').val();
    const rangoFechas = $('#rango-fechas').val();
    let fecha_inicio = '', fecha_fin = '';
    if (rangoFechas && rangoFechas.includes(' a ')) {
      [fecha_inicio, fecha_fin] = rangoFechas.split(' a ');
    }
    
    console.log('Parámetros de búsqueda:', { usuario_id, mascota_id, mac, page, fecha_inicio, fecha_fin, mostrarTodos });
    
    $.getJSON(MONITOR_URL + 'getRegistros', {
      usuario_id, mascota_id, mac, page, perPage: 20, fecha_inicio, fecha_fin, mostrar_todos: mostrarTodos
    }, function(resp) {
      console.log('Respuesta recibida:', resp);
      let html = '';
      resp.data.forEach((r, i) => {
        let tempClass = r.temperatura > 39 ? 'text-danger fw-bold' : 'text-primary';
        let bpmClass = r.ritmo_cardiaco > 180 ? 'text-danger fw-bold' : 'text-success';
        let batClass = r.bateria < 20 ? 'text-warning fw-bold' : 'text-success';
        html += `<tr data-lat="${r.latitud}" data-lng="${r.longitud}">
          <td>${r.fecha_hora}</td>
          <td>${r.mascota_nombre || '-'}</td>
          <td>${r.dueno_nombre || '-'}</td>
          <td>${r.mac || '-'}</td>
          <td class="${tempClass}">${r.temperatura ?? '-'}</td>
          <td class="${bpmClass}">${r.ritmo_cardiaco ?? '-'}</td>
          <td>${r.ubicacion || '-'}</td>
          <td class="${batClass}">${r.bateria ?? '-'}</td>
        </tr>`;
      });
      $('#tabla-registros tbody').html(html);
      
      // Paginador
      let totalPages = Math.ceil(resp.total / (resp.perPage || 20));
      let pagHtml = '';
      if (totalPages > 1) {
        for (let i = 1; i <= Math.min(totalPages, 10); i++) {
          pagHtml += `<button class="btn btn-sm ${i === resp.page ? 'btn-primary' : 'btn-outline-primary'} mx-1" onclick="actualizarPaginacion(${i})">${i}</button>`;
        }
        if (totalPages > 10) {
          pagHtml += `<span class="mx-2">... ${totalPages} páginas</span>`;
        }
      }
      $('#paginador').html(pagHtml);
      
      // Mapa: centrar en el primer registro
      if (map && resp.data.length && resp.data[0].latitud && resp.data[0].longitud) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([resp.data[0].latitud, resp.data[0].longitud]).addTo(map);
        map.setView([resp.data[0].latitud, resp.data[0].longitud], 15);
      }
    }).fail(function(xhr, status, error) {
      console.error('Error en la petición AJAX:', { xhr, status, error });
      console.log('URL de la petición:', MONITOR_URL + 'getRegistros');
    });
  }
  
  // Función global para paginación
  window.cargarRegistros = cargarRegistros;
  window.actualizarPaginacion = function(page) {
    cargarRegistros(page);
  };

  // Mostrar todas las mascotas en el mapa con popups
  function cargarUltimasUbicaciones() {
    if (!map) return;
    $.getJSON(MONITOR_URL + 'getUltimasUbicaciones', function(mascotas) {
      if (!map) return;
      if (markersGroup) map.removeLayer(markersGroup);
      markersGroup = L.layerGroup();
      mascotas.forEach(m => {
        if (m.latitude && m.longitude) {
          const marker = L.marker([m.latitude, m.longitude]).bindPopup(
            `<b>${m.mascota_nombre}</b><br>Dueño: ${m.dueno_nombre}<br>MAC: <span class='text-monospace'>${m.mac}</span><br><small>${m.fecha}</small>`
          );
          markersGroup.addLayer(marker);
        }
      });
      markersGroup.addTo(map);
      if (mascotas.length > 0) {
        const bounds = L.latLngBounds(mascotas.map(m => [m.latitude, m.longitude]));
        map.fitBounds(bounds, {padding: [30, 30]});
      }
    });
  }

  // Actualizar ubicaciones cuando cambien los filtros
  $(document).on('change', '#propietario, #mascota, #mac', function() {
    if (map && $('#reportes-panel').hasClass('active')) {
      cargarUltimasUbicaciones();
    }
  });
}); 