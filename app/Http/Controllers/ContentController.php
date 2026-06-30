<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use Illuminate\Http\Request;

/**
 * Admin content manager (the foundation of the CMS). Lists every editable
 * content block grouped by page/section and lets the team edit + save them
 * inline. Saving busts the per-key cache (ContentBlock::booted) so changes
 * show live on the site - proven on /cms-demo.
 *
 * Lives behind the portal gate alongside the other /portal admin tools.
 */
class ContentController extends Controller
{
    /** Friendly section titles for the known groups (falls back to the slug). */
    private const GROUP_LABELS = [
        'home' => 'Home page',
        'for-business' => 'For business',
        'footer' => 'Footer (shared)',
        'general' => 'General',
    ];

    /** List all blocks, grouped by page/section, ready for inline editing. */
    public function index()
    {
        $groups = ContentBlock::query()
            ->orderBy('group')
            ->orderBy('sort')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn ($blocks, $group) => [
                'label' => self::GROUP_LABELS[$group] ?? ucfirst(str_replace('-', ' ', $group)),
                'blocks' => $blocks,
            ]);

        return view('portal.content.index', [
            'groups' => $groups,
            'total' => ContentBlock::count(),
        ]);
    }

    /** Save a single block's value from the inline editor. */
    public function update(Request $request, ContentBlock $block)
    {
        $data = $request->validate([
            'value' => ['nullable', 'string', 'max:65000'],
        ]);

        $block->update([
            'value' => $data['value'] ?? '',
            'updated_by' => $request->input('updated_by', 'team'),
        ]);

        return back()->with('status', 'Saved "'.$block->key.'" - live on the site now.');
    }
}
