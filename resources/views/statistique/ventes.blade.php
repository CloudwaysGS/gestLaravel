@include('header')

<div class="container mt-5">
    <h2 class="text-center mb-4 font-weight-bold text-primary">Tableau des Ventes Totales</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover shadow-lg rounded-lg overflow-hidden">
            <thead class="thead-light">
            <tr>
                <th class="text-center">Date</th>
                <th class="text-center">Total des Ventes</th>
                <th class="text-center">Vente Maximale</th>
                <th class="text-center">Vente Minimale</th>
                <th class="text-center">Produit le Plus Vendu</th>
            </tr>
            </thead>
            <tbody>
            @foreach($salesData as $key => $data)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                    <td class="text-center text-success font-weight-bold">{{ number_format($data['total'], 2) }} FCFA</td>
                    <td class="text-center text-success font-weight-bold">{{ number_format($data['max'], 2) }} FCFA</td>
                    <td class="text-center text-danger font-weight-bold">{{ number_format($data['min'], 2) }} FCFA</td>
                    <td class="text-center">
                        @if($data['topProduct'])
                                <?php
                                // Récupérer le nom du produit, en supposant que vous ayez une table Product et une relation
                                $topProductName = \App\Models\Product::find($data['topProduct']) ? \App\Models\Product::find($data['topProduct'])->name : 'Non disponible';
                                ?>
                            {{ $topProductName }}
                        @else
                            Aucun produit
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

@include('footer')
