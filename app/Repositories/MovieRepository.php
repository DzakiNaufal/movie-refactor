<?php
namespace App\Repositories;

use App\Models\Movie;
use App\Interfaces\MovieRepositoryInterface;

class MovieRepository implements MovieRepositoryInterface
{
    public function getAllPaginated($perPage = 6, $search = null)
    {
        $query = Movie::with('category')->latest();
        
        if ($search) {
            $query->where('judul', 'like', '%' . $search . '%')
                ->orWhere('sinopsis', 'like', '%' . $search . '%');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getLatestPaginated($perPage = 10)
    {
        return Movie::with('category')->latest()->paginate($perPage);
    }

    public function getById($id)
    {
        return Movie::with('category')->findOrFail($id);
    }

    public function create(array $data)
    {
        return Movie::create($data);
    }

    public function update($id, array $data)
    {
        $movie = $this->getById($id);
        $movie->update($data);
        return $movie;
    }

    public function delete($id)
    {
        $movie = $this->getById($id);
        $movie->delete();
        return $movie;
    }
}
