<?php

namespace App\Http\Controllers;
use App\Models\Users;
use App\Models\Beneficiaries;
use App\Models\Archives;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    function delete_user($id){

        $user = Users::find($id);
        $result=$user->delete();
        if($result){
        return ["result"=>"record has been deleted".$id];
    }
    else{
        return ["result"=>"delete has failed"];
    }
  
    }

    public function update_user(Request $request, $id)
{
    $record = Users::find($id);

    $data = $request->all();

    foreach ($data as $key => $value) {
        if ($key !== 'password') {
            $record->$key = $value;
            
        }
        return ["result"=>"change password has failed"];
    }

    $record->save();

    return response()->json(['message' => 'Record updated successfully', 'data' => $record], 200);
}
public function changePassword(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'current_password' => 'required',
        'new_password' => 'required|min:6',
    ]);

    $user = Users::find($request->input('user_id'));

    // Verify the current password
    if (!Hash::check($request->input('current_password'), $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Incorrect current password',
        ], 400);
    }

    // Change the password
    $user->password = Hash::make($request->input('new_password'));
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Password changed successfully',
    ]);
}

    //////////////////////


public function makeDonation(Request $request)
{
    $donaterId = $request->input('donater_id');
    $beneficiarId = $request->input('beneficiar_id');
    $amount = $request->input('amount');

    // Find the beneficiar and update the status
    $beneficiar = Beneficiaries::find($beneficiarId);

    if (!$beneficiar) {
        return response()->json(['error' => 'Beneficiar not found.'], 404);
    }

    $beneficiar->status = 1;
    $beneficiar->save();

    // Get additional information for the archive
    $service = $beneficiar->needy_type;
    $overview = $beneficiar->overview;

    // Find the user and get additional information
    $user = Users::find($donaterId);

    if (!$user) {
        return response()->json(['error' => 'User not found.'], 404);
    }

    $usersName = $user->full_name;
    $charityId = $beneficiar->charity_id;

    // Find the charity and get additional information
    $charity = Beneficiaries::find($charityId);

    if (!$charity) {
        return response()->json(['error' => 'Charity not found.'], 404);
    }

    $charityId = $charity->charity_id;
    $beneficiariesName = $beneficiar->full_name;

    // Create a new archive record
    Archives::create([
        'service' => $service,
        'overview' => $overview,
        'total_amount_of_donation' => $amount,
        'users_id' => $donaterId,
        'users_name' => $usersName,
        'charity_id' => $charityId,
        'Beneficiaries_id' => $beneficiarId,
        'Beneficiaries_name' => $beneficiariesName,
    ]);

    return response()->json(['message' => 'Donation successful']);
}


}
