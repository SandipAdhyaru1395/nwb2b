<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\ProductController;
use App\Http\Controllers\apps\BrandController;
use App\Http\Controllers\apps\CategoryController;
use App\Http\Controllers\apps\EcommerceOrderList;
use App\Http\Controllers\apps\EcommerceOrderDetails;
use App\Http\Controllers\apps\EcommerceCustomerAll;
use App\Http\Controllers\apps\EcommerceCustomerDetailsOverview;
use App\Http\Controllers\apps\EcommerceCustomerDetailsSecurity;
use App\Http\Controllers\apps\EcommerceCustomerDetailsBilling;
use App\Http\Controllers\apps\EcommerceCustomerDetailsNotifications;
use App\Http\Controllers\apps\EcommerceManageReviews;
use App\Http\Controllers\apps\EcommerceReferrals;
use App\Http\Controllers\apps\EcommerceSettingsDetails;
use App\Http\Controllers\apps\EcommerceSettingsPayments;
use App\Http\Controllers\apps\EcommerceSettingsCheckout;
use App\Http\Controllers\apps\EcommerceSettingsShipping;
use App\Http\Controllers\apps\EcommerceSettingsLocations;
use App\Http\Controllers\apps\EcommerceSettingsNotifications;
use App\Http\Controllers\apps\InvoiceList;
use App\Http\Controllers\apps\InvoicePreview;
use App\Http\Controllers\apps\InvoicePrint;
use App\Http\Controllers\apps\InvoiceEdit;
use App\Http\Controllers\apps\InvoiceAdd;
use App\Http\Controllers\apps\UserController;
use App\Http\Controllers\apps\UserViewAccount;
use App\Http\Controllers\apps\UserViewSecurity;
use App\Http\Controllers\apps\UserViewBilling;
use App\Http\Controllers\apps\UserViewNotifications;
use App\Http\Controllers\apps\UserViewConnections;
use App\Http\Controllers\apps\AccessRoles;
use App\Http\Controllers\pages\UserProfile;
use App\Http\Controllers\pages\UserTeams;
use App\Http\Controllers\pages\UserProjects;
use App\Http\Controllers\pages\UserConnections;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Auth\RegisterController as AuthRegisterController;

// Current Working Routes

