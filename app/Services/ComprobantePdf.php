<?php

namespace App\Services;

use Dompdf\Dompdf;

class ComprobantePdf
{
    public function generar($pedido): string
    {
        $pdf = new Dompdf([
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'fontCache' => storage_path('framework/cache'),
        ]);
        $pdf->loadHtml(view('shop.comprobante_pdf', compact('pedido'))->render(), 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        return $pdf->output();
    }
}
