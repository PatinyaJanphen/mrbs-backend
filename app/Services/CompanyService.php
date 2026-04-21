<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyService
{
    /**
     * List all companies
     */
    public function list(): Collection
    {
        return Company::all();
    }


    /**
     * Create a new company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

}
