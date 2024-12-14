<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Models\Voucher;
use App\Models\Course;
use Carbon\Carbon;
use App\Http\Controllers\DateTime;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class YapeVoucherController extends Controller
{
    private $ocrApiKey = 'K89059208388957'; 

    public function process(Request $request)
    {
        $request->validate([
            'voucher_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    
        // Guarda la imagen
        $image = $request->file('voucher_image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/vouchers', $imageName);
    
        $fullImagePath = storage_path('app/public/vouchers/' . $imageName);
    
        if (!file_exists($fullImagePath)) {
            return "Error: Imagen no encontrada en la ruta: " . $fullImagePath;
        }
    
        try {
            $text = $this->ocrImage($fullImagePath);
        } catch (\Exception $e) {
            Log::error('Error al procesar la imagen: ' . $e->getMessage()); 
            return redirect()->back()->with('error', 'Error al procesar la imagen. Por favor, sube una imagen válida.');
        }
    
        // Extraer datos
        $fecha = $this->extractAndConvertOperationDate($text);
        $operacion = $this->operacion($text);
   
        $hora = $this->hora($text);
        $monto = $this->processMonto($this->Monto($text)); // convierte monto en float
        $courses = Course::where('precio', '=', $monto)->get();

        return view('voucher.Plinresul', compact('fecha', 'operacion', 'monto', 'hora', 'courses', 'imageName'));
    }


    
    private function ocrImage($fullImagePath)
{
    if (!file_exists($fullImagePath)) {
        throw new \Exception("Error: El archivo no existe en la ruta: " . $fullImagePath);
    }

    $client = new Client();
    $fileType = pathinfo($fullImagePath, PATHINFO_EXTENSION);
    $fileType = strtolower($fileType);

    if (!in_array($fileType, ['jpeg', 'jpg', 'png'])) {
        throw new \Exception('Tipo de archivo no soportado. Asegúrate de que el archivo sea una imagen JPEG o PNG.');
    }

    $fileResource = fopen($fullImagePath, 'r');
    if ($fileResource === false) {
        throw new \Exception('Error al abrir el archivo: ' . $fullImagePath);
    }

    Log::info('Archivo abierto correctamente: ' . $fullImagePath);

    $response = $client->post('https://api.ocr.space/parse/image', [
        'multipart' => [
            [
                'name'     => 'apikey',
                'contents' => $this->ocrApiKey,
            ],
            [
                'name'     => 'file',
                'contents' => $fileResource,
                'filename' => basename($fullImagePath),
            ],
            [
                'name'     => 'language',
                'contents' => 'spa',
            ],
            [
                'name'     => 'filetype',
                'contents' => $fileType,
            ],
        ],
    ]);



    $body = json_decode((string) $response->getBody(), true);
    Log::info('Respuesta de OCR: ', $body);

    if (isset($body['ErrorMessage']) && !empty($body['ErrorMessage'])) {
        throw new \Exception('Error en el OCR: ' . implode(', ', $body['ErrorMessage']));
    }

    if (isset($body['ParsedResults'][0]['ParsedText'])) {
        return $body['ParsedResults'][0]['ParsedText'];
    }

    throw new \Exception('Error: No se encontró texto procesado en la respuesta.');
}



private function extractAndConvertOperationDate($text)
{

    Log::info('Texto procesado por OCR:', [$text]);

   
    if (preg_match('/(\d{1,2})?\s*(de)?\s*([a-zA-Z]+)\s*de\s*(\d{4})/', $text, $matches)) {

        $dia = $matches[1] ?? '01';
        $mesTexto = strtolower($matches[3] ?? '');
        $anio = $matches[4] ?? '';

 
        $meses = [
            'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
            'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
            'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'
        ];

 
        $mes = $meses[$mesTexto] ?? null;

       
        if ($anio && $mes && $dia) {
            try {

                return Carbon::createFromFormat('Y-m-d', "$anio-$mes-$dia")->format('Y-m-d');
            } catch (\Exception $e) {
                Log::error('Error al crear la fecha: ' . $e->getMessage());
                return 'Fecha incompleta';
            }
        }
    }

    return 'No encontrado'; 
}

















private function hora($text)
{

    if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)/i', $text, $matches)) {
        $hora = intval($matches[1]);
        $minutos = $matches[2];
        $meridiano = strtoupper($matches[3]);


        if ($meridiano === 'PM' && $hora < 12) {
            $hora += 12;
        } elseif ($meridiano === 'AM' && $hora === 12) {
            $hora = 0;
        }

        return sprintf('%02d:%02d', $hora, $minutos);
    }

    return 'No encontrado';
}

private function operacion($text)
{

    if (preg_match('/Número de operación\s*(\d+)/i', $text, $matches)) {
        return $matches[1];
    }
    

    if (preg_match('/\b\d{6,8}\b/', $text, $matches)) {
        return $matches[0];
    }

    return 'No encontrado';
}

private function Monto($text)
{

    if (preg_match('/S\/\s*(\d+(?:\.\d{2})?)/', $text, $matches)) {
        return 'S/ ' . $matches[1];
    }

    
    if (preg_match('/\b(\d+\.\d{2})\b/', $text, $matches)) {
        return 'S/ ' . $matches[1];
    }

    return 'No encontrado';
}

private function processMonto($montoStr)
{

    $montoStr = preg_replace('/[^0-9.]/', '', $montoStr);
    

    return number_format(floatval($montoStr), 2, '.', '');
}





public function confirm(Request $request)
{
    

    $operacion = $request->input('operacion');
    $existingVoucher = Voucher::where('operacion', $operacion)->first();


    if ($existingVoucher) {
        return redirect()->route('voucher')->with('error', 'El número de operación ya existe.');
    }

    $voucher = new Voucher();
    $voucher->fecha = $request->input('fecha');
    $voucher->hora = $request->input('hora');
    $voucher->operacion = $operacion;
    $voucher->monto = $this->processMonto($request->input('monto'));
    $voucher->codigo_dni = $request->input('codigo_dni');
    $voucher->servicio = $request->input('servicio');
    $voucher->save();

    return redirect()->route('voucher.success')->with('success', 'Voucher guardado correctamente');
}














  
}
