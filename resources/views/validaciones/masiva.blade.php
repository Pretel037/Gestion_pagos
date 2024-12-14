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

</head>
<body>
<div class="container">
    <h2>Vouchers para Validar</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Operación</th>
                <th>Monto</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vouchers as $voucher)
                <tr>
                    <td>{{ $voucher->operacion }}</td>
                    <td>{{ $voucher->monto }}</td>
                    <td>{{ $voucher->fecha }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-success" id="validarMasivo">Validar Todos</button>
</div>
</body>
<script>
    document.getElementById('validarMasivo').addEventListener('click', function() {
        fetch('{{ route("vouchers.validar.masivo") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });
</script>
@endsection
