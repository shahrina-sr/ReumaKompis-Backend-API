<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Returns distinct the diagnosis list of users
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDiagnosisList()
    {
        try {
            return response()->json([
                'data' => User::select('diagnosis')->distinct()->get()->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Returns user search result
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $users = User::select('id', 'name', 'email', 'phone', 'diagnosis', 'years_of_being_sick');
            if (isset($request->diagnosis) && $request->diagnosis != 'all') {
                $users = $users->where('diagnosis', $request->diagnosis);
            }
            if (isset($request->years_of_being_sick) && $request->years_of_being_sick != 'all') {
                $users = $users->where('years_of_being_sick', '!=', $request->years_of_being_sick);
            }

            $users = $users->where('id', '!=', auth()->user()->id)->get();


            return response()->json([
                'data' => $users->isEmpty() ? [] : $users->random(1)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Changes the user's password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|string|max:255',
            'new_password' => 'required|string|min:6|max:255|confirmed',
        ]);
        try {
            $user = auth()->user();
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Invalid current password'
                ], 422);
            }
            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            return response()->json([
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
