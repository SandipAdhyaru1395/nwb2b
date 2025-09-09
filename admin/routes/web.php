<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\EcommerceProductList;
use App\Http\Controllers\apps\EcommerceCollectionList;
use App\Http\Controllers\apps\EcommerceProductAdd;
use App\Http\Controllers\apps\EcommerceProductCategory;
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
use App\Http\Controllers\apps\UserList;
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
        Route::get('/product/list', [EcommerceProductList::class, 'index'])->name('product-list');
        Route::get('/product/list/ajax', [EcommerceProductList::class, 'ajaxList'])->name('product-ajax-list');
        Route::get('/product/edit/{id}', [EcommerceProductAdd::class, 'edit'])->name('product.edit');
    });

    Route::middleware('permission:product-add.read')->group(function () {
        Route::get('/product/add', [EcommerceProductAdd::class, 'index'])->name('product-add.read');
    });

    Route::middleware('permission:product-add.create')->group(function () {
        Route::post('/product/create', [EcommerceProductAdd::class, 'create'])->name('product.create');
    });

    Route::middleware('permission:product-list.write')->group(function () {
        Route::get('/product/status/change/{id}', [EcommerceProductList::class, 'changeStatus'])->name('product.change.status');
        Route::post('/product/update', [EcommerceProductAdd::class, 'update'])->name('product.update');
    });

    Route::middleware('permission:collection-list.read')->group(function () {
        Route::get('/collection/list', [EcommerceCollectionList::class, 'index'])->name('collection-list');
       Route::get('/collection/edit/{id}', [EcommerceCollectionList::class, 'edit'])->name('collection.edit');
        Route::get('/collection/list/ajax', [EcommerceCollectionList::class, 'ajaxList'])->name('collection-ajax-list');
    });

    Route::middleware('permission:collection-list.write')->group(function () {
         Route::post('/collection/update', [EcommerceCollectionList::class, 'update'])->name('collection.update');
    });

    Route::middleware('permission:collection-list.create')->group(function () {
        Route::get('/collection/add', [EcommerceCollectionList::class, 'add'])->name('collection.add');
         Route::post('/collection/create', [EcommerceCollectionList::class, 'create'])->name('collection.create');
    });


    Route::middleware('permission:product-category.read')->group(function () {
        Route::get('/product/category', [EcommerceProductCategory::class, 'index'])->name('category.read');
    });

    Route::middleware('permission:product-category.create')->group(function () {
        Route::post('/category/create', [EcommerceProductCategory::class, 'create'])->name('category.create');
    });

    Route::middleware('permission:product-category.write')->group(function () {
        Route::post('/category/update', [EcommerceProductCategory::class, 'update'])->name('category.update');
    });


    //Order
    Route::middleware('permission:order-list.read')->group(function () {
        Route::get('/order/list', [EcommerceOrderList::class, 'index'])->name('order-list.read');
    });

    Route::middleware('permission:order-details.read')->group(function () {
        Route::get('/order/details', [EcommerceOrderDetails::class, 'index'])->name('order-details.read');
    });

    //Customer
    Route::middleware('permission:customer-all.read')->group(function () {
        Route::get('/customer/all', [EcommerceCustomerAll::class, 'index'])->name('customer-all.read');
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
        Route::get('/invoice/list', [InvoiceList::class, 'index'])->name('invoice-list.read');
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

        Route::get('/user/list', [UserList::class, 'index'])->name('user-list.read');
        Route::get('/ajax/user/list/with/roles', [UserList::class, 'ajaxUserListWithRoles'])->name('user-ajax.read');
        Route::get('/ajax/user/list/all', [UserList::class, 'ajaxUserAll'])->name('user-ajax.all');
        Route::get('/user/ajax/show', [UserList::class, 'ajaxShow'])->name('user-ajax.show');

        Route::get('/user/view/account/{id}', [UserViewAccount::class, 'index'])->name('user-view-account.read');
        Route::get('/user/view/security/{id}', [UserViewSecurity::class, 'index'])->name('user-view-security.read');
        Route::get('/user/view/billing/{id}', [UserViewBilling::class, 'index'])->name('user-view-billing.read');
        Route::get('/user/view/notifications/{id}', [UserViewNotifications::class, 'index'])->name('user-view-notifications.read');
        Route::get('/user/view/connections/{id}', [UserViewConnections::class, 'index'])->name('user-view-connections.read');
    });

    Route::middleware('permission:user-list.write')->group(function () {
        Route::post('/user/update', [UserList::class, 'update'])->name('user.update');
        Route::post('/user/update/password', [UserList::class, 'updatePassword'])->name('user.update-password');
        Route::get('user/change/status/{id}', [UserList::class, 'changeStatus'])->name('user.change-status');

    });

    Route::middleware('permission:user-list.create')->group(function () {
        Route::post('/user/create', [UserList::class, 'create'])->name('user.create');
        Route::get('/user/delete/{id}', [UserList::class, 'delete'])->name('user.delete');
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


    Route::get('/app/ecommerce/customer/details/overview', [EcommerceCustomerDetailsOverview::class, 'index'])->name('app-ecommerce-customer-details-overview');
    Route::get('/app/ecommerce/customer/details/security', [EcommerceCustomerDetailsSecurity::class, 'index'])->name('app-ecommerce-customer-details-security');
    Route::get('/app/ecommerce/customer/details/billing', [EcommerceCustomerDetailsBilling::class, 'index'])->name('app-ecommerce-customer-details-billing');
    Route::get('/app/ecommerce/customer/details/notifications', [EcommerceCustomerDetailsNotifications::class, 'index'])->name('app-ecommerce-customer-details-notifications');
});

Route::get('/', [AuthLoginController::class, 'show']);
// Auth routes (blank layout, custom blades)
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');
Route::get('/login', [AuthLoginController::class, 'show'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])->name('login.post');

Route::get('/register', [AuthRegisterController::class, 'show'])->name('register');
Route::post('/register', [AuthRegisterController::class, 'register'])->name('register.post');

////////////// End working routes

