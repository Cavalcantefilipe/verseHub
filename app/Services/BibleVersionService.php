<?php

namespace App\Services;

use App\Models\BibleVersion;
use Illuminate\Database\Eloquent\Collection;

class BibleVersionService
{
    /**
     * Get all available Bible versions.
     */
    public function getAllVersions(): Collection
    {
        return BibleVersion::orderBy('name')->get();
    }

    /**
     * Get a Bible version by slug.
     */
    public function getBySlug(string $slug): ?BibleVersion
    {
        return BibleVersion::where('slug', $slug)->first();
    }

    /**
     * Create a new Bible version.
     */
    public function create(array $data): BibleVersion
    {
        return BibleVersion::create($data);
    }

    /**
     * Update a Bible version.
     */
    public function update(BibleVersion $version, array $data): BibleVersion
    {
        $version->update($data);
        return $version->fresh();
    }

    /**
     * Delete a Bible version.
     */
    public function delete(BibleVersion $version): bool
    {
        return $version->delete();
    }
}
