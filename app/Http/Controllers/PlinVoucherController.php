<?php 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

use App\Models\Voucher;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class PlinVoucherController extends Controller
{
    private $ocrApiKey = 'K89059208388957'; 

    public function process(Request $request)
    {
        $request->validate([
            'voucher_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    

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
    

        $fecha = $this->extractAndConvertOperationDate($text);
        $operacion = $this->operacion($text);
   
        $hora = $this->hora($text);
        $monto = $this->processMonto($this->Monto($text)); 
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
    $voucher->fecha = $request->input('fecha');
    $voucher->hora = $request->input('hora');
    $voucher->operacion = $operacion;
    $voucher->monto = $this->processMonto($request->input('monto'));
    $voucher->codigo_dni = $request->input('codigo_dni');
    $voucher->servicio = $request->input('servicio');

    $voucher->save();

    return redirect()->route('voucher.success')->with('success', 'Voucher guardado correctamente');


}

    






    private function extractAndConvertOperationDate($text)
    {
        
        if (preg_match('/(\d{2}) (\w{3}) (\d{4})/', $text, $matches)) {
            $dia = $matches[1];
            $mesAbreviado = $matches[2];
            $anio = $matches[3];
    
            // Mapeo de abreviaturas de meses en español a números
            $meses = [
                'Ene' => '01', 'Feb' => '02', 'Mar' => '03', 'Abr' => '04',
                'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Ago' => '08',
                'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dic' => '12'
            ];
    
            if (isset($meses[$mesAbreviado])) {
                $mes = $meses[$mesAbreviado];
                
                return Carbon::createFromFormat('Y-m-d', "$anio-$mes-$dia")->format('Y-m-d');
            }
        }
    
        return 'No encontrado'; 
    }




    private function processMonto($montoStr)
    {

        $montoStr = preg_replace('/[^0-9.]/', '', $montoStr);

        return floatval($montoStr);
    }

    private function Fecha($text)
    {
        preg_match('/\d{2} \w+ \d{4}/', $text, $matches);
        return $matches[0] ?? 'No encontrado';
    }

    private function hora($text)
    {

    if (preg_match('/\d{2}:\d{2} [APM]{2}/i', $text, $matches)) {
        $horaStr = $matches[0];


        $dateTime = \DateTime::createFromFormat('h:i A', $horaStr);
        
        if ($dateTime) {
   
            return $dateTime->format('H:i');
        }
    }

   
    return 'No encontrado';
    }

    private function operacion($text)
    {
        preg_match_all('/\b\d{8}\b/', $text, $matches);
        return $matches[0][0] ?? 'No encontrado';
    }

    private function Monto($text)
    {
        preg_match('/S\/\s?\d+(?:\.\d{2})?/', $text, $matches);
        return $matches[0] ?? 'No encontrado';
    }

  
}
