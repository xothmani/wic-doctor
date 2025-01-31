<?php


namespace App\Http\Controllers;
use App\DataTables\ParrainerDataTable; // Assurez-vous d'importer le bon DataTable

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParrainageMail;
use App\Models\DoctorRequest;
use App\Models\Doctor;

class ParrainerController extends Controller
{   
  
    public function index(ParrainerDataTable $dataTable)
    {
    
    
        // Return the dataTable with the filtered results
        return $dataTable->render('parrainers.index');
    }
    

//   public function index(ParrainerDataTable $dataTable)
// {
//     if (Auth::check()) {

//             $parrainCode = Auth::user()->doctor->getAttribute('code_parent');
//             $doctors = DoctorRequest::where('code_parent', $parrainCode)->get(); 

//         // Renvoyer directement le DataTable sans filtrage
//         return $dataTable->render('parrainers.index', [
//             'doctors' => $doctors, 'parrainCode' => $parrainCode,

//         ]);
//     } else {
//         return redirect()->back()->with('error', 'Utilisateur non authentifié.');
//     }
// }
//  public function index()
//     {
//         if (Auth::check()) {
//             // Récupérer le code_parent du médecin connecté
//             $parrainCode = Auth::user()->doctor->getAttribute('code_parent');
            
//             // Filtrer les médecins dans la table doctor_requests_b2b en fonction du code_parent du médecin connecté
//             $doctors = DoctorRequest::where('code_parent', $parrainCode)->get(); 
            
//             // Passer le code_parent et les médecins filtrés à la vue
//             return view('parrainers.index', [
//                 'parrainCode' => $parrainCode,
//                 'doctors' => $doctors, // Passer les médecins à la vue
//             ]);
//         }
    
//         // Rediriger l'utilisateur vers la page de connexion si non authentifié
//         return redirect('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
//     }
    
    public function parrainer()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $doctor = $user->doctor;
    
            if ($doctor) {
                $parrainCode = $doctor->getAttribute('code_doctor');
    
                if ($parrainCode) {
                    // Construire le lien de parrainage
                    $link = 'https://wic-doctor.com/inscription-professionnel/inscription.html?code=' . $parrainCode;
    
                    // Passer le lien à la vue
                    return view('parrainers.parrainer', ['link' => $link]);
                }
            }
    
            // Aucun code de parrainage trouvé
            return redirect()->back()->with('error', 'Aucun code de parrainage trouvé.');
        }
    
        // Rediriger vers la connexion si l'utilisateur n'est pas authentifié
        return redirect('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
    }
    



    // Optionally, function to display the parrainCode in the view
    public function showParrainCode()
    {
        // Vérifier si l'utilisateur est authentifié
        if (Auth::check()) {
            // Récupérer l'utilisateur authentifié
            $user = Auth::user();
            
            // Afficher des informations pour le débogage
            dd($user); // Vérifie que l'utilisateur est bien récupéré
    
            // Vérifier si l'utilisateur a un médecin associé
            $doctor = $user->doctor;
    
            if ($doctor) {
                // Vérifier que le médecin a bien un code_parent
                $parrainCode = $doctor->getAttribute('code_doctor');
                dd($parrainCode); // Vérifie la valeur du code de parrainage
    
                if ($parrainCode) {
                    // Construire le lien de parrainage avec le code
                    $link = 'https://wic-doctor.com/inscription-professionnel/inscription.html?code=' . $parrainCode;
    
                    // Passer le lien à la vue
                    return view('parrainers.parrainer', ['link' => $link]);
                }
            }
    
            // Si aucun code de parrainage n'est trouvé, rediriger ou afficher un message
            return redirect()->back()->with('error', 'Aucun code de parrainage trouvé.');
        }
    
        // Rediriger si l'utilisateur n'est pas authentifié
        return redirect('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
    }


public function envoyerEmail(Request $request)
{
    // Vérifiez si l'utilisateur est authentifié
    if (Auth::check()) {
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Récupérer le code parrain de l'utilisateur
        $parrainCode = $user->doctor->getAttribute('code_doctor');

        // Récupérer l'email du destinataire depuis la requête
        $destinataire = $request->input('email');

        // Construire le lien de parrainage avec le code parrain
        $link = 'https://wic-doctor.com/inscription-professionnel/inscription.html?code=' . $parrainCode;

        // Envoyer l'email avec le lien de parrainage
        Mail::to($destinataire)->send(new ParrainageMail($link));

        // Ajouter un message de succès dans la session
        session()->flash('success', 'E-mail envoyé avec succès!');

        // Retourner à la page précédente (sans redirection vers un autre template)
        return redirect()->back();
    }

    // Si l'utilisateur n'est pas authentifié, retourner une erreur
    session()->flash('error', 'Utilisateur non authentifié.');
    return redirect()->back();
}


    

}
