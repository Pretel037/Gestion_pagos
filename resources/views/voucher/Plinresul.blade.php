<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(45deg, #2193b0, #6dd5ed);
            padding: 20px;
            border-bottom: none;
        }
        
        .card-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 30px;
            background: white;
        }
        
        .data-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2193b0;
        }
        
        .form-label {
            font-weight: 500;
            color: #2193b0;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #2193b0;
            box-shadow: 0 0 0 0.2rem rgba(33, 147, 176, 0.25);
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #f86e7c);
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: fadeInDown 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        @if ($errors->any())
        <div class="alert alert-danger animate__animated animate__shakeX">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
        @endif

        <div class="card animate-fade-in">
            <div class="card-header">
                <h2 class="mb-0 text-white animate__animated animate__fadeInDown">
                    <i class="fas fa-receipt me-2"></i>Confirmación de Pago
                </h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 animate__animated animate__fadeInLeft">
                        <div class="data-box">
                            <p class="mb-2"><i class="fas fa-calendar-alt me-2"></i><strong>Fecha de Pago:</strong> {{ $fecha }}</p>
                            <p class="mb-0"><i class="fas fa-clock me-2"></i><strong>Hora de Pago:</strong> {{ $hora }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 animate__animated animate__fadeInRight">
                        <div class="data-box">
                            <p class="mb-2"><i class="fas fa-hashtag me-2"></i><strong>Número de Operación:</strong> {{ $operacion }}</p>
                            <p class="mb-0"><i class="fas fa-money-bill-wave me-2"></i><strong>Monto:</strong> {{ $monto }}</p>
                        </div>
                    </div>
                </div>

                <form id="voucherForm" action="{{ route('voucher.confirm') }}" method="POST" class="mt-4">
                    @csrf
                    <div class="mb-4 animate__animated animate__fadeInUp">
                        <label for="codigo_dni" class="form-label">
                            <i class="fas fa-id-card me-2"></i>Código/DNI
                        </label>
                        <input 
                            type="text" 
                            name="codigo_dni" 
                            id="codigo_dni" 
                            class="form-control" 
                            required 
                            pattern="\d{8}" 
                            inputmode="numeric" 
                            title="El código/DNI debe tener exactamente 8 dígitos numéricos."
                            placeholder="Ingrese su DNI"
                        >
                    </div>

                    <div class="mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <label for="servicio" class="form-label">
                            <i class="fas fa-book me-2"></i>Curso
                        </label>
                        <select name="servicio" id="servicio" class="form-control" required>
                            <option value="">Selecciona un curso</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->name }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" name="hora" value="{{ $hora }}">
                    <input type="hidden" name="operacion" value="{{ $operacion }}">
                    <input type="hidden" name="monto" value="{{ $monto }}">

                    <div class="d-grid gap-3 d-md-flex justify-content-md-end animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Confirmar y Guardar
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}"
                footer: '<a href="{{ url()->previous() }}">.</a>'

            });
        @endif
    });
    </script>