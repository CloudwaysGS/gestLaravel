<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .table th, .table td {
        vertical-align: middle;
        text-align: center;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .badge-info {
        background-color: #17a2b8;
        color: #fff;
    }
    .btn-sm {
        padding: 0.2rem 0.5rem;
    }
</style>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="container mt-5">
    <a href="{{url('accueille')}}" class="text-left mb-4 font-weight-bold text-primary">Retour</a>
    <a href="{{url('/expenses/create')}}" class="float-end mb-4 font-weight-bold text-primary"><h5>Ajouter une dépense</h5></a>
    <h2 class="text-center mb-4 font-weight-bold text-primary">Tableau des Dépenses</h2>
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
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover shadow-lg rounded-lg">
            <thead class="thead-dark">
            <tr>
                <th class="text-center">#</th>
                <th class="text-center">Date</th>
                <th class="text-center">Montant</th>
                <th class="text-center">Catégorie</th>
                <th class="text-center">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($expenses as $index => $expense)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($expense->date)->format('d/m/Y') }}</td>
                    <td class="text-center text-success font-weight-bold">{{ number_format($expense->amount, 2) }} FCFA</td>
                    <td class="text-center"><span class="badge badge-info">{{ $expense->category }}</span></td>
                    <td class="text-center">
                        <a href="{{ route('expenses.edit', $expense->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="d-inline delete-expense-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-sm delete-expense-btn">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Liens de pagination -->
        <div class="d-flex justify-content-center">
            {{ $expenses->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteButtons = document.querySelectorAll('.delete-expense-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                // Afficher une alerte de confirmation
                const confirmed = confirm("Êtes-vous sûr de vouloir supprimer cette dépense ?");
                if (confirmed) {
                    // Soumettre le formulaire associé
                    button.closest('form').submit();
                }
            });
        });
    });

</script>
