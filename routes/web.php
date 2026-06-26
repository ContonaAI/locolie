<?php

use App\Http\Controllers\BusinessPortalController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Public presentation / marketing site (ngrok root = our showcase) ─────────
Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/for-business', [SiteController::class, 'forBusiness'])->name('site.for-business');
Route::get('/category/{slug}', [SiteController::class, 'category'])->name('site.category');
Route::get('/shop/{slug}', [SiteController::class, 'business'])->name('site.business');

// ── Programmatic local SEO: "{category} in {area}" landing pages + hubs ──────
Route::get('/local', [\App\Http\Controllers\SeoController::class, 'index'])->name('seo.index');
Route::get('/local/{area}', [\App\Http\Controllers\SeoController::class, 'area'])->name('seo.area');
Route::get('/local/{area}/{category}', [\App\Http\Controllers\SeoController::class, 'categoryInArea'])->name('seo.landing');

// ── Legal ────────────────────────────────────────────────────────────────────
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/cookies', [LegalController::class, 'cookies'])->name('legal.cookies');

// ── Subscription preferences + one-click unsubscribe (signed, login-free) ─────
Route::get('/preferences', [SubscriptionController::class, 'preferences'])->name('subscriptions.preferences');
Route::post('/preferences', [SubscriptionController::class, 'update'])->name('subscriptions.update');
Route::match(['get', 'post'], '/unsubscribe', [SubscriptionController::class, 'unsubscribe'])->name('subscriptions.unsubscribe');

// ── Google Search Console: serve the googleXXXX.html ownership file ──────────
Route::get('/{gscfile}', [\App\Http\Controllers\SearchConsoleController::class, 'file'])
    ->where('gscfile', 'google[A-Za-z0-9_]+\.html')
    ->name('gsc.file');

// ── SEO: robots + sitemap ────────────────────────────────────────────────────
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /portal\nDisallow: /admin\nDisallow: /business\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']));
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// ── The app (same responsive build; consumer + business roles) ───────────────
Route::get('/app', [PortalController::class, 'mobile'])->name('app');
// Back-compat: the old /m URL now redirects to the clean /app (preserving query).
Route::get('/m', fn (\Illuminate\Http\Request $r) => redirect('/app'.($r->getQueryString() ? '?'.$r->getQueryString() : '')));

// ── Business self-serve CRM (email + password) ───────────────────────────────
// Retailer onboarding lives at /business/join (USPs + inline sign-in / sign-up).
// /business/login is kept as a back-compat alias so existing links keep working.
Route::get('/business/join', [BusinessPortalController::class, 'onboard'])->name('business.join');
Route::get('/business/login', fn () => redirect()->route('business.join'))->name('business.login');
Route::post('/business/login', [BusinessPortalController::class, 'login'])->middleware('throttle:5,1')->name('business.login.submit');
Route::post('/business/register', [BusinessPortalController::class, 'register'])->middleware('throttle:5,1')->name('business.register.submit');
Route::post('/business/logout', [BusinessPortalController::class, 'logout'])->name('business.logout');
Route::middleware('auth:business')->group(function () {
    Route::get('/business', [BusinessPortalController::class, 'dashboard'])->name('business.dashboard');
    Route::post('/business/listing', [BusinessPortalController::class, 'updateListing'])->name('business.listing');
    Route::post('/business/upgrade', [BusinessPortalController::class, 'upgrade'])->name('business.upgrade');
    Route::get('/business/upgrade/success', [BusinessPortalController::class, 'upgradeSuccess'])->name('business.upgrade.success');
    Route::get('/business/customers.csv', [BusinessPortalController::class, 'exportCustomers'])->name('business.customers.export');
    Route::post('/business/customers/email', [BusinessPortalController::class, 'emailCustomers'])->name('business.customers.email');

    // Retailer reporting suite
    Route::get('/business/reports', [BusinessPortalController::class, 'reports'])->name('business.reports');
    Route::get('/business/reports.csv', [BusinessPortalController::class, 'reportsExport'])->name('business.reports.export');

    // Loyalty scheme: customisable rules engine the retailer configures.
    Route::get('/business/loyalty', [\App\Http\Controllers\Business\LoyaltyController::class, 'index'])->name('business.loyalty');
    Route::post('/business/loyalty', [\App\Http\Controllers\Business\LoyaltyController::class, 'saveProgram'])->name('business.loyalty.save');
    Route::post('/business/loyalty/rules', [\App\Http\Controllers\Business\LoyaltyController::class, 'storeRule'])->name('business.loyalty.rules.store');
    Route::post('/business/loyalty/rules/{rule}/toggle', [\App\Http\Controllers\Business\LoyaltyController::class, 'toggleRule'])->name('business.loyalty.rules.toggle');
    Route::delete('/business/loyalty/rules/{rule}', [\App\Http\Controllers\Business\LoyaltyController::class, 'destroyRule'])->name('business.loyalty.rules.destroy');
    Route::post('/business/loyalty/rewards/{reward}/redeem', [\App\Http\Controllers\Business\LoyaltyController::class, 'redeemReward'])->name('business.loyalty.rewards.redeem');

    // Retailer self-serve messaging: brand + send email/SMS/push to own customers
    Route::get('/business/messaging', [BusinessPortalController::class, 'messaging'])->name('business.messaging');
    Route::post('/business/brand', [BusinessPortalController::class, 'saveBrand'])->name('business.brand');
    Route::post('/business/messaging/preview', [BusinessPortalController::class, 'messagingPreview'])->name('business.messaging.preview');
    Route::post('/business/messaging/send', [BusinessPortalController::class, 'messagingSend'])->name('business.messaging.send');
});

