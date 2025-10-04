/**
 * Loader Utility
 * Controla la visibilidad del loader #msgLoading
 * Autor: Tú 🔥
 */

const loaderId = 'msgLoading';

export function showLoader() {
    const loader = document.getElementById(loaderId);
    if (loader) loader.style.display = 'flex';
}

export function hideLoader() {
    const loader = document.getElementById(loaderId);
    if (loader) loader.style.display = 'none';
}
