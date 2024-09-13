<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function showQr($matricule)
    {
        // Trouver l'utilisateur par matricule
        $student = User::where('matricule', $matricule)->firstOrFail();

        // Vérifier si l'utilisateur a le rôle d'Apprenant ou de Formateur
        if (!$student->hasAnyRole(['Apprenant', 'Formateur'])) {
            return response()->json(['error' => 'Accès refusé, cet utilisateur n\'est ni un apprenant ni un formateur.'], 403);
        }

        // Générer les données du QR code
        $qrData = "ID: {$student->id}\nMatricule: {$student->matricule}\nNom: {$student->nom}\nPrénom: {$student->prenom}\nRôle: " . $student->getRoleNames()->first();

        // Générer le code QR avec les informations de l'utilisateur
        $qrCode = QrCode::size(300)->generate($qrData);

        // Retourner le QR code dans la réponse
        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

}

