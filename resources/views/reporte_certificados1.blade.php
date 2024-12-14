<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Certificados PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            text-align: center;
            color: #fbfdff;
            margin-top: 0;
        }

        h1 {
            font-size: 1.8em;
            font-weight: bold;
        }

        h2 {
            font-size: 1.2em;
            color: #018abd;
            margin-top: 5px;
        }

        .header {
            background-color: #004581;
            color: #fff;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
        }

        .report-info {
            text-align: center;
            font-size: 0.9em;
            margin-top: -10px;
            color: #555;
        }

        .table-container {
            margin: 0 auto;
            padding: 0 20px;
            width: 90%;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.9em;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        .table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tfoot td {
            font-weight: bold;
            color: #004581;
            border-top: 2px solid #ddd;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Reporte de Certificados</h1>
        <h2>Desde: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} Hasta: {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h2>
    </div>

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre del Certificado</th>
                    <th>Número de Operación</th>
                    <th>Fecha de Pago</th>
                    <th>Total Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                    <tr>
                        <td>{{ $pago->nombre_certificado }}</td>
                        <td>{{ $pago->numero_operacion }}</td>
                        <td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td>
                        <td>${{ number_format($pago->total_monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total General</td>
                    <td>
                        ${{ number_format($pagos->sum('total_monto'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

</body>
</html>
