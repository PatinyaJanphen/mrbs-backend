<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{
    /**
     * Create a new company
     */
    public function createCompany(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Get all companies
     */
    public function getAllCompanies()
    {
        return Company::all();
    }
}
