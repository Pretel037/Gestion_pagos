@extends('index')
@section('content')
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --background-color: #f1f5f9;
            --card-background: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }

        h1, h2 {
            text-align: center;
            margin-top: 20px;
        }

        h1 {
            color: #004581;
        }

        h2 {
            color: #018abd;
        }

        .page-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        .filter-card {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-control, .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            border-color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
        }

        .table-container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8fafc;
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text-secondary);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8fafc;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
            transition: background-color 0.3s ease;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
    <h1 class="text-center mt-4">Balance de Ingresos - {{ $mes }}/{{ $año }}</h1>
    <h2 class="text-center">Número de alumnos: {{ $numeroVouchers }}</h2>
    <h2 class="text-center">Ingresos del mes: S/ {{ number_format($ingresosTotales, 2) }}</h2>

    <h3 class="text-center mt-4">Resumen de pagos por día</h3>


    <form method="GET" action="{{ route('mostrarReporte') }}" class="filter-form mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="mes">Mes:</label>
                <select id="mes" name="mes" class="form-control">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == $mes ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label for="año">Año:</label>
                <select id="año" name="año" class="form-control">
                    @for ($i = now()->year; $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $i == $año ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>
    <table class="table table-striped table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>Día</th>
                <th>Número de Vouchers</th>
                <th>Monto Total (S/)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dias as $index => $dia)
                <tr>
                    <td>{{ $dia }}</td>
                    <td>{{ $numeroVouchersPorDia[$index] }}</td>
                    <td>S/ {{ number_format($montosPorDia[$index], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Botón para descargar el PDF -->
    <div class="text-center mt-4">
        <form action="{{ route('descargarPDF') }}" method="POST" class="d-inline">
            @csrf
            <input type="hidden" name="mes" value="{{ $mes }}">
            <input type="hidden" name="año" value="{{ $año }}">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-download"></i> Descargar PDF
            </button>
        </form>
    </div>
</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection

