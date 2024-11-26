<div class="breadcomb-list">
    <form id="search-form">
        <div class="input-group">
            <input
                type="text"
                name="search"
                id="search-input"
                class="form-control"
                placeholder="Rechercher par nom"
                value="{{ request('search') }}">
        </div>
        <div id="search-results"></div> <!-- Conteneur pour afficher les résultats -->

    </form>
</div>
