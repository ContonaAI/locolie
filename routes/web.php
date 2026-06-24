<?php

use App\Http\Controllers\BusinessPortalController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// ── Public presentation / marketing site (ngrok root = our showcase) ─────────
Route::get('/', [SiteController::class, 'home'])->name('site.home');
Route::get('/for-business', [SiteController::class, 'forBusiness'])->name('site.for-business');
Route::get('/category/{slug}', [SiteController::class, 'category'])->name('site.category');
Route::get('/shop/{slug}', [SiteController::class, 'business'])->name('site.business');

// ── SEO: robots + sitemap ────────────────────────────────────────────────────
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /portal\nDisallow: /admin\nDisallow: /business\nSitemap: ".url('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain']));
Route::get('/sitemap.xml', function () {
    $urls = ['/', '/for-business', '/app'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) {
        $xml .= '<url><loc>'.url($u).'</loc><changefreq>weekly</changefreq></url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

// ── The app (same responsive build; consumer + business roles) ───────────────
Route::get('/app', [PortalController::class, 'mobile'])->name('app');
// Back-compat: the old /m URL now redirects to the clean /app (preserving query).
Route::get('/m', fn (\Illuminate\Http\Request $r) => redirect('/app'.($r->getQueryString() ? '?'.$r->getQueryString() : '')));

// ── Business self-serve CRM (email + password) ───────────────────────────────
Route::get('/business/login', [BusinessPortalController::class, 'showLogin'])->name('business.login');
Route::post('/business/login', [BusinessPortalController::class, 'login'])->name('business.login.submit');
Route::post('/business/logout', [BusinessPortalController::class, 'logout'])->name('business.logout');
Route::middleware('auth:business')->group(function () {
    Route::get('/business', [BusinessPortalController::class, 'dashboard'])->name('business.dashboard');
    Route::post('/business/listing', [BusinessPortalController::class, 'updateListing'])->name('business.listing');
    Route::post('/business/upgrade', [BusinessPortalController::class, 'upgrade'])->name('business.upgrade');
    Route::get('/business/upgrade/success', [BusinessPortalController::class, 'upgradeSuccess'])->name('business.upgrade.success');
    Route::get('/business/customers.csv', [BusinessPortalController::class, 'exportCustomers'])->name('business.customers.export');
    Route::post('/business/customers/email', [BusinessPortalController::class, 'emailCustomers'])->name('business.customers.email');
});

// ── QR window-sticker deep link + printable sticker ──────────────────────────
Route::get('/c/{token}', [PortalController::class, 'qrRedirect'])->name('qr.redirect');
Route::get('/s/{secret}', [PortalController::class, 'sticker'])->name('qr.sticker');

// ── Internal team portal (design / brand / admin CRM / ideas) ────────────────
Route::get('/login', [PortalController::class, 'loginForm'])->name('portal.login');
Route::post('/login', [PortalController::class, 'login'])->name('portal.login.submit');
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

    // Admin CRM
    Route::get('/admin', [PortalController::class, 'admin'])->name('portal.admin');
    Route::get('/admin/settings', [PortalController::class, 'settings'])->name('portal.settings');
    Route::post('/admin/business/{business}/onboard', [PortalController::class, 'adminToggleOnboard'])->name('admin.onboard');
    Route::post('/admin/business/{business}/plan', [PortalController::class, 'adminSetPlan'])->name('admin.plan');
    Route::post('/admin/prospect/search', [PortalController::class, 'adminProspectSearch'])->name('admin.prospect.search');
    Route::post('/admin/prospect/add', [PortalController::class, 'adminAddProspect'])->name('admin.prospect.add');
    Route::post('/admin/campaign', [PortalController::class, 'adminSendCampaign'])->name('admin.campaign');
});
