<?php
namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function download($id)
    {
        $user = User::findOrFail($id);

        if ($user->form_status == 'pending' ) {
            abort(403, 'Only approved forms can download acknowledgement.');
        }

        $pdf = Pdf::loadView('pdf.acknowledgement', compact('user'));

        // return $pdf->download('Acknowledgement_' . $user->form_status . '.pdf');
        return $pdf->stream('Acknowledgement_' . $user->form_status . '.pdf');

    }
}
