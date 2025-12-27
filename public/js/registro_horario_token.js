document.addEventListener("DOMContentLoaded", function() {
    const tokenInput = document.getElementById("remember_token_code");
    const userSelect = document.getElementById("userUuid");

    function clearSelectionAndOptions() {
        userSelect.value = '';
        while (userSelect.firstChild) userSelect.removeChild(userSelect.firstChild);
    }

    tokenInput.addEventListener("input", function() {
        const code = tokenInput.value.trim();
        clearSelectionAndOptions(); // Reset al cambiar input
        if (code.length < 8) {
            return;
        }
        fetch(`/api/users/by-remember-token?code=${encodeURIComponent(code)}`)
            .then(r=>r.json())
            .then(users => {
                if (Array.isArray(users) && users.length) {
                    const optionDefault = document.createElement('option');
                    optionDefault.value = '';
                    optionDefault.textContent = 'Selecciona un usuario';
                    userSelect.appendChild(optionDefault);
                    users.forEach(user => {
                        const opt = document.createElement('option');
                        opt.value = user.uuid;
                        opt.textContent = `${user.name} (${user.email})`;
                        userSelect.appendChild(opt);
                    });
                }
            });
    });
});