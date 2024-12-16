<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\salesItem;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesController extends Controller
{
    public function preview($id){
        $image = base64_encode(file_get_contents(public_path('/images/logo.png')));
        $sale = Sale::with('items')->find($id);
        return view('invoice.index', ['sale'=>$sale,'image'=>$image, 'isDownload'=>false]);
    }
    public function download($id){
        $sale = Sale::with('items')->find($id);
        $image = base64_encode(file_get_contents(public_path('/images/logo.png')));
        $pdf = Pdf::loadView('invoice.index', ['sale'=>$sale, 'image'=>$image, 'isDownload'=>true]);
        return $pdf->download('invoice.pdf');
    }
}
