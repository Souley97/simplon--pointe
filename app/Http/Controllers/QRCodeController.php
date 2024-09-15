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
    $student = User::where('matricule', $matricule)->first();

    if (!$student) {
        return response()->json(['error' => 'Étudiant non trouvé.'], 404);
    }

    // Vérifier si l'utilisateur a le rôle d'Apprenant ou de Formateur
    if (!$student->hasAnyRole(['Apprenant', 'Formateur'])) {
        return response()->json(['error' => 'Accès refusé, cet utilisateur n\'est ni un apprenant ni un formateur.'], 403);
    }

    // Générer les données du QR code
    $qrData = "ID: {$student->id}\nMatricule: {$student->matricule}\nNom: {$student->nom}\nPrénom: {$student->prenom}\nRôle: " . $student->getRoleNames()->first();

    // Générer le code QR avec UTF-8 et retourner sous forme d'image SVG
    $qrCode = QrCode::encoding('UTF-8')->size(300)->generate($qrData);

    // Retourner le QR code sous forme d'image SVG
    return response($qrCode)->header('Content-Type', 'image/svg+xml');
}


}

