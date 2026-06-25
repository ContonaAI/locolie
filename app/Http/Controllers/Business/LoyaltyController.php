<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyProgress;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Retailer-facing loyalty configuration. The signed-in business turns the scheme
 * on/off, manages its rules, and sees who's earning what. Scoped to the
 * authenticated business via the `business` guard.
 */
class LoyaltyController extends Controller
{
    protected function business()
    {
        return Auth::guard('business')->user();
    }

    public function index()
    {
        $business = $this->business();
        $program = $business->loyaltyProgram ?? new LoyaltyProgram(['active' => false]);
        $rules = $business->loyaltyRules()->get();

        // Top members by visits, plus rewards still to be claimed in store.
        $members = LoyaltyProgress::where('business_id', $business->id)
            ->orderByDesc('visits')->limit(50)->get();

        $rewards = LoyaltyReward::where('business_id', $business->id)
            ->where('status', 'earned')->latest('id')->limit(50)->get();

        return view('business.loyalty', compact('business', 'program', 'rules', 'members', 'rewards'));
    }

    /** Turn the scheme on/off and set the customer-facing copy. */
    public function saveProgram(Request $request)
    {
        $data = $request->validate([
            'active' => ['nullable', 'boolean'],
            'headline' => ['nullable', 'string', 'max:60'],
            'blurb' => ['nullable', 'string', 'max:140'],
            'terms' => ['nullable', 'string', 'max:2000'],
        ]);

        LoyaltyProgram::updateOrCreate(
            ['business_id' => $this->business()->id],
            [
                'active' => (bool) ($data['active'] ?? false),
                'headline' => $data['headline'] ?? null,
                'blurb' => $data['blurb'] ?? null,
                'terms' => $data['terms'] ?? null,
            ],
        );

        return back()->with('status', 'Loyalty scheme updated.');
    }

    /** Add a rule ("visit 5 times -> free coffee" or "spend £50 -> 10% off"). */
    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'metric' => ['required', 'in:visits,spend'],
            'threshold' => ['required', 'numeric', 'min:1'],
            'repeat' => ['nullable', 'boolean'],
            'reward_type' => ['required', 'in:free,percent,amount,gift'],
            'reward_value' => ['nullable', 'numeric', 'min:0'],
            'reward_label' => ['required', 'string', 'max:80'],
        ]);

        // Spend thresholds and amount rewards are entered in pounds, stored as pence.
        $threshold = $data['metric'] === 'spend'
            ? (int) round((float) $data['threshold'] * 100)
            : (int) $data['threshold'];

        $rewardValue = match ($data['reward_type']) {
            'amount' => (int) round((float) ($data['reward_value'] ?? 0) * 100),
            'percent' => (int) ($data['reward_value'] ?? 0),
            default => null,
        };

        LoyaltyRule::create([
            'business_id' => $this->business()->id,
            'active' => true,
            'sort' => (int) (LoyaltyRule::where('business_id', $this->business()->id)->max('sort') + 1),
            'name' => $data['name'],
            'metric' => $data['metric'],
            'threshold' => max(1, $threshold),
            'repeat' => (bool) ($data['repeat'] ?? false),
            'reward_type' => $data['reward_type'],
            'reward_value' => $rewardValue,
            'reward_label' => $data['reward_label'],
        ]);

        return back()->with('status', 'Rule added.');
    }

    public function toggleRule(LoyaltyRule $rule)
    {
        abort_unless($rule->business_id === $this->business()->id, 403);
        $rule->update(['active' => ! $rule->active]);

        return back()->with('status', 'Rule '.($rule->active ? 'activated' : 'paused').'.');
    }

    public function destroyRule(LoyaltyRule $rule)
    {
        abort_unless($rule->business_id === $this->business()->id, 403);
        $rule->delete();

        return back()->with('status', 'Rule removed.');
    }

    /** Mark a customer's earned reward as redeemed at the till. */
    public function redeemReward(LoyaltyReward $reward)
    {
        abort_unless($reward->business_id === $this->business()->id, 403);
        $reward->update(['status' => 'redeemed', 'redeemed_at' => now()]);

        return back()->with('status', 'Reward marked redeemed.');
    }
}
