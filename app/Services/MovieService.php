<?php
namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Interfaces\MovieRepositoryInterface;

class MovieService
{
    public const IMAGES_PATH = 'images';
    protected $movieRepository;

    public function __construct(MovieRepositoryInterface $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }

    public function getAllMovies($search = null)
    {
        return $this->movieRepository->getAllPaginated(6, $search);
    }

    public function getMoviesData()
    {
        return $this->movieRepository->getLatestPaginated(10);
    }

    public function getMovieById($id)
    {
        return $this->movieRepository->getById($id);
    }

    public function createMovie(array $data, $file = null)
    {
        if ($file) {
            $data['foto_sampul'] = $this->uploadFile($file);
        }
        return $this->movieRepository->create($data);
    }

    public function updateMovie($id, array $data, $file = null)
    {
        if ($file) {
            $movie = $this->getMovieById($id);
            $this->deleteFile($movie->foto_sampul);
            $data['foto_sampul'] = $this->uploadFile($file);
        }
        return $this->movieRepository->update($id, $data);
    }

    public function deleteMovie($id)
    {
        $movie = $this->getMovieById($id);
        $this->deleteFile($movie->foto_sampul);
        return $this->movieRepository->delete($id);
    }

    private function uploadFile($file)
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path(self::IMAGES_PATH), $fileName);
        return $fileName;
    }

    private function deleteFile($fileName)
    {
        if (!$fileName) return;
        $filePath = public_path(self::IMAGES_PATH . '/' . $fileName);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
