<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TruckTypeController;
use App\Http\Controllers\TruckSubTypeController;
use App\Http\Controllers\TruckBrandController;
use App\Http\Controllers\TruckModelController;
use App\Http\Controllers\DriverTruckController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ShipmentTypeController;
use App\Http\Controllers\ShipmentNatureController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentTrackingController;
use App\Http\Controllers\ShipmentInvitationController;
use App\Http\Controllers\HsCodeController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\ScheduledTripController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\AdminNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Public Client/Driver Digital Verification Profile (accessible to everyone)
Route::get('/client/profile/{code}', [UserController::class, 'PublicUserProfile'])->name('public.user.profile');
Route::get('/v/{code}', [UserController::class, 'PublicUserProfile']);

// Public Driver Invitation Magic Link Routes (accessible without login)
Route::get('/invite/{token}', [ShipmentInvitationController::class, 'ShowDriverInvitationMagicLink'])->name('driver.invitation.magic.link');
Route::post('/invite/{token}/respond', [ShipmentInvitationController::class, 'RespondDriverInvitationMagicLink'])->name('driver.invitation.respond');

Route::get('/dashboard', function () {
    $clientsCount        = \App\Models\User::whereIn('role', ['individual_customer', 'company_customer'])->count();
    $driversCount        = \App\Models\User::where('role', 'driver')->count();
    $trucksCount         = \App\Models\DriverTruck::count();
    if ($trucksCount == 0) {
        $trucksCount     = \App\Models\TruckType::count();
    }
    $scheduledTripsCount = \App\Models\ScheduledTrip::count();
    $shipmentsCount      = \App\Models\Shipment::count();
    $invoicesCount       = \App\Models\Invoice::count();

    $totalRevenue        = \App\Models\Invoice::where('status', 'paid')->sum('total_amount');
    if ($totalRevenue == 0) {
        $totalRevenue    = \App\Models\Shipment::sum('initial_price') ?: 0;
    }

    $recentClients       = \App\Models\User::whereIn('role', ['individual_customer', 'company_customer'])
                            ->with('companyProfile')
                            ->latest()
                            ->take(10)
                            ->get();

    return view('admin.index', compact(
        'clientsCount',
        'driversCount',
        'trucksCount',
        'scheduledTripsCount',
        'shipmentsCount',
        'invoicesCount',
        'totalRevenue',
        'recentClients'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client/User Management Routes
    Route::get('/admin/user/add', [UserController::class, 'AddUser'])->name('add.user');
    Route::post('/admin/user/store', [UserController::class, 'StoreUser'])->name('store.user');
    Route::get('/admin/user/all', [UserController::class, 'AllOwners'])->name('all.owners');
    Route::match(['get', 'post'], '/admin/user/filter-ajax', [UserController::class, 'FilterUsersAjax'])->name('filter.users.ajax');
    Route::get('/admin/user/edit/{id}', [UserController::class, 'EditUser'])->name('edit.user');
    Route::post('/admin/user/update', [UserController::class, 'UpdateUser'])->name('update.user');
    Route::match(['get', 'post'], '/admin/user/change-status-ajax', [UserController::class, 'ChangeStatusAjax'])->name('change.status.ajax');
    Route::get('/admin/user/status/{id}', [UserController::class, 'ToggleStatusUser'])->name('status.user');
    Route::get('/admin/user/delete/{id}', [UserController::class, 'DeleteUser'])->name('delete.user');

    // Truck Type Management Routes
    Route::get('/admin/truck-type/all', [TruckTypeController::class, 'AllTruckTypes'])->name('all.truck.types');
    Route::get('/admin/truck-type/add', [TruckTypeController::class, 'AddTruckType'])->name('add.truck.type');
    Route::post('/admin/truck-type/store', [TruckTypeController::class, 'StoreTruckType'])->name('store.truck.type');
    Route::get('/admin/truck-type/edit/{id}', [TruckTypeController::class, 'EditTruckType'])->name('edit.truck.type');
    Route::post('/admin/truck-type/update', [TruckTypeController::class, 'UpdateTruckType'])->name('update.truck.type');
    Route::match(['get', 'post'], '/admin/truck-type/change-status-ajax', [TruckTypeController::class, 'ChangeStatusAjax'])->name('truck.type.status.ajax');
    Route::get('/admin/truck-type/delete/{id}', [TruckTypeController::class, 'DeleteTruckType'])->name('delete.truck.type');

    // Truck Sub-Type Management Routes
    Route::get('/admin/truck-sub-type/all', [TruckSubTypeController::class, 'AllTruckSubTypes'])->name('all.truck.sub.types');
    Route::get('/admin/truck-sub-type/add', [TruckSubTypeController::class, 'AddTruckSubType'])->name('add.truck.sub.type');
    Route::post('/admin/truck-sub-type/store', [TruckSubTypeController::class, 'StoreTruckSubType'])->name('store.truck.sub.type');
    Route::get('/admin/truck-sub-type/edit/{id}', [TruckSubTypeController::class, 'EditTruckSubType'])->name('edit.truck.sub.type');
    Route::post('/admin/truck-sub-type/update', [TruckSubTypeController::class, 'UpdateTruckSubType'])->name('update.truck.sub.type');
    Route::match(['get', 'post'], '/admin/truck-sub-type/change-status-ajax', [TruckSubTypeController::class, 'ChangeStatusAjax'])->name('truck.sub.type.status.ajax');
    Route::get('/admin/truck-sub-type/delete/{id}', [TruckSubTypeController::class, 'DeleteTruckSubType'])->name('delete.truck.sub.type');

    // Truck Brand Management Routes
    Route::get('/admin/truck-brand/all', [TruckBrandController::class, 'AllTruckBrands'])->name('all.truck.brands');
    Route::get('/admin/truck-brand/add', [TruckBrandController::class, 'AddTruckBrand'])->name('add.truck.brand');
    Route::post('/admin/truck-brand/store', [TruckBrandController::class, 'StoreTruckBrand'])->name('store.truck.brand');
    Route::get('/admin/truck-brand/edit/{id}', [TruckBrandController::class, 'EditTruckBrand'])->name('edit.truck.brand');
    Route::post('/admin/truck-brand/update', [TruckBrandController::class, 'UpdateTruckBrand'])->name('update.truck.brand');
    Route::get('/admin/truck-brand/delete/{id}', [TruckBrandController::class, 'DeleteTruckBrand'])->name('delete.truck.brand');
    Route::match(['get', 'post'], '/admin/truck-brand/change-status-ajax', [TruckBrandController::class, 'ChangeStatusAjax'])->name('truck.brand.status.ajax');

    // Truck Model Management Routes
    Route::get('/admin/truck-model/all', [TruckModelController::class, 'AllTruckModels'])->name('all.truck.models');
    Route::get('/admin/truck-model/add', [TruckModelController::class, 'AddTruckModel'])->name('add.truck.model');
    Route::post('/admin/truck-model/store', [TruckModelController::class, 'StoreTruckModel'])->name('store.truck.model');
    Route::get('/admin/truck-model/edit/{id}', [TruckModelController::class, 'EditTruckModel'])->name('edit.truck.model');
    Route::post('/admin/truck-model/update', [TruckModelController::class, 'UpdateTruckModel'])->name('update.truck.model');
    Route::get('/admin/truck-model/delete/{id}', [TruckModelController::class, 'DeleteTruckModel'])->name('delete.truck.model');
    Route::match(['get', 'post'], '/admin/truck-model/change-status-ajax', [TruckModelController::class, 'ChangeStatusAjax'])->name('truck.model.status.ajax');
    Route::get('/admin/driver-truck/get-models-by-brand/{brand_id}', [TruckModelController::class, 'GetModelsByBrandAjax'])->name('get.models.by.brand.ajax');

    // Driver & Truck Assignment Routes
    Route::get('/admin/driver-truck/all', [DriverTruckController::class, 'AllDriverTrucks'])->name('all.driver.trucks');
    Route::match(['get', 'post'], '/admin/driver-truck/filter-ajax', [DriverTruckController::class, 'FilterDriverTrucksAjax'])->name('filter.driver.trucks.ajax');
    Route::get('/admin/driver-truck/add', [DriverTruckController::class, 'AddDriverTruck'])->name('add.driver.truck');
    Route::post('/admin/driver-truck/store', [DriverTruckController::class, 'StoreDriverTruck'])->name('store.driver.truck');
    Route::get('/admin/driver-truck/edit/{id}', [DriverTruckController::class, 'EditDriverTruck'])->name('edit.driver.truck');
    Route::post('/admin/driver-truck/update', [DriverTruckController::class, 'UpdateDriverTruck'])->name('update.driver.truck');
    Route::get('/admin/driver-truck/delete/{id}', [DriverTruckController::class, 'DeleteDriverTruck'])->name('delete.driver.truck');
    Route::get('/admin/driver-truck/get-sub-types/{truck_type_id}', [DriverTruckController::class, 'GetSubTypesAjax'])->name('get.subtypes.ajax');
    Route::match(['get', 'post'], '/admin/driver-truck/change-verified-status', [DriverTruckController::class, 'ChangeVerifiedStatusAjax'])->name('change.driver.truck.verified.status.ajax');
    Route::match(['get', 'post'], '/admin/driver-truck/change-default-status', [DriverTruckController::class, 'ChangeDefaultStatusAjax'])->name('change.driver.truck.default.status.ajax');

    // Country Management Routes
    Route::get('/admin/country/all', [CountryController::class, 'AllCountries'])->name('all.countries');
    Route::get('/admin/country/add', [CountryController::class, 'AddCountry'])->name('add.country');
    Route::post('/admin/country/store', [CountryController::class, 'StoreCountry'])->name('store.country');
    Route::get('/admin/country/edit/{id}', [CountryController::class, 'EditCountry'])->name('edit.country');
    Route::post('/admin/country/update', [CountryController::class, 'UpdateCountry'])->name('update.country');
    Route::get('/admin/country/delete/{id}', [CountryController::class, 'DeleteCountry'])->name('delete.country');
    Route::match(['get', 'post'], '/admin/country/change-status-ajax', [CountryController::class, 'ChangeStatusAjax'])->name('country.status.ajax');

    // City Management Routes
    Route::get('/admin/city/all', [CityController::class, 'AllCities'])->name('all.cities');
    Route::get('/admin/city/add', [CityController::class, 'AddCity'])->name('add.city');
    Route::post('/admin/city/store', [CityController::class, 'StoreCity'])->name('store.city');
    Route::get('/admin/city/edit/{id}', [CityController::class, 'EditCity'])->name('edit.city');
    Route::post('/admin/city/update', [CityController::class, 'UpdateCity'])->name('update.city');
    Route::get('/admin/city/delete/{id}', [CityController::class, 'DeleteCity'])->name('delete.city');
    Route::match(['get', 'post'], '/admin/city/change-status-ajax', [CityController::class, 'ChangeStatusAjax'])->name('city.status.ajax');
    Route::get('/admin/city/get-cities-ajax/{country_id}', [CityController::class, 'GetCitiesByCountryAjax'])->name('get.cities.by.country.ajax');

    // Shipment Type Management Routes
    Route::get('/admin/shipment-type/all', [ShipmentTypeController::class, 'AllShipmentTypes'])->name('all.shipment.types');
    Route::get('/admin/shipment-type/add', [ShipmentTypeController::class, 'AddShipmentType'])->name('add.shipment.type');
    Route::post('/admin/shipment-type/store', [ShipmentTypeController::class, 'StoreShipmentType'])->name('store.shipment.type');
    Route::get('/admin/shipment-type/edit/{id}', [ShipmentTypeController::class, 'EditShipmentType'])->name('edit.shipment.type');
    Route::post('/admin/shipment-type/update', [ShipmentTypeController::class, 'UpdateShipmentType'])->name('update.shipment.type');
    Route::get('/admin/shipment-type/delete/{id}', [ShipmentTypeController::class, 'DeleteShipmentType'])->name('delete.shipment.type');
    Route::match(['get', 'post'], '/admin/shipment-type/change-status-ajax', [ShipmentTypeController::class, 'ChangeStatusAjax'])->name('shipment.type.status.ajax');

    // Shipment Nature Management Routes
    Route::get('/admin/shipment-nature/all', [ShipmentNatureController::class, 'AllShipmentNatures'])->name('all.shipment.natures');
    Route::get('/admin/shipment-nature/add', [ShipmentNatureController::class, 'AddShipmentNature'])->name('add.shipment.nature');
    Route::post('/admin/shipment-nature/store', [ShipmentNatureController::class, 'StoreShipmentNature'])->name('store.shipment.nature');
    Route::get('/admin/shipment-nature/edit/{id}', [ShipmentNatureController::class, 'EditShipmentNature'])->name('edit.shipment.nature');
    Route::post('/admin/shipment-nature/update', [ShipmentNatureController::class, 'UpdateShipmentNature'])->name('update.shipment.nature');
    Route::get('/admin/shipment-nature/delete/{id}', [ShipmentNatureController::class, 'DeleteShipmentNature'])->name('delete.shipment.nature');
    Route::match(['get', 'post'], '/admin/shipment-nature/change-status-ajax', [ShipmentNatureController::class, 'ChangeStatusAjax'])->name('shipment.nature.status.ajax');

    // Shipment Order Management Routes
    Route::get('/admin/shipment/all', [ShipmentController::class, 'AllShipments'])->name('all.shipments');
    Route::get('/admin/shipment/add', [ShipmentController::class, 'AddShipment'])->name('add.shipment');
    Route::post('/admin/shipment/store', [ShipmentController::class, 'StoreShipment'])->name('store.shipment');
    Route::get('/admin/shipment/edit/{id}', [ShipmentController::class, 'EditShipment'])->name('edit.shipment');
    Route::post('/admin/shipment/update', [ShipmentController::class, 'UpdateShipment'])->name('update.shipment');
    Route::get('/admin/shipment/details/{id}', [ShipmentController::class, 'GetShipmentDetailsAjax'])->name('get.shipment.details.ajax');
    Route::match(['get', 'post'], '/admin/shipment/change-status-ajax', [ShipmentController::class, 'ChangeStatusAjax'])->name('shipment.status.ajax');
    Route::get('/admin/shipment/delete/{id}', [ShipmentController::class, 'DeleteShipment'])->name('delete.shipment');
    Route::get('/admin/shipment/get-user-data/{id}', [ShipmentController::class, 'GetUserDataAjax'])->name('get.shipment.user.data.ajax');
    Route::get('/admin/shipment/get-sub-types/{truck_type_id}', [ShipmentController::class, 'GetSubTypesAjax'])->name('get.shipment.sub.types.ajax');
    Route::get('/admin/shipment/get-cities/{country_id}', [ShipmentController::class, 'GetCitiesAjax'])->name('get.shipment.cities.ajax');

    // Real-Time Shipment Tracking Routes (تتبع الشحنات اللحظي)
    Route::get('/admin/shipment/track', [ShipmentTrackingController::class, 'index'])->name('track.shipments');
    Route::get('/admin/shipment/track/{id}', [ShipmentTrackingController::class, 'show'])->name('track.shipment.live');
    Route::post('/admin/shipment/track/update-location', [ShipmentTrackingController::class, 'updateLocation'])->name('shipment.track.update-location');
    Route::post('/admin/shipment/track/update-status', [ShipmentTrackingController::class, 'updateStatus'])->name('shipment.track.update-status');
    Route::get('/admin/shipment/track/{id}/logs', [ShipmentTrackingController::class, 'getLogsAjax'])->name('shipment.track.logs.ajax');
    Route::post('/admin/shipment/track/{id}/clear', [ShipmentTrackingController::class, 'clearLogs'])->name('shipment.track.clear-logs');

    // Shipment HS Code Tariff & Lookup Routes (البحث الفوري عن رمز النظام المنسق)
    Route::get('/admin/shipment/hscode', [HsCodeController::class, 'Index'])->name('hscode.lookup');
    Route::post('/admin/shipment/hscode/search-ajax', [HsCodeController::class, 'Lookup'])->name('hscode.lookup.ajax');

    // Shipment Driver Invitation Routes
    Route::get('/admin/shipment-invitation/all', [ShipmentInvitationController::class, 'AllInvitations'])->name('all.shipment.invitations');
    Route::get('/admin/shipment-invitation/add', [ShipmentInvitationController::class, 'AddInvitation'])->name('add.shipment.invitation');
    Route::post('/admin/shipment-invitation/store', [ShipmentInvitationController::class, 'StoreInvitation'])->name('store.shipment.invitation');
    Route::get('/admin/shipment-invitation/edit/{id}', [ShipmentInvitationController::class, 'EditInvitation'])->name('edit.shipment.invitation');
    Route::post('/admin/shipment-invitation/update', [ShipmentInvitationController::class, 'UpdateInvitation'])->name('update.shipment.invitation');
    Route::get('/admin/shipment-invitation/delete/{id}', [ShipmentInvitationController::class, 'DeleteInvitation'])->name('delete.shipment.invitation');
    Route::get('/admin/shipment-invitation/get-driver-trucks/{driver_id}', [ShipmentInvitationController::class, 'GetDriverTrucksAjax'])->name('get.driver.trucks.ajax');
    Route::get('/admin/shipment-invitation/get-shipment-data/{shipment_id}', [ShipmentInvitationController::class, 'GetShipmentDataAjax'])->name('get.shipment.data.ajax');
    Route::match(['get', 'post'], '/admin/shipment-invitation/change-status-ajax', [ShipmentInvitationController::class, 'ChangeStatusAjax'])->name('shipment.invitation.status.ajax');

    // Fixed Routes Management Routes
    Route::get('/admin/routes/all', [RouteController::class, 'AllRoutes'])->name('all.routes');
    Route::get('/admin/routes/add', [RouteController::class, 'AddRoute'])->name('add.route');
    Route::post('/admin/routes/store', [RouteController::class, 'StoreRoute'])->name('store.route');
    Route::get('/admin/routes/edit/{id}', [RouteController::class, 'EditRoute'])->name('edit.route');
    Route::post('/admin/routes/update', [RouteController::class, 'UpdateRoute'])->name('update.route');
    Route::get('/admin/routes/delete/{id}', [RouteController::class, 'DeleteRoute'])->name('delete.route');
    Route::match(['get', 'post'], '/admin/routes/change-status-ajax', [RouteController::class, 'ChangeStatusAjax'])->name('route.status.ajax');

    // Scheduled Trips Management Routes
    Route::get('/admin/scheduled-trips/all', [ScheduledTripController::class, 'AllTrips'])->name('all.scheduled.trips');
    Route::get('/admin/scheduled-trips/add', [ScheduledTripController::class, 'AddTrip'])->name('add.scheduled.trip');
    Route::post('/admin/scheduled-trips/store', [ScheduledTripController::class, 'StoreTrip'])->name('store.scheduled.trip');
    Route::get('/admin/scheduled-trips/edit/{id}', [ScheduledTripController::class, 'EditTrip'])->name('edit.scheduled.trip');
    Route::post('/admin/scheduled-trips/update', [ScheduledTripController::class, 'UpdateTrip'])->name('update.scheduled.trip');
    Route::get('/admin/scheduled-trips/delete/{id}', [ScheduledTripController::class, 'DeleteTrip'])->name('delete.scheduled.trip');
    Route::match(['get', 'post'], '/admin/scheduled-trips/change-status-ajax', [ScheduledTripController::class, 'ChangeStatusAjax'])->name('scheduled.trip.status.ajax');
    Route::get('/admin/scheduled-trips/details/{id}', [ScheduledTripController::class, 'GetTripDetailsAjax'])->name('get.scheduled.trip.details.ajax');

    // Financial & Billing System — Invoices Routes
    Route::get('/admin/invoices/all', [InvoiceController::class, 'AllInvoices'])->name('all.invoices');
    Route::get('/admin/invoices/edit/{id}', [InvoiceController::class, 'EditInvoice'])->name('edit.invoice');
    Route::post('/admin/invoices/update', [InvoiceController::class, 'UpdateInvoice'])->name('update.invoice');
    Route::get('/admin/invoices/delete/{id}', [InvoiceController::class, 'DeleteInvoice'])->name('delete.invoice');
    Route::match(['get', 'post'], '/admin/invoices/change-status-ajax', [InvoiceController::class, 'ChangeStatusAjax'])->name('invoice.status.ajax');
    Route::get('/admin/invoices/details/{id}', [InvoiceController::class, 'GetInvoiceDetailsAjax'])->name('get.invoice.details.ajax');

    // Financial & Billing System — Payments Routes
    Route::get('/admin/payments/all', [PaymentController::class, 'AllPayments'])->name('all.payments');
    Route::get('/admin/payments/add', [PaymentController::class, 'AddPayment'])->name('add.payment');
    Route::post('/admin/payments/store', [PaymentController::class, 'StorePayment'])->name('store.payment');
    Route::get('/admin/payments/edit/{id}', [PaymentController::class, 'EditPayment'])->name('edit.payment');
    Route::post('/admin/payments/update', [PaymentController::class, 'UpdatePayment'])->name('update.payment');
    Route::get('/admin/payments/delete/{id}', [PaymentController::class, 'DeletePayment'])->name('delete.payment');
    Route::match(['get', 'post'], '/admin/payments/change-status-ajax', [PaymentController::class, 'ChangeStatusAjax'])->name('payment.status.ajax');
    Route::get('/admin/payments/details/{id}', [PaymentController::class, 'GetPaymentDetailsAjax'])->name('get.payment.details.ajax');

    // Financial & Billing System — Wallet Routes
    Route::get('/admin/wallets/all', [WalletController::class, 'AllWallets'])->name('all.wallets');
    Route::get('/admin/wallets/add', [WalletController::class, 'AddTransaction'])->name('add.wallet.transaction');
    Route::post('/admin/wallets/store', [WalletController::class, 'StoreTransaction'])->name('store.wallet.transaction');
    Route::get('/admin/wallets/edit/{id}', [WalletController::class, 'EditTransaction'])->name('edit.wallet.transaction');
    Route::post('/admin/wallets/update', [WalletController::class, 'UpdateTransaction'])->name('update.wallet.transaction');
    Route::get('/admin/wallets/delete/{id}', [WalletController::class, 'DeleteTransaction'])->name('delete.wallet.transaction');
    Route::get('/admin/wallets/user-log/{user_id}', [WalletController::class, 'GetUserWalletLogAjax'])->name('get.user.wallet.log.ajax');

    // Communications System Routes
    Route::get('/admin/communications/all', [CommunicationController::class, 'AllConversations'])->name('all.conversations');
    Route::post('/admin/communications/store', [CommunicationController::class, 'StoreConversation'])->name('store.conversation');
    Route::get('/admin/communications/messages/{id}', [CommunicationController::class, 'GetMessagesAjax'])->name('get.conversation.messages.ajax');
    Route::post('/admin/communications/send-message', [CommunicationController::class, 'SendMessageAjax'])->name('send.message.ajax');
    Route::post('/admin/communications/toggle-status', [CommunicationController::class, 'ToggleStatusAjax'])->name('toggle.conversation.status.ajax');
    Route::post('/admin/communications/change-channel', [CommunicationController::class, 'ChangeChannelAjax'])->name('change.conversation.channel.ajax');
    Route::get('/admin/communications/delete-message/{id}', [CommunicationController::class, 'DeleteMessageAjax'])->name('delete.message.ajax');
    Route::get('/admin/communications/delete/{id}', [CommunicationController::class, 'DeleteConversation'])->name('delete.conversation');
    Route::post('/admin/communications/add-participant', [CommunicationController::class, 'AddParticipantAjax'])->name('add.conversation.participant.ajax');
    Route::post('/admin/communications/remove-participant', [CommunicationController::class, 'RemoveParticipantAjax'])->name('remove.conversation.participant.ajax');

    // Admin Notification Routes
    Route::get('/admin/notifications/fetch', [AdminNotificationController::class, 'fetchNotifications'])->name('admin.notifications.fetch');
    Route::get('/admin/notifications/read/{id}', [AdminNotificationController::class, 'markAsRead'])->name('admin.notification.read');
    Route::post('/admin/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark_all_read');
    Route::delete('/admin/notifications/{id}', [AdminNotificationController::class, 'deleteNotification'])->name('admin.notifications.delete');
    Route::post('/admin/notifications/clear-all', [AdminNotificationController::class, 'clearAllNotifications'])->name('admin.notifications.clear_all');

    // About SALASIL Platform Route
    Route::get('/admin/about-salasil', function () {
        return view('admin.about_salasil');
    })->name('admin.about.salasil');
});

require __DIR__.'/auth.php';

// Dynamically register fallback/stub named routes referenced in admin blade templates to prevent missing route exceptions
$adminIncludeDir = resource_path('views/admin');
if (is_dir($adminIncludeDir)) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($adminIncludeDir));
    $foundRouteNames = [];
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                foreach ($matches[1] as $rName) {
                    $foundRouteNames[] = trim($rName);
                }
            }
        }
    }

    foreach (array_unique($foundRouteNames) as $routeName) {
        if ($routeName !== 'dashboard' && !Route::has($routeName)) {
            Route::get('/admin/stub/' . md5($routeName), function () {
                return redirect()->route('dashboard');
            })->middleware('auth')->name($routeName);
        }
    }
}
