<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Método de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/voucher.css') }}" rel="stylesheet">

</head>
<body>
    <div class="container-fluid">
        <h2 class="custom-title">Seleccione el tipo de voucher</h2>

        <div class="payment-options-container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card payment-option" onclick="selectPayment('bcp')">
                        <img src="https://play-lh.googleusercontent.com/VmEDA548jPYQVBNrWYb1ZNqAr-opQbRBrxIjKBHmS9kVX4tD1hh6LkzVzxSR0TXhiK0" alt="BCP Logo" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">Pagalo Pe</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card payment-option" onclick="selectPayment('yape')">
                        <img src="https://yt3.googleusercontent.com/l048nvZUXxmhjaDjxdJntZWSj03oOAK0ETKCQZup-Ea-aM_h8M94Jz87cw8JiwCHSEbv8llH=s900-c-k-c0x00ffffff-no-rj" alt="Yape Logo" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">Yape</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card payment-option" onclick="selectPayment('plin')">
                        <img src="https://yt3.googleusercontent.com/TaU2FLGf17S_L0OREVzdmLa2fS52bC9d0tCHCTZEz0cMyaQg2rLmBDn6Pqxbh268C_qbwuWWEw=s900-c-k-c0x00ffffff-no-rj" alt="Plin Logo" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">Plin</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="voucher-section">
            <h3 class="section-title">Ejemplo de Voucher</h3>
            <img id="exampleImage" class="example-image" src="" alt="Imagen de Ejemplo" style="display: none;">

            <h3 class="section-title">Subir Voucher</h3>
            <div id="voucherForm" style="display: none;" class="upload-form">
                <form id="uploadForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="voucher_image" class="form-label">Subir imagen del voucher:</label>
                        <input type="file" class="form-control" name="voucher_image" id="voucher_image" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn-process">Procesar Voucher</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('inicio') }}" class="btn-back">Volver a la Página Inicial</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // El código JavaScript se mantiene igual
        function selectPayment(method) {
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.payment-option[onclick="selectPayment('${method}')"]`).classList.add('selected');

            const form = document.getElementById('uploadForm');
            const exampleImage = document.getElementById('exampleImage');

            switch(method) {
                case 'bcp':
                    form.action = "{{ route('voucher.process') }}";
                    exampleImage.src ="https://scontent.fchm2-1.fna.fbcdn.net/v/t1.15752-9/462579396_1056054792671671_6105832876578447105_n.jpg?_nc_cat=110&ccb=1-7&_nc_sid=9f807c&_nc_eui2=AeGBQJx4KLd_c_cTNnhADzBNQvaeSItm95BC9p5Ii2b3kHTyVPSsiE9u3HYI6JyRGGd_CpHp4o6JFLBdZKSYYkUP&_nc_ohc=fJ6G-cSqmXsQ7kNvgER_Hgy&_nc_zt=23&_nc_ht=scontent.fchm2-1.fna&_nc_gid=AlL2Ud1KmSHNZFl5wdjL1RU&oh=03_Q7cD1QG-X8BzQVHthD7gcP4HhWJOofHH8oUTaOd3mgwYucEMxQ&oe=6747DCF8";
                    break;
                case 'yape':
                    form.action = "{{ route('voucher.processyape') }}";
                    exampleImage.src = "https://scontent.fchm2-1.fna.fbcdn.net/v/t1.15752-9/462575758_581365237653106_2692232694394399405_n.jpg?_nc_cat=108&ccb=1-7&_nc_sid=9f807c&_nc_eui2=AeHY5gt-w5pQ8rXlnZOlpGM5eCT8pldnnLN4JPymV2ecswHo-A8V_38w_tasYgFDY63qiGIIz_8sCVDgT0_kDn2B&_nc_ohc=GAFpRgWryKAQ7kNvgESmVih&_nc_zt=23&_nc_ht=scontent.fchm2-1.fna&_nc_gid=AyYsjDnEtL_AtmuLCI1ycU1&oh=03_Q7cD1QHUr4RYERlF561GRS3Ux0SJ6DujRJLRg0t61wkyLSzkVA&oe=6747E574";
                    break;
                case 'plin':
                    form.action = "{{ route('voucher.processplin') }}";
                    exampleImage.src = "https://scontent.fchm2-1.fna.fbcdn.net/v/t1.15752-9/462547194_531111093019094_4312666891985038766_n.jpg?_nc_cat=103&ccb=1-7&_nc_sid=9f807c&_nc_eui2=AeFlJGfkxDKHLNus4PEMFh3lOOUVj4VLx3E45RWPhUvHcf8Gw7H9d39ULtsS0XMZEPeDxg9BcBytzECWQgYmyTGR&_nc_ohc=nUxekhrprdUQ7kNvgGrYrOl&_nc_zt=23&_nc_ht=scontent.fchm2-1.fna&_nc_gid=AqzeH6J2hbWnN8HoK3y6YBJ&oh=03_Q7cD1QFg0WNq4D6ZZaW7_xZ3rgiirKJYyO9LJYTWAF_HnA5d4g&oe=6747CA6E";
                    break;
            }

            exampleImage.style.width = '300px';
            exampleImage.style.display = 'block';
            document.getElementById('voucherForm').style.display = 'block';
            document.getElementById('voucherForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}",
               
            });
        @endif
    });
    </script>


</body>
</html>