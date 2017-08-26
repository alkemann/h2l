function api(url, opts) {
    const protocol = location.protocol;
    const slashes = protocol.concat("//");
    const base = slashes.concat(window.location.host)
    const host = base.concat("/");
    const full = host.concat(url)
    return fetch(full, Object.assign({
        // credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Origin': host
        }
    }, opts)).then(res => {
        const { headers } = res;
        if (res.status !== 200) {
            const message = res.headers.get('x-error-message') || res.statusText;
            const error = new Error(message);
            error.statusCode = res.status;
            error.statusText = res.statusText

            // TODO: Handle this in each component
            alert(`${error.message}. \n\nError code: ${error.statusCode}\nError message: ${error.statusText}`);;

            throw error;
        }

        return res.json().then(response => {
            return { response, headers };
        });
    });
}

export default api;
