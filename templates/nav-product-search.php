<div class="nav-product-search position-relative mx-3 flex-grow-1" style="max-width: 460px;">
    <div class="input-group">
        <input
            type="search"
            id="navProductSearch"
            class="form-control"
            placeholder="<?= $_SESSION['Idioma'] === 'es' ? 'Buscar productos' : 'Search products' ?>"
            aria-label="<?= $_SESSION['Idioma'] === 'es' ? 'Buscar productos' : 'Search products' ?>"
            aria-autocomplete="list"
            aria-controls="navProductSearchResults"
            aria-expanded="false"
            autocomplete="off">
        <select
            id="navProductCategory"
            class="form-select"
            aria-label="<?= $_SESSION['Idioma'] === 'es' ? 'Categoría' : 'Category' ?>"
            style="max-width: 150px;">
            <option value="0"><?= $_SESSION['Idioma'] === 'es' ? 'Todas' : 'All' ?></option>
        </select>
    </div>
    <div
        id="navProductSearchResults"
        class="list-group position-absolute shadow"
        role="listbox"
        style="z-index: 1050; max-height: 320px; overflow-y: auto; display: none; width: min(460px, calc(100vw - 2rem));">
    </div>
    <div id="navProductSearchStatus" class="small text-white-50 mt-1" aria-live="polite"></div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('navProductSearch');
        const categorySelect = document.getElementById('navProductCategory');
        const resultsList = document.getElementById('navProductSearchResults');
        const status = document.getElementById('navProductSearchStatus');
        const apiUrl = <?= json_encode(URL_API, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const productUrl = <?= json_encode(rtrim(URL_BASE, '/').'/product/', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const token = <?= json_encode($jwt, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const clientId = <?= json_encode(ID_CLIENT, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const language = <?= json_encode($_SESSION['Idioma'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        let searchTimer;
        let activeSearch;
        let searchSequence = 0;

        function apiHeaders() {
            return {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-ID-CLIENT': clientId,
                'LNG': language
            };
        }

        function hideResults() {
            resultsList.replaceChildren();
            resultsList.style.display = 'none';
            searchInput.setAttribute('aria-expanded', 'false');
        }

        function setStatus(message) {
            status.textContent = message;
        }

        fetch(apiUrl + 'searchcategories', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({})
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('No se pudieron cargar las categorías (' + response.status + ').');
                }
                return response.json();
            })
            .then(function (response) {
                const categories = Array.isArray(response)
                    ? response
                    : response.status === 'success' && Array.isArray(response.data)
                        ? response.data
                        : null;
                if (!categories) {
                    throw new Error(response.message || 'La respuesta de categorías no es válida.');
                }

                categories.forEach(function (category) {
                    if (category.Id === undefined || category.Nombre === undefined) {
                        return;
                    }
                    const option = document.createElement('option');
                    option.value = category.Id;
                    option.textContent = category.Nombre;
                    categorySelect.appendChild(option);
                });
            })
            .catch(function (error) {
                console.error('Error al consultar categorías:', error);
                setStatus(<?= json_encode($_SESSION['Idioma'] === 'es' ? 'No se pudieron cargar las categorías.' : 'Categories could not be loaded.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
            });

        function searchProducts() {
            const name = searchInput.value.trim();
            const sequence = ++searchSequence;

            if (activeSearch) {
                activeSearch.abort();
            }
            hideResults();

            if (name.length < 4) {
                setStatus(name.length === 0 ? '' : <?= json_encode($_SESSION['Idioma'] === 'es' ? 'Escribe al menos 4 caracteres.' : 'Enter at least 4 characters.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
                return;
            }

            setStatus(<?= json_encode($_SESSION['Idioma'] === 'es' ? 'Buscando...' : 'Searching...', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
            activeSearch = new AbortController();

            fetch(apiUrl + 'searchproductcategories', {
                method: 'POST',
                headers: apiHeaders(),
                body: JSON.stringify({
                    IdCat: Number(categorySelect.value) || 0,
                    Nombre: name
                }),
                signal: activeSearch.signal
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('La búsqueda de productos falló (' + response.status + ').');
                    }
                    return response.json();
                })
                .then(function (response) {
                    if (sequence !== searchSequence) {
                        return;
                    }
                    const products = Array.isArray(response)
                        ? response
                        : response.status === 'success' && Array.isArray(response.data)
                            ? response.data
                            : null;
                    if (!products) {
                        throw new Error(response.message || 'La respuesta de productos no es válida.');
                    }

                    resultsList.replaceChildren();
                    products.slice(0, 10).forEach(function (product) {
                        if (product.Id === undefined || !product.Name) {
                            return;
                        }

                        const link = document.createElement('a');
                        link.className = 'list-group-item list-group-item-action';
                        link.setAttribute('role', 'option');
                        link.href = productUrl
                            + encodeURIComponent(String(product.Name).trim().replace(/\s+/g, '-'))
                            + '?Idp=' + encodeURIComponent(String(product.Id));
                        link.textContent = product.Name;
                        resultsList.appendChild(link);
                    });

                    resultsList.style.display = resultsList.childElementCount ? 'block' : 'none';
                    searchInput.setAttribute('aria-expanded', resultsList.childElementCount ? 'true' : 'false');
                    setStatus(resultsList.childElementCount
                        ? ''
                        : <?= json_encode($_SESSION['Idioma'] === 'es' ? 'No se encontraron productos.' : 'No products found.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
                })
                .catch(function (error) {
                    if (error.name === 'AbortError' || sequence !== searchSequence) {
                        return;
                    }
                    console.error('Error al buscar productos:', error);
                    hideResults();
                    setStatus(<?= json_encode($_SESSION['Idioma'] === 'es' ? 'No se pudo completar la búsqueda.' : 'The search could not be completed.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>);
                });
        }

        searchInput.addEventListener('input', function () {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(searchProducts, 1500);
        });

        categorySelect.addEventListener('change', function () {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(searchProducts, 1500);
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.nav-product-search')) {
                hideResults();
            }
        });
    });
</script>