//dashboard
Route::middleware(['auth', 'sidebar'])->group(function () {

    Route::middleware('permission:dashboard.read')->group(function () {
        Route::get('/dashboard', [EcommerceDashboard::class, 'index'])->name('dashboard.read');
    });

    // Product
    Route::middleware('permission:product-list.read')->group(function () {
        Route::get('/product', [ProductController::class, 'index'])->name('product.list');
        Route::get('/product/list/ajax', [ProductController::class, 'ajaxList'])->name('product.list.ajax');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
    });
    
    Route::middleware('permission:product-list.create')->group(function () {
        Route::get('/product/add', [ProductController::class, 'add'])->name('product.add');
        Route::post('/product/create', [ProductController::class, 'create'])->name('product.create');
    });

    Route::middleware('permission:product-list.write')->group(function () {
        Route::post('/product/update', [ProductController::class, 'update'])->name('product.update');
    });

    Route::middleware('permission:brand-list.read')->group(function () {
        Route::get('/brand', [BrandController::class, 'index'])->name('brand.list');
       Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::get('/brand/list/ajax', [BrandController::class, 'ajaxList'])->name('brand.list.ajax');
    });

    Route::middleware('permission:brand-list.write')->group(function () {
         Route::post('/brand/update', [BrandController::class, 'update'])->name('brand.update');
    });

    Route::middleware('permission:brand-list.create')->group(function () {
        Route::get('/brand/add', [BrandController::class, 'add'])->name('brand.add');
         Route::post('/brand/create', [BrandController::class, 'create'])->name('brand.create');
    });


    Route::middleware('permission:category-list.read')->group(function () {
        Route::get('/category', [CategoryController::class, 'index'])->name('category.list');
        Route::get('/category/list/ajax', [CategoryController::class, 'ajaxList'])->name('category.list.ajax');
        Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        
    });

    Route::middleware('permission:category-list.create')->group(function () {
        Route::get('/category/add', [CategoryController::class, 'add'])->name('category.add');
        Route::post('/category/create', [CategoryController::class, 'create'])->name('category.create');
    });

    Route::middleware('permission:category-list.write')->group(function () {
        Route::post('/category/update', [CategoryController::class, 'update'])->name('category.update');
    });


    //Order
    Route::middleware('permission:order-list.read')->group(function () {
        Route::get('/order', [EcommerceOrderList::class, 'index'])->name('order-list.read');
    });

    Route::middleware('permission:order-details.read')->group(function () {
        Route::get('/order/details', [EcommerceOrderDetails::class, 'index'])->name('order-details.read');
    });

    //Customer
    Route::middleware('permission:customer-all.read')->group(function () {
        Route::get('/customer', [EcommerceCustomerAll::class, 'index'])->name('customer-all.read');
    });

    Route::middleware('permission:manage-reviews.read')->group(function () {
        Route::get('/manage/reviews', [EcommerceManageReviews::class, 'index'])->name('manage-reviews.read');
    });

    Route::middleware('permission:referrals.read')->group(function () {
        Route::get('/referrals', [EcommerceReferrals::class, 'index'])->name('referrals.read');
    });

    Route::middleware('permission:settings-details.read')->group(function () {
        Route::get('/settings/details', [EcommerceSettingsDetails::class, 'index'])->name('settings-details.read');
    });

    Route::middleware('permission:settings-payments.read')->group(function () {
        Route::get('/settings/payments', [EcommerceSettingsPayments::class, 'index'])->name('settings-payments.read');
    });

    Route::middleware('permission:settings-checkout.read')->group(function () {
        Route::get('/settings/checkout', [EcommerceSettingsCheckout::class, 'index'])->name('settings-checkout.read');
    });

    Route::middleware('permission:settings-shipping.read')->group(function () {
        Route::get('/settings/shipping', [EcommerceSettingsShipping::class, 'index'])->name('settings-shipping.read');
    });

    Route::middleware('permission:settings-locations.read')->group(function () {
        Route::get('/settings/locations', [EcommerceSettingsLocations::class, 'index'])->name('settings-locations.read');
    });

    Route::middleware('permission:settings-notifications.read')->group(function () {
        Route::get('/settings/notifications', [EcommerceSettingsNotifications::class, 'index'])->name('settings-notifications.read');
    });


    // Invoice
    Route::middleware('permission:invoice-list.read')->group(function () {
        Route::get('/invoice', [InvoiceList::class, 'index'])->name('invoice-list.read');
    });

    Route::middleware('permission:invoice-preview.read')->group(function () {
        Route::get('/invoice/preview', [InvoicePreview::class, 'index'])->name('invoice-preview.read');
    });

    Route::middleware('permission:invoice-print.read')->group(function () {
        Route::get('/invoice/print', [InvoicePrint::class, 'index'])->name('invoice-print.read');
    });

    Route::middleware('permission:invoice-edit.read')->group(function () {
        Route::get('/invoice/edit', [InvoiceEdit::class, 'index'])->name('invoice-edit.read');
    });

    Route::middleware('permission:invoice-add.read')->group(function () {
        Route::get('/invoice/add', [InvoiceAdd::class, 'index'])->name('invoice-add.read');
    });


    // Users
    Route::middleware('permission:user-list.read')->group(function () {

        Route::get('/user', [UserController::class, 'index'])->name('user.list');
        Route::get('/user/list/ajax', [UserController::class, 'ajaxUserAll'])->name('user.list.ajax');

        Route::get('/user/ajax/list/with/roles', [UserController::class, 'ajaxUserListWithRoles'])->name('user.list.ajax.with.roles');
        
        Route::get('/user/ajax/show', [UserController::class, 'ajaxShow'])->name('user-ajax.show');

        Route::get('/user/view/account/{id}', [UserController::class, 'viewAccount'])->name('user-view-account.read');
        Route::get('/user/view/security/{id}', [UserController::class, 'viewSecurity'])->name('user-view-security.read');
        Route::get('/user/view/billing/{id}', [UserController::class, 'viewBilling'])->name('user-view-billing.read');
        Route::get('/user/view/notifications/{id}', [UserController::class, 'viewNotifications'])->name('user-view-notifications.read');
        Route::get('/user/view/connections/{id}', [UserController::class, 'viewConnections'])->name('user-view-connections.read');
    });

    Route::middleware('permission:user-list.write')->group(function () {
        Route::post('/user/update', [UserController::class, 'update'])->name('user.update');
        Route::post('/user/update/password', [UserController::class, 'updatePassword'])->name('user.update-password');
        Route::get('user/change/status/{id}', [UserController::class, 'changeStatus'])->name('user.change-status');
    });

    Route::middleware('permission:user-list.create')->group(function () {
        Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::get('/user/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
    });

    // Roles & Permissions
    Route::middleware('permission:access-roles.read')->group(function () {
        Route::get('/access-roles', [AccessRoles::class, 'index'])->name('access-roles.read');
        Route::get('/access-roles/show', [AccessRoles::class, 'show'])->name('access-roles.show');
    });

    Route::middleware('permission:access-roles.create')->group(function () {
        Route::post('/access-roles/store', [AccessRoles::class, 'store'])->name('access-roles.store');
    });

    Route::middleware('permission:access-roles.write')->group(function () {
        Route::post('/access-roles/update', [AccessRoles::class, 'update'])->name('access-roles.update');
    });

    Route::get('/profile-user', [UserProfile::class, 'index'])->name('profile-user.read');
    Route::get('/profile-teams', [UserTeams::class, 'index'])->name('profile-teams.read');
    Route::get('/profile-projects', [UserProjects::class, 'index'])->name('profile-projects.read');
    Route::get('/profile-connections', [UserConnections::class, 'index'])->name('profile-connections.read');


    Route::get('/customer/details/overview', [EcommerceCustomerDetailsOverview::class, 'index'])->name('app-ecommerce-customer-details-overview');
    Route::get('/customer/details/security', [EcommerceCustomerDetailsSecurity::class, 'index'])->name('app-ecommerce-customer-details-security');
    Route::get('/customer/details/billing', [EcommerceCustomerDetailsBilling::class, 'index'])->name('app-ecommerce-customer-details-billing');
    Route::get('/customer/details/notifications', [EcommerceCustomerDetailsNotifications::class, 'index'])->name('app-ecommerce-customer-details-notifications');
});

Route::get('/', [AuthLoginController::class, 'show']);
// Auth routes (blank layout, custom blades)
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');
Route::get('/login', [AuthLoginController::class, 'show'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])->name('login.post');

Route::get('/register', [AuthRegisterController::class, 'show'])->name('register');
Route::post('/register', [AuthRegisterController::class, 'register'])->name('register.post');

////////////// End working routes

