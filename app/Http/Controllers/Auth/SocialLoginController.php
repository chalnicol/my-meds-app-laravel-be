<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Exception\Auth\IdTokenVerificationFailed;
use Kreait\Laravel\Firebase\Facades\Firebase; // Import the Firebase Facade
use App\Http\Resources\UserResource;

class SocialLoginController extends Controller
{
    
    
  public function socialLogin(Request $request)
  {
      $request->validate([
          'idToken' => 'required|string',
          'provider' => 'required|in:google,facebook',
      ]);

      $idToken = $request->input('idToken');
      $provider = $request->input('provider');

      try {

          $firebaseAuth = Firebase::auth();

          $verifiedIdToken = $firebaseAuth->verifyIdToken($idToken);

          $uid = $verifiedIdToken->claims()->get('sub');
          $email = $verifiedIdToken->claims()->get('email');
          $name = $verifiedIdToken->claims()->get('name');

          // 3. FIND OR CREATE THE USER IN YOUR DATABASE
          // Use the email as the single source of truth
          $user = User::where('email', $email)->first();

          if ($user) {
              // Check if user is blocked
              if ($user->isBlocked()) {
                  return response()->json(['message' => 'Your account has been blocked.'], 403);
              }
              
              // If a traditional user logs in with social, link the account
              if (!$user->firebase_uid) {
                  $user->firebase_uid = $uid;
              }
              
              $user->save();

          } else {
              // User does not exist, so create a new one
              $user = User::create([
                  'fullname' => $name,
                  'email' => $email,
                  'firebase_uid' => $uid,
                  'email_verified_at' => now(),
                  'password' => null, // Password is null for social users
              ]);
          }
          
          // 4. LOG THE USER INTO LARAVEL'S AUTHENTICATION SYSTEM
          Auth::login($user);

          // 5. Return a successful response
          $user->load('roles.permissions');

          return response()->json([
              'message' => 'Logged in successfully',
              'user' => new UserResource($user)
          ]);

      } catch (\Kreait\Firebase\Exception\Auth\IdTokenVerificationFailed $e) {
          // Token is invalid or expired
          return response()->json(['error' => 'Invalid or expired authentication token.'], 401);
      } 
      // catch (\Exception $e) {
      //     // General error
      //     return response()->json(['error' => 'An authentication error occurred.'], 500);
      // }
  }

 
}