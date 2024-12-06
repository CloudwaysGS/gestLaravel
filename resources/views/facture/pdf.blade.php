<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture</title>
    <style>

        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            background-color: #ffffff;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h3 {
            text-align: center;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
        }

        .info-section p {
            margin: 5px 0;
            color: #555;
            font-size: 13px;
        }

        .info-section .client, .info-section .vendeur {
            width: 48%;
        }

        .facture-info {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            color: #777;
        }

        .facture-info p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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

        .total {
            text-align: right;
            font-size: 15px;
            font-weight: bold;
            margin-top: 20px;
            font-size: 18px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #888;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Facture</h3>

    <!-- Informations du client et du vendeur -->
    <div class="info-section">
        <div class="client">
            <p><strong>Client :</strong> {{ $client->nomClient }}</p>
        </div>

    </div>

    <!-- Informations de la facture -->
    <div class="facture-info">
        <p><strong>Vendeur :</strong> {{ $vendeur->name }}</p>
        <p><strong>Adresse :</strong> Rue Daloa / Kaolack</p>
        <p><strong>Téléphone :</strong> 77 449 15 29</p>
        <p><strong>NINEA :</strong> 0848942 - RC : 10028</p>
        <p><strong>Référence :</strong> {{ $reference }}</p>
        <p><strong>Date de la facture :</strong> {{ $date }}</p>
    </div>

    <!-- Liste des factures -->
    <table>
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
                <td colspan="4" class="text-center">Aucune facture trouvée.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <p class="total">Total Montant : <strong>{{ number_format($totalMontants, 2) }} FCFA</strong></p>

    <div class="footer">
        <p>Merci pour votre confiance !</p>
        <p>Si vous avez des questions, contactez-nous à support@example.com</p>
    </div>
</div>

</body>
</html>
