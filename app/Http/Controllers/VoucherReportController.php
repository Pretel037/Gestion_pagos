<?php

namespace App\Http\Controllers;

use App\Models\VoucherValidado;
use Illuminate\Http\Request;
use PDF;
use Carbon\Carbon;
Carbon::setLocale('es');
class VoucherReportController extends Controller
{
    // Método para mostrar los datos sin descargar el PDF
    public function mostrarReporte(Request $request)
    {
        // Obtén el mes actual o el mes proporcionado
        $fechaActual = Carbon::now();
        $mes = $request->input('mes', $fechaActual->format('m'));
        $año = $request->input('año', $fechaActual->format('Y'));

        // Obtén los vouchers del mes
        $vouchers = VoucherValidado::whereYear('fecha_pago', $año)
                                    ->whereMonth('fecha_pago', $mes)
                                    ->get();

        // Calcula el número total de vouchers y la suma de los montos
        $numeroVouchers = $vouchers->count();
        $ingresosTotales = $vouchers->sum('monto');

        // Agrupa por día y suma los montos de cada día
        $pagosPorDia = $vouchers->groupBy(function ($voucher) {
            return Carbon::parse($voucher->fecha_pago)->format('d');
        })->map(function ($dayVouchers) {
            return [
                'numero_vouchers' => $dayVouchers->count(),
                'monto_total' => $dayVouchers->sum('monto')
            ];
        });

        // Generar los datos para la vista
        $dias = $pagosPorDia->keys()->toArray();
        $numeroVouchersPorDia = $pagosPorDia->pluck('numero_vouchers')->toArray();
        $montosPorDia = $pagosPorDia->pluck('monto_total')->toArray();

        // Retorna la vista con los datos
        return view('reporte_pdf', compact('numeroVouchers', 'ingresosTotales', 'dias', 'numeroVouchersPorDia', 'montosPorDia', 'mes', 'año'));
    }

    // Método para generar y descargar el PDF
    public function descargarPDF(Request $request)
    {
        $mes = $request->input('mes');
        $año = $request->input('año');
    
        // Obtén los vouchers validados para el mes y año especificados
        $vouchers = VoucherValidado::whereYear('fecha_pago', $año)
                                    ->whereMonth('fecha_pago', $mes)
                                    ->get();
    
        // Contar el número total de vouchers y calcular los ingresos totales
        $numeroVouchers = $vouchers->count();
        $ingresosTotales = $vouchers->sum('monto');
    
        // Agrupar los vouchers por día
        $pagosPorDia = $vouchers->groupBy(function ($voucher) {
            return Carbon::parse($voucher->fecha_pago)->format('d');
        })->map(function ($dayVouchers) {
            return [
                'numero_vouchers' => $dayVouchers->count(),
                'monto_total' => $dayVouchers->sum('monto')
            ];
        });
    
        // Extraer los días, números de vouchers y montos totales por día
        $dias = $pagosPorDia->keys()->toArray();
        $numeroVouchersPorDia = $pagosPorDia->pluck('numero_vouchers')->toArray();
        $montosPorDia = $pagosPorDia->pluck('monto_total')->toArray();
    
        // Generar el PDF con los datos
        $pdf = PDF::loadView('reporte_pdf_1', compact('numeroVouchers', 'ingresosTotales', 'dias', 'numeroVouchersPorDia', 'montosPorDia', 'mes', 'año'));
    
        // Descargar el PDF
        return $pdf->download('reporte_vouchers_' . $mes . '_' . $año . '.pdf');
    }
}
