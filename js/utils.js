// Unified JSON fetch with error handling
export async function fetchJSON(url, data = null, method = 'GET')
{
    const options = {
        method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    return response.json();
}

// Simplified event delegation
export function on(selector, event, handler, root = document)
{
    root.addEventListener(event, (e) => {
        const target = e.target.closest(selector);
        if (target) {
            handler(e, target);
        }
    });
}

// Format ISO or timestamp into readable string
export function formatDateTime(input)
{
    const dt = new Date(input);
    return dt.toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}
