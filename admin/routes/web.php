<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\ProductController;
use App\Http\Controllers\apps\BrandController;
use App\Http\Controllers\apps\CategoryController;
use App\Http\Controllers\apps\CustomerController;
use App\Http\Controllers\apps\OrderController;
use App\Http\Controllers\apps\EcommerceCustomerAll;
use App\Http\Controllers\apps\EcommerceCustomerDetailsOverview;
use App\Http\Controllers\apps\EcommerceCustomerDetailsSecurity;
use App\Http\Controllers\apps\EcommerceCustomerDetailsBilling;
use App\Http\Controllers\apps\EcommerceCustomerDetailsNotifications;
use App\Http\Controllers\apps\EcommerceManageReviews;
use App\Http\Controllers\apps\BranchController;
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
use App\Http\Controllers\apps\SettingController;
use App\Http\Controllers\apps\UserViewSecurity;
use App\Http\Controllers\apps\UserViewNotifications;
use App\Http\Controllers\apps\UserViewConnections;
use App\Http\Controllers\apps\RoleController;
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
    Route::middleware('permission:product.read')->group(function () {
        Route::get('/product', [ProductController::class, 'index'])->name('product.list');
        Route::get('/product/list/ajax', [ProductController::class, 'ajaxList'])->name('product.list.ajax');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
        Route::get('/product/search/ajax', [ProductController::class, 'searchAjax'])->name('product.search.ajax');
    });
    
    Route::middleware('permission:product.create')->group(function () {
        Route::get('/product/add', [ProductController::class, 'add'])->name('product.add');
        Route::post('/product/create', [ProductController::class, 'create'])->name('product.create');
    });

    Route::middleware('permission:product.write')->group(function () {
        Route::post('/product/update', [ProductController::class, 'update'])->name('product.update');
        Route::get('/product/delete/{id}', [ProductController::class, 'delete'])->name('product.delete');
    });

    Route::middleware('permission:brand.read')->group(function () {
        Route::get('/brand', [BrandController::class, 'index'])->name('brand.list');
       Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::get('/brand/list/ajax', [BrandController::class, 'ajaxList'])->name('brand.list.ajax');
    });

    Route::middleware('permission:brand.write')->group(function () {
         Route::post('/brand/update', [BrandController::class, 'update'])->name('brand.update');
         Route::get('/brand/delete/{id}', [BrandController::class, 'delete'])->name('brand.delete');
    });

    Route::middleware('permission:brand.create')->group(function () {
        Route::get('/brand/add', [BrandController::class, 'add'])->name('brand.add');
         Route::post('/brand/create', [BrandController::class, 'create'])->name('brand.create');
    });


    Route::middleware('permission:category.read')->group(function () {
        Route::get('/category', [CategoryController::class, 'index'])->name('category.list');
        Route::get('/category/list/ajax', [CategoryController::class, 'ajaxList'])->name('category.list.ajax');
        Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        
    });

    Route::middleware('permission:category.create')->group(function () {
        Route::get('/category/add', [CategoryController::class, 'add'])->name('category.add');
        Route::post('/category/create', [CategoryController::class, 'create'])->name('category.create');
    });

    Route::middleware('permission:category.write')->group(function () {
        Route::post('/category/update', [CategoryController::class, 'update'])->name('category.update');
        Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');
    });


    //Order
    Route::middleware('permission:order.read')->group(function () {
        Route::get('/order', [OrderController::class, 'index'])->name('order.list');
        Route::get('/order/details', [OrderController::class, 'getOrderDetails'])->name('order-details');
        Route::get('/order/edit/{id}', [OrderController::class, 'edit'])->name('order.edit');
        Route::get('/order/list/ajax', [OrderController::class, 'ajaxList'])->name('order.list.ajax');
        Route::get('/order/items/ajax', [OrderController::class, 'itemsAjax'])->name('order.items.ajax');
        Route::post('/order/update', [OrderController::class, 'update'])->name('order.update');
    });

    Route::middleware('permission:order.write')->group(function () {
        Route::get('/order/delete/{id}', [OrderController::class, 'delete'])->name('order.delete');
    });

    // Order Items CRUD
    Route::middleware('permission:order.write')->group(function () {
        Route::post('/order/item/create', [OrderController::class, 'createItem'])->name('order.item.create');
        Route::post('/order/item/update', [OrderController::class, 'updateItem'])->name('order.item.update');
        Route::delete('/order/item/delete/{id}', [OrderController::class, 'deleteItem'])->name('order.item.delete');
    });

    //Customer
    Route::middleware('permission:customer.read')->group(function () {
        Route::get('/customer', [CustomerController::class, 'index'])->name('customer.list');
        Route::get('/customer/list/ajax', [CustomerController::class, 'ajaxList'])->name('customer.list.ajax');
        Route::get('/customer/{id}/orders/ajax', [CustomerController::class, 'ordersAjax'])->name('customer.orders.ajax');
    });
    Route::middleware('permission:customer.write')->group(function () {
        Route::delete('/customer/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');
        Route::post('/customer/update/password', [CustomerController::class, 'updatePassword'])->name('customer.update-password');
    });
    

    // Invoice
    Route::middleware('permission:invoice.read')->group(function () {
        Route::get('/invoice', [InvoiceList::class, 'index'])->name('invoice-list.read');
        Route::get('/invoice/preview', [InvoicePreview::class, 'index'])->name('invoice-preview.read');
        Route::get('/invoice/print', [InvoicePrint::class, 'index'])->name('invoice-print.read');
        Route::get('/invoice/edit', [InvoiceEdit::class, 'index'])->name('invoice-edit.read');
        Route::get('/invoice/add', [InvoiceAdd::class, 'index'])->name('invoice-add.read');
    });

    Route::middleware('permission:invoice.create')->group(function () {
        Route::get('/invoice/add', [InvoiceAdd::class, 'index'])->name('invoice-add.read');
    });

    Route::middleware('permission:invoice.write')->group(function () {
        Route::get('/invoice/edit', [InvoiceEdit::class, 'index'])->name('invoice-edit.read');
    });



    // Users
    Route::middleware('permission:user.read')->group(function () {

        Route::get('/user', [UserController::class, 'index'])->name('user.list');
        Route::get('/user/list/ajax', [UserController::class, 'ajaxUserAll'])->name('user.list.ajax');
        
        Route::get('/user/ajax/show', [UserController::class, 'ajaxShow'])->name('user-ajax.show');

        Route::get('/user/view/account/{id}', [UserController::class, 'viewAccount'])->name('user-view-account.read');
        Route::get('/user/view/security/{id}', [UserController::class, 'viewSecurity'])->name('user-view-security.read');
        Route::get('/user/view/notifications/{id}', [UserController::class, 'viewNotifications'])->name('user-view-notifications.read');
    });

    Route::middleware('permission:user.write')->group(function () {
        Route::post('/user/update', [UserController::class, 'update'])->name('user.update');
        Route::post('/user/update/password', [UserController::class, 'updatePassword'])->name('user.update-password');
        Route::get('user/change/status/{id}', [UserController::class, 'changeStatus'])->name('user.change-status');
    });

    Route::middleware('permission:user.create')->group(function () {
        Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::get('/user/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
    });

    // Roles & Permissions
    Route::middleware('permission:role.read')->group(function () {
        Route::get('/role', [RoleController::class, 'index'])->name('role.list');
        Route::get('/role/show', [RoleController::class, 'show'])->name('role.show');
        Route::get('/user/ajax/list/with/roles', [UserController::class, 'ajaxUserListWithRoles'])->name('user.list.ajax.with.roles');
    });

    Route::middleware('permission:role.create')->group(function () {
        Route::post('/role/store', [RoleController::class, 'store'])->name('role.store');
    });

    Route::middleware('permission:role.write')->group(function () {
        Route::post('/role/update', [RoleController::class, 'update'])->name('role.update');
    });

    Route::get('/profile-user', [UserProfile::class, 'index'])->name('profile-user.read');
    Route::get('/profile-teams', [UserTeams::class, 'index'])->name('profile-teams.read');
    Route::get('/profile-projects', [UserProjects::class, 'index'])->name('profile-projects.read');
    Route::get('/profile-connections', [UserConnections::class, 'index'])->name('profile-connections.read');


    Route::get('/customer/{id?}/overview', [CustomerController::class, 'overview'])->name('customer.overview');
    Route::get('/customer/{id?}/security', [CustomerController::class, 'security'])->name('customer.security');
    Route::get('/customer/{id?}/branches', [CustomerController::class, 'branches'])->name('customer.branches');
    Route::get('/customer/{id?}/notifications', [CustomerController::class, 'notifications'])->name('customer.notifications');

    Route::post('/customer/branch/store', [BranchController::class, 'store'])->name('customer.branch.store');
    // Branch Management Routes - Temporarily without permission middleware for testing
    Route::get('/customer/branch/edit', [BranchController::class, 'edit'])->name('customer.branch.edit');
    Route::post('/customer/branch/update', [BranchController::class, 'update'])->name('customer.branch.update');
    Route::get('/customer/branch/{branch}/delete', [BranchController::class, 'destroy'])->name('customer.branch.delete');
    
    Route::post('/customer/store', [CustomerController::class, 'store'])->name('customer.store');
    Route::post('/customer/update', [CustomerController::class, 'update'])->name('customer.update');

    Route::middleware('permission:settings.read')->group(function () {
        Route::get('/settings', [SettingController::class, 'viewGeneralSettings'])->name('settings.general');
        Route::get('/settings/banner', [SettingController::class, 'viewBannerSettings'])->name('settings.banner');
        Route::get('/settings/maintenance', [SettingController::class, 'viewMaintenanceSettings'])->name('settings.maintenance');
        Route::get('/settings/delivery-method', [SettingController::class, 'viewDeliveryMethod'])->name('settings.deliveryMethod');
        Route::get('/settings/delivery-method/list/ajax', [SettingController::class, 'deliveryMethodListAjax'])->name('settings.deliveryMethod.list.ajax');
        Route::get('/settings/delivery-method/ajax/show', [SettingController::class, 'deliveryMethodShow'])->name('settings.deliveryMethod.ajax.show');
        // VAT Methods (read)
        Route::get('/settings/vat-method', [SettingController::class, 'viewVatMethod'])->name('settings.vatMethod');
        Route::get('/settings/vat-method/list/ajax', [SettingController::class, 'vatMethodListAjax'])->name('settings.vatMethod.list.ajax');
        Route::get('/settings/vat-method/ajax/show', [SettingController::class, 'vatMethodShow'])->name('settings.vatMethod.ajax.show');
    });

    Route::middleware('permission:settings.update')->group(function () {
        Route::post('/settings/general/update', [SettingController::class, 'updateGeneralSettings'])->name('settings.general.update');
        Route::post('/settings/banner/update', [SettingController::class, 'updateBannerSettings'])->name('settings.banner.update');
        Route::post('/settings/maintenance/update', [SettingController::class, 'updateMaintenanceSettings'])->name('settings.maintenance.update');
        Route::post('/settings/delivery-method/store', [SettingController::class, 'deliveryMethodStore'])->name('settings.deliveryMethod.store');
        Route::post('/settings/delivery-method/update', [SettingController::class, 'deliveryMethodUpdate'])->name('settings.deliveryMethod.update');
        // VAT Methods (write)
        Route::post('/settings/vat-method/store', [SettingController::class, 'vatMethodStore'])->name('settings.vatMethod.store');
        Route::post('/settings/vat-method/update', [SettingController::class, 'vatMethodUpdate'])->name('settings.vatMethod.update');
    });
});

Route::get('/', [AuthLoginController::class, 'show']);
// Auth routes (blank layout, custom blades)
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');
Route::get('/login', [AuthLoginController::class, 'show'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])->name('login.post');

Route::get('/register', [AuthRegisterController::class, 'show'])->name('register');
Route::post('/register', [AuthRegisterController::class, 'register'])->name('register.post');

////////////// End working routes

