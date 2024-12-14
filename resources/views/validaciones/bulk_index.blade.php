

@extends('index')
@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Vouchers</title>

    <!-- Estilos de Bootstrap y Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Estilos de SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
        }
        h1 {
        text-align: center;
        color: #004581;
        margin: 30px 0;
    }
    .row {
        justify-content: center; /* Centra el contenido horizontalmente */
    }
    .col-md-6 {
        flex: 0 0 80%; /* Haz que la columna ocupe el 80% del ancho del contenedor */
        max-width: 80%;
    }
    .container-fluid {
        max-width: 1200px; /* Ajusta el ancho según necesites */
        margin: auto;
        padding: 20px;
        position: relative;
    }
        .table-responsive {
            max-height: 800px;
            overflow-y: auto;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .table {
            background-color: white;
            border-radius: 10px;
        }
        .table th {
            background-color: #004581;
            color: white;
            position: sticky;
            top: 0;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .table-hover tbody tr:hover {
            background-color: #d7ecff;
        }
        .btn-validate {
            padding: 10px 20px;
            background-color: #018abd;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: block;
            margin: 20px auto;
        }
        .btn-validate:hover {
            background-color: #016a95;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <h1>Validación de Vouchers</h1>
        <div class="row">
            <div class="col-md-6">
            <form id="bulk-validation-form">
                <p>Pagos SIGGA</p>
                <div class="table-responsive">


                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>N° Operación</th>
                                <th>Monto</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vouchers as $voucher)
                            <tr>
                                <td>
                                    <input type="checkbox" name="voucher_ids[]" value="{{ $voucher->id }}">
                                </td>
                                <td>{{ $voucher->operacion }}</td>
                                <td>{{ number_format($voucher->monto, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($voucher->fecha_pago)->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn-validate">Validar Todos</button>
            </form>
        </div>
    </div>
    </div>

@section('scripts')
<script>
    document.getElementById('bulk-validation-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('{{ route("bulk.validate") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload(); // Recargar la página para ver los cambios
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Ocurrió un error al validar los vouchers.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    });
</script>
@endsection