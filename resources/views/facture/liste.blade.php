@include('header')
@notifyCss

<x-notify::notify />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<!-- Breadcomb area Start-->

<!-- Breadcomb area End-->
<!-- Inbox area Start-->

{{--<style>
    .table-responsive {
        max-height: 150px; /* Hauteur réduite par défaut */
        overflow: hidden;  /* Cache le contenu dépassant la hauteur */
        transition: max-height 0.5s ease; /* Transition fluide */
    }

    .table-responsive:hover {
        max-height: 100%; /* Affiche tout le contenu au survol */
    }
</style>--}}
<div class="inbox-area">

    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-3 col-xs-12">
                <div class="inbox-left-sd">

                        <form method="post" action="{{url('/facture/ajout')}}">
                            @csrf

                            <div class="inbox-status">
                                <ul class="inbox-st-nav">
                                    <li>
                                        <input type="hidden" name="client_id" value=""> <!-- Champ caché pour forcer l'envoi -->
                                        <select id="client-choices" name="client_id" class="form-control input-sm">
                                            <option value="" disabled selected>Sélectionner un client</option> <!-- Option par défaut vide -->
                                            @foreach($clients as $client)
                                                <option value="{{ $client['id'] }}" {{ old('client_id') == $client['id'] ? 'selected' : '' }}>
                                                    {{ $client['nom'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </li>
                                    <li>
                                        <select id="produit-choices" name="nom" class="form-control input-sm">
                                            @foreach($produits as $produit)
                                                <option value="{{ $produit['id'] }}" {{ old('nom') == $produit['id'] ? 'selected' : '' }}>
                                                    {{ $produit['nom'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('nom')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </li>
                                    <li>
                                        <div class="nk-int-st">
                                            <input type="text" name="quantite" value="{{ old('quantite') }}" class="form-control input-sm" placeholder="Entrer la quantité">
                                        </div>
                                        @error('quantite')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </li>

                                </ul>
                            </div>

                            <hr>
                            <div class="compose-ml">
                                <button class="btn" type="submit">Ajouter</button>
                            </div>
                        </form>


                </div>
            </div>
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 custom-width">
                <div class="inbox-text-list sm-res-mg-t-30">

                    <div class="inbox-btn-st-ls btn-toolbar">
                        <div class="btn-group ib-btn-gp active-hook nk-email-inbox">
                            <a href="{{url('facturotheque/create')}}" class="btn btn-default btn-sm" title="Télécharger le rapport">Sauvegarder</a>
                            <a href="{{url('/facture/pdf')}}" class="btn btn-default btn-sm">Extraire</a>
                            <button class="btn btn-default btn-sm"><i class="notika-icon notika-checked"></i></button>
                            <button class="btn btn-default btn-sm"><i class="notika-icon notika-promos"></i></button>
                        </div>
                        <div class="btn-group ib-btn-gp active-hook nk-act nk-email-inbox">
                            <form action="{{ route('factures.deleteAll') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer toutes les factures ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-default btn-sm"><i class="notika-icon notika-trash"></i></button>
                            </form>
                        </div>
                        <div class="btn-group ib-btn-gp active-hook nk-act nk-email-inbox">
                            <button class="btn btn-default btn-sm">Total: {{ number_format($totalMontants, 2) }} FCFA</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-inbox">
                            <thead>
                            <tr>
                                <th>libelle</th>
                                <th>qte</th>
                                <th>prixUnit</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($facture as $facture)
                                <tr>
                                    <td>{{ $facture->nom }}</td>
                                    <td>{{ $facture->quantite }}</td>
                                    <td>{{ number_format($facture->prix, 2) }} FCFA</td>
                                    <td>{{ number_format($facture->montant, 2) }} FCFA</td>
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="notika-icon notika-menu"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                    <li><a class="dropdown-item" href="{{ route('facture.modifier', $facture->id) }}">modifier</a></li>
                                                    <li><a class="dropdown-item" href="{{url('/facture/delete', $facture->id)}}">Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Aucune facture trouvée.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Inbox area End-->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const element = document.getElementById('produit-choices');
        const choices = new Choices(element, {
            searchEnabled: true, // Activer la recherche
            placeholderValue: 'Sélectionner un produit', // Placeholder
            noResultsText: 'Aucun produit trouvé', // Message si aucun résultat
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const element = document.getElementById('client-choices');
        const choices = new Choices(element, {
            searchEnabled: true, // Activer la recherche
            placeholderValue: 'Sélectionner un client', // Placeholder
            noResultsText: 'Aucun client trouvé', // Message si aucun résultat
        });
    });
</script>
<script src="notika/js/vendor/jquery-1.12.4.min.js"></script>
<!-- bootstrap JS
    ============================================ -->
<script src="notika/js/bootstrap.min.js"></script>
<!-- wow JS
    ============================================ -->
<script src="notika/js/wow.min.js"></script>
<!-- price-slider JS
    ============================================ -->
<script src="notika/js/jquery-price-slider.js"></script>
<!-- owl.carousel JS
    ============================================ -->
<script src="notika/js/owl.carousel.min.js"></script>
<!-- scrollUp JS
    ============================================ -->
<script src="notika/js/jquery.scrollUp.min.js"></script>
<!-- meanmenu JS
    ============================================ -->
<script src="notika/js/meanmenu/jquery.meanmenu.js"></script>
<!-- counterup JS
    ============================================ -->
<script src="notika/js/counterup/jquery.counterup.min.js"></script>
<script src="notika/js/counterup/waypoints.min.js"></script>
<script src="notika/js/counterup/counterup-active.js"></script>
<!-- mCustomScrollbar JS
    ============================================ -->
<script src="notika/js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<!-- jvectormap JS
    ============================================ -->
<script src="notika/js/jvectormap/jquery-jvectormap-2.0.2.min.js"></script>
<script src="notika/js/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="notika/js/jvectormap/jvectormap-active.js"></script>
<!-- sparkline JS
    ============================================ -->
<script src="notika/js/sparkline/jquery.sparkline.min.js"></script>
<script src="notika/js/sparkline/sparkline-active.js"></script>
<!-- sparkline JS
    ============================================ -->
<script src="notika/js/flot/jquery.flot.js"></script>
<script src="notika/js/flot/jquery.flot.resize.js"></script>
<script src="notika/js/flot/curvedLines.js"></script>
<script src="notika/js/flot/flot-active.js"></script>
<!-- knob JS
    ============================================ -->
<script src="notika/js/knob/jquery.knob.js"></script>
<script src="notika/js/knob/jquery.appear.js"></script>
<script src="notika/js/knob/knob-active.js"></script>
<!--  wave JS
    ============================================ -->
<script src="notika/js/wave/waves.min.js"></script>
<script src="notika/js/wave/wave-active.js"></script>
<!--  todo JS
    ============================================ -->
<script src="notika/js/todo/jquery.todo.js"></script>
<!-- plugins JS
    ============================================ -->
<script src="notika/js/plugins.js"></script>
<!--  Chat JS
    ============================================ -->
<script src="notika/js/chat/moment.min.js"></script>
<script src="notika/js/chat/jquery.chat.js"></script>
<!-- main JS
    ============================================ -->
<script src="notika/js/main.js"></script>
<!-- tawk chat JS
    ============================================ -->
{{--<script src="notika/js/tawk-chat.js"></script>--}}

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script src="js/vendor/jquery-1.12.4.min.js"></script>
<!-- bootstrap JS
    ============================================ -->
<script src="js/bootstrap.min.js"></script>
<!-- wow JS
    ============================================ -->
<script src="js/wow.min.js"></script>
<!-- price-slider JS
    ============================================ -->
<script src="js/jquery-price-slider.js"></script>
<!-- owl.carousel JS
    ============================================ -->
<script src="js/owl.carousel.min.js"></script>
<!-- scrollUp JS
    ============================================ -->
<script src="js/jquery.scrollUp.min.js"></script>
<!-- meanmenu JS
    ============================================ -->
<script src="js/meanmenu/jquery.meanmenu.js"></script>
<!-- counterup JS
    ============================================ -->
<script src="js/counterup/jquery.counterup.min.js"></script>
<script src="js/counterup/waypoints.min.js"></script>
<script src="js/counterup/counterup-active.js"></script>
<!-- mCustomScrollbar JS
    ============================================ -->
<script src="js/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<!-- sparkline JS
    ============================================ -->
<script src="js/sparkline/jquery.sparkline.min.js"></script>
<script src="js/sparkline/sparkline-active.js"></script>
<!-- flot JS
    ============================================ -->
<script src="js/flot/jquery.flot.js"></script>
<script src="js/flot/jquery.flot.resize.js"></script>
<script src="js/flot/flot-active.js"></script>
<!-- knob JS
    ============================================ -->
<script src="js/knob/jquery.knob.js"></script>
<script src="js/knob/jquery.appear.js"></script>
<script src="js/knob/knob-active.js"></script>
<!--  Chat JS
    ============================================ -->
<script src="js/chat/jquery.chat.js"></script>
<!--  todo JS
    ============================================ -->
<script src="js/todo/jquery.todo.js"></script>
<!--  wave JS
    ============================================ -->
<script src="js/wave/waves.min.js"></script>
<script src="js/wave/wave-active.js"></script>
<!-- plugins JS
    ============================================ -->
<script src="js/plugins.js"></script>
<!-- Data Table JS
    ============================================ -->
<script src="js/data-table/jquery.dataTables.min.js"></script>
<script src="js/data-table/data-table-act.js"></script>
<!-- main JS
    ============================================ -->
<script src="js/main.js"></script>
<!-- tawk chat JS
    ============================================ -->
<script src="js/tawk-chat.js"></script>

@notifyJs
</html>
</body>
