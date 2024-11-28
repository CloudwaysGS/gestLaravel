@include('header')
<style>
    .breadcomb-report {
        display: inline-block;
        margin-right: 10px; /* Espace entre les boutons */
    }

    #pagination-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    #pagination-controls button {
        background-color: #007bff;
        color: #fff;
        border: none;
        padding: 10px 15px;
        margin: 0 5px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #pagination-controls button:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }

    #pagination-controls button:hover:not(:disabled) {
        background-color: #0056b3;
    }

    #pagination-controls #page-number {
        margin: 0 10px;
        font-weight: bold;
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
                                <th>Dépot</th>
                                <th>Date</th>
                                <th>Etat</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="table-body">
                            @foreach ($dette as $dette)
                                <tr class="data-row">
                                    <td>{{ $dette->nom }}</td>
                                    <td>{{ $dette->montant }}</td>
                                    <td>{{ $dette->reste }}</td>
                                    <td>{{ $dette->depot }}</td>
                                    <td>{{ $dette->created_at }}</td>
                                    <td>
                                        <button class="btn btn-xs" style="background-color: {{ $dette->etat === 'payée' ? '#00c292' : '#dc3545' }}; color: white;">
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
                                                    <li><a class="dropdown-item" href="{{ route('dette.modifier', $dette->id) }}">modifier</a></li>
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
                            <button style="background-color: #00c292; color: white;" id="prev" onclick="changePage(-1)">Précédent</button>
                            <span id="page-number">Page 1</span>
                            <button style="background-color: #00c292; color: white;" id="next" onclick="changePage(1)">Suivant</button>
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
        const prevButton = document.getElementById('prev');
        const nextButton = document.getElementById('next');
        const pageNumberDisplay = document.getElementById('page-number');

        let currentPage = 1; // Page actuelle
        const pageSize = 5; // Nombre de résultats par page
        let totalPages = 1; // Total des pages (sera mis à jour)

        // Fonction pour charger les données avec pagination
        function loadData(query = '', page = 1) {
            fetch(`/dette/searchAjax?query=${query}&page=${page}&size=${pageSize}`)
                .then((response) => response.json())
                .then((data) => {
                    // Mettre à jour les données
                    const { items, total } = data;
                    tableBody.innerHTML = '';

                    if (items.length > 0) {
                        items.forEach((item) => {
                            const row = document.createElement('tr');
                            row.className = 'data-row';

                            row.innerHTML = `
                            <td>${item.nom}</td>
                            <td>${item.montant}</td>
                            <td>${item.reste}</td>
                            <td>${item.depot}</td>
                            <td>${new Date(item.created_at).toISOString().split('T')[0]}</td>

                            <td>
                                <button class="btn btn-xs ${
                                item.etat === 'payée'
                                    ? 'btn-success notika-btn-success'
                                    : 'btn-danger notika-btn-danger'
                            }">${item.etat}</button>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="notika-icon notika-menu"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" href="/dette/${item.id}/paiement">Paiement</a></li>
                                            <li><a class="dropdown-item" href="/dette/${item.id/modifier}">Modifier</a></li>
                                            <li><a class="dropdown-item" href="/dette/delete/${item.id}">Supprimer</a></li>
                                            <li><a class="dropdown-item" href="/dette/detail/${item.id}">Détail</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        `;
                            tableBody.appendChild(row);
                        });

                        // Mettre à jour le nombre total de pages
                        totalPages = Math.ceil(total / pageSize);

                        // Mettre à jour l'affichage du numéro de page
                        pageNumberDisplay.textContent = `Page ${currentPage} sur ${totalPages}`;

                        // Désactiver les boutons si nécessaire
                        prevButton.disabled = currentPage === 1;
                        nextButton.disabled = currentPage === totalPages;
                    } else {
                        // Aucun résultat trouvé
                        const noResultRow = document.createElement('tr');
                        noResultRow.innerHTML = `<td colspan="5" class="text-center">Aucun résultat trouvé</td>`;
                        tableBody.appendChild(noResultRow);

                        pageNumberDisplay.textContent = 'Page 1 sur 1';
                        prevButton.disabled = true;
                        nextButton.disabled = true;
                    }
                })
                .catch((error) => {
                    console.error('Erreur lors du chargement des données :', error);
                });
        }

        // Écouter les frappes dans l'input de recherche
        searchInput.addEventListener('keyup', function () {
            const query = searchInput.value.trim();

            if (query.length >= 3 || query.length === 0) {
                currentPage = 1; // Réinitialiser à la première page lors d'une nouvelle recherche
                loadData(query, currentPage);
            }
        });

        // Gestion des boutons de pagination
        prevButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadData(searchInput.value.trim(), currentPage);
            }
        });

        nextButton.addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                loadData(searchInput.value.trim(), currentPage);
            }
        });

        // Charger les données initiales
        loadData();
    });
</script>
<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('dropdown-item') && e.target.textContent.trim() === 'Supprimer') {
            e.preventDefault(); // Empêche la redirection immédiate
            const url = e.target.getAttribute('href');
            if (confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                window.location.href = url; // Redirige vers l'URL de suppression
            }
        }
    });

</script>

@notifyJs
