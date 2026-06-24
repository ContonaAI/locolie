<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Offer;
use App\Services\PlacesService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function __construct(protected PlacesService $places) {}

    /** Live Google Places search for the signup "find your business" box. */
    public function placesSearch(Request $request)
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:120']]);

        return $this->places->search($request->query('q'));
    }

    /**
     * Retailer self-serve signup. If a Google place_id is supplied, the listing is
     * populated from the SAME Places data (name, address, coords, rating, photo)
     * that customers see — so the two stay in sync.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'postcode' => ['nullable', 'string', 'max:12'],
            'email' => ['nullable', 'email', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'place_id' => ['nullable', 'string', 'max:300'],
        ]);

        $attrs = [
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'postcode' => $data['postcode'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'active',
        ];

        // Pull the full Google listing so it matches the customer-facing data.
        $place = ! empty($data['place_id']) ? $this->places->details($data['place_id']) : null;
        if ($place) {
            $attrs['name'] = data_get($place, 'displayName.text', $attrs['name']);
            $attrs['google_place_id'] = data_get($place, 'id');
            $attrs['address'] = data_get($place, 'formattedAddress');
            $attrs['lat'] = data_get($place, 'location.latitude');
            $attrs['lng'] = data_get($place, 'location.longitude');
            $attrs['rating'] = data_get($place, 'rating');
            $attrs['reviews_count'] = (int) data_get($place, 'userRatingCount', 0);
            $attrs['postcode'] = $this->places->postcode($attrs['address']) ?? $attrs['postcode'];
        }

        $business = Business::create($attrs);

        if ($place) {
            if ($url = $this->places->downloadPhoto(data_get($place, 'photos.0.name'), $business->id)) {
                $business->update(['photos' => [$url]]);
            }
        }

        return response()->json([
            'business' => $this->summary($business),
            'owner_secret' => $business->owner_secret,
            'qr_token' => $business->qr_token,
        ], 201);
    }

    public function offers(string $secret)
    {
        $business = $this->resolve($secret);

        return $business->offers()->latest()->get()->map(fn ($o) => [
            'id' => $o->id, 'title' => $o->title, 'badge' => $o->badge, 'terms' => $o->terms,
            'sale_type' => $o->sale_type, 'quantity' => $o->quantity, 'redeemed_count' => $o->redeemed_count,
            'remaining' => $o->remaining(), 'sold_out' => $o->isSoldOut(), 'status' => $o->status,
        ]);
    }

    public function storeOffer(Request $request, string $secret)
    {
        $business = $this->resolve($secret);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'badge' => ['nullable', 'string', 'max:20'],
            'terms' => ['nullable', 'string', 'max:200'],
            'sale_type' => ['nullable', 'in:ongoing,limited,seasonal'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ]);

        $offer = $business->offers()->create([
            'title' => $data['title'],
            'badge' => $data['badge'] ?? 'OFFER',
            'terms' => $data['terms'] ?? null,
            'discount_type' => 'other',
            'sale_type' => $data['sale_type'] ?? 'ongoing',
            'quantity' => ($data['sale_type'] ?? 'ongoing') === 'limited' ? ($data['quantity'] ?? 20) : null,
            'status' => 'active',
        ]);

        return response()->json($offer->only(['id', 'title', 'badge', 'status']), 201);
    }

    public function destroyOffer(string $secret, Offer $offer)
    {
        $business = $this->resolve($secret);
        abort_unless($offer->business_id === $business->id, 403);

        $offer->delete();

        return response()->json(['ok' => true]);
    }

    /** Redeemed + pending counts and recent redemptions for the dashboard. */
    public function redemptions(string $secret)
    {
        $business = $this->resolve($secret);

        $rows = \App\Models\Redemption::with('offer')
            ->whereHas('offer', fn ($q) => $q->where('business_id', $business->id))
            ->latest('id')
            ->limit(50)
            ->get();

        return [
            'redeemed' => $rows->where('status', 'redeemed')->count(),
            'pending' => $rows->where('status', 'pending')->count(),
            'recent' => $rows->where('status', 'redeemed')->take(10)->map(fn ($r) => [
                'code' => $r->code,
                'offer' => $r->offer?->title,
                'at' => $r->redeemed_at?->toDateTimeString(),
            ])->values(),
        ];
    }

    protected function resolve(string $secret): Business
    {
        return Business::where('owner_secret', $secret)->firstOr(fn () => abort(403, 'Invalid business key.'));
    }

    protected function summary(Business $b): array
    {
        return [
            'id' => $b->id,
            'name' => $b->name,
            'slug' => $b->slug,
            'category' => $b->category?->name,
            'postcode' => $b->postcode,
        ];
    }
}
