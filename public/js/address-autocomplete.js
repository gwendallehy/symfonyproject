(() => {
    const input = document.getElementById('place_street');
    const cityTextInput = document.getElementById('place_cityName');
    const cityHiddenIdInput = document.getElementById('place_cityId');
    const latInput = document.getElementById('place_latitude');
    const lonInput = document.getElementById('place_longitude');

    if (!input || !cityTextInput || !cityHiddenIdInput || !latInput || !lonInput) {
        return;
    }

    const dropdown = document.createElement('ul');
    dropdown.classList.add('address-dropdown');
    input.parentNode.style.position = 'relative';
    input.parentNode.appendChild(dropdown);

    let debounceTimeout;
    input.addEventListener('input', function () {
        clearTimeout(debounceTimeout);

        debounceTimeout = setTimeout(() => {
            const query = input.value.trim();
            if (query.length < 3) {
                dropdown.innerHTML = '';
                return;
            }

            fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=5`)
                .then(response => response.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    data.features.forEach(feature => {
                        const li = document.createElement('li');
                        li.textContent = feature.properties.label;

                        li.addEventListener('mousedown', () => {
                            input.value = feature.properties.name;
                            latInput.value = feature.geometry.coordinates[1];
                            lonInput.value = feature.geometry.coordinates[0];

                            const cityName = feature.properties.city;
                            const postalCode = feature.properties.postcode;

                            fetch('/api/city', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    name: cityName,
                                    postalCode: postalCode
                                })
                            })
                                .then(res => res.json())
                                .then(city => {
                                    cityTextInput.value = `${city.name} (${city.postalCode})`;
                                    cityHiddenIdInput.value = city.id;
                                });

                            dropdown.innerHTML = '';
                        });

                        dropdown.appendChild(li);
                    });
                });
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== input) {
            dropdown.innerHTML = '';
        }
    });
})();
