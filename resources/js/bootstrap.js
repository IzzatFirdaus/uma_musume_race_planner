/**
 * This file bootstraps the JS application by configuring libraries like Axios.
 */

import axios from "axios";
window.axios = axios;

// Set default headers for Axios requests
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * We'll read the CSRF token from the meta tag and set it as a default header
 * for all Axios requests. This is a secure way to handle CSRF tokens with SPAs.
 */
const csrfToken = document.head.querySelector('meta[name="csrf-token"]');

if (csrfToken) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = csrfToken.content;
} else {
    console.error(
        "CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token",
    );
}