// ── Email open + click tracking (encrypted tokens, public) ───────────────────
Route::get('/e/open', [\App\Http\Controllers\TrackingController::class, 'open'])->name('track.open');
Route::get('/e/click', [\App\Http\Controllers\TrackingController::class, 'click'])->name('track.click');

// ── Customer-facing report ("Your locolie" - savings & impact) ───────────────
Route::get('/my-locolie', [\App\Http\Controllers\CustomerReportController::class, 'entry'])->name('customer.report.entry');
Route::post('/my-locolie', [\App\Http\Controllers\CustomerReportController::class, 'lookup'])->name('customer.report.lookup');
Route::get('/my-locolie/view', [\App\Http\Controllers\CustomerReportController::class, 'show'])->name('customer.report')->middleware('signed');

// ── QR window-sticker deep link + printable sticker ──────────────────────────
Route::get('/c/{token}', [PortalController::class, 'qrRedirect'])->name('qr.redirect');
Route::get('/s/{secret}', [PortalController::class, 'sticker'])->name('qr.sticker');

// ── Internal team portal (design / brand / admin CRM / ideas) ────────────────
Route::get('/login', [PortalController::class, 'loginForm'])->name('portal.login');
Route::post('/login', [PortalController::class, 'login'])->middleware('throttle:5,1')->name('portal.login.submit');
Route::post('/logout', [PortalController::class, 'logout'])->name('portal.logout');

