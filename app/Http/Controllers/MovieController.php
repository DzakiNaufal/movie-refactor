<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\MovieService;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }

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

    public function index(Request $request)
    {
        $movies = $this->movieService->getAllMovies($request->search);
        return view('homepage', compact('movies'));
    }

    public function detail($id)
    {
        $movie = $this->movieService->getMovieById($id);
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

        $movieData = $this->getMovieData($request, true);
        $this->movieService->createMovie($movieData, $request->file('foto_sampul'));

        return redirect('/')->with('success', 'Data berhasil disimpan');
    }

    public function data()
    {
        $movies = $this->movieService->getMoviesData();
        return view('data-movies', compact('movies'));
    }

    public function edit($id)
    {
        $movie = $this->movieService->getMovieById($id);
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

        $movieData = $this->getMovieData($request);
        $this->movieService->updateMovie($id, $movieData, $request->file('foto_sampul'));

        return redirect('/movies/data')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $this->movieService->deleteMovie($id);
        return redirect('/movies/data')->with('success', 'Data berhasil dihapus');
    }
}
