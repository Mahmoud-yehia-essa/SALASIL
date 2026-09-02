<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TruckBrandController;
use App\Http\Controllers\Api\TruckModelController;
use App\Http\Controllers\Api\TruckTypeController;
use App\Http\Controllers\Api\TruckSubTypeController;
use App\Http\Controllers\Api\DriverTruckController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ShipmentTypeController;
use App\Http\Controllers\Api\ShipmentNatureController;
use App\Http\Controllers\Api\HsCodeController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\ShipmentTrackingController;
use App\Http\Controllers\Api\ShipmentInvitationController;
use App\Http\Controllers\Api\RouteController as ApiRouteController;
use App\Http\Controllers\Api\ScheduledTripController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes (Public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public Countries & Cities Data (For Registration & Address Selection)
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{id}', [CountryController::class, 'show']);
Route::get('/countries/{country_id}/cities', [CityController::class, 'getByCountry']);

Route::get('/cities', [CityController::class, 'index']);
Route::get('/cities/{id}', [CityController::class, 'show']);

// Public Trucks Data (For Registration & Selection)
Route::get('/truck-brands', [TruckBrandController::class, 'index']);
Route::get('/truck-brands/{id}', [TruckBrandController::class, 'show']);
Route::get('/truck-brands/{brand_id}/models', [TruckModelController::class, 'getByBrand']);

Route::get('/truck-models', [TruckModelController::class, 'index']);
Route::get('/truck-models/{id}', [TruckModelController::class, 'show']);

Route::get('/truck-types', [TruckTypeController::class, 'index']);
Route::get('/truck-types/{id}', [TruckTypeController::class, 'show']);
Route::get('/truck-types/{truck_type_id}/sub-types', [TruckSubTypeController::class, 'getByTruckType']);

Route::get('/truck-sub-types', [TruckSubTypeController::class, 'index']);
Route::get('/truck-sub-types/{id}', [TruckSubTypeController::class, 'show']);

// Public Shipment Configurations & HS Code Lookup
Route::get('/shipment-types', [ShipmentTypeController::class, 'index']);
Route::get('/shipment-types/{id}', [ShipmentTypeController::class, 'show']);

Route::get('/shipment-natures', [ShipmentNatureController::class, 'index']);
Route::get('/shipment-natures/{id}', [ShipmentNatureController::class, 'show']);

Route::post('/shipment/hscode/lookup', [HsCodeController::class, 'lookup']);

// Public Routes & Scheduled Trips (For Browsing & Quote Selection)
Route::get('/routes', [ApiRouteController::class, 'index']);
Route::get('/routes/statistics', [ApiRouteController::class, 'statistics']);
Route::get('/routes/{id}', [ApiRouteController::class, 'show']);

Route::get('/scheduled-trips', [ScheduledTripController::class, 'index']);
Route::get('/scheduled-trips/statistics', [ScheduledTripController::class, 'statistics']);
Route::get('/scheduled-trips/{id}', [ScheduledTripController::class, 'show']);

// Protected Routes (Requires Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    // Auth & User Profile
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/update', [ProfileController::class, 'updateProfile']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::post('/profile/update-firebase-token', [ProfileController::class, 'updateFirebaseToken']);

    // Countries Management
    Route::post('/countries', [CountryController::class, 'store']);
    Route::post('/countries/{id}/update', [CountryController::class, 'update']);
    Route::post('/countries/{id}/status', [CountryController::class, 'changeStatus']);
    Route::delete('/countries/{id}', [CountryController::class, 'destroy']);

    // Cities Management
    Route::post('/cities', [CityController::class, 'store']);
    Route::post('/cities/{id}/update', [CityController::class, 'update']);
    Route::post('/cities/{id}/status', [CityController::class, 'changeStatus']);
    Route::delete('/cities/{id}', [CityController::class, 'destroy']);

    // Truck Brands Management
    Route::post('/truck-brands', [TruckBrandController::class, 'store']);
    Route::post('/truck-brands/{id}/update', [TruckBrandController::class, 'update']);
    Route::post('/truck-brands/{id}/status', [TruckBrandController::class, 'changeStatus']);
    Route::delete('/truck-brands/{id}', [TruckBrandController::class, 'destroy']);

    // Truck Models Management
    Route::post('/truck-models', [TruckModelController::class, 'store']);
    Route::post('/truck-models/{id}/update', [TruckModelController::class, 'update']);
    Route::post('/truck-models/{id}/status', [TruckModelController::class, 'changeStatus']);
    Route::delete('/truck-models/{id}', [TruckModelController::class, 'destroy']);

    // Truck Types Management
    Route::post('/truck-types', [TruckTypeController::class, 'store']);
    Route::post('/truck-types/{id}/update', [TruckTypeController::class, 'update']);
    Route::post('/truck-types/{id}/status', [TruckTypeController::class, 'changeStatus']);
    Route::delete('/truck-types/{id}', [TruckTypeController::class, 'destroy']);

    // Truck Sub-Types Management
    Route::post('/truck-sub-types', [TruckSubTypeController::class, 'store']);
    Route::post('/truck-sub-types/{id}/update', [TruckSubTypeController::class, 'update']);
    Route::post('/truck-sub-types/{id}/status', [TruckSubTypeController::class, 'changeStatus']);
    Route::delete('/truck-sub-types/{id}', [TruckSubTypeController::class, 'destroy']);

    // Driver & Truck Assignments
    Route::get('/driver-trucks', [DriverTruckController::class, 'index']);
    Route::get('/driver-trucks/my-trucks', [DriverTruckController::class, 'myTrucks']);
    Route::get('/driver-trucks/{id}', [DriverTruckController::class, 'show']);
    Route::post('/driver-trucks', [DriverTruckController::class, 'store']);
    Route::post('/driver-trucks/{id}/update', [DriverTruckController::class, 'update']);
    Route::post('/driver-trucks/{id}/set-default', [DriverTruckController::class, 'setDefault']);
    Route::post('/driver-trucks/{id}/verify', [DriverTruckController::class, 'changeVerifiedStatus']);
    Route::delete('/driver-trucks/{id}', [DriverTruckController::class, 'destroy']);

    // Shipment Types Management
    Route::post('/shipment-types', [ShipmentTypeController::class, 'store']);
    Route::post('/shipment-types/{id}/update', [ShipmentTypeController::class, 'update']);
    Route::post('/shipment-types/{id}/status', [ShipmentTypeController::class, 'changeStatus']);
    Route::delete('/shipment-types/{id}', [ShipmentTypeController::class, 'destroy']);

    // Shipment Natures Management
    Route::post('/shipment-natures', [ShipmentNatureController::class, 'store']);
    Route::post('/shipment-natures/{id}/update', [ShipmentNatureController::class, 'update']);
    Route::post('/shipment-natures/{id}/status', [ShipmentNatureController::class, 'changeStatus']);
    Route::delete('/shipment-natures/{id}', [ShipmentNatureController::class, 'destroy']);

    // Shipments Core Management
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/my-shipments', [ShipmentController::class, 'myShipments']);
    Route::get('/shipments/statistics', [ShipmentController::class, 'statistics']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::post('/shipments/{id}/update', [ShipmentController::class, 'update']);
    Route::post('/shipments/{id}/status', [ShipmentController::class, 'changeStatus']);
    Route::post('/shipments/{id}/assign-driver', [ShipmentController::class, 'assignDriver']);
    Route::delete('/shipments/{id}', [ShipmentController::class, 'destroy']);

    // Live Shipment Tracking & Trajectory
    Route::get('/shipment-tracking', [ShipmentTrackingController::class, 'index']);
    Route::get('/shipment-tracking/{id}', [ShipmentTrackingController::class, 'show']);
    Route::post('/shipment-tracking/update-location', [ShipmentTrackingController::class, 'updateLocation']);
    Route::post('/shipment-tracking/update-status', [ShipmentTrackingController::class, 'updateStatus']);
    Route::get('/shipment-tracking/{id}/logs', [ShipmentTrackingController::class, 'getLogs']);
    Route::delete('/shipment-tracking/{id}/logs', [ShipmentTrackingController::class, 'clearLogs']);

    // Driver Invitations for Shipments
    Route::get('/shipment-invitations', [ShipmentInvitationController::class, 'index']);
    Route::get('/shipment-invitations/my-invitations', [ShipmentInvitationController::class, 'myInvitations']);
    Route::get('/shipment-invitations/{id}', [ShipmentInvitationController::class, 'show']);
    Route::post('/shipment-invitations', [ShipmentInvitationController::class, 'store']);
    Route::post('/shipment-invitations/{id}/update', [ShipmentInvitationController::class, 'update']);
    Route::post('/shipment-invitations/{id}/respond', [ShipmentInvitationController::class, 'respond']);
    Route::delete('/shipment-invitations/{id}', [ShipmentInvitationController::class, 'destroy']);

    // Routes Management
    Route::post('/routes', [ApiRouteController::class, 'store']);
    Route::post('/routes/{id}/update', [ApiRouteController::class, 'update']);
    Route::post('/routes/{id}/status', [ApiRouteController::class, 'changeStatus']);
    Route::delete('/routes/{id}', [ApiRouteController::class, 'destroy']);

    // Scheduled Trips Management
    Route::get('/scheduled-trips/my-trips', [ScheduledTripController::class, 'myTrips']);
    Route::post('/scheduled-trips', [ScheduledTripController::class, 'store']);
    Route::post('/scheduled-trips/{id}/update', [ScheduledTripController::class, 'update']);
    Route::post('/scheduled-trips/{id}/status', [ScheduledTripController::class, 'changeStatus']);
    Route::post('/scheduled-trips/{id}/assign-driver', [ScheduledTripController::class, 'assignDriver']);
    Route::delete('/scheduled-trips/{id}', [ScheduledTripController::class, 'destroy']);

    // Invoices Management
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/my-invoices', [InvoiceController::class, 'myInvoices']);
    Route::get('/invoices/statistics', [InvoiceController::class, 'statistics']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::post('/invoices/{id}/update', [InvoiceController::class, 'update']);
    Route::post('/invoices/{id}/status', [InvoiceController::class, 'changeStatus']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);

    // Payments Management
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/my-payments', [PaymentController::class, 'myPayments']);
    Route::get('/payments/statistics', [PaymentController::class, 'statistics']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::post('/payments/{id}/update', [PaymentController::class, 'update']);
    Route::post('/payments/{id}/status', [PaymentController::class, 'changeStatus']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);

    // Wallets & Balances Management
    Route::get('/wallets', [WalletController::class, 'index']);
    Route::get('/wallets/my-wallet', [WalletController::class, 'myWallet']);
    Route::get('/wallets/statistics', [WalletController::class, 'statistics']);
    Route::get('/wallets/transactions', [WalletController::class, 'transactions']);
    Route::get('/wallets/my-transactions', [WalletController::class, 'myTransactions']);
    Route::get('/wallets/{user_id}', [WalletController::class, 'show']);
    Route::post('/wallets/transactions', [WalletController::class, 'storeTransaction']);
    Route::post('/wallets/transactions/{id}/update', [WalletController::class, 'updateTransaction']);
    Route::delete('/wallets/transactions/{id}', [WalletController::class, 'deleteTransaction']);

    // Direct Chat & System Communications
    Route::get('/chat/conversations', [ChatController::class, 'index']);
    Route::get('/chat/statistics', [ChatController::class, 'statistics']);
    Route::post('/chat/conversations', [ChatController::class, 'store']);
    Route::get('/chat/conversations/{id}', [ChatController::class, 'show']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::post('/chat/conversations/{id}/status', [ChatController::class, 'toggleStatus']);
    Route::post('/chat/conversations/{id}/channel', [ChatController::class, 'changeChannel']);
    Route::delete('/chat/messages/{id}', [ChatController::class, 'deleteMessage']);
    Route::delete('/chat/conversations/{id}', [ChatController::class, 'destroy']);
});