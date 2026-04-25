<?php
namespace App\Interfaces;

interface MovieRepositoryInterface
{
    public function getAllPaginated($perPage = 6, $search = null);
    public function getLatestPaginated($perPage = 10);
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
