<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'name', 'slug', 'icon', 'sort'])]
class Category extends Model
{
    /** Inline SVG inner-paths per category slug (stroke style, 24×24 viewBox). */
    public const ICONS = [
        // ── Parent (top-level) categories ──────────────────────────────────
        'eat-drink' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>',
        'health-beauty' => '<path d="m12 3 1.9 5.8H20l-5 3.6 1.9 5.8L12 14.6 6.1 18.2 8 12.4l-5-3.6h6.1Z"/>',
        'fitness-leisure' => '<path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/>',
        'home-maintenance' => '<path d="M3 11 12 4l9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"/><path d="M10 20v-5h4v5"/>',
        'motoring' => '<circle cx="7" cy="17" r="1.8"/><circle cx="17" cy="17" r="1.8"/><path d="M5.2 17H3v-4.5l1.8-4h8.5l3.5 4H21V17h-2.2"/><line x1="8.8" y1="17" x2="15.2" y2="17"/>',
        'shopping' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'pets' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5 3 3 0 0 1-3 3 4.5 4.5 0 0 1-2.3-.8 4.5 4.5 0 0 0-3.4 0A2 2 0 0 1 4 18a5 5 0 0 1 5-8z"/>',
        'professional' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',

        // ── Leaf (sub) categories ──────────────────────────────────────────
        'food-drink' => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>',
        'pubs-bars' => '<path d="M8 22h8"/><path d="M12 11v11"/><path d="M5 3h14l-1.5 8a5 5 0 0 1-11 0Z"/>',
        'takeaways' => '<path d="M6 2h12l-1 4H7z"/><path d="M5 6h14l-1.2 13.2A2 2 0 0 1 15.8 21H8.2a2 2 0 0 1-2-1.8z"/><line x1="9" y1="10" x2="15" y2="10"/>',
        'bakeries' => '<path d="M5 12h14l-1.2 7H6.2z"/><path d="M5 12a4 4 0 0 1 .6-3.4 3.5 3.5 0 0 1 5.9-.8 3.5 3.5 0 0 1 5.9.8A4 4 0 0 1 19 12"/>',
        'hairdressers' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>',
        'barbers' => '<circle cx="6" cy="6" r="2.5"/><circle cx="6" cy="18" r="2.5"/><line x1="8" y1="7.5" x2="20" y2="17"/><line x1="8" y1="16.5" x2="20" y2="7"/>',
        'beauty' => '<path d="m12 3 1.9 5.8H20l-5 3.6 1.9 5.8L12 14.6 6.1 18.2 8 12.4l-5-3.6h6.1Z"/>',
        'spa' => '<path d="M12 22c5-2 8-6 8-11 0 0-4 1-6 4 0-4 2-7 2-9-3 1-5 4-6 7-1-3-3-6-6-7 0 2 2 5 2 9-2-3-6-4-6-4 0 5 3 9 8 11z"/>',
        'health' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
        'fitness' => '<path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/>',
        'yoga' => '<circle cx="12" cy="4.5" r="2"/><path d="M12 6.5v6"/><path d="m12 12.5-5 3"/><path d="m12 12.5 5 3"/><path d="M6.5 21 12 15l5.5 6"/>',
        'activities' => '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2 2 2 0 0 0 0 4 2 2 0 0 1-2 2H5a2 2 0 0 1-2-2 2 2 0 0 0 0-4z"/><line x1="13" y1="6" x2="13" y2="18" stroke-dasharray="2 2.5"/>',
        'builders' => '<path d="M2 20h20"/><path d="M4 20V9l8-5 8 5v11"/><path d="M9 20v-6h6v6"/>',
        'trades' => '<path d="M2 22 16 8"/><path d="M9 15 3.5 9.5a2.1 2.1 0 0 1 3-3L12 12"/><path d="m17 7 3-3 2 2-3 3"/><path d="M14 4l6 6"/>',
        'decorators' => '<rect x="3" y="4" width="13" height="6" rx="1"/><path d="M16 7h3a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-7"/><path d="M12 13v3a2 2 0 0 1-2 2H9v3h2"/>',
        'cleaning' => '<path d="M9 3h4v3H9z"/><path d="M8 6h6v4H8z"/><path d="M8 10h6v9a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1z"/><path d="M15 4h4M15 7h5"/>',
        'gardening' => '<path d="M12 22V11"/><path d="M12 11C12 7 9 4 4 4c0 4 3 7 8 7z"/><path d="M12 13c0-3 3-6 8-6 0 3-3 6-8 6z"/>',
        'mechanics' => '<circle cx="7" cy="17" r="1.8"/><circle cx="17" cy="17" r="1.8"/><path d="M5.2 17H3v-4.5l1.8-4h8.5l3.5 4H21V17h-2.2"/><line x1="8.8" y1="17" x2="15.2" y2="17"/>',
        'tyres' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><line x1="12" y1="3" x2="12" y2="8.5"/><line x1="12" y1="15.5" x2="12" y2="21"/><line x1="3" y1="12" x2="8.5" y2="12"/><line x1="15.5" y1="12" x2="21" y2="12"/>',
        'valeting' => '<path d="M12 3s6 6 6 10a6 6 0 0 1-12 0c0-4 6-10 6-10z"/>',
        'retail' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'fashion' => '<path d="M8 4 4 6.5 6 11l2-1v9h8v-9l2 1 2-4.5L16 4a4 4 0 0 1-8 0z"/>',
        'florists' => '<circle cx="12" cy="8" r="3"/><path d="M12 11v10"/><path d="M12 8c0-3-2-5-5-5 0 3 2 5 5 5z"/><path d="M12 8c0-3 2-5 5-5 0 3-2 5-5 5z"/>',
        'pet-care' => '<circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5 3 3 0 0 1-3 3 4.5 4.5 0 0 1-2.3-.8 4.5 4.5 0 0 0-3.4 0A2 2 0 0 1 4 18a5 5 0 0 1 5-8z"/>',
        'vets' => '<path d="M12 21s-7-4.5-7-10a3.7 3.7 0 0 1 7-1.5A3.7 3.7 0 0 1 19 11c0 5.5-7 10-7 10z"/><path d="M10 11h4M12 9v4"/>',
        'services' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4l-6 6a2 2 0 1 0 2.8 2.8l6-6a4 4 0 0 0 5.4-5.4l-2.3 2.3-2.1-.6-.6-2.1z"/><path d="m18 2 4 4-3 3-4-4z"/>',
        'estate-agents' => '<path d="M3 11 12 4l9 7"/><path d="M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"/><path d="M10 20v-5h4v5"/>',
        'photography' => '<rect x="3" y="7" width="18" height="13" rx="2"/><circle cx="12" cy="13.5" r="3.5"/><path d="M8.5 7 10 4h4l1.5 3"/>',
    ];

    public static function iconPath(?string $slug): string
    {
        return self::ICONS[$slug] ?? self::ICONS['services'];
    }

    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort');
    }

    /** Top-level (parent) categories. */
    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** Leaf (sub) categories — the ones businesses actually belong to. */
    public function scopeLeaves(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }
}
