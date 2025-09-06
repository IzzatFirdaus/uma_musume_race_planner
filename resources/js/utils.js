/**
 * js/utils.js
 *
 * Contains shared utility functions for the application.
 */

/**
 * Escapes HTML special characters in a string to prevent XSS.
 * @param {string | number | null | undefined} str The string to escape.
 * @returns {string} The escaped string.
 */
export function escapeHtml(str) {
    if (str === null || str === undefined) {
        return "";
    }
    return str
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
