<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Menampilkan halaman profil
    public function edit()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    // Update profil
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Debug: Log request data
        \Log::info('Profile Update Request - User ID: ' . $user->id);
        \Log::info('Profile Update Request - Method: ' . $request->method());
        \Log::info('Profile Update Request - Has File: ' . ($request->hasFile('profile_photo') ? 'Yes' : 'No'));
        
        // Simple validation
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload FIRST - before anything else
        if ($request->hasFile('profile_photo')) {
            \Log::info('Processing photo upload...');
            
            $file = $request->file('profile_photo');
            
            // Debug file info
            \Log::info('File original name: ' . $file->getClientOriginalName());
            \Log::info('File size: ' . $file->getSize());
            \Log::info('File mime: ' . $file->getMimeType());
            
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            
            \Log::info('Generated filename: ' . $filename);
            
            // Hapus foto lama jika ada
            if ($user->profile_photo) {
                $oldPath = storage_path('app/public/profile-photos/' . $user->profile_photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                    \Log::info('Old photo deleted: ' . $oldPath);
                }
            }
            
            // Store file
            $storedPath = $file->storeAs('profile-photos', $filename, 'public');
            
            \Log::info('Stored path: ' . $storedPath);
            
            if ($storedPath) {
                // Update user dengan photo
                $user->profile_photo = $filename;
                \Log::info('Photo filename set: ' . $filename);
            } else {
                \Log::error('Failed to store photo file');
            }
        } else {
            \Log::info('No photo file in request');
        }

        // Update basic info
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        // Save user data (termasuk photo jika ada)
        $user->save();

        \Log::info('Profile updated successfully. Photo: ' . ($user->profile_photo ?? 'NULL'));

        return redirect()->route('profil-admin')->with('success', 'Profil berhasil diperbarui!');
    }

    // Hapus foto profil
    public function removePhoto()
    {
        $user = Auth::user();
        
        if ($user->profile_photo) {
            // Delete photo file
            $photoPath = storage_path('app/public/profile-photos/' . $user->profile_photo);
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
            
            // Update database
            $user->update(['profile_photo' => null]);
        }
        
        return redirect()->route('profil-admin')->with('success', 'Foto profil berhasil dihapus!');
    }

    // Hapus akun profil
    public function destroy()
    {
        $user = Auth::user();
        Auth::logout();
        $user->delete();

        return redirect('/')->with('success', 'Akun berhasil dihapus.');
    }
}
