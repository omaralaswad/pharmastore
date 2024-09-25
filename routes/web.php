<?php

use App\Models\Archives;
use App\Models\Beneficiaries;
use App\Models\Charites;
use App\Models\Dividable_donations;
use App\Models\Donation_types;
use App\Models\Needs;
use App\Models\Needs_types;
use App\Models\Users;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/find',function(){
//     $donation = Donations::find(1);
//     $donation->user1->full_name;
// return ($donation);
// });

  Route::get('/find',function(){
     $user = Users::find(1);

     if ($user) {
        foreach($user->archives as $archive)
         echo$archive->total_amount_of_donation."<br />";
        
     } else {
         // Handle the case where the user with ID 1 is not found.
         echo "User not found.";
     }
    
  });

  Route::get('/rev',function(){
    
    $comment = Archives::find(1);
    
    return $comment->users->full_name;
  });

  Route::get('/find1',function(){
    $Beneficiar = Beneficiaries::find(1);

    if ($Beneficiar) {
       foreach($Beneficiar->archives as $archive)
        echo$archive."<br />";
       
    } else {
        // Handle the case where the user with ID 1 is not found.
        echo "User not found.";
    }
   
 });
 Route::get('/rev1',function(){
    
    $arci = Archives::find(1);
    
    return $arci->benefit->full_name;

});

Route::get('/test',function(){
    $Beneficiar = Beneficiaries::find(1);
    if ($Beneficiar !== null) {
        // Access the "id" property
        $id = $Beneficiar->id;
    } else {
        echo "error";
        // Handle the case where the variable is null
        // You can log an error, provide a default value, or take appropriate action.
    }
});


Route::get('/find2',function(){
    $charte = Charites::find(1);

    if ($charte) {
       foreach($charte->benifeats as $benifeat)
        echo$benifeat."<br />";
       
    } else {
        // Handle the case where the user with ID 1 is not found.
        echo "User not found.";
    }
   
 });
 Route::get('/rev2',function(){
    
    $comment2 = Beneficiaries::find(1);
    
    return $comment2->charte->name;
  });

  Route::get('/find3',function(){
    $charte = Charites::find(1);

    if ($charte) {
       foreach($charte->Dividabledonations as $Dividabledonation)
        echo$Dividabledonation."<br />";
       
    } else {
        // Handle the case where the user with ID 1 is not found.
        echo "User not found.";
    }
   
 });
 Route::get('/rev3',function(){
    
    $comment3 = Dividable_donations::find(1);
    
    return $comment3->charte->name;
  });
  Route::get('/find4',function(){
    $donation = Donation_types::find(1);

    if ($donation) {
       foreach($donation->archives as $archives)
        echo$archives."<br />"."ghf";
       
    } 
   
 });

 Route::get('/rev4',function(){
    
    $comment4 = Archives::find(1);
    
    return $comment4->donation_type->type;
  });
Route::get('/test1',function(){
    $donation = Needs_types::find(1);
    return $donation->type;
});


Route::get('/find5',function(){
    $donation = Charites::find(1);

    if ($donation) {
       foreach($donation->need as $archives)
        echo$archives."<br />";
       
    } else {
        // Handle the case where the user with ID 1 is not found.
        echo "User not found.";
    }
   
 });

 Route::get('/rev5',function(){
    
    $comment5 = Needs::find(1);
    
    return $comment5->charte->name;
  });

  Route::get('/find6',function(){
    $donation = Needs_types::find(1);

    if ($donation) {
       foreach($donation->need as $archives)
        echo$archives."<br />";
       
    } else {
        // Handle the case where the user with ID 1 is not found.
        echo "User not found.";
    }
   
 });

 Route::get('/rev6',function(){
    
    $comment5 = Needs::find(1);
    
    return $comment5->needtype->type;
  });

//  Route::get('/find',function(){
//      $donation = Archives::find(1);
//      return $donation->type_of_donation;
//  });

// Route::get('/find',function(){
//     $users =User::all();
//     return $users;
// });

