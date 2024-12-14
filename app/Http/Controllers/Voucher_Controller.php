<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\PagosSiggass;
use App\Models\VoucherValidado;
use DB;

class Voucher_Controller extends Controller
{
    public function index()
    {
        // Obtener números de operación validados
        $validados = VoucherValidado::pluck('numero_operacion')->toArray();

        // Obtener los pagos y vouchers no validados
        $pagosSigga = PagosSiggass::whereNotIn('numero_operacion', $validados)->get();
        $vouchers = Voucher::whereIn('operacion', $pagosSigga->pluck('numero_operacion'))->get();

        // Si es una solicitud AJAX, devolver solo la vista de la tabla
        if (request()->ajax()) {
            return view('validaciones.partials.tabla_vouchers', compact('pagosSigga', 'vouchers'))->render();
        }

        return view('validaciones.index', compact('pagosSigga', 'vouchers'));
    }

    // Buscar el voucher que coincida con el numero_operacion
    public function buscarVoucher(Request $request)
    {
        $voucher = Voucher::where('operacion', $request->numero_operacion)
                           ->where('monto', $request->monto)
                           ->where('fecha', $request->fecha_pago)
                           ->first();

        if ($voucher) {
            $pagoSigga = PagosSiggass::where('numero_operacion', $voucher->operacion)->first();

            return response()->json([
                'success' => true,
                'voucher' => view('validaciones.partials.voucher_row', compact('voucher', 'pagoSigga'))->render(),
            ]);
        } else {
            return response()->json(['success' => false, 'message' => 'No se encontró un voucher que coincida.']);
        }
    }

    public function validarVoucher(Request $request)
    {
        // Validar los datos de entrada
        $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'pagos_siga_id' => 'required|exists:pagos_s_i_g_g_a_s,id',
        ]);

        try {
            DB::beginTransaction();

            // Obtener datos de PagosSiggass y validar existencia de número de operación
            $pagoSigga = PagosSiggass::findOrFail($request->pagos_siga_id);
            $voucher = Voucher::findOrFail($request->voucher_id);

            if (VoucherValidado::where('numero_operacion', $pagoSigga->numero_operacion)->exists()) {
                return response()->json(['success' => false, 'message' => 'El número de operación ya ha sido validado.'], 400);
            }

            // Crear nuevo VoucherValidado
            VoucherValidado::create([
                'voucher_id' => $voucher->id,
                'pagos_siga_id' => $pagoSigga->id,
                'numero_operacion' => $pagoSigga->numero_operacion,
                'fecha_pago' => $pagoSigga->fecha_pago,
                'monto' => $pagoSigga->monto_pago,
                'dni_codigo' => $voucher->codigo_dni,
                'nombres' => $pagoSigga->nombres,
                'apellidos' => $pagoSigga->apellidos,
                'nombre_curso_servicio' => $voucher->servicio,
                'estado' => 1,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Voucher validado e insertado correctamente.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error al validar el voucher: ' . $e->getMessage()], 500);
        }
    }
    
}
