<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointages</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Pointages du {{ $start_date }} au {{ $end_date }}</h1>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                @foreach ($pointages as $user)
                    @foreach (array_keys($user['dates']) as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($pointages as $user)
                <tr>
                    <td>{{ $user['user']->nom }}</td>
                    <td>{{ $user['user']->prenom }}</td>
                    @foreach ($user['dates'] as $date => $status)
                        <td>{{ $status }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
