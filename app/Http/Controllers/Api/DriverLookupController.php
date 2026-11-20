<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ScheduledTripDriverInvitation;
use App\Models\Shipment;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DriverLookupController extends Controller
{

    /**
     * Search drivers from database (returns array of matching drivers)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $rawQuery = $request->input('query') ?? $request->input('search') ?? $request->input('q') ?? '';
        $cleanQuery = trim($rawQuery);
        $cleanQuery = ltrim($cleanQuery, '#');

        Log::info("=== [DriverLookupController] Search requested with query: '{$cleanQuery}' ===");

        $query = User::where('role', 'driver')
            ->with(['driverProfile', 'trucks', 'country', 'city']);

        if (!empty($cleanQuery)) {
            $query->where(function ($q) use ($cleanQuery) {
                $q->where('client_code', 'LIKE', "%{$cleanQuery}%")
                  ->orWhere('fname', 'LIKE', "%{$cleanQuery}%")
                  ->orWhere('lname', 'LIKE', "%{$cleanQuery}%")
                  ->orWhere(DB::raw("CONCAT(fname, ' ', lname)"), 'LIKE', "%{$cleanQuery}%")
                  ->orWhere('phone', 'LIKE', "%{$cleanQuery}%");
            });
        }

        $drivers = $query->limit(20)->get();
        $completedTripsMap = $this->getCompletedTripsCounts($drivers->pluck('id')->all());

        $results = $drivers->map(function ($driver) use ($completedTripsMap) {
            return $this->formatDriver($driver, $completedTripsMap[$driver->id] ?? 0);
        })->values();

        Log::info("=== [DriverLookupController] Search returning " . count($results) . " drivers ===");

        return response()->json([
            'status'  => true,
            'message' => 'تم استرجاع نتائج البحث بنجاح من قاعدة البيانات.',
            'data'    => $results,
        ], 200);
    }

    /**
     * Professional Driver Lookup API
     * Searches driver by client_code (e.g. DR-72288), name (e.g. Ahmed), phone, or QR Code payload.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookup(Request $request)
    {
        $rawQuery = $request->input('query') ?? $request->input('code') ?? $request->input('search');
        Log::info("=== [DriverLookupController] Lookup requested with query: '{$rawQuery}' ===");

        if (empty($rawQuery)) {
            return response()->json([
                'status'  => false,
                'message' => 'يرجى إدخال اسم السائق أو كود السائق للبحث.',
                'data'    => null,
            ], 400);
        }

        // Clean query: strip extra spaces, hash signs, and handle QR code prefixes if any
        $cleanQuery = trim($rawQuery);
        $cleanQuery = ltrim($cleanQuery, '#');

        // If scanned QR code contains a JSON payload: {"code":"DR-72288"}
        if (str_starts_with($cleanQuery, '{') && str_ends_with($cleanQuery, '}')) {
            $decoded = json_decode($cleanQuery, true);
            if (isset($decoded['code'])) {
                $cleanQuery = trim($decoded['code']);
            } elseif (isset($decoded['driver_id'])) {
                $cleanQuery = trim($decoded['driver_id']);
            }
        }

        // If QR code contains deep link: salasil://driver/DR-72288
        if (str_contains($cleanQuery, 'driver/')) {
            $cleanQuery = substr($cleanQuery, strpos($cleanQuery, 'driver/') + 7);
        }

        // 1. First priority: Exact match on client_code (case-insensitive)
        $driver = User::where('role', 'driver')
            ->where(function ($q) use ($cleanQuery) {
                $q->whereRaw('LOWER(client_code) = ?', [strtolower($cleanQuery)])
                  ->orWhere('client_code', $cleanQuery);
            })
            ->with(['driverProfile', 'trucks', 'country', 'city'])
            ->first();

        // 2. Second priority: If query looks like a code without prefix (e.g. 72288), search DR-72288
        if (!$driver && is_numeric($cleanQuery)) {
            $driver = User::where('role', 'driver')
                ->where(function ($q) use ($cleanQuery) {
                    $q->where('client_code', 'DR-' . $cleanQuery)
                      ->orWhere('id', (int)$cleanQuery)
                      ->orWhere('phone', 'LIKE', "%{$cleanQuery}%");
                })
                ->with(['driverProfile', 'trucks', 'country', 'city'])
                ->first();
        }

        // 3. Third priority: Search by Name (fname, lname, or full name) or partial code / phone
        if (!$driver) {
            $driver = User::where('role', 'driver')
                ->where(function ($q) use ($cleanQuery) {
                    $q->where('client_code', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('fname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('lname', 'LIKE', "%{$cleanQuery}%")
                      ->orWhere(DB::raw("CONCAT(fname, ' ', lname)"), 'LIKE', "%{$cleanQuery}%")
                      ->orWhere('phone', 'LIKE', "%{$cleanQuery}%");
                })
                ->with(['driverProfile', 'trucks', 'country', 'city'])
                ->first();
        }

        // 4. Fourth priority: If still not found and query contains spaces (e.g. 'Ahmed mohamed')
        if (!$driver && str_contains($cleanQuery, ' ')) {
            $parts = explode(' ', $cleanQuery);
            $driver = User::where('role', 'driver')
                ->where(function ($q) use ($parts) {
                    $q->where('fname', 'LIKE', "%{$parts[0]}%")
                      ->orWhere('lname', 'LIKE', "%{$parts[count($parts)-1]}%");
                })
                ->with(['driverProfile', 'trucks', 'country', 'city'])
                ->first();
        }

        if (!$driver) {
            Log::warning("=== [DriverLookupController] Driver NOT found for query: '{$rawQuery}' ===");
            return response()->json([
                'status'  => false,
                'message' => 'لم يتم العثور على سائق مطابق للبحث: ' . $rawQuery,
                'data'    => null,
            ], 404);
        }

        $driverData = $this->formatDriver($driver);

        Log::info("=== [DriverLookupController] Driver found: " . json_encode($driverData));

        return response()->json([
            'status'  => true,
            'message' => 'تم العثور على بيانات السائق بنجاح.',
            'data'    => $driverData,
        ], 200);
    }

    /**
     * Count real completed trips (scheduled trips + delivered shipments) for a set of driver IDs.
     *
     * @param array<int> $driverIds
     * @return array<int, int>
     */
    private function getCompletedTripsCounts(array $driverIds): array
    {
        if (empty($driverIds)) {
            return [];
        }

        $invitationCounts = ScheduledTripDriverInvitation::whereIn('driver_id', $driverIds)
            ->where('status', 'completed')
            ->selectRaw('driver_id, COUNT(*) as total')
            ->groupBy('driver_id')
            ->pluck('total', 'driver_id')
            ->all();

        $shipmentCounts = Shipment::whereIn('driver_id', $driverIds)
            ->where('status', 'delivered')
            ->selectRaw('driver_id, COUNT(*) as total')
            ->groupBy('driver_id')
            ->pluck('total', 'driver_id')
            ->all();

        $totals = [];
        foreach ($driverIds as $id) {
            $totals[$id] = (int) ($invitationCounts[$id] ?? 0) + (int) ($shipmentCounts[$id] ?? 0);
        }

        return $totals;
    }

    /**
     * Resolve driver profile photo URL consistent with AuthController uploads.
     */
    private function resolveAvatarUrl(?string $photo): ?string
    {
        $photo = trim((string) $photo);
        if ($photo === '') {
            return null;
        }

        if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
            return $photo;
        }

        $normalized = ltrim($photo, '/');
        if (str_starts_with($normalized, 'upload/') || str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('upload/user_images/' . $normalized);
    }

    /**
     * Format a driver User model to API array using real database values only.
     *
     * @param User $driver
     * @param int|null $completedTripsCount
     * @return array
     */
    private function formatDriver(User $driver, ?int $completedTripsCount = null): array
    {
        $defaultTruck = $driver->trucks->where('is_default', 1)->first() ?? $driver->trucks->first();
        $plateNumber = $defaultTruck && trim((string) $defaultTruck->plate_number) !== ''
            ? trim((string) $defaultTruck->plate_number)
            : null;

        $rating = null;
        if ($driver->driverProfile && $driver->driverProfile->rating !== null && (float) $driver->driverProfile->rating > 0) {
            $rating = (float) $driver->driverProfile->rating;
        }

        $countryName = $driver->country?->name_en ?: ($driver->country?->name_ar ?: null);

        if ($completedTripsCount === null) {
            $counts = $this->getCompletedTripsCounts([$driver->id]);
            $completedTripsCount = $counts[$driver->id] ?? 0;
        }

        $fullName = trim((string) ($driver->name ?: trim(($driver->fname ?? '') . ' ' . ($driver->lname ?? ''))));

        return [
            'id'                 => (string) $driver->id,
            'client_code'        => $driver->client_code ?? 'DR-' . $driver->id,
            'name'               => $fullName,
            'fname'              => $driver->fname ?? '',
            'lname'              => $driver->lname ?? '',
            'phone'              => $driver->phone ?? '',
            'avatar'             => $this->resolveAvatarUrl($driver->photo),
            'vehicle_number'     => $plateNumber,
            'rating'             => $rating,
            'country'            => $countryName,
            'bank_name'          => null,
            'iban'               => null,
            'payout_destination' => 'Salasil Driver Wallet',
            'trips_completed'    => (int) $completedTripsCount,
            'is_verified'        => (bool) $driver->is_verified,
        ];
    }


    /**
     * Process Payment to Driver
     * Supports Salasil Wallet deduction and crediting driver.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function payDriver(Request $request)
    {
        Log::info("=== [DriverLookupController] Pay driver request: " . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'driver_id'      => 'required',
            'amount'         => 'required|numeric|min:0.001',
            'payment_method' => 'required|string',
            'note'           => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 1. Identify customer
        $user = $request->user('sanctum') ?? $request->user();
        if (!$user) {
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearerToken);
                if ($tokenModel) {
                    $user = $tokenModel->tokenable;
                }
            }
        }
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->user_id);
        }
        if (!$user) {
            // Default to company_customer user ID 12 (mahmoudessa)
            $user = User::where('id', 12)->first() ?? User::where('role', 'company_customer')->first();
        }

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer account not found',
            ], 404);
        }

        // 2. Identify driver
        $driverId = $request->driver_id;
        $driver = User::where('role', 'driver')
            ->where(function ($q) use ($driverId) {
                $q->where('id', $driverId)
                  ->orWhere('client_code', $driverId)
                  ->orWhere('client_code', 'DR-' . $driverId)
                  ->orWhereHas('driverProfile', function ($dp) use ($driverId) {
                      $dp->where('id', $driverId);
                  });
            })
            ->with('driverProfile')
            ->first();

        if (!$driver && is_numeric($driverId)) {
            $driver = User::where('role', 'driver')->find($driverId);
        }

        $amount = (float) $request->amount;
        $paymentMethod = strtolower($request->payment_method);
        $note = $request->filled('note') ? trim($request->note) : null;
        $driverName = $driver ? ($driver->fname . ' ' . ($driver->lname ?? '')) : "Driver #{$driverId}";
        $driverCode = $driver ? ($driver->client_code ?? "DR-{$driverId}") : "DR-{$driverId}";

        $txnId = (string) (2147000000 + mt_rand(100000, 999999));

        DB::beginTransaction();
        try {
            $customerTxn = null;
            $driverTxn = null;
            $newCustomerBalance = null;
            $driverNewBalance = null;

            // 3. Process Salasil Wallet deduction
            if ($paymentMethod === 'wallet' || $paymentMethod === 'salasil_wallet') {
                $lastTxn = WalletTransaction::where('user_id', $user->id)->latest('id')->first();
                $currentBalance = $lastTxn ? (float) $lastTxn->balance_after : (float) ($user->credit ?? 0.00);

                if ($currentBalance < $amount) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => 'Insufficient wallet balance. Available: ' . number_format($currentBalance, 3) . ' KWD',
                    ], 400);
                }

                $newCustomerBalance = round($currentBalance - $amount, 3);

                // Update customer credit column in users table
                $user->credit = $newCustomerBalance;
                $user->save();

                // Build clear description for Customer Wallet Activity
                $description = "Payment to driver: {$driverName} ({$driverCode})";
                if ($note) {
                    $description .= " - {$note}";
                }

                $customerTxn = WalletTransaction::create([
                    'user_id'           => $user->id,
                    'amount'            => $amount,
                    'type'              => 'withdrawal',
                    'balance_after'     => $newCustomerBalance,
                    'description'       => $description,
                    'shipment_id'       => null,
                    'scheduled_trip_id' => null,
                ]);

                // Credit the driver's wallet
                if ($driver) {
                    $driverLastTxn = WalletTransaction::where('user_id', $driver->id)->latest('id')->first();
                    $driverCurrentBalance = $driverLastTxn ? (float) $driverLastTxn->balance_after : (float) ($driver->credit ?? 0.00);
                    $driverNewBalance = round($driverCurrentBalance + $amount, 3);

                    $driver->credit = $driverNewBalance;
                    $driver->save();

                    if ($driver->driverProfile) {
                        $driver->driverProfile->wallet_balance = $driverNewBalance;
                        $driver->driverProfile->save();
                    }

                    $driverTxn = WalletTransaction::create([
                        'user_id'           => $driver->id,
                        'amount'            => $amount,
                        'type'              => 'trip_earnings',
                        'balance_after'     => $driverNewBalance,
                        'description'       => "Payment received from {$user->fname} ({$user->client_code})" . ($note ? " - {$note}" : ''),
                        'shipment_id'       => null,
                        'scheduled_trip_id' => null,
                    ]);
                }
            }

            DB::commit();

            // 4. Send professional in-app notification to the Driver for Pay to a Driver
            if ($driver) {
                try {
                    $user->loadMissing('companyProfile');
                    $companyName = trim((string) optional($user->companyProfile)->company_name);
                    $personName = trim(($user->fname ?? '') . ' ' . ($user->lname ?? ''));
                    $customerDisplay = $companyName !== ''
                        ? ($personName !== '' ? "{$companyName} ({$personName})" : $companyName)
                        : ($personName !== '' ? $personName : ($user->client_code ?: 'Customer'));

                    $formattedAmt = number_format($amount, 3);
                    $bodyText = "You received {$formattedAmt} KWD from {$customerDisplay}";
                    if ($note) {
                        $bodyText .= " · Note: {$note}";
                    }
                    if ($driverNewBalance !== null) {
                        $bodyText .= ". Updated wallet balance: " . number_format($driverNewBalance, 3) . " KWD.";
                    } else {
                        $bodyText .= ".";
                    }

                    $driver->notify(new \App\Notifications\CustomerActivityNotification([
                        'title'        => "Payment Received · +{$formattedAmt} KWD",
                        'body'         => $bodyText,
                        'type'         => 'payment',
                        'reference_id' => 'TXN-' . $txnId,
                        'action_route' => '/driver_earnings_screen',
                        'metadata'     => [
                            'event'           => 'pay_to_driver',
                            'transaction_id'  => $txnId,
                            'wallet_txn_id'   => $driverTxn ? $driverTxn->id : null,
                            'amount_kwd'      => round($amount, 3),
                            'currency'        => 'KWD',
                            'payment_method'  => $paymentMethod,
                            'customer_id'     => $user->id,
                            'customer_name'   => $customerDisplay,
                            'customer_code'   => $user->client_code,
                            'note'            => $note,
                            'new_balance_kwd' => $driverNewBalance,
                        ],
                    ]));
                } catch (\Throwable $notifEx) {
                    Log::warning('[DriverLookupController] Driver payment notification warning: ' . $notifEx->getMessage());
                }
            }

            Log::info("=== [DriverLookupController] Pay driver success: Txn ID {$txnId}, Customer {$user->id}, New Balance {$newCustomerBalance} ===");

            return response()->json([
                'status'  => true,
                'message' => 'Payment processed successfully.',
                'data'    => [
                    'id'                  => $customerTxn ? $customerTxn->id : $txnId,
                    'transaction_id'      => $txnId,
                    'amount'              => $amount,
                    'currency'            => 'KD',
                    'payment_method'      => $paymentMethod,
                    'driver_id'           => $driver ? (string) $driver->id : (string) $driverId,
                    'driver_name'         => $driverName,
                    'driver_code'         => $driverCode,
                    'new_balance'         => $newCustomerBalance,
                    'customer_txn'        => $customerTxn,
                ],
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Driver payout error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json([
                'status'  => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}

