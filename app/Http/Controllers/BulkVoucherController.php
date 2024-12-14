<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\PagosSiggass;
use App\Models\VoucherValidado;
use DB;

class BulkVoucherController extends Controller
{
    public function index()
    {
        // Obtener números de operación validados
        $validados = VoucherValidado::pluck('numero_operacion')->toArray();

        // Obtener los pagos y vouchers no validados
        $pagosSigga = PagosSiggass::whereNotIn('numero_operacion', $validados)->get();
        $vouchers = Voucher::whereIn('operacion', $pagosSigga->pluck('numero_operacion'))->get();

        return view('validaciones.bulk_index', compact('pagosSigga', 'vouchers'));
    }

    public function validarTodos(Request $request)
    {
        // Validar que se recibieron vouchers
        $request->validate([
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'exists:vouchers,id',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Iterar sobre los IDs de vouchers seleccionados
            foreach ($request->voucher_ids as $voucher_id) {
                $voucher = Voucher::findOrFail($voucher_id);
                $pagoSigga = PagosSiggass::where('numero_operacion', $voucher->operacion)->first();
    
                if (!$pagoSigga) {
                    // Si no hay pago asociado, puedes lanzar una excepción o continuar
                    continue;
                }
    
                if (VoucherValidado::where('numero_operacion', $pagoSigga->numero_operacion)->exists()) {
                    // Salta este voucher si ya ha sido validado
                    continue; 
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
            }
    
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Todos los vouchers han sido validados.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error al validar los vouchers: ' . $e->getMessage()], 500);
        }
    }
    
    
}
