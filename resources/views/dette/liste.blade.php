@include('header')
<style>
    .breadcomb-report {
        display: inline-block;
        margin-right: 10px; /* Espace entre les boutons */
    }
</style>
@notifyCss

<x-notify::notify />
<div class="breadcomb-area">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="breadcomb-list">
                    @if(session('success'))
                        <div style="color: green;">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                            <div class="breadcomb-wp">
                                <div class="breadcomb-icon">
                                    <i class="notika-icon notika-windows"></i>
                                </div>
                                <div class="breadcomb-ctn">
                                    <h2>Tableau de données sorties</h2>
                                    <p>Bienvenue sur le <span class="bread-ntd">modèle d'administration</span> Coulibaly</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-3" style="display: flex; align-items: center;">
                            <div class="breadcomb-report">
                                <button data-toggle="tooltip" data-placement="left" title="Télécharger le rapport" class="btn"><i class="notika-icon notika-sent"></i></button>
                            </div>
                            <div class="breadcomb-report">
                                <a href="{{ url('/dette/ajout') }}"><button data-toggle="tooltip" data-placement="left" class="btn">Ajouter une dette</button></a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="data-table-area">
    <div class="container">

        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                <div class="data-table-list">
                    <form method="GET" action="{{ url('/dette') }}">
                    @include('search')
                    </form>

                    <div class="table-responsive">
                        <table id="data-table-basic" class="table table-striped">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Montant</th>
                                <th>Reste</th>
                                <th>Etat</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="table-body">
                            @foreach ($dette as $dette)
                                <tr class="data-row">
                                    <td>{{ $dette->client->nom }}</td>
                                    <td>{{ $dette->montant }}</td>
                                    <td>{{ $dette->reste }}</td>
                                    <td>
                                        <button class="btn btn-xs {{ $dette->etat === 'payée' ? 'btn-success notika-btn-success' : 'btn-danger notika-btn-danger' }}">
                                            {{$dette->etat}}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="notika-icon notika-menu"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li><a class="dropdown-item" href="{{ route('paiement.ajout', $dette->id) }}">Paiement</a></li>
                                                    <li><a class="dropdown-item" href="{{url('/dette/delete', $dette->id)}}">Supprimer</a></li>
                                                    <li><a class="dropdown-item" href="{{url('/dette/detail', $dette->id)}}">Détail</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <!-- Pagination Controls -->
                        <div id="pagination-controls">
                            <button id="prev" onclick="changePage(-1)">Précédent</button>
                            <span id="page-number">Page 1</span>
                            <button id="next" onclick="changePage(1)">Suivant</button>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@include('footer')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search-input');
        const tableBody = document.getElementById('table-body');

        searchInput.addEventListener('keyup', function () {
            const query = searchInput.value;

            if (query.length > 2) { // Effectuer une recherche après 3 caractères
                fetch(`/dette/search?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        updateTable(data);
                    })
                    .catch(error => console.error('Erreur:', error));
            } else {
                resetTable(); // Réinitialiser les données si la recherche est vide
            }
        });

        function updateTable(data) {
            tableBody.innerHTML = ''; // Vider le contenu du tableau

            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5">Aucun résultat trouvé.</td></tr>';
                return;
            }

            data.forEach(item => {
                const row = `
                <tr>
                    <td>${item.client.nom}</td>
                    <td>${item.montant}</td>
                    <td>${item.reste}</td>
                    <td>
                        <button class="btn btn-xs ${item.etat === 'payée' ? 'btn-success notika-btn-success' : 'btn-danger notika-btn-danger'}">
                            ${item.etat}
                        </button>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="dropdown">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="notika-icon notika-menu"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                    <li><a class="dropdown-item" href="/dette/${item.id}/paiement">Paiement</a></li>
                                    <li><a class="dropdown-item" href="/dette/delete/${item.id}">Supprimer</a></li>
                                    <li><a class="dropdown-item" href="/dette/detail/${item.id}">Détail</a></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });
        }

        function resetTable() {
            // Recharger les données originales en cas de champ vide
            fetch(`/dette/search?query=`)
                .then(response => response.json())
                .then(data => {
                    updateTable(data);
                });
        }
    });

</script>

<script>
    let currentPage = 1;
    let rowsPerPage = 10;  // Set how many rows you want per page

    function paginateTable() {
        let rows = document.querySelectorAll('.data-row');  // Select all rows
        let totalRows = rows.length;
        let totalPages = Math.ceil(totalRows / rowsPerPage);  // Calculate total pages

        // Hide all rows initially
        rows.forEach((row, index) => {
            row.style.display = 'none';
        });

        // Show only rows that belong to the current page
        let startRow = (currentPage - 1) * rowsPerPage;
        let endRow = startRow + rowsPerPage;
        for (let i = startRow; i < endRow && i < totalRows; i++) {
            rows[i].style.display = '';
        }

        // Update page number text
        document.getElementById('page-number').textContent = `Page ${currentPage}`;

        // Disable/Enable previous and next buttons
        document.getElementById('prev').disabled = currentPage === 1;
        document.getElementById('next').disabled = currentPage === totalPages;
    }

    function changePage(direction) {
        currentPage += direction;  // Increment or decrement the page number
        paginateTable();  // Update the table
    }

    // Initialize pagination on page load
    window.onload = function() {
        paginateTable();
    };

</script>
@notifyJs
