// JS integrado para Monitor IoT con funcionalidad de reportes
$(function() {
  console.log('Monitor.js cargado correctamente');
  
  // Variables globales para reportes
  let map = null;
  let marker = null;
  let markersGroup = null;
  
  // Inicializar directamente al cargar la página
  inicializarReportes();
  
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
      console.log('Propietario seleccionado:', usuario_id);
      
      if (!usuario_id) {
        $('#mascota').prop('disabled', true).val(null).trigger('change');
        return;
      }
      
      // Habilitar y reinicializar el campo mascota
      $('#mascota').prop('disabled', false);
      
      // Destruir select2 anterior si existe
      if ($('#mascota').hasClass('select2-hidden-accessible')) {
        $('#mascota').select2('destroy');
      }
      
      // Crear nuevo select2 con las mascotas del propietario
      $('#mascota').val(null).select2({
        ajax: {
          url: MONITOR_URL + 'getMascotasPorPropietario',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              usuario_id: usuario_id,
              q: params.term || ''
            };
          },
          processResults: function(data) {
            console.log('Mascotas recibidas:', data);
            return data;
          }
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

    // Cargar tabla al cambiar filtros (después de configurar cada campo)
    $(document).off('change.reportes', '#propietario, #mascota, #mac').on('change.reportes', '#propietario, #mascota, #mac', function() {
      console.log('Cambio detectado en filtro:', $(this).attr('id'), 'valor:', $(this).val());
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
      
      // Actualizar contador de registros
      $('#total-registros').text(`${resp.total || 0} registros`);
      
      // Paginador mejorado
      let totalPages = Math.ceil(resp.total / (resp.perPage || 20));
      let currentPage = resp.page || 1;
      let pagHtml = '';
      
      if (totalPages > 1) {
        // Botón Anterior
        if (currentPage > 1) {
          pagHtml += `<button class="btn btn-sm btn-outline-secondary me-2" onclick="actualizarPaginacion(${currentPage - 1})">
            <i class="fas fa-chevron-left"></i> Anterior
          </button>`;
        }
        
        // Primera página
        if (currentPage > 3) {
          pagHtml += `<button class="btn btn-sm btn-outline-primary mx-1" onclick="actualizarPaginacion(1)">1</button>`;
          if (currentPage > 4) {
            pagHtml += `<span class="mx-2">...</span>`;
          }
        }
        
        // Páginas alrededor de la actual
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
          pagHtml += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'} mx-1" onclick="actualizarPaginacion(${i})">${i}</button>`;
        }
        
        // Última página
        if (currentPage < totalPages - 2) {
          if (currentPage < totalPages - 3) {
            pagHtml += `<span class="mx-2">...</span>`;
          }
          pagHtml += `<button class="btn btn-sm btn-outline-primary mx-1" onclick="actualizarPaginacion(${totalPages})">${totalPages}</button>`;
        }
        
        // Botón Siguiente
        if (currentPage < totalPages) {
          pagHtml += `<button class="btn btn-sm btn-outline-secondary ms-2" onclick="actualizarPaginacion(${currentPage + 1})">
            Siguiente <i class="fas fa-chevron-right"></i>
          </button>`;
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
  
  // Funciones globales para paginación
  window.cargarRegistros = cargarRegistros;
  window.actualizarPaginacion = function(page) {
    cargarRegistros(page);
  };
  

  
  // Navegación con teclado simplificada
  $(document).on('keydown', function(e) {
    // Solo si no estamos escribiendo en un input
    if (e.target.tagName.toLowerCase() !== 'input' && e.target.tagName.toLowerCase() !== 'textarea') {
      const currentPageBtn = $('.btn-primary[onclick*="actualizarPaginacion"]');
      if (currentPageBtn.length) {
        const currentPage = parseInt(currentPageBtn.text());
        
        // Flecha izquierda = página anterior
        if (e.key === 'ArrowLeft' && currentPage > 1) {
          e.preventDefault();
          cargarRegistros(currentPage - 1);
        }
        
        // Flecha derecha = página siguiente  
        if (e.key === 'ArrowRight') {
          e.preventDefault();
          const nextBtn = $('button[onclick*="actualizarPaginacion"]:contains("Siguiente")');
          if (nextBtn.length) {
            cargarRegistros(currentPage + 1);
          }
        }
      }
    }
  });

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
    if (map) {
      cargarUltimasUbicaciones();
    }
  });
}); 