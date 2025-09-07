// Import the initial bootstrap configuration (for Axios, etc.)
import "./bootstrap";

// Import SweetAlert2
import Swal from "sweetalert2";
window.Swal = Swal;

// Import and run your main application logic
import "./main.js";
// Import character-specific styles so they're bundled into app.css
import "../css/characters.css";