Route::middleware('portal')->group(function () {
    Route::get('/portal', [PortalController::class, 'home'])->name('portal.home');
    Route::get('/business-plan', [PortalController::class, 'businessPlan'])->name('portal.plan');
    Route::get('/brand', [PortalController::class, 'brand'])->name('portal.brand');
    Route::get('/design', [PortalController::class, 'design'])->name('portal.design');
    Route::get('/design/raw', [PortalController::class, 'designRaw'])->name('portal.design.raw');
    Route::get('/mockups', [PortalController::class, 'mockups'])->name('portal.mockups');
    Route::post('/mockups', [PortalController::class, 'uploadMockup'])->name('portal.mockups.upload');
    Route::get('/ideas', [PortalController::class, 'ideas'])->name('portal.ideas');
    Route::post('/ideas', [PortalController::class, 'storeIdea'])->name('portal.ideas.store');
    Route::delete('/ideas/{idea}', [PortalController::class, 'deleteIdea'])->name('portal.ideas.delete');

    // Platform reporting (team)
    Route::get('/reports', [\App\Http\Controllers\ReportsController::class, 'platform'])->name('portal.reports');

    // Go-live / integration setup status
    Route::get('/setup', [\App\Http\Controllers\SetupController::class, 'index'])->name('portal.setup');

    // Google Search Console verification settings
    Route::post('/admin/search-console', [\App\Http\Controllers\SearchConsoleController::class, 'save'])->name('admin.search-console');

    // Custom <head> scripts (analytics / pixels), injected site-wide
    Route::post('/admin/scripts', [\App\Http\Controllers\ScriptsController::class, 'save'])->name('admin.scripts');

    // Admin CRM
    Route::get('/admin', [PortalController::class, 'admin'])->name('portal.admin');
    Route::get('/admin/settings', [PortalController::class, 'settings'])->name('portal.settings');
    Route::post('/admin/business/{business}/onboard', [PortalController::class, 'adminToggleOnboard'])->name('admin.onboard');
    Route::post('/admin/business/{business}/plan', [PortalController::class, 'adminSetPlan'])->name('admin.plan');
    Route::post('/admin/prospect/search', [PortalController::class, 'adminProspectSearch'])->name('admin.prospect.search');
    Route::post('/admin/prospect/add', [PortalController::class, 'adminAddProspect'])->name('admin.prospect.add');
    Route::post('/admin/campaign', [PortalController::class, 'adminSendCampaign'])->name('admin.campaign');

    // ── Messaging Studio: branded email / SMS / push across web + native ─────
    Route::get('/messaging', [\App\Http\Controllers\MessagingController::class, 'studio'])->name('messaging.studio');
    Route::post('/messaging/brand/{business}', [\App\Http\Controllers\MessagingController::class, 'saveBrand'])->name('messaging.brand');
    Route::post('/messaging/connect', [\App\Http\Controllers\MessagingController::class, 'connect'])->name('messaging.connect');
    Route::post('/messaging/disconnect', [\App\Http\Controllers\MessagingController::class, 'disconnect'])->name('messaging.disconnect');

    // Email channel
    Route::get('/messaging/email', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'index'])->name('messaging.email');
    Route::post('/messaging/email/preview', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'preview'])->name('messaging.email.preview');
    Route::post('/messaging/email/test', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'test'])->name('messaging.email.test');
    Route::post('/messaging/email/send', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'send'])->name('messaging.email.send');
    Route::get('/messaging/email/connect/google', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'connectGoogle'])->name('messaging.email.google');
    Route::get('/messaging/email/google/callback', [\App\Http\Controllers\Messaging\EmailStudioController::class, 'googleCallback'])->name('messaging.email.google.callback');

    // SMS channel
    Route::get('/messaging/sms', [\App\Http\Controllers\Messaging\SmsStudioController::class, 'index'])->name('messaging.sms');
    Route::post('/messaging/sms/preview', [\App\Http\Controllers\Messaging\SmsStudioController::class, 'preview'])->name('messaging.sms.preview');
    Route::post('/messaging/sms/test', [\App\Http\Controllers\Messaging\SmsStudioController::class, 'test'])->name('messaging.sms.test');
    Route::post('/messaging/sms/send', [\App\Http\Controllers\Messaging\SmsStudioController::class, 'send'])->name('messaging.sms.send');

    // Push channel
    Route::get('/messaging/push', [\App\Http\Controllers\Messaging\PushStudioController::class, 'index'])->name('messaging.push');
    Route::post('/messaging/push/preview', [\App\Http\Controllers\Messaging\PushStudioController::class, 'preview'])->name('messaging.push.preview');
    Route::post('/messaging/push/test', [\App\Http\Controllers\Messaging\PushStudioController::class, 'test'])->name('messaging.push.test');
    Route::post('/messaging/push/send', [\App\Http\Controllers\Messaging\PushStudioController::class, 'send'])->name('messaging.push.send');
});
