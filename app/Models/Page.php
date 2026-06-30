<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * An admin-managed page for the future page-builder: an ordered list of blocks
 * stored as JSON, with a publish status. Not routed yet - the foundation just
 * defines the shape so the builder can grow on top of it.
 */
#[Fillable(['slug', 'title', 'status', 'blocks', 'meta', 'sort', 'updated_by', 'published_at'])]
class Page extends Model
{
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'meta' => 'array',
            'sort' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
