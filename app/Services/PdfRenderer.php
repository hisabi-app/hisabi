<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class PdfRenderer
{
    public static function render($view, $data)
    {
        if (! defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }
    
        $dompdfOptions = new Options();
        $dompdfOptions->setChroot(base_path());
    
        $dompdf = new Dompdf($dompdfOptions);
        $dompdf->setPaper('A4');
        
        $dompdf->loadHtml(View::make($view, $data)->render());
        $dompdf->render();
    
        return (string) $dompdf->output();
    }
}