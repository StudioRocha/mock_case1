<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;
        return view('mypage.profile', compact('user', 'profile'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        $payload = [
            'user_id' => $user->id,
            'usernames' => $data['username'],
            'postal_codes' => $data['postal_code'],
            'addresses' => $data['address'],
            'building_names' => $data['building_name'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $payload['avatar_paths'] = $path;
        }

        Profile::updateOrCreate(['user_id' => $user->id], $payload);

        return redirect('/')->with('success', 'プロフィールを更新しました');
    }
}
