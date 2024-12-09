<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>
        .container {
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }


        /* Titre principal */
        h3 {
            text-align: center;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        /* Section informations client et vendeur */
        .info-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .info-section p {
            text-align: right;
            margin: 0;
            color: #777;
            font-size: 13px;
        }

        .info-section .client {
            text-align: left; /* Alignement à gauche */
        }

        .info-section .vendeur {
            text-align: right; /* Alignement à droite */
        }
        .info-section .client, .info-section .vendeur {
            width: 100%;
        }

        /* Section informations de la facture */
        .facture-info {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-top: -100px;
            font-size: 13px;
            color: #777;
        }

        .facture-info p {
            margin: 5px 0;
            flex: 1 1 45%;
        }

        /* Table des factures */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }

        td {
            font-size: 14px;
            color: #555;
        }

        /* Total */
        .total {
            text-align: right;
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
            margin-bottom: -10px;
        }
        .acompte {
            text-align: left;
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: -30px;
            margin-bottom: -10px;
        }
        .reste {
            text-align: left;
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
            margin-bottom: -10px;
        }

        .depot {
            text-align: left;
            font-size: 15px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
            margin-bottom: -10px;
        }

        /* Pied de page */
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #888;
        }

        .footer p {
            margin: 5px 0;
        }

        /* Styles responsifs */
        @media (max-width: 768px) {
            .info-section {
                flex-direction: column;
            }

            .info-section .client, .info-section .vendeur {
                width: 100%;
            }

            .facture-info p {
                flex: 1 1 100%;
            }

            table {
                font-size: 12px;
            }
        }

        .info {
            float: left;
            width: 48%;
            color: #777;
            font-size: 13px;
        }

        .facture {
            text-align: right;
            width: 100%;
            color: #777;
            font-size: 13px;

        }

        /* Pour s'assurer que les divs parents englobent les éléments flottants */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Facture</h3>

    <div class="clearfix">
        <div class="info">
            <div class="client">
                <p><strong>Client :</strong> {{ $client->nomClient ?? 'Non spécifié' }}</p>
                <p><strong>Référence :</strong> {{ $reference ?? 'N/A' }}</p>
                <p><strong>Date de la facture :</strong> {{ $date ?? 'Non spécifiée' }}</p>
            </div>
            <div class="vendeur">

            </div>
        </div>

        <div class="facture">
            <p><strong>Vendeur :</strong> {{ $vendeur->name ?? 'Non spécifié' }}</p>
            <p><strong>Adresse :</strong> Rue Daloa / Kaolack</p>
            <p><strong>Téléphone :</strong> 77 449 15 29</p>
            <p><strong>NINEA :</strong> 0848942 - RC : 10028</p>
        </div>
    </div>

    <!-- Liste des factures -->
    <table aria-label="Détails des articles de la facture">
        <thead>
        <tr>
            <th>Libellé</th>
            <th>Quantité</th>
            <th>Prix Unitaire</th>
            <th>Montant</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($facture as $items)
            <tr>
                <td>{{ $items->nom }}</td>
                <td>{{ $items->quantite }}</td>
                <td>{{ number_format($items->prix, 2) }} FCFA</td>
                <td>{{ number_format($items->montant, 2) }} FCFA</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center;">Aucune facture trouvée.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <!-- Total -->
    <p class="total">Total Montant : <strong>{{ number_format($totalMontants, 2) }} FCFA</strong></p>
    @if($avance > 0)
        <p class="acompte">Acompte : <strong>{{ number_format($avance, 2) }} FCFA</strong></p>
    @endif

    @if($reste > 0)
        <p class="reste">Reste : <strong>{{ number_format($reste, 2) }} FCFA</strong></p>
    @endif

    @if($depot > 0)
        <p class="depot">Dépot : <strong>{{ number_format($depot, 2) }} FCFA</strong></p>
    @endif


    <!-- Pied de page -->
    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p>Si vous avez des questions, contactez-nous à support@example.com</p>
    </div>
</div>

</body>
</html>
