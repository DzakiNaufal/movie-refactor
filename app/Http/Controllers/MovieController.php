<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    public const IMAGES_PATH = 'images';

    // ============ Helper Methods ============
    
    /**
     * Validate store request
     */
    private function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'id' => ['required', 'string', 'max:255', Rule::unique('movies', 'id')],
            'judul' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sinopsis' => 'required|string',
            'tahun' => 'required|integer',
            'pemain' => 'required|string',
            'foto_sampul' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    /**
     * Validate update request
     */
    private function validateUpdate(Request $request)
    {
        return Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sinopsis' => 'required|string',
            'tahun' => 'required|integer',
            'pemain' => 'required|string',
            'foto_sampul' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }

    /**
     * Upload file and return filename
     */
    private function uploadFile($file)
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path(self::IMAGES_PATH), $fileName);
        return $fileName;
    }

    /**
     * Delete file if exists
     */
    private function deleteFile($fileName)
    {
        $filePath = public_path(self::IMAGES_PATH . '/' . $fileName);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    /**
     * Get prepared movie data from request
     */
    private function getMovieData(Request $request, $includeId = false)
    {
        $data = [
            'judul' => $request->judul,
            'sinopsis' => $request->sinopsis,
            'category_id' => $request->category_id,
            'tahun' => $request->tahun,
            'pemain' => $request->pemain,
        ];

        if ($includeId) {
            $data['id'] = $request->id;
        }

        return $data;
    }

    // ============ Controller Actions ============

    public function index()
    {
        $query = Movie::with('category')->latest();
        
        if (request('search')) {
            $query->where('judul', 'like', '%' . request('search') . '%')
                ->orWhere('sinopsis', 'like', '%' . request('search') . '%');
        }

        $movies = $query->paginate(6)->withQueryString();
        return view('homepage', compact('movies'));
    }

    public function detail($id)
    {
        $movie = Movie::with('category')->findOrFail($id);
        return view('detail', compact('movie'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('input', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = $this->validateStore($request);

        if ($validator->fails()) {
            return redirect('movies/create')
                ->withErrors($validator)
                ->withInput();
        }

        $fileName = $this->uploadFile($request->file('foto_sampul'));
        
        $movieData = $this->getMovieData($request, true);
        $movieData['foto_sampul'] = $fileName;

        Movie::create($movieData);

        return redirect('/')->with('success', 'Data berhasil disimpan');
    }

    public function data()
    {
        $movies = Movie::with('category')->latest()->paginate(10);
        return view('data-movies', compact('movies'));
    }

    public function edit($id)
    {
        $movie = Movie::findOrFail($id);
        $categories = Category::all();
        return view('form-edit', compact('movie', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateUpdate($request);

        if ($validator->fails()) {
            return redirect("/movies/{$id}/edit")
                ->withErrors($validator)
                ->withInput();
        }

        $movie = Movie::findOrFail($id);
        $movieData = $this->getMovieData($request);

        if ($request->hasFile('foto_sampul')) {
            $this->deleteFile($movie->foto_sampul);
            $movieData['foto_sampul'] = $this->uploadFile($request->file('foto_sampul'));
        }

        $movie->update($movieData);

        return redirect('/movies/data')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $this->deleteFile($movie->foto_sampul);
        $movie->delete();

        return redirect('/movies/data')->with('success', 'Data berhasil dihapus');
    }
}
