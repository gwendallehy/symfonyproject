document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-city-form');
    const cityForm = document.getElementById('city-embedded-form');
    const container = document.getElementById('city-form-container');

    if (toggleBtn && container && cityForm) {
        toggleBtn.addEventListener('click', function () {
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
        });

        cityForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const data = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => {
                    if (!response.ok) return response.json().then(err => Promise.reject(err));
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const select = document.querySelector('select[name$="[city]"]');
                        const option = document.createElement('option');
                        option.value = data.city.id;
                        option.textContent = data.city.name;
                        option.selected = true;
                        select.appendChild(option);

                        container.style.display = 'none';
                        form.reset();
                    } else {
                        alert('Erreur : ' + (data.errors || []).join(', '));
                    }
                })
                .catch(err => {
                    alert('Erreur lors de l\'ajout de la ville');
                    console.error(err);
                });
        });
    }
});
