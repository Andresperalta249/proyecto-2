/**
 * Utilidades de Fecha y Hora
 * =========================
 * 
 * Archivo: assets/js/date-utils.js
 * 
 * Propósito:
 *   - Funciones para manipulación y formateo de fechas.
 *   - Conversiones entre diferentes formatos de fecha.
 *   - Cálculos de tiempo y rangos de fechas.
 * 
 * Funciones principales:
 *   - formatearFecha(): Formatea una fecha en formato legible.
 *   - formatearHora(): Formatea una hora en formato legible.
 *   - calcularDiferencia(): Calcula la diferencia entre dos fechas.
 *   - obtenerFechaActual(): Obtiene la fecha actual formateada.
 * 
 * Uso:
 *   Este archivo proporciona utilidades para el manejo de fechas en toda
 *   la aplicación. Se usa principalmente en tablas y formularios.
 */

// Importar date-fns desde CDN
import { format, parseISO, differenceInMinutes, isAfter, isBefore } from 'https://cdn.jsdelivr.net/npm/date-fns@3.6.0/+esm';

// Funciones de utilidad para fechas
export const formatDate = (date, formatStr = 'yyyy-MM-dd HH:mm:ss') => {
    return format(new Date(date), formatStr);
};

export const parseDate = (dateString) => {
    return parseISO(dateString);
};

export const getMinutesDifference = (date1, date2) => {
    return differenceInMinutes(new Date(date1), new Date(date2));
};

export const isDateAfter = (date1, date2) => {
    return isAfter(new Date(date1), new Date(date2));
};

export const isDateBefore = (date1, date2) => {
    return isBefore(new Date(date1), new Date(date2));
};

// Función para formatear fechas relativas (ej: "hace 5 minutos")
export const formatRelativeTime = (date) => {
    const now = new Date();
    const diff = getMinutesDifference(now, date);
    
    if (diff < 1) return 'ahora mismo';
    if (diff < 60) return `hace ${diff} minutos`;
    if (diff < 1440) return `hace ${Math.floor(diff / 60)} horas`;
    return `hace ${Math.floor(diff / 1440)} días`;
}; 