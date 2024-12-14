<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Carbon\Carbon;
use App\Models\Course;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
class VoucherController extends Controller
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


    if (!$this->isVoucherContentValid($text)) {
        return back()->withErrors(['voucher_image' => 'La imagen no contiene un voucher válido.']);
    }

    // Nuevas extracciones
    $operacion = $this->extractSequence($text);
    $fecha = $this->extractAndConvertOperationDate($text);
    $hora = $this->extractOperationTime($text);
    $monto = $this->extractTotalAmount($text);
    $courses = Course::where('precio', '=', $monto)->get();


    return view('voucher.result', compact('operacion', 'fecha', 'hora', 'monto', 'courses'));
}

private function isVoucherContentValid($text)
{

    return preg_match('/IMPORTE TOTAL:/', $text) && preg_match('/\d{6}/', $text);
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

    // Abrir el archivo
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
    Log::info('Respuesta de OCR: ', $body); // Log para depuración

    if (isset($body['ErrorMessage']) && !empty($body['ErrorMessage'])) {
        throw new \Exception('Error en el OCR: ' . implode(', ', $body['ErrorMessage']));
    }

    if (isset($body['ParsedResults'][0]['ParsedText'])) {
        return $body['ParsedResults'][0]['ParsedText'];
    }

    throw new \Exception('Error: No se encontró texto procesado en la respuesta.');
}








    public function confirm(Request $request)
    {
 

        $operacion = $request->input('operacion');
        $existingVoucher = Voucher::where('operacion', $operacion)->first();


        if ($existingVoucher) {
            return redirect()->route('voucher')->with('error', 'El número de operación ya existe.');
        }

        $voucher = new Voucher();
        $voucher->hora = $request->input('hora');
        $voucher->operacion = $request->input('operacion');
        

        $montoStr = $request->input('monto');
        $monto = $this->processMonto($montoStr);
        $voucher->monto = $monto;

        $voucher->codigo_dni = $request->input('codigo_dni');
        $voucher->servicio = $request->input('servicio');
        $voucher->fecha = $request->input('fecha');
        $voucher->save();

        return redirect()->route('voucher.success')->with('success', 'Voucher guardado correctamente');
    }
    private function processMonto($montoStr)
    {
        $montoStr = preg_replace('/[^0-9.]/', '', $montoStr);
        return floatval($montoStr);
    }

    private function extractAndConvertOperationDate($text)
    {
        // Buscar patrones comunes de fecha en el texto
        $patterns = [
            '/FECHA:\s*(\d{2})[\/\-](\d{2})[\/\-](\d{4})/', 
            '/(\d{2})[\/\-](\d{2})[\/\-](\d{4})/',          
            '/(\d{2})([A-Z]{3})(\d{4})/',                  
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                try {
                    if (count($matches) === 4) {
                        if (is_numeric($matches[2])) {
                         
                            return Carbon::createFromFormat('Y-m-d', 
                                "{$matches[3]}-{$matches[2]}-{$matches[1]}")->format('Y-m-d');
                        } else {
                           
                            $meses = [
                                'ENE' => '01', 'FEB' => '02', 'MAR' => '03', 'ABR' => '04',
                                'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AGO' => '08',
                                'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DIC' => '12'
                            ];
                            $mes = $meses[$matches[2]] ?? '01';
                            return Carbon::createFromFormat('Y-m-d', 
                                "{$matches[3]}-{$mes}-{$matches[1]}")->format('Y-m-d');
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error al procesar fecha: ' . $e->getMessage());
                    continue;
                }
            }
        }
        
        return null;
    }
    

    
    private function extractSequence($text)
    {
        preg_match('/(\d{6})\s*-/', $text, $matches);
    
        return $matches[1] ?? 'No encontrado';
    }
    
    private function extractOperationDate($text)
    {
        preg_match('/(\d{2}[A-Z]{3}\d{4})/', $text, $matches);
        return $matches[1] ?? 'No encontrado';
    }
    

    private function extractOperationTime($text)
    {
        preg_match('/(\d{2}:\d{2}:\d{2})/', $text, $matches);
        return $matches[1] ?? 'No encontrado';
    }


    private function extractTotalAmount($text)
    {
       
        $patterns = [
            '/IMPORTE\s*TOTAL\s*:?\s*S\/?\.\s*(\d+[.,]\d{2})/', 
            '/TOTAL\s*:?\s*S\/?\.\s*(\d+[.,]\d{2})/',           
            '/MONTO\s*:?\s*S\/?\.\s*(\d+[.,]\d{2})/',          
            '/S\/?\.\s*(\d+[.,]\d{2})/',                         
            '/(\d+[.,]\d{2})/'                                   
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
              
                $monto = str_replace(',', '.', $matches[1]); 
                $monto = preg_replace('/[^0-9.]/', '', $monto); 
            
                if (is_numeric($monto)) {
                    return number_format((float)$monto, 2, '.', '');
                }
            }
        }

        return null;
    }




}