<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PagosSiggass;
use Shuchkin\SimpleXLSX;

class PagosSIGGAController extends Controller
{
    public function importExcel(Request $request)
    {
        // Verificar si se ha cargado un archivo
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getRealPath();

            // Usar SimpleXLSX para parsear el archivo
            require_once app_path('Imports/SimpleXLSX.php');

            if ($xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
                $expectedColumnCount = 8; // Número esperado de columnas
                foreach ($xlsx->rows() as $index => $row) {
                    // Verificar el número de columnas en cada fila
                    if (count($row) < $expectedColumnCount) {
                        return redirect()->back()->with('error', 'El archivo Excel no tiene el formato adecuado.');
                    }

                    // Validar datos básicos de las filas (omitir encabezados)
                    if ($index > 0) {
                        $numero_operacion = isset($row[0]) ? $row[0] : null;
                        $nombres = isset($row[1]) ? $row[1] : null;
                        $apellidos = isset($row[2]) ? $row[2] : null;
                        $monto_pago = isset($row[3]) && is_numeric($row[3]) ? $row[3] : null;
                        $fecha_pago = isset($row[4]) ? date('Y-m-d', strtotime($row[4])) : null;
                        $hora = isset($row[5]) ? $row[5] : null;
                        $dni = isset($row[6]) ? $row[6] : null;
                        $sucursal = isset($row[7]) ? $row[7] : null;

                        // Si algún dato requerido está ausente, interrumpir el proceso
                        if (is_null($numero_operacion) || is_null($nombres) || is_null($apellidos) || is_null($monto_pago) || is_null($fecha_pago) || is_null($hora) || is_null($dni) || is_null($sucursal)) {
                            return redirect()->back()->with('error', 'El archivo Excel no tiene el formato adecuado.');
                        }
                    }
                }

                // Procesar e insertar las filas válidas
                foreach ($xlsx->rows() as $index => $row) {
                    // Omitir encabezados
                    if ($index == 0) {
                        continue;
                    }

                    PagosSiggass::create([
                        'numero_operacion' => $row[0],
                        'nombres' => $row[1],
                        'apellidos' => $row[2],
                        'monto_pago' => $row[3],
                        'fecha_pago' => date('Y-m-d', strtotime($row[4])),
                        'hora' => $row[5],
                        'dni' => $row[6],
                        'sucursal' => $row[7],
                    ]);
                }

                return redirect()->back()->with('success', 'Datos importados exitosamente.');
            } else {
                return redirect()->back()->with('error', SimpleXLSX::parseError());
            }
        } else {
            return redirect()->back()->with('error', 'Por favor, cargue un archivo válido.');
        }
    }
}
