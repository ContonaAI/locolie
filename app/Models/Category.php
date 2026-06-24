<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'icon', 'sort'])]
class Category extends Model
{
    /** Inline SVG inner-paths per category (stroke style, 24×24 viewBox). */
    public const ICONS = [
        'food-drink' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>',
        'pubs-bars' => '<path d="M8 22h8"/><path d="M12 11v11"/><path d="M5 3h14l-1.5 8a5 5 0 0 1-11 0Z"/>',
        'retail' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'hairdressers' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
        'beauty' => '<path d="m12 3 1.9 5.8H20l-5 3.6 1.9 5.8L12 14.6 6.1 18.2 8 12.4l-5-3.6h6.1Z"/>',
        'fitness' => '<path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/>',
        'builders' => '<path d="M2 20h20"/><path d="M4 20V9l8-5 8 5v11"/><path d="M9 20v-6h6v6"/>',
        'mechanics' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.6 2.6-2.4-.6-.6-2.4z"/>',
        'trades' => '<path d="M2 22 16 8"/><path d="M9 15 3.5 9.5a2.1 2.1 0 0 1 3-3L12 12"/><path d="m17 7 3-3 2 2-3 3"/><path d="M14 4l6 6"/>',
        'pet-care' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5 3 3 0 0 1-3 3 4.5 4.5 0 0 1-2.3-.8 4.5 4.5 0 0 0-3.4 0A2 2 0 0 1 4 18a5 5 0 0 1 5-8z"/>',
        'health' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'services' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6 6a2 2 0 1 0 2.8 2.8l6-6a4 4 0 0 0 5.4-5.4l-2.3 2.3-2.1-.6-.6-2.1z"/><path d="m18 2 4 4-3 3-4-4z"/>',
    ];

    public static function iconPath(?string $slug): string
    {
        return self::ICONS[$slug] ?? self::ICONS['services'];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
