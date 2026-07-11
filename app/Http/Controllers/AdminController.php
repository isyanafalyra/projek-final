<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Port;
use App\Models\Article;
use App\Models\Country;
use App\Models\ActivityLog;
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
    public function toggleUserRole(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat mendemosi akun Anda sendiri.');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        $roleName = $user->is_admin ? 'Admin' : 'User';
        $this->logActivity($request, 'TOGGLE_ROLE', 'User', $user->id, "Mengubah role user {$user->name} menjadi {$roleName}");

        return back()->with('success', "Role user {$user->name} berhasil diubah.");
    }

    /**
     * Menghapus user.
     */
    public function deleteUser(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $id = $user->id;
        $user->delete();

        $this->logActivity($request, 'DELETE_USER', 'User', $id, "Menghapus user: {$name}");

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

        $port = Port::create($request->only(['name', 'country_id', 'latitude', 'longitude', 'code']));

        $this->logActivity($request, 'CREATE_PORT', 'Port', $port->id, "Menambahkan pelabuhan baru: {$port->name} ({$port->code})");

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

        $this->logActivity($request, 'UPDATE_PORT', 'Port', $port->id, "Memperbarui data pelabuhan: {$port->name}");

        return back()->with('success', 'Data pelabuhan berhasil diperbarui.');
    }

    /**
     * Menghapus pelabuhan.
     */
    public function deletePort(Request $request, Port $port)
    {
        $name = $port->name;
        $id = $port->id;
        $port->delete();

        $this->logActivity($request, 'DELETE_PORT', 'Port', $id, "Menghapus pelabuhan: {$name}");

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

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'slug' => $slug,
            'content' => $request->input('content'),
            'image_path' => $imagePath,
            'published_at' => $request->input('published_at') ?? now(),
        ]);

        $this->logActivity($request, 'CREATE_ARTICLE', 'Article', $article->id, "Menerbitkan artikel baru: {$article->title}");

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

        $this->logActivity($request, 'UPDATE_ARTICLE', 'Article', $article->id, "Memperbarui artikel: {$article->title}");

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    /**
     * Menghapus postingan artikel.
     */
    public function deleteArticle(Request $request, Article $article)
    {
        // Hapus file gambar terkait
        if ($article->image_path && file_exists(public_path($article->image_path))) {
            @unlink(public_path($article->image_path));
        }

        $title = $article->title;
        $id = $article->id;
        $article->delete();

        $this->logActivity($request, 'DELETE_ARTICLE', 'Article', $id, "Menghapus artikel: {$title}");

        return back()->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Helper untuk mencatat aktivitas log admin.
     */
    private function logActivity(Request $request, string $action, string $modelType, ?int $modelId, string $details)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'details' => $details,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mencatat log aktivitas admin: " . $e->getMessage());
        }
    }
}
