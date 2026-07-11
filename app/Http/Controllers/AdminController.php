<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Port;
use App\Models\Article;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Tampilan utama panel admin.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        $ports = Port::with('country')->orderBy('name')->get();
        $articles = Article::with('user')->orderBy('published_at', 'desc')->get();
        $countries = Country::orderBy('name')->get();

        return view('admin.dashboard', compact('users', 'ports', 'articles', 'countries'));
    }

    /**
     * Mengubah peran user (Admin <=> User).
     */
    public function toggleUserRole(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat mendemosi akun Anda sendiri.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('success', "Role user {$user->name} berhasil diubah.");
    }

    /**
     * Menghapus user.
     */
    public function deleteUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Menyimpan data pelabuhan baru.
     */
    public function storePort(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'code' => 'nullable|string|max:50',
        ]);

        Port::create($request->only(['name', 'country_id', 'latitude', 'longitude', 'code']));

        return back()->with('success', 'Pelabuhan baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data pelabuhan.
     */
    public function updatePort(Request $request, Port $port)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'code' => 'nullable|string|max:50',
        ]);

        $port->update($request->only(['name', 'country_id', 'latitude', 'longitude', 'code']));

        return back()->with('success', 'Data pelabuhan berhasil diperbarui.');
    }

    /**
     * Menghapus pelabuhan.
     */
    public function deletePort(Port $port)
    {
        $port->delete();

        return back()->with('success', 'Pelabuhan berhasil dihapus.');
    }

    /**
     * Menyimpan postingan artikel baru.
     */
    public function storeArticle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|max:2048' // Max 2MB
        ]);

        $slug = Str::slug($request->input('title')) . '-' . rand(100, 999);
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Simpan gambar secara publik di uploads
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/articles'), $imageName);
            $imagePath = 'uploads/articles/' . $imageName;
        }

        Article::create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image_path' => $imagePath,
            'published_at' => $request->input('published_at') ?? now(),
        ]);

        return back()->with('success', 'Artikel baru berhasil diterbitkan.');
    }

    /**
     * Memperbarui postingan artikel.
     */
    public function updateArticle(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|max:2048'
        ]);

        $slug = Str::slug($request->input('title')) . '-' . rand(100, 999);

        $imagePath = $article->image_path;
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/articles'), $imageName);
            $imagePath = 'uploads/articles/' . $imageName;
        }

        $article->update([
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image_path' => $imagePath,
            'published_at' => $request->input('published_at') ?? $article->published_at,
        ]);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    /**
     * Menghapus postingan artikel.
     */
    public function deleteArticle(Article $article)
    {
        // Hapus file gambar terkait
        if ($article->image_path && file_exists(public_path($article->image_path))) {
            @unlink(public_path($article->image_path));
        }

        $article->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
