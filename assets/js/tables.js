/**
 * SISTEMA DE TABLAS MEJORADO
 * Funcionalidades: Columnas colapsables, vista compacta, vista móvil
 */

class TablaSistema {
    constructor(elemento, opciones = {}) {
        this.tabla = elemento;
        this.opciones = {
            vistaCompacta: true,
            ...opciones
        };
        this.inicializar();
    }

    inicializar() {
        this.aplicarVistaCompacta();
        this.configurarVistaMovil();
    }

    aplicarVistaCompacta() {
        if (this.opciones.vistaCompacta) {
            this.tabla.classList.add('vista-compacta');
        }
    }

    configurarVistaMovil() {
        const mediaQuery = window.matchMedia('(max-width: 768px)');
        
        const manejarCambio = (e) => {
            if (e.matches) {
                this.activarVistaTarjetas();
            } else {
                this.desactivarVistaTarjetas();
            }
        };

        mediaQuery.addListener(manejarCambio);
        manejarCambio(mediaQuery);
    }

    activarVistaTarjetas() {
        const tbody = this.tabla.querySelector('tbody');
        if (!tbody) return;

        // Crear contenedor de tarjetas si no existe
        let contenedorTarjetas = this.tabla.parentNode.querySelector('.vista-tarjetas');
        if (!contenedorTarjetas) {
            contenedorTarjetas = document.createElement('div');
            contenedorTarjetas.className = 'vista-tarjetas';
            this.tabla.parentNode.insertBefore(contenedorTarjetas, this.tabla);
        }

        // Obtener headers
        const headers = Array.from(this.tabla.querySelectorAll('thead th')).map(th => th.textContent.trim());

        // Limpiar contenedor
        contenedorTarjetas.innerHTML = '';

        // Crear tarjetas desde las filas
        const filas = tbody.querySelectorAll('tr');
        filas.forEach(fila => {
            const tarjeta = document.createElement('div');
            tarjeta.className = 'tarjeta-item';

            const header = document.createElement('div');
            header.className = 'tarjeta-header';
            header.innerHTML = `<h4>${headers[0]}: ${fila.cells[0].textContent}</h4>`;
            tarjeta.appendChild(header);

            const contenido = document.createElement('div');
            contenido.className = 'tarjeta-contenido';

            // Crear campos para cada columna
            for (let i = 1; i < headers.length; i++) {
                if (fila.cells[i]) {
                    const campo = document.createElement('div');
                    campo.className = 'tarjeta-campo';
                    campo.innerHTML = `
                        <span class="tarjeta-etiqueta">${headers[i]}:</span>
                        <span class="tarjeta-valor">${fila.cells[i].innerHTML}</span>
                    `;
                    contenido.appendChild(campo);
                }
            }

            tarjeta.appendChild(contenido);
            contenedorTarjetas.appendChild(tarjeta);
        });

        // Ocultar tabla
        this.tabla.style.display = 'none';
        contenedorTarjetas.style.display = 'block';
    }

    desactivarVistaTarjetas() {
        const contenedorTarjetas = this.tabla.parentNode.querySelector('.vista-tarjetas');
        if (contenedorTarjetas) {
            contenedorTarjetas.style.display = 'none';
        }
        this.tabla.style.display = 'table';
    }
}

// Inicializar para todas las tablas del sistema
document.addEventListener('DOMContentLoaded', function() {
    const tablas = document.querySelectorAll('.tabla-sistema');
    tablas.forEach(tabla => {
        new TablaSistema(tabla);
    });
}); 