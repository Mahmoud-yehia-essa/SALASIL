<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTrip;
use App\Models\ScheduledTripDriverInvitation;
use App\Models\Shipment;
use App\Models\ShipmentDriverInvitation;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\ScheduledTripOrder;
use App\Models\ProofOfDelivery;
use App\Models\DriverTruck;
use App\Models\DriverProfile;
use App\Models\TruckType;
use App\Models\TruckSubType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverHomeController extends Controller
{
    /**
     * Get home statistics and active trip summary for the driver.
     */
    public function homeStats(Request $request)
    {
        $user = $request->user();

        // 1. Resolve Driver ID
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                $driverId = $found->id;
                $user = $found;
            }
        }

        // Fallback to active/demo driver if none provided (e.g. Mahmoud driver, ID 18)
        if (!$driverId) {
            $driverId = 18;
            $user = User::find(18);
        }

        $driver = $user ?: User::find($driverId);

        // 2. Count "Trip Requests" (Pending invitations + Matching new customer orders + Open scheduled trips + Direct shipment invitations)
        $countryFilter = $this->resolveDriverCountryFilter($request);
        [$driverTruckTypeId] = $this->resolveDriverTruckFilters((int) $driverId);
        $requestBuckets = $this->getDriverRequestBuckets((int) $driverId, $countryFilter);
        $scheduledRequestsCount = $requestBuckets['invitations']->count()
            + $requestBuckets['matchingOrders']->count()
            + $requestBuckets['matchingTrips']->count();

        $shipmentInvs = ShipmentDriverInvitation::with([
            'shipment.pickupCountry',
            'shipment.pickupCity.country',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'pending')
            ->get()
            ->filter(function ($sInv) use ($countryFilter, $driverTruckTypeId) {
                $sh = $sInv->shipment;
                if (!$sh) {
                    return false;
                }
                if ($driverTruckTypeId && $sh->truck_type_id && (int) $sh->truck_type_id !== (int) $driverTruckTypeId) {
                    return false;
                }
                [$countryId, $country] = $this->resolveShipmentOriginCountry($sh);
                return $this->matchesOriginCountry($countryId, $country, $countryFilter);
            });

        $shipmentRequestsCount = $shipmentInvs->count();

        $tripRequestsCount = $scheduledRequestsCount + $shipmentRequestsCount;

        // 3. Count "Active Trips" (Accepted invitations where THIS driver hasn't completed delivery yet)
        $acceptedScheduledTripsCount = ScheduledTripDriverInvitation::where('driver_id', $driverId)
            ->where('status', 'accepted')
            ->where(function ($q) {
                $q->whereNull('driver_stage')
                  ->orWhere('driver_stage', '!=', 'delivered');
            })
            ->whereHas('scheduledTrip', function ($sq) {
                $sq->whereNotIn('status', ['completed', 'cancelled', 'canceled']);
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_trip_order_id')
                  ->orWhereHas('scheduledTripOrder', function ($oq) {
                      $oq->whereNotIn('status', ['canceled', 'cancelled']);
                  });
            })
            ->count();

        $directScheduledTripsCount = ScheduledTrip::where('driver_id', $driverId)
            ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
            ->whereDoesntHave('driverInvitations', function ($iq) use ($driverId) {
                $iq->where('driver_id', $driverId)->where('status', 'completed');
            })
            ->count();

        $acceptedShipmentTripsCount = ShipmentDriverInvitation::where('driver_id', $driverId)
            ->where('status', 'accepted')
            ->whereHas('shipment', function ($q) {
                $q->whereIn('status', ['assigned', 'picked_up', 'in_transit']);
            })
            ->count();

        $directShipmentTripsCount = Shipment::where('driver_id', $driverId)
            ->whereIn('status', ['assigned', 'picked_up', 'in_transit'])
            ->count();

        $activeTripsCount = max($acceptedScheduledTripsCount, $directScheduledTripsCount)
            + max($acceptedShipmentTripsCount, $directShipmentTripsCount);

        // 4. Count "Completed Trips"
        $completedTripsList = $this->getCompletedTripsForDriver($driverId);
        $completedShipmentsCount = Shipment::where('driver_id', $driverId)
            ->where('status', 'delivered')
            ->count();

        $completedTripsCount = count($completedTripsList) + $completedShipmentsCount;

        // 5. Calculate "Earnings" (Earned strictly from completed trips and credited wallet balance)
        $walletEarnings = (float) WalletTransaction::where('user_id', $driverId)
            ->whereIn('type', ['trip_earnings'])
            ->sum('amount');

        $completedTripEarnings = (float) ScheduledTripDriverInvitation::where('driver_id', $driverId)
            ->where(function ($q) {
                $q->where('status', 'completed')
                  ->orWhere(function ($oq) {
                      $oq->whereHas('scheduledTripOrder', function ($orderQ) {
                          $orderQ->where('status', 'delivered');
                      })->where('status', '!=', 'rejected');
                  });
            })
            ->where(function ($q) {
                // Strictly exclude ongoing trips that are not delivered yet
                $q->whereDoesntHave('scheduledTripOrder', function ($oq) {
                    $oq->whereNotIn('status', ['delivered', 'completed']);
                });
            })
            ->sum('offered_price');

        $driverCredit = $driver ? (float) ($driver->credit ?? 0.0) : 0.0;

        $totalEarnings = max($walletEarnings, $completedTripEarnings, $driverCredit);

        // 6. Fetch Active Trip Details (if any active trip exists)
        $activeTripData = null;
        $activeInvitation = ScheduledTripDriverInvitation::with([
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.shipmentType',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'accepted')
            ->where(function ($q) {
                $q->whereNull('driver_stage')
                  ->orWhere('driver_stage', '!=', 'delivered');
            })
            ->whereHas('scheduledTrip', function ($sq) {
                $sq->whereNotIn('status', ['completed', 'cancelled', 'canceled']);
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_trip_order_id')
                  ->orWhereHas('scheduledTripOrder', function ($oq) {
                      $oq->whereNotIn('status', ['canceled', 'cancelled']);
                  });
            })
            ->latest()
            ->first();

        if ($activeInvitation && $activeInvitation->scheduledTrip) {
            $trip = $activeInvitation->scheduledTrip;
            $order = $activeInvitation->scheduledTripOrder;

            $originName = 'Kuwait City';
            $destName = 'Dammam';
            if ($order && $order->pickupCity) {
                $originName = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
            } elseif ($trip->route && $trip->route->originCity) {
                $originName = $trip->route->originCity->name_en;
            }
            if ($order && $order->dropoffCity) {
                $destName = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
            } elseif ($trip->route && $trip->route->destinationCity) {
                $destName = $trip->route->destinationCity->name_en;
            }

            $truckName = ($order && $order->truckType) ? $order->truckType->name_en : ($trip->truckType ? $trip->truckType->name_en : 'Flatbed');
            $weightStr = ($order && $order->weight) ? number_format($order->weight, 0) . ' Kg' : ($trip->total_weight_ton ? number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : '20,000 Kg');
            $cargoType = $truckName . ' · ' . $weightStr;
            $pricing = $this->resolveDriverTripPricing($trip, $order, $activeInvitation);
            $fleet = $this->getOrderFleetStats($order, $trip);
            $tripVal = 'KD ' . number_format($pricing['driver_price'], 0);
            $driverStage = $activeInvitation->driver_stage ?: ($order ? $order->status : $trip->status);

            $loadingDateStr = '16 Jul 2026, 14:00';
            if ($order && $order->loading_date) {
                $loadingDateStr = date('d M Y', strtotime($order->loading_date)) . ($order->loading_time ? ', ' . substr($order->loading_time, 0, 5) : ', 14:00');
            } elseif ($trip->trip_date) {
                $loadingDateStr = date('d M Y', strtotime($trip->trip_date)) . ($trip->trip_time ? ', ' . substr($trip->trip_time, 0, 5) : '');
            }

            $activeTripData = [
                'id'                  => 'TRP-' . $trip->id,
                'origin'              => $originName,
                'destination'         => $destName,
                'status'              => (!$fleet['fleet_ready'] && $fleet['required'] > 1)
                    ? "Waiting fleet ({$fleet['accepted']}/{$fleet['required']})"
                    : ucfirst(str_replace('_', ' ', $driverStage ?: 'In progress')),
                'loading_date'        => $loadingDateStr,
                'cargo_type'          => $cargoType,
                'trip_value'          => $tripVal,
                'stops_remaining'     => '6 left',
                'fleet_required'      => $fleet['required'],
                'fleet_accepted'      => $fleet['accepted'],
                'fleet_remaining'     => $fleet['remaining'],
                'fleet_ready'         => $fleet['fleet_ready'],
                'driver_payment_type' => $pricing['payment_type'],
            ];
        }

        // Driver initials helper
        $name = $driver ? trim(($driver->fname ?? '') . ' ' . ($driver->lname ?? '')) : 'Driver';
        $parts = array_filter(preg_split('/\s+/', $name));
        $initials = 'DR';
        if (count($parts) >= 2) {
            $first = mb_substr(reset($parts), 0, 1);
            $last = mb_substr(end($parts), 0, 1);
            $initials = mb_strtoupper($first . $last);
        } elseif (count($parts) === 1) {
            $single = reset($parts);
            $initials = mb_strtoupper(mb_substr($single, 0, 2));
        }

        $driverProfile = $driver ? $driver->driverProfile : null;
        if (!$driverProfile && $driver) {
            $driverProfile = \App\Models\DriverProfile::where('user_id', $driver->id)->first();
        }
        $truckTypeName = 'Tanker';
        if ($driverProfile && $driverProfile->truckType) {
            $truckTypeName = $driverProfile->truckType->name_en ?: $driverProfile->truckType->name_ar;
        }
        $plateNum = $driverProfile && $driverProfile->plate_number ? $driverProfile->plate_number : '1234';
        $ratingVal = $driverProfile && $driverProfile->rating ? (float)$driverProfile->rating : 5.0;
        $registrationDateStr = $driver && $driver->created_at ? $driver->created_at->format('d M Y') : '03 Nov 2026';

        return response()->json([
            'status'  => true,
            'message' => 'Driver home statistics retrieved successfully',
            'data'    => [
                'driver_id'             => $driver ? ($driver->client_code ?: 'DR-' . $driver->id) : 'DR-95818',
                'driver_name'           => $name,
                'driver_initials'       => $initials,
                'driver_email'          => $driver ? ($driver->email ?: 'mahmoudessapsffff@gmail.com') : 'mahmoudessapsffff@gmail.com',
                'driver_phone'          => $driver ? ($driver->phone ? '+965 ' . $driver->phone : '+965 94959092') : '+965 94959092',
                'rating'                => $ratingVal,
                'trips_done'            => (int) $completedTripsCount,
                'km_driven'             => (string) ($completedTripsCount > 0 ? ($completedTripsCount * 100) : 0),
                'registration_date'     => $registrationDateStr,
                'with_salasil'          => $registrationDateStr,
                'truck_type'            => $truckTypeName,
                'plate_number'          => $plateNum,
                'trip_requests_count'   => (int) $tripRequestsCount,
                'active_trips_count'    => (int) $activeTripsCount,
                'completed_trips_count' => (int) $completedTripsCount,
                'earnings'              => (float) $totalEarnings,
                'earnings_formatted'    => number_format($totalEarnings, 0, '.', '') . ' KD',
                'active_trip'           => $activeTripData,
            ]
        ], 200);
    }

    /**
     * Resolve the driver's truck type and sub-type IDs (default truck first, then profile fallback).
     */
    private function resolveDriverTruckFilters(int $driverId): array
    {
        $truck = DriverTruck::where('driver_id', $driverId)->where('is_default', 1)->first()
            ?: DriverTruck::where('driver_id', $driverId)->latest()->first();
        $driverProfile = DriverProfile::where('user_id', $driverId)->first();

        $driverTruckTypeId = $truck ? $truck->truck_type_id : ($driverProfile ? $driverProfile->truck_type_id : null);
        $driverTruckSubTypeId = $truck ? $truck->truck_sub_type_id : null;

        return [$driverTruckTypeId, $driverTruckSubTypeId];
    }

    /**
     * Compute required/accepted/remaining truck slots and fleet movement readiness.
     */
    private function getOrderFleetStats(?ScheduledTripOrder $order, ?ScheduledTrip $trip = null): array
    {
        if ($order) {
            $required = max(1, (int) ($order->number_of_trucks ?: $order->booked_capacity ?: 1));
            $accepted = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
                ->whereIn('status', ['accepted', 'completed'])
                ->count();
            $completed = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
                ->where('status', 'completed')
                ->count();
            if ($accepted === 0 && $order->driver_id) {
                $accepted = 1;
                if (strtolower((string) $order->status) === 'delivered') {
                    $completed = 1;
                }
            }
        } elseif ($trip) {
            $required = max(1, (int) ($trip->number_of_trucks ?: 1));
            $accepted = ScheduledTripDriverInvitation::where('scheduled_trip_id', $trip->id)
                ->whereNull('scheduled_trip_order_id')
                ->whereIn('status', ['accepted', 'completed'])
                ->count();
            $completed = ScheduledTripDriverInvitation::where('scheduled_trip_id', $trip->id)
                ->whereNull('scheduled_trip_order_id')
                ->where('status', 'completed')
                ->count();
            if ($accepted === 0 && $trip->driver_id) {
                $accepted = 1;
            }
        } else {
            $required = 1;
            $accepted = 0;
            $completed = 0;
        }

        $remaining = max(0, $required - $accepted);
        $override = $trip ? (bool) $trip->fleet_override : false;
        $fleetReady = ($accepted >= $required) || $override;

        return [
            'required'       => $required,
            'accepted'       => $accepted,
            'completed'      => $completed,
            'remaining'      => $remaining,
            'fleet_ready'    => $fleetReady,
            'fleet_override' => $override,
        ];
    }

    /**
     * Resolve separate driver payout & payment schedule (100% after vs 50%/50% split).
     */
    private function resolveDriverTripPricing(?ScheduledTrip $trip, ?ScheduledTripOrder $order = null, ?ScheduledTripDriverInvitation $inv = null): array
    {
        $driverPrice = 0.0;
        if ($inv && floatval($inv->offered_price) > 0) {
            $driverPrice = floatval($inv->offered_price);
        } elseif ($trip && $trip->driver_price !== null && floatval($trip->driver_price) > 0) {
            $driverPrice = floatval($trip->driver_price);
        } elseif ($trip && floatval($trip->price) > 0) {
            $driverPrice = floatval($trip->price);
        } elseif ($order && floatval($order->trip_price) > 0) {
            $driverPrice = floatval($order->trip_price);
        }

        $paymentType = ($trip && in_array($trip->driver_payment_type, ['full_after', 'split_50_50']))
            ? $trip->driver_payment_type
            : 'full_after';

        $payoutLabel = $paymentType === 'split_50_50'
            ? '50% at trip start + 50% upon delivery'
            : '100% upon delivery completion';

        return [
            'driver_price' => $driverPrice,
            'payment_type' => $paymentType,
            'payout_label' => $payoutLabel,
        ];
    }

    /**
     * Atomically & idempotently credit a driver's wallet for an invitation phase ('upfront' or 'final').
     */
    private function creditDriverInvitationPayout(
        int $driverId,
        ?ScheduledTripDriverInvitation $inv,
        ?ScheduledTrip $trip,
        ?ScheduledTripOrder $order,
        string $phase
    ): float {
        $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);
        $totalPayout = (float) $pricing['driver_price'];
        $paymentType = $pricing['payment_type'];

        if ($totalPayout <= 0 || $driverId <= 0) {
            return 0.0;
        }

        if ($paymentType !== 'split_50_50' && $phase === 'upfront') {
            return 0.0;
        }

        return (float) DB::transaction(function () use ($driverId, $inv, $trip, $order, $phase, $totalPayout, $paymentType) {
            $lockedInv = $inv ? ScheduledTripDriverInvitation::where('id', $inv->id)->lockForUpdate()->first() : null;
            $driver = User::where('id', $driverId)->lockForUpdate()->first();
            if (!$driver) {
                return 0.0;
            }

            $upfrontHalf = round($totalPayout * 0.5, 2);
            $refText = $order ? ('order #' . $order->order_number) : ('scheduled trip #' . ($trip ? $trip->id : '0'));

            if ($phase === 'upfront') {
                if ($lockedInv && $lockedInv->upfront_paid_at) {
                    return 0.0;
                }

                $alreadyUpfront = WalletTransaction::where('user_id', $driver->id)
                    ->where('type', 'trip_earnings')
                    ->where(function ($q) use ($lockedInv, $order, $trip) {
                        if ($lockedInv) {
                            $q->where('scheduled_trip_invitation_id', $lockedInv->id)
                              ->where('description', 'like', '%50% start%');
                        } elseif ($order) {
                            $q->where('scheduled_trip_order_id', $order->id)
                              ->where('description', 'like', '%50% start%');
                        } elseif ($trip) {
                            $q->where('scheduled_trip_id', $trip->id)
                              ->where('description', 'like', '%50% start%');
                        }
                    })
                    ->exists();

                if ($alreadyUpfront) {
                    if ($lockedInv && !$lockedInv->upfront_paid_at) {
                        $lockedInv->upfront_paid_at = now();
                        $lockedInv->save();
                    }
                    return 0.0;
                }

                $creditAmount = $upfrontHalf;
                $desc = 'Upfront 50% start payout for ' . $refText . ' (الدفعة الأولى 50% عند بداية الرحلة)';
            } else {
                if ($lockedInv && $lockedInv->final_paid_at) {
                    return 0.0;
                }

                $paidQuery = WalletTransaction::where('user_id', $driver->id)
                    ->where('type', 'trip_earnings');

                if ($lockedInv) {
                    $paidQuery->where('scheduled_trip_invitation_id', $lockedInv->id);
                } elseif ($order) {
                    $paidQuery->where(function ($q) use ($order) {
                        $q->where('scheduled_trip_order_id', $order->id)
                          ->orWhere('description', 'like', '%order #' . $order->id . '%')
                          ->orWhere('description', 'like', '%' . $order->order_number . '%');
                    });
                } elseif ($trip) {
                    $paidQuery->where('scheduled_trip_id', $trip->id);
                }

                $alreadyPaidTotal = (float) $paidQuery->sum('amount');
                $creditAmount = round(max(0.0, $totalPayout - $alreadyPaidTotal), 2);

                if ($creditAmount <= 0.009) {
                    if ($lockedInv && !$lockedInv->final_paid_at) {
                        $lockedInv->final_paid_at = now();
                        $lockedInv->save();
                    }
                    return 0.0;
                }

                $desc = ($paymentType === 'split_50_50' && $alreadyPaidTotal > 0)
                    ? 'Final 50% completion payout for ' . $refText . ' (الدفعة النهائية 50% عند التسليم)'
                    : 'Earnings for completing delivery for ' . $refText . ' (أرباح إكمال الرحلة وتسليم الشحنة)';
            }

            $newBal = round((float) ($driver->credit ?? 0.0) + $creditAmount, 2);
            $driver->credit = $newBal;
            $driver->save();

            WalletTransaction::create([
                'user_id'                      => $driver->id,
                'scheduled_trip_id'            => $trip ? $trip->id : null,
                'scheduled_trip_order_id'      => $order ? $order->id : null,
                'scheduled_trip_invitation_id' => $lockedInv ? $lockedInv->id : null,
                'amount'                       => $creditAmount,
                'type'                         => 'trip_earnings',
                'balance_after'                => $newBal,
                'description'                  => $desc,
            ]);

            if ($lockedInv) {
                if ($phase === 'upfront') {
                    $lockedInv->upfront_paid_at = now();
                } else {
                    $lockedInv->final_paid_at = now();
                }
                $lockedInv->save();
            }

            return $creditAmount;
        });
    }

    /**
     * Compute aggregate ScheduledTripOrder status across all assigned fleet trucks.
     */
    private function syncMultiTruckOrderStatus(?ScheduledTripOrder $order, ?ScheduledTrip $trip = null): void
    {
        if (!$order) {
            return;
        }

        $required = max(1, (int) ($order->number_of_trucks ?: $order->booked_capacity ?: 1));
        $invs = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
            ->whereIn('status', ['accepted', 'completed'])
            ->get();

        if ($invs->isEmpty()) {
            return;
        }

        $stages = $invs->map(function ($i) use ($order) {
            return $i->driver_stage ?: ($i->status === 'completed' ? 'delivered' : ($order->status ?: 'confirmed'));
        })->values();

        $completedCount = $invs->filter(fn($i) => $i->status === 'completed' || $i->driver_stage === 'delivered')->count();

        if ($completedCount >= $required) {
            $order->status = 'delivered';
            $order->save();

            if ($trip) {
                $pendingOrders = $trip->orders()
                    ->whereNotIn('status', ['delivered', 'completed', 'canceled'])
                    ->count();
                if ($pendingOrders === 0 && (int) $trip->available_capacity <= 0) {
                    $trip->status = 'completed';
                    $trip->save();
                }
            }
            return;
        }

        if ($stages->contains('in_transit') || $stages->contains('arrived_dropoff') || $completedCount > 0) {
            $order->status = 'in_transit';
        } elseif ($stages->contains('loaded')) {
            $order->status = 'loaded';
        } elseif ($stages->contains('arrived_pickup')) {
            $order->status = 'arrived_pickup';
        } else {
            $order->status = 'confirmed';
        }
        $order->save();

        if ($trip && $order->status === 'in_transit' && !in_array($trip->status, ['completed', 'canceled'])) {
            $trip->status = 'in_transit';
            $trip->save();
        }
    }

    /**
     * Resolve the driver's current physical country filter from request query/body params.
     */
    private function resolveDriverCountryFilter(Request $request): array
    {
        $rawCode = strtoupper(trim((string) $request->input('current_country_code', '')));
        $rawName = trim((string) $request->input('current_country', ''));

        if ($rawCode === '' && $rawName === '') {
            return [
                'has_filter'   => false,
                'country_id'   => null,
                'country_code' => '',
                'country_name' => '',
                'raw_name'     => '',
            ];
        }

        $countries = Country::all();
        $matched = null;

        if ($rawCode !== '') {
            $matched = $countries->first(
                fn($c) => strtoupper(trim((string) $c->code)) === $rawCode
            );
        }

        if (!$matched && $rawName !== '') {
            $normalizedRaw = $this->normalizeCountryToken($rawName);
            $matched = $countries->first(function ($c) use ($normalizedRaw) {
                $en = $this->normalizeCountryToken((string) $c->name_en);
                $ar = $this->normalizeCountryToken((string) $c->name_ar);
                if ($normalizedRaw !== '' && ($en === $normalizedRaw || $ar === $normalizedRaw)) {
                    return true;
                }
                return $this->countryAliasesMatch($c, $normalizedRaw, '');
            });
        }

        if (!$matched && $rawCode !== '') {
            $matched = $countries->first(
                fn($c) => $this->countryAliasesMatch($c, '', $rawCode)
            );
        }

        return [
            'has_filter'   => true,
            'country_id'   => $matched ? (int) $matched->id : null,
            'country_code' => $matched ? strtoupper(trim((string) $matched->code)) : $rawCode,
            'country_name' => $matched ? trim((string) $matched->name_en) : $rawName,
            'raw_name'     => $rawName,
        ];
    }

    private function normalizeCountryToken(string $value): string
    {
        $clean = mb_strtolower(trim($value), 'UTF-8');
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        return trim((string) preg_replace('/\s+/', ' ', (string) $clean));
    }

    private function countryAliasesMatch(?Country $country, string $normalizedName, string $isoCode): bool
    {
        if (!$country) {
            return false;
        }

        $dbCode = strtoupper(trim((string) $country->code));
        if ($isoCode !== '' && $dbCode !== '' && $dbCode === $isoCode) {
            return true;
        }

        $aliasMap = [
            'KW' => ['kuwait', 'state of kuwait', 'الكويت', 'دولة الكويت', 'kw'],
            'SA' => ['saudi arabia', 'kingdom of saudi arabia', 'ksa', 'saudi', 'السعودية', 'السعوديه', 'المملكة العربية السعودية', 'المملكه العربيه السعوديه', 'sa'],
            'AE' => ['uae', 'united arab emirates', 'u a e', 'emirates', 'الامارات', 'الإمارات', 'الامارات العربية المتحدة', 'الإمارات العربية المتحدة', 'ae'],
            'QA' => ['qatar', 'state of qatar', 'قطر', 'دولة قطر', 'qa'],
            'BH' => ['bahrain', 'kingdom of bahrain', 'البحرين', 'مملكة البحرين', 'bh'],
            'OM' => ['oman', 'sultanate of oman', 'عمان', 'سلطنة عمان', 'om'],
            'EG' => ['egypt', 'arab republic of egypt', 'مصر', 'جمهورية مصر العربية', 'eg'],
            'JO' => ['jordan', 'hashemite kingdom of jordan', 'الاردن', 'الأردن', 'المملكة الأردنية الهاشمية', 'jo'],
        ];

        $en = $this->normalizeCountryToken((string) $country->name_en);
        $ar = $this->normalizeCountryToken((string) $country->name_ar);

        foreach ($aliasMap as $code => $aliases) {
            $dbBelongs = ($dbCode === $code) || in_array($en, $aliases, true) || in_array($ar, $aliases, true);
            if (!$dbBelongs) {
                continue;
            }
            if ($isoCode !== '' && $isoCode === $code) {
                return true;
            }
            if ($normalizedName !== '' && in_array($normalizedName, $aliases, true)) {
                return true;
            }
        }

        if ($normalizedName !== '') {
            if (($en !== '' && $en === $normalizedName) || ($ar !== '' && $ar === $normalizedName)) {
                return true;
            }
        }

        return false;
    }

    private function resolveOrderOriginCountry(ScheduledTripOrder $order): array
    {
        $country = $order->pickupCountry
            ?: ($order->pickupCity ? $order->pickupCity->country : null)
            ?: ($order->scheduledTrip && $order->scheduledTrip->route ? $order->scheduledTrip->route->originCountry : null)
            ?: ($order->scheduledTrip && $order->scheduledTrip->route && $order->scheduledTrip->route->originCity
                ? $order->scheduledTrip->route->originCity->country
                : null);

        $countryId = $order->pickup_country_id
            ?: ($order->pickupCity ? $order->pickupCity->country_id : null)
            ?: ($order->scheduledTrip && $order->scheduledTrip->route ? $order->scheduledTrip->route->origin_country_id : null)
            ?: ($order->scheduledTrip && $order->scheduledTrip->route && $order->scheduledTrip->route->originCity
                ? $order->scheduledTrip->route->originCity->country_id
                : null);

        return [$countryId ? (int) $countryId : null, $country];
    }

    private function resolveTripOriginCountry(ScheduledTrip $trip): array
    {
        $route = $trip->route;
        $country = $route
            ? ($route->originCountry ?: ($route->originCity ? $route->originCity->country : null))
            : null;
        $countryId = $route
            ? ($route->origin_country_id ?: ($route->originCity ? $route->originCity->country_id : null))
            : null;

        return [$countryId ? (int) $countryId : null, $country];
    }

    private function resolveShipmentOriginCountry(Shipment $shipment): array
    {
        $country = $shipment->pickupCountry
            ?: ($shipment->pickupCity ? $shipment->pickupCity->country : null);
        $countryId = $shipment->pickup_country_id
            ?: ($shipment->pickupCity ? $shipment->pickupCity->country_id : null);

        return [$countryId ? (int) $countryId : null, $country];
    }

    private function matchesOriginCountry(?int $originCountryId, ?Country $originCountry, array $countryFilter): bool
    {
        if (empty($countryFilter['has_filter'])) {
            return true;
        }

        if (!empty($countryFilter['country_id'])) {
            if ($originCountryId !== null && (int) $originCountryId === (int) $countryFilter['country_id']) {
                return true;
            }
            if ($originCountry) {
                $targetCode = strtoupper(trim((string) ($countryFilter['country_code'] ?? '')));
                $targetName = $this->normalizeCountryToken((string) ($countryFilter['country_name'] ?? ''));
                if ($this->countryAliasesMatch($originCountry, $targetName, $targetCode)) {
                    return true;
                }
            }
            return false;
        }

        // Driver is in a country that has no matching row in DB `countries` (e.g. Egypt while DB only lists GCC).
        if ($originCountry) {
            $rawName = $this->normalizeCountryToken((string) ($countryFilter['raw_name'] ?? $countryFilter['country_name'] ?? ''));
            $isoCode = strtoupper(trim((string) ($countryFilter['country_code'] ?? '')));
            return $this->countryAliasesMatch($originCountry, $rawName, $isoCode);
        }

        return false;
    }

    /**
     * Fetch matching pending invitations, new uninvited orders, and open scheduled trips for a driver.
     */
    private function getDriverRequestBuckets(int $driverId, array $countryFilter = []): array
    {
        [$driverTruckTypeId, $driverTruckSubTypeId] = $this->resolveDriverTruckFilters($driverId);

        $excludedTripIds = ScheduledTripDriverInvitation::where('driver_id', $driverId)
            ->whereIn('status', ['rejected', 'accepted'])
            ->whereNull('scheduled_trip_order_id')
            ->pluck('scheduled_trip_id')
            ->filter()
            ->toArray();

        $invitations = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.customer.companyProfile',
            'scheduledTripOrder.pickupCountry',
            'scheduledTripOrder.pickupCity.country',
            'scheduledTripOrder.dropoffCountry',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.truckSubType',
            'scheduledTripOrder.shipmentType',
            'scheduledTripOrder.shipmentNature',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity.country',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTrip.shipmentType',
            'scheduledTrip.shipmentNature',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->filter(function ($inv) use ($countryFilter, $driverTruckTypeId) {
                $ord = $inv->scheduledTripOrder;
                $trip = $inv->scheduledTrip;
                if (!$trip && !$ord) {
                    return false;
                }

                $itemTruckTypeId = $ord
                    ? ($ord->truck_type_id ?: ($trip ? $trip->truck_type_id : null))
                    : ($trip ? $trip->truck_type_id : null);
                if ($driverTruckTypeId && $itemTruckTypeId && (int) $itemTruckTypeId !== (int) $driverTruckTypeId) {
                    return false;
                }

                if ($ord) {
                    [$countryId, $country] = $this->resolveOrderOriginCountry($ord);
                } else {
                    [$countryId, $country] = $this->resolveTripOriginCountry($trip);
                }

                return $this->matchesOriginCountry($countryId, $country, $countryFilter);
            })
            ->values();

        $seenOrderIds = $invitations->pluck('scheduled_trip_order_id')->filter()->values()->toArray();
        $seenTripIds = $invitations->whereNull('scheduled_trip_order_id')
            ->pluck('scheduled_trip_id')
            ->filter()
            ->values()
            ->toArray();

        // Orders already handled by THIS driver (accepted, completed, rejected, pending invitation)
        $handledOrderIds = ScheduledTripDriverInvitation::whereNotNull('scheduled_trip_order_id')
            ->where('driver_id', $driverId)
            ->whereIn('status', ['pending', 'accepted', 'rejected', 'completed'])
            ->pluck('scheduled_trip_order_id')
            ->unique()
            ->values()
            ->toArray();

        // Cutoff timestamps per trip if driver already completed a trip-level run
        $completedTripCutoffs = ScheduledTripDriverInvitation::where('driver_id', $driverId)
            ->whereNull('scheduled_trip_order_id')
            ->where('status', 'completed')
            ->whereNotNull('scheduled_trip_id')
            ->get()
            ->groupBy('scheduled_trip_id')
            ->map(fn($rows) => $rows->max('updated_at'));

        $matchingOrders = collect();
        if ($driverTruckTypeId) {
            $orderQuery = ScheduledTripOrder::with([
                'scheduledTrip.route.originCountry',
                'scheduledTrip.route.originCity',
                'scheduledTrip.route.destinationCountry',
                'scheduledTrip.route.destinationCity',
                'scheduledTrip.truckType',
                'scheduledTrip.shipmentType',
                'pickupCountry',
                'pickupCity.country',
                'dropoffCountry',
                'dropoffCity',
                'truckType',
                'truckSubType',
                'shipmentType',
                'shipmentNature',
                'driverInvitations',
            ])
                ->whereIn('status', ['new', 'confirmed'])
                ->whereHas('scheduledTrip', function ($sq) {
                    $sq->whereNotIn('status', ['completed', 'cancelled', 'canceled']);
                })
                ->where(function ($q) use ($driverTruckTypeId) {
                    $q->where('truck_type_id', $driverTruckTypeId)
                      ->orWhere(function ($tq) use ($driverTruckTypeId) {
                          $tq->whereNull('truck_type_id')
                             ->whereHas('scheduledTrip', fn($sq) => $sq->where('truck_type_id', $driverTruckTypeId));
                      });
                });

            if ($driverTruckSubTypeId) {
                $orderQuery->where(function ($q) use ($driverTruckSubTypeId) {
                    $q->whereNull('truck_sub_type_id')
                      ->orWhere('truck_sub_type_id', $driverTruckSubTypeId);
                });
            }

            if (!empty($handledOrderIds)) {
                $orderQuery->whereNotIn('id', $handledOrderIds);
            }
            if (!empty($seenOrderIds)) {
                $orderQuery->whereNotIn('id', $seenOrderIds);
            }

            $matchingOrders = $orderQuery->latest()->get()->filter(function ($ord) use ($completedTripCutoffs, $driverId, $countryFilter) {
                $cutoff = $completedTripCutoffs->get($ord->scheduled_trip_id);
                if ($cutoff && $ord->created_at && $ord->created_at->lte($cutoff)) {
                    return false;
                }
                [$countryId, $country] = $this->resolveOrderOriginCountry($ord);
                if (!$this->matchesOriginCountry($countryId, $country, $countryFilter)) {
                    return false;
                }
                $required = max(1, (int) ($ord->number_of_trucks ?: $ord->booked_capacity ?: 1));
                $acceptedCount = $ord->driverInvitations
                    ->whereIn('status', ['accepted', 'completed'])
                    ->count();
                if ($acceptedCount === 0 && $ord->driver_id && (int) $ord->driver_id !== $driverId) {
                    return false;
                }
                // Show order to remaining drivers as long as fleet slots remain open
                return ($required - $acceptedCount) > 0;
            })->values();
        }

        $orderCoveredTripIds = $matchingOrders->pluck('scheduled_trip_id')->filter()->unique()->values()->toArray();
        $excludedForTripMatch = array_values(array_unique(array_merge($excludedTripIds, $seenTripIds, $orderCoveredTripIds)));

        $matchingTrips = collect();
        if ($driverTruckTypeId) {
            $matchingQuery = ScheduledTrip::with([
                'route.originCountry',
                'route.originCity.country',
                'route.destinationCountry',
                'route.destinationCity',
                'truckType',
                'truckSubType',
                'shipmentType',
                'shipmentNature',
                'driverInvitations',
            ])
                ->whereIn('status', ['published', 'open', 'scheduled', 'active'])
                ->whereNotIn('status', ['completed', 'cancelled', 'canceled'])
                ->where(function ($q) use ($driverId) {
                    $q->whereNull('driver_id')
                      ->orWhere('driver_id', $driverId);
                })
                ->whereDoesntHave('orders', function ($oq) {
                    $oq->whereNotIn('status', ['canceled', 'cancelled']);
                })
                ->where('truck_type_id', $driverTruckTypeId);

            if (!empty($excludedForTripMatch)) {
                $matchingQuery->whereNotIn('id', $excludedForTripMatch);
            }

            if ($driverTruckSubTypeId) {
                $matchingQuery->where(function ($q) use ($driverTruckSubTypeId) {
                    $q->whereNull('truck_sub_type_id')
                      ->orWhere('truck_sub_type_id', $driverTruckSubTypeId);
                });
            }

            $matchingTrips = $matchingQuery->latest()->get()->filter(function ($trip) use ($countryFilter) {
                [$countryId, $country] = $this->resolveTripOriginCountry($trip);
                if (!$this->matchesOriginCountry($countryId, $country, $countryFilter)) {
                    return false;
                }
                $required = max(1, (int) ($trip->number_of_trucks ?: 1));
                $accepted = $trip->driverInvitations
                    ->whereNull('scheduled_trip_order_id')
                    ->whereIn('status', ['accepted', 'completed'])
                    ->count();
                return ($required - $accepted) > 0;
            })->values();
        }

        return [
            'invitations'    => $invitations,
            'matchingOrders' => $matchingOrders,
            'matchingTrips'  => $matchingTrips,
        ];
    }

    /**
     * Resolve ScheduledTrip, ScheduledTripOrder, and ScheduledTripDriverInvitation from flexible ID formats.
     */
    private function resolveTripEntities($id, int $driverId): array
    {
        $rawId = trim((string) $id);
        $isExplicitTripId = (bool) preg_match('/^#?(TRP-|trip_)/i', $rawId);
        $cleanId = preg_replace('/^#?(TRP-|SL-|trip_)/i', '', $rawId);

        $order = null;
        $trip = null;
        $inv = null;

        if (preg_match('/^#?order_(\d+)/i', $cleanId, $om)) {
            $order = ScheduledTripOrder::find((int) $om[1]);
            if ($order) {
                $trip = $order->scheduledTrip;
                $inv = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
                    ->where('driver_id', $driverId)
                    ->latest()
                    ->first();
            }
        } elseif (str_starts_with(strtoupper($cleanId), 'STO-')) {
            $order = ScheduledTripOrder::where('order_number', $cleanId)->first();
            if ($order) {
                $trip = $order->scheduledTrip;
            }
        } else {
            $numId = (int) $cleanId;
            if ($numId > 0) {
                if ($isExplicitTripId) {
                    $trip = ScheduledTrip::find($numId);
                    if (!$trip) {
                        $inv = ScheduledTripDriverInvitation::where('id', $numId)
                            ->where('driver_id', $driverId)
                            ->first() ?: ScheduledTripDriverInvitation::find($numId);
                        if ($inv) {
                            $trip = $inv->scheduledTrip;
                            $order = $inv->scheduledTripOrder;
                        }
                    }
                    if (!$trip && !$inv) {
                        $order = ScheduledTripOrder::find($numId);
                        if ($order) {
                            $trip = $order->scheduledTrip;
                        }
                    }
                } else {
                    $inv = ScheduledTripDriverInvitation::where('id', $numId)
                        ->where('driver_id', $driverId)
                        ->first() ?: ScheduledTripDriverInvitation::find($numId);
                    if ($inv) {
                        $trip = $inv->scheduledTrip;
                        $order = $inv->scheduledTripOrder;
                    }
                    if (!$trip) {
                        $trip = ScheduledTrip::find($numId);
                    }
                    if (!$trip && !$order) {
                        $order = ScheduledTripOrder::find($numId);
                        if ($order) {
                            $trip = $order->scheduledTrip;
                        }
                    }
                }
            }
        }

        if ($trip && !$inv) {
            $inv = ScheduledTripDriverInvitation::where('scheduled_trip_id', $trip->id)
                ->where('driver_id', $driverId)
                ->when($order, fn($q) => $q->where('scheduled_trip_order_id', $order->id))
                ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
                ->latest('id')
                ->first();
            if (!$order && $inv && $inv->scheduledTripOrder) {
                $order = $inv->scheduledTripOrder;
            }
        } elseif ($order && !$inv) {
            $inv = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
                ->where('driver_id', $driverId)
                ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
                ->latest('id')
                ->first();
        }

        if ($trip && !$order) {
            $order = $trip->orders()
                ->where(function ($q) use ($driverId) {
                    $q->where('driver_id', $driverId)->orWhereNull('driver_id');
                })
                ->orderByRaw('CASE WHEN driver_id = ? THEN 0 ELSE 1 END', [$driverId])
                ->latest('id')
                ->first() ?: ($trip->orders()->latest('id')->first());
        }

        return [
            'trip'       => $trip,
            'order'      => $order,
            'invitation' => $inv,
        ];
    }

    /**
     * Save uploaded POD photos and signature image to public/upload/proof_of_delivery.
     */
    private function storePodMedia(Request $request, $refId): array
    {
        $uploadDir = public_path('upload/proof_of_delivery');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $savedPhotos = [];
        if ($request->hasFile('photos')) {
            $photos = $request->file('photos');
            if (!is_array($photos)) {
                $photos = [$photos];
            }
            foreach ($photos as $idx => $photoFile) {
                if ($photoFile && $photoFile->isValid()) {
                    $ext = $photoFile->getClientOriginalExtension() ?: 'jpg';
                    $targetName = 'pod_photo_' . $refId . '_' . time() . '_' . $idx . '_' . uniqid() . '.' . $ext;
                    $photoFile->move($uploadDir, $targetName);
                    $savedPhotos[] = 'upload/proof_of_delivery/' . $targetName;
                }
            }
        }

        $savedSignature = null;
        if ($request->hasFile('signature')) {
            $sigFile = $request->file('signature');
            if ($sigFile && $sigFile->isValid()) {
                $ext = $sigFile->getClientOriginalExtension() ?: 'png';
                $sigName = 'pod_sig_' . $refId . '_' . time() . '.' . $ext;
                $sigFile->move($uploadDir, $sigName);
                $savedSignature = 'upload/proof_of_delivery/' . $sigName;
            }
        } elseif ($request->filled('signature_base64')) {
            $rawB64 = $request->input('signature_base64');
            $cleanB64 = preg_replace('#^data:image/\w+;base64,#i', '', $rawB64);
            $decoded = base64_decode($cleanB64);
            if ($decoded !== false && strlen($decoded) > 0) {
                $sigName = 'pod_sig_' . $refId . '_' . time() . '.png';
                file_put_contents($uploadDir . '/' . $sigName, $decoded);
                $savedSignature = 'upload/proof_of_delivery/' . $sigName;
            }
        }

        return [$savedPhotos, $savedSignature];
    }

    /**
     * Mark a trip as completed by driver (confirms delivery, saves POD, and credits earnings).
     */
    public function completeTrip(Request $request, $id)
    {
        $driverId = (int) $this->resolveDriverId($request);
        $rawId = trim((string) $id);

        // Handle direct shipment invitations (shipment_inv_{id})
        if (preg_match('/^#?shipment_inv_(\d+)/i', $rawId, $m)) {
            $sInvId = (int) $m[1];
            $sInv = ShipmentDriverInvitation::with('shipment')->find($sInvId);
            if (!$sInv) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Shipment invitation not found',
                ], 404);
            }

            $shipment = $sInv->shipment;
            $sInv->status = 'completed';
            $sInv->save();

            if ($shipment) {
                $shipment->status = 'delivered';
                $shipment->driver_id = $driverId;
                $shipment->save();

                ShipmentDriverInvitation::where('shipment_id', $shipment->id)
                    ->where('driver_id', $driverId)
                    ->where('status', '!=', 'completed')
                    ->update(['status' => 'completed']);
            }

            $refId = $shipment ? ('ship_' . $shipment->id) : ('ship_inv_' . $sInv->id);
            [$savedPhotos, $savedSignature] = $this->storePodMedia($request, $refId);

            $pod = ProofOfDelivery::create([
                'scheduled_trip_order_id' => null,
                'scheduled_trip_id'       => null,
                'driver_id'               => $driverId,
                'receiver_name'           => $request->input('receiver_name') ?: 'Receiver',
                'receiver_phone'          => $request->input('receiver_phone'),
                'receiver_civil_id'       => $request->input('receiver_civil_id'),
                'photos'                  => !empty($savedPhotos) ? $savedPhotos : null,
                'signature_image'         => $savedSignature,
                'notes'                   => $request->input('notes'),
                'delivered_at'            => now(),
            ]);

            $amount = floatval($sInv->offered_price > 0 ? $sInv->offered_price : ($shipment ? $shipment->total_cost : 0));
            $driver = User::find($driverId);
            if ($driver && $amount > 0 && $shipment) {
                $alreadyCredited = WalletTransaction::where('user_id', $driver->id)
                    ->where('type', 'trip_earnings')
                    ->where('description', 'like', '%shipment #' . $shipment->id . '%')
                    ->exists();
                if (!$alreadyCredited) {
                    $lastBal = (float) ($driver->credit ?? 0.0);
                    $newBal = $lastBal + $amount;
                    $driver->credit = $newBal;
                    $driver->save();

                    WalletTransaction::create([
                        'user_id'           => $driver->id,
                        'scheduled_trip_id' => null,
                        'amount'            => $amount,
                        'type'              => 'trip_earnings',
                        'balance_after'     => $newBal,
                        'description'       => 'Earnings for completing delivery for shipment #' . $shipment->id . ' (أرباح إكمال الرحلة وتسليم الشحنة)',
                    ]);
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Delivery completed successfully and Proof of Delivery saved.',
                'data'    => [
                    'shipment'          => $shipment,
                    'proof_of_delivery' => $pod,
                    'amount'            => $amount,
                    'earnings'          => $driver ? (float) $driver->credit : 0,
                ],
            ], 200);
        }

        $resolved = $this->resolveTripEntities($rawId, $driverId);
        $trip = $resolved['trip'];
        $order = $resolved['order'];
        $inv = $resolved['invitation'];

        if (!$trip && !$order && !$inv) {
            return response()->json([
                'status'  => false,
                'message' => 'Trip or order not found'
            ], 404);
        }

        if (!$inv && $order) {
            $inv = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $order->id)
                ->where('driver_id', $driverId)
                ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 WHEN status = 'completed' THEN 1 ELSE 2 END")
                ->latest('id')
                ->first();
        }
        if (!$inv && $trip) {
            $inv = ScheduledTripDriverInvitation::where('scheduled_trip_id', $trip->id)
                ->where('driver_id', $driverId)
                ->latest('id')
                ->first();
        }

        $fleet = $this->getOrderFleetStats($order, $trip);
        if (!$fleet['fleet_ready']) {
            return response()->json([
                'status'  => false,
                'message' => "Cannot complete delivery yet. Waiting for all required trucks to be accepted ({$fleet['accepted']}/{$fleet['required']}).",
                'data'    => ['fleet' => $fleet],
            ], 422);
        }

        $refId = ($order ? $order->id : ($trip ? $trip->id : '0')) . '_d' . $driverId;
        [$savedPhotos, $savedSignature] = $this->storePodMedia($request, $refId);

        // Key ProofOfDelivery per driver/invitation so multi-truck drivers don't overwrite each other
        $podMatch = $order
            ? [
                'scheduled_trip_order_id' => $order->id,
                'driver_id'               => $driverId,
            ]
            : [
                'scheduled_trip_order_id' => null,
                'scheduled_trip_id'       => $trip ? $trip->id : null,
                'driver_id'               => $driverId,
            ];

        $pod = ProofOfDelivery::updateOrCreate(
            $podMatch,
            [
                'scheduled_trip_invitation_id' => $inv ? $inv->id : null,
                'scheduled_trip_id'            => $trip ? $trip->id : ($order ? $order->scheduled_trip_id : null),
                'driver_id'                    => $driverId,
                'receiver_name'                => $request->input('receiver_name') ?: ($order ? $order->arrival_contact_name : 'Receiver'),
                'receiver_phone'               => $request->input('receiver_phone') ?: ($order ? $order->arrival_contact_phone : null),
                'receiver_civil_id'            => $request->input('receiver_civil_id'),
                'photos'                       => !empty($savedPhotos) ? $savedPhotos : null,
                'signature_image'              => $savedSignature,
                'notes'                        => $request->input('notes'),
                'delivered_at'                 => now(),
            ]
        );

        // Update THIS driver's matching invitation(s) to completed
        if ($inv) {
            $inv->status = 'completed';
            $inv->driver_stage = 'delivered';
            $inv->completed_at = now();
            $inv->save();
        }

        $invQuery = ScheduledTripDriverInvitation::where('driver_id', $driverId)
            ->where('status', '!=', 'completed');
        $invQuery->where(function ($q) use ($order, $trip, $inv) {
            if ($inv) {
                $q->orWhere('id', $inv->id);
            }
            if ($order) {
                $q->orWhere('scheduled_trip_order_id', $order->id);
            }
            if ($trip && !$order) {
                $q->orWhere(function ($sq) use ($trip) {
                    $sq->where('scheduled_trip_id', $trip->id)
                       ->whereNull('scheduled_trip_order_id');
                });
            }
        });
        $invQuery->update([
            'status'       => 'completed',
            'driver_stage' => 'delivered',
            'completed_at' => now(),
        ]);

        // Update aggregate order & trip status only once all required trucks finish
        if ($order) {
            if (empty($order->driver_id)) {
                $order->driver_id = $driverId;
            }
            if ($request->filled('receiver_name') && empty($order->arrival_contact_name)) {
                $order->arrival_contact_name = $request->input('receiver_name');
            }
            if ($request->filled('receiver_phone') && empty($order->arrival_contact_phone)) {
                $order->arrival_contact_phone = $request->input('receiver_phone');
            }
            $order->save();
            $this->syncMultiTruckOrderStatus($order, $trip);
        } elseif ($trip) {
            $updatedFleet = $this->getOrderFleetStats(null, $trip);
            if ($updatedFleet['completed'] >= $updatedFleet['required'] || ($updatedFleet['accepted'] <= 1 && (int) $trip->available_capacity <= 0)) {
                $trip->status = 'completed';
                $trip->driver_id = $trip->driver_id ?: $driverId;
                $trip->save();
            }
        }

        $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);
        $creditedNow = $this->creditDriverInvitationPayout($driverId, $inv, $trip, $order, 'final');
        $amount = (float) $pricing['driver_price'];
        $driver = User::find($driverId);

        return response()->json([
            'status'  => true,
            'message' => 'Delivery completed successfully and Proof of Delivery saved.',
            'data'    => [
                'trip'              => $trip ? $trip->fresh() : null,
                'order'             => $order ? $order->fresh() : null,
                'proof_of_delivery' => $pod,
                'amount'            => $amount,
                'credited_now'      => $creditedNow,
                'earnings'          => $driver ? (float) $driver->credit : 0,
            ]
        ], 200);
    }

    /**
     * Get all active trips for the driver (accepted invitations / ongoing trips).
     */
    public function activeTrips(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) $driverId = $found->id;
        }
        if (!$driverId) $driverId = 18;

        $invitations = ScheduledTripDriverInvitation::with([
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTrip.orders.pickupCity',
            'scheduledTrip.orders.dropoffCity',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.shipmentType',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'accepted')
            ->where(function ($q) {
                $q->whereNull('driver_stage')
                  ->orWhere('driver_stage', '!=', 'delivered');
            })
            ->whereHas('scheduledTrip', function ($sq) {
                $sq->whereNotIn('status', ['completed', 'cancelled', 'canceled']);
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_trip_order_id')
                  ->orWhereHas('scheduledTripOrder', function ($oq) {
                      $oq->whereNotIn('status', ['canceled', 'cancelled']);
                  });
            })
            ->latest()
            ->get();

        $list = [];
        foreach ($invitations as $inv) {
            $trip = $inv->scheduledTrip;
            if (!$trip) continue;

            $order = $inv->scheduledTripOrder ?: $trip->orders->first();

            $fromCity = 'Farwaniya';
            $toCity = 'Dammam';
            if ($order) {
                if ($order->pickupCity && ($order->pickupCity->name_en || $order->pickupCity->name_ar)) {
                    $fromCity = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
                } elseif ($trip->route && $trip->route->originCity) {
                    $fromCity = $trip->route->originCity->name_en;
                }
                if ($order->dropoffCity && ($order->dropoffCity->name_en || $order->dropoffCity->name_ar)) {
                    $toCity = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
                } elseif ($trip->route && $trip->route->destinationCity) {
                    $toCity = $trip->route->destinationCity->name_en;
                }
            } elseif ($trip->route) {
                $fromCity = $trip->route->originCity ? $trip->route->originCity->name_en : 'Farwaniya';
                $toCity = $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dammam';
            }

            $fleet = $this->getOrderFleetStats($order, $trip);
            $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);
            $orderStatus = $inv->driver_stage ?: ($order && $order->status ? strtolower($order->status) : 'confirmed');
            $statusTag = 'In progress';
            if (!$fleet['fleet_ready'] && $fleet['required'] > 1) {
                $statusTag = "Waiting fleet ({$fleet['accepted']}/{$fleet['required']})";
            } elseif ($orderStatus === 'in_transit') {
                $statusTag = 'In transit';
            } elseif ($orderStatus === 'arrived_dropoff') {
                $statusTag = 'Arrived at drop-off';
            } elseif ($orderStatus === 'arrived_pickup') {
                $statusTag = 'Arrived at pickup';
            } elseif ($orderStatus === 'loaded') {
                $statusTag = 'Loaded';
            } elseif ($orderStatus === 'confirmed') {
                $statusTag = 'Confirmed';
            }

            $price = (float) $pricing['driver_price'];
            $weight = $trip->total_weight_ton ? number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : '20,000 Kg';
            if ($order && $order->weight) {
                $weight = number_format(floatval($order->weight), 0) . ' Kg';
            }

            $truckType = ($order && $order->truckType) ? $order->truckType->name_en : ($trip->truckType ? $trip->truckType->name_en : 'Flatbed');
            $cargoType = ($order && $order->shipmentType) ? $order->shipmentType->name_en : ($order && $order->shipment_name ? $order->shipment_name : 'General cargo');

            $loadingDate = $trip->trip_date ? date('d M, H:i', strtotime($trip->trip_date . ' ' . ($trip->trip_time ?: '14:00'))) : '16 Jul, 14:00';
            if ($order && $order->loading_date) {
                $loadingDate = date('d M', strtotime($order->loading_date)) . ($order->loading_time ? ', ' . substr($order->loading_time, 0, 5) : ', 14:00');
            }

            $dist = ($trip->route && $trip->route->estimated_distance) ? number_format($trip->route->estimated_distance, 0) . ' km' : '472 km';
            $note = $order && $order->notes ? $order->notes : ($trip->notes ?: 'Driver assists');

            $list[] = [
                'id'                   => 'TRP-' . $trip->id,
                'trip_id'              => $trip->id,
                'order_id'             => $order ? $order->id : null,
                'order_number'         => $order ? $order->order_number : null,
                'status_tag'           => $statusTag,
                'order_status'         => $orderStatus,
                'posted_ago'           => $inv->updated_at ? $inv->updated_at->diffForHumans() : 'Active now',
                'has_unread_dot'       => false,
                'from_city'            => $fromCity,
                'to_city'              => $toCity,
                'price_kwd'            => $price,
                'formatted_price'      => 'KWD ' . number_format($price, 0),
                'loading_date'         => $loadingDate,
                'total_weight'         => 'Up to ' . $weight,
                'truck_type'           => $truckType,
                'note'                 => $note,
                'cargo_type'           => $cargoType,
                'distance'             => $dist,
                'category'             => 'all',
                'requested_trucks'     => 1,
                'fleet_required'       => $fleet['required'],
                'fleet_accepted'       => $fleet['accepted'],
                'fleet_remaining'      => $fleet['remaining'],
                'fleet_ready'          => $fleet['fleet_ready'],
                'driver_payment_type'  => $pricing['payment_type'],
                'driver_payout_label'  => $pricing['payout_label'],
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Active trips fetched successfully',
            'data'    => $list,
        ], 200);
    }

    /**
     * Get all completed / delivered trips for the driver.
     */
    public function completedTrips(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) $driverId = $found->id;
        }
        if (!$driverId) $driverId = 18;

        $list = $this->getCompletedTripsForDriver($driverId);

        return response()->json([
            'status'  => true,
            'message' => 'Completed trips fetched successfully',
            'data'    => $list,
        ], 200);
    }

    /**
     * Get detailed earnings summary and activity history for driver.
     */
    public function earningsHistory(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) $driverId = $found->id;
        }
        if (!$driverId) $driverId = 18;

        $driver = $user ?: User::find($driverId);
        $driverCredit = $driver ? (float) ($driver->credit ?? 0.0) : 0.0;

        // 1. Fetch wallet transactions for driver of type trip_earnings (and deposits)
        $txQuery = WalletTransaction::with([
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.orders.customer',
            'scheduledTrip.orders.pickupCity',
            'scheduledTrip.orders.dropoffCity',
            'scheduledTrip.orders.truckType',
            'scheduledTrip.orders.proofOfDelivery',
        ])
            ->where('user_id', $driverId)
            ->whereIn('type', ['trip_earnings', 'deposit'])
            ->latest('id')
            ->get();

        $transactions = [];
        $totalEarnings = 0.0;

        foreach ($txQuery as $tx) {
            $amount = (float) $tx->amount;
            $totalEarnings += $amount;

            $trip = $tx->scheduledTrip;
            $order = null;

            // Try to match specific order from description or trip
            if (preg_match('/(STO-\d+-[A-Z0-9]+)/i', $tx->description, $matches)) {
                $orderNumber = $matches[1];
                $order = ScheduledTripOrder::with(['customer', 'pickupCity', 'dropoffCity', 'truckType', 'proofOfDelivery'])
                    ->where('order_number', $orderNumber)
                    ->first();
            } elseif (preg_match('/order #(\d+)/i', $tx->description, $matches)) {
                $order = ScheduledTripOrder::with(['customer', 'pickupCity', 'dropoffCity', 'truckType', 'proofOfDelivery'])
                    ->find((int) $matches[1]);
            }

            if (!$order && $trip && $trip->orders && $trip->orders->isNotEmpty()) {
                $order = $trip->orders->where('driver_id', $driverId)->first() ?: $trip->orders->first();
            }

            $fromCity = 'Farwaniya';
            $toCity = 'Dubai';

            if ($order) {
                if ($order->pickupCity) {
                    $fromCity = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
                }
                if ($order->dropoffCity) {
                    $toCity = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
                }
            } elseif ($trip && $trip->route) {
                if ($trip->route->originCity) $fromCity = $trip->route->originCity->name_en ?: 'Farwaniya';
                if ($trip->route->destinationCity) $toCity = $trip->route->destinationCity->name_en ?: 'Dubai';
            }
            $routeText = "$fromCity → $toCity";

            $tripCode = $trip ? "TRP-{$trip->id}" : 'TRP-14';
            $orderNumber = $order ? $order->order_number : ($tripCode);
            $customerName = ($order && $order->customer)
                ? (($order->customer->company_name) ?: "{$order->customer->fname} {$order->customer->lname}")
                : 'essa co';

            $receiverName = ($order && $order->proofOfDelivery && $order->proofOfDelivery->receiver_name)
                ? $order->proofOfDelivery->receiver_name
                : ($order ? ($order->arrival_contact_name ?? 'Authorized Recipient') : 'Authorized Recipient');

            $receiverPhone = ($order && $order->proofOfDelivery && $order->proofOfDelivery->receiver_phone)
                ? $order->proofOfDelivery->receiver_phone
                : ($order ? ($order->arrival_contact_phone ?? '') : '');

            $designatedTruck = 'Tanker (2021) · Plate: 1234';
            if ($order && $order->truckType) {
                $designatedTruck = ($order->truckType->name_en ?: 'Tanker') . ' (2021) · Plate: 1234';
            } elseif ($trip && $trip->truckType) {
                $designatedTruck = ($trip->truckType->name_en ?: 'Truck') . ' · Plate: 1234';
            }

            $dateFormatted = $tx->created_at ? $tx->created_at->format('d M Y') : date('d M Y');
            $timeFormatted = $tx->created_at ? $tx->created_at->format('H:i') : date('H:i');
            $refNumber = 'PAY-' . ($tx->created_at ? $tx->created_at->format('Y-md') : date('Y-md')) . '-' . str_pad($tx->id, 4, '0', STR_PAD_LEFT);

            $transactions[] = [
                'id'                      => "tx_{$tx->id}",
                'transaction_id'          => $tx->id,
                'reference_number'        => $refNumber,
                'trip_id'                 => "#$tripCode",
                'order_number'            => $orderNumber,
                'route'                   => $routeText,
                'from_city'               => $fromCity,
                'to_city'                 => $toCity,
                'amount'                  => $amount,
                'amount_formatted'        => 'KD ' . number_format($amount, 3),
                'amount_short'            => number_format($amount, 0) . ' KD',
                'status'                  => 'paid',
                'status_label'            => 'Paid & Settled',
                'date'                    => $dateFormatted,
                'time'                    => $timeFormatted,
                'status_date_label'       => "Paid on {$dateFormatted}, {$timeFormatted}",
                'delivered_date'          => "Delivered {$dateFormatted}",
                'customer_name'           => $customerName,
                'receiver_name'           => $receiverName,
                'receiver_phone'          => $receiverPhone,
                'designated_truck'        => $designatedTruck,
                'distance'                => '100 km',
                'payout_method'           => 'Driver Wallet (محفظة السائق)',
                'bank_name'               => 'Salasil Driver Wallet',
                'masked_iban'             => 'Wallet ID •••• ' . ($driver ? $driver->client_code : 'DR-95818'),
                'base_fare'               => 'KD ' . number_format($amount, 3),
                'waiting_time_fee'        => 'KD 0.000',
                'fuel_surcharge'          => 'KD 0.000',
                'platform_fee'            => 'KD 0.000',
                'net_payout'              => 'KD ' . number_format($amount, 3),
                'balance_after'           => (float) $tx->balance_after,
                'balance_after_formatted' => 'KD ' . number_format((float) $tx->balance_after, 3),
                'description'             => $tx->description,
                'breakdown'               => [
                    ['label' => 'Base trip fare', 'amount' => 'KD ' . number_format($amount, 3), 'is_deduction' => false],
                    ['label' => 'Waiting time (0 min)', 'amount' => 'KD 0.000', 'is_deduction' => false],
                    ['label' => 'Fuel surcharge', 'amount' => 'KD 0.000', 'is_deduction' => false],
                    ['label' => 'Platform fee (Covered)', 'amount' => 'KD 0.000', 'is_deduction' => false],
                ],
            ];
        }

        $effectiveBalance = max($totalEarnings, $driverCredit);
        $chartData = $this->buildEarningsChartData($txQuery, $driverId);

        return response()->json([
            'status'  => true,
            'message' => 'Driver earnings and payment activity history retrieved successfully',
            'data'    => [
                'total_earnings'           => $effectiveBalance,
                'total_earnings_formatted' => 'KD ' . number_format($effectiveBalance, 3),
                'total_earnings_short'     => number_format($effectiveBalance, 0) . ' KD',
                'available_balance'        => 'KD ' . number_format($effectiveBalance, 3),
                'available_balance_short'  => number_format($effectiveBalance, 0) . ' KD',
                'pending_earnings'         => 'KD 0.000',
                'trips_count'              => count($transactions),
                'growth_rate'              => '+100%',
                'weekly_date_range'        => $chartData['this_week']['date_range'],
                'weekly_bars'              => $chartData['this_week']['bars'],
                'chart_data'               => $chartData,
                'transactions'             => $transactions,
            ],
        ], 200);
    }

    /**
     * Build dynamic earnings chart data for this_week, this_month, and all_time periods.
     */
    private function buildEarningsChartData($txQuery, $driverId)
    {
        $now = \Carbon\Carbon::now();

        // 1. THIS WEEK (Sat to Fri)
        $startOfWeek = $now->copy()->startOfWeek(\Carbon\Carbon::SATURDAY);
        $endOfWeek = $startOfWeek->copy()->addDays(6)->endOfDay();
        $weekDateRange = $startOfWeek->format('d M') . ' – ' . $endOfWeek->format('d M');

        $weekDays = [];
        $maxWeekAmount = 0.0;
        $totalWeekEarnings = 0.0;
        $totalWeekTrips = 0;

        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $dayStr = $dayDate->format('Y-m-d');
            $dayLabel = $dayDate->format('D');

            $dayAmount = 0.0;
            $dayTrips = 0;
            foreach ($txQuery as $tx) {
                if ($tx->created_at && $tx->created_at->format('Y-m-d') === $dayStr) {
                    $dayAmount += (float) $tx->amount;
                    $dayTrips++;
                }
            }

            if ($dayAmount > $maxWeekAmount) {
                $maxWeekAmount = $dayAmount;
            }
            $totalWeekEarnings += $dayAmount;
            $totalWeekTrips += $dayTrips;

            $weekDays[] = [
                'day_label'        => $dayLabel,
                'full_date'        => $dayDate->format('d M'),
                'amount'           => $dayAmount,
                'amount_formatted' => 'KD ' . number_format($dayAmount, 3),
                'amount_short'     => number_format($dayAmount, 0) . ' KD',
                'trips_count'      => $dayTrips,
                'is_today'         => ($dayStr === $now->format('Y-m-d')),
                'raw_date'         => $dayStr,
            ];
        }

        $weekBars = [];
        foreach ($weekDays as $wd) {
            $ratio = 0.12;
            if ($maxWeekAmount > 0) {
                $ratio = max(0.12, ($wd['amount'] / $maxWeekAmount) * 0.95);
            }
            $isSelected = ($wd['amount'] > 0 && $wd['amount'] == $maxWeekAmount) || ($maxWeekAmount == 0 && $wd['is_today']);
            $wd['ratio'] = round($ratio, 2);
            $wd['is_selected'] = $isSelected;
            $weekBars[] = $wd;
        }

        // 2. THIS MONTH (Weeks of current month)
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $monthDateRange = $startOfMonth->format('d M') . ' – ' . $endOfMonth->format('d M Y');

        $monthPeriods = [
            ['label' => '1-7 ' . $now->format('M'), 'start' => 1, 'end' => 7],
            ['label' => '8-14 ' . $now->format('M'), 'start' => 8, 'end' => 14],
            ['label' => '15-21 ' . $now->format('M'), 'start' => 15, 'end' => 21],
            ['label' => '22-' . $endOfMonth->format('d') . ' ' . $now->format('M'), 'start' => 22, 'end' => (int) $endOfMonth->format('d')],
        ];

        $maxMonthPeriodAmount = 0.0;
        $totalMonthEarnings = 0.0;
        $totalMonthTrips = 0;
        $monthBarsData = [];

        foreach ($monthPeriods as $p) {
            $pAmount = 0.0;
            $pTrips = 0;
            foreach ($txQuery as $tx) {
                if ($tx->created_at && $tx->created_at->format('Y-m') === $now->format('Y-m')) {
                    $dayNum = (int) $tx->created_at->format('d');
                    if ($dayNum >= $p['start'] && $dayNum <= $p['end']) {
                        $pAmount += (float) $tx->amount;
                        $pTrips++;
                    }
                }
            }
            if ($pAmount > $maxMonthPeriodAmount) {
                $maxMonthPeriodAmount = $pAmount;
            }
            $totalMonthEarnings += $pAmount;
            $totalMonthTrips += $pTrips;

            $monthBarsData[] = [
                'day_label'        => $p['label'],
                'full_date'        => $p['label'],
                'amount'           => $pAmount,
                'amount_formatted' => 'KD ' . number_format($pAmount, 3),
                'amount_short'     => number_format($pAmount, 0) . ' KD',
                'trips_count'      => $pTrips,
            ];
        }

        $monthBars = [];
        foreach ($monthBarsData as $mb) {
            $ratio = 0.12;
            if ($maxMonthPeriodAmount > 0) {
                $ratio = max(0.12, ($mb['amount'] / $maxMonthPeriodAmount) * 0.95);
            }
            $isSelected = ($mb['amount'] > 0 && $mb['amount'] == $maxMonthPeriodAmount);
            $mb['ratio'] = round($ratio, 2);
            $mb['is_selected'] = $isSelected;
            $monthBars[] = $mb;
        }

        // 3. ALL TIME (Last 6 months)
        $allTimeDateRange = $now->copy()->subMonths(5)->format('M') . ' – ' . $now->format('M Y');
        $maxAllTimeAmount = 0.0;
        $allTimeBarsData = [];
        $totalAllTimeEarnings = 0.0;
        $totalAllTimeTrips = 0;

        for ($m = 5; $m >= 0; $m--) {
            $monthDate = $now->copy()->subMonths($m);
            $mStr = $monthDate->format('Y-m');
            $mLabel = $monthDate->format('M');

            $mAmount = 0.0;
            $mTrips = 0;
            foreach ($txQuery as $tx) {
                if ($tx->created_at && $tx->created_at->format('Y-m') === $mStr) {
                    $mAmount += (float) $tx->amount;
                    $mTrips++;
                }
            }
            if ($mAmount > $maxAllTimeAmount) {
                $maxAllTimeAmount = $mAmount;
            }
            $totalAllTimeEarnings += $mAmount;
            $totalAllTimeTrips += $mTrips;

            $allTimeBarsData[] = [
                'day_label'        => $mLabel,
                'full_date'        => $monthDate->format('F Y'),
                'amount'           => $mAmount,
                'amount_formatted' => 'KD ' . number_format($mAmount, 3),
                'amount_short'     => number_format($mAmount, 0) . ' KD',
                'trips_count'      => $mTrips,
                'is_current'       => ($m === 0),
            ];
        }

        $allTimeBars = [];
        foreach ($allTimeBarsData as $ab) {
            $ratio = 0.12;
            if ($maxAllTimeAmount > 0) {
                $ratio = max(0.12, ($ab['amount'] / $maxAllTimeAmount) * 0.95);
            }
            $isSelected = ($ab['amount'] > 0 && $ab['amount'] == $maxAllTimeAmount) || ($maxAllTimeAmount == 0 && ($ab['is_current'] ?? false));
            $ab['ratio'] = round($ratio, 2);
            $ab['is_selected'] = $isSelected;
            $allTimeBars[] = $ab;
        }

        return [
            'this_week' => [
                'title'        => 'Weekly earnings',
                'date_range'   => $weekDateRange,
                'growth_rate'  => '+100%',
                'trips_count'  => $totalWeekTrips,
                'km_driven'    => (string) ($totalWeekTrips * 100),
                'avg_per_trip' => $totalWeekTrips > 0 ? (string) round($totalWeekEarnings / $totalWeekTrips) : '0',
                'bars'         => $weekBars,
            ],
            'this_month' => [
                'title'        => 'Monthly earnings',
                'date_range'   => $monthDateRange,
                'growth_rate'  => '+100%',
                'trips_count'  => $totalMonthTrips,
                'km_driven'    => (string) ($totalMonthTrips * 100),
                'avg_per_trip' => $totalMonthTrips > 0 ? (string) round($totalMonthEarnings / $totalMonthTrips) : '0',
                'bars'         => $monthBars,
            ],
            'all_time' => [
                'title'        => 'All time earnings',
                'date_range'   => $allTimeDateRange,
                'growth_rate'  => '+100%',
                'trips_count'  => $totalAllTimeTrips,
                'km_driven'    => (string) ($totalAllTimeTrips * 100),
                'avg_per_trip' => $totalAllTimeTrips > 0 ? (string) round($totalAllTimeEarnings / $totalAllTimeTrips) : '0',
                'bars'         => $allTimeBars,
            ],
        ];
    }

    /**
     * Helper to get list of completed trips for a driver.
     */
    public function getCompletedTripsForDriver($driverId)
    {
        $list = [];
        $seenOrderIds = [];
        $seenTripIds = [];
        $tripIdsWithoutOrderInv = [];

        // 1. Completed invitations for THIS driver (supports multi-truck independent completions)
        $invitations = ScheduledTripDriverInvitation::with([
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.shipmentType',
            'scheduledTripOrder.proofOfDelivery',
            'proofOfDelivery',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->get();

        foreach ($invitations as $inv) {
            $order = $inv->scheduledTripOrder;
            $trip = $inv->scheduledTrip ?: ($order ? $order->scheduledTrip : null);
            if (!$trip && !$order) {
                continue;
            }
            if ($order) {
                if (in_array($order->id, $seenOrderIds)) continue;
                $seenOrderIds[] = $order->id;
            }
            if ($trip) {
                $seenTripIds[] = $trip->id;
            }

            $fromCity = 'Farwaniya';
            $toCity = 'Dubai';
            if ($order && $order->pickupCity) {
                $fromCity = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->originCity) {
                $fromCity = $trip->route->originCity->name_en;
            }
            if ($order && $order->dropoffCity) {
                $toCity = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->destinationCity) {
                $toCity = $trip->route->destinationCity->name_en;
            }

            $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);
            $price = (float) $pricing['driver_price'];
            $pod = $inv->proofOfDelivery ?: ($order
                ? (ProofOfDelivery::where('scheduled_trip_order_id', $order->id)->where('driver_id', $driverId)->latest('id')->first() ?: $order->proofOfDelivery)
                : ProofOfDelivery::where('scheduled_trip_id', $trip->id)->where('driver_id', $driverId)->latest('id')->first());

            $deliveredAt = ($pod && $pod->delivered_at) ? $pod->delivered_at : ($inv->completed_at ?: $inv->updated_at);
            $receiverName = ($pod && $pod->receiver_name) ? $pod->receiver_name : ($order && $order->arrival_contact_name ? $order->arrival_contact_name : 'Received on-site');

            $weight = ($order && $order->weight)
                ? number_format(floatval($order->weight), 0) . ' Kg'
                : ($trip && $trip->total_weight_ton ? number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : '20,000 Kg');
            $truckType = ($order && $order->truckType) ? $order->truckType->name_en : ($trip && $trip->truckType ? $trip->truckType->name_en : 'Tanker');
            $cargoType = ($order && $order->shipmentType) ? $order->shipmentType->name_en : ($order && $order->shipment_name ? $order->shipment_name : 'General Cargo');
            $dist = ($trip && $trip->route && $trip->route->estimated_distance) ? number_format($trip->route->estimated_distance, 0) . ' km' : '100 km';

            $list[] = [
                'id'              => $order ? ('ORD-' . $order->id) : ('TRP-' . $trip->id),
                'trip_id'         => $trip ? $trip->id : null,
                'order_id'        => $order ? $order->id : null,
                'order_number'    => $order ? $order->order_number : null,
                'status_tag'      => 'Completed',
                'order_status'    => 'delivered',
                'posted_ago'      => $deliveredAt ? 'Delivered ' . $deliveredAt->diffForHumans() : 'Delivered recently',
                'has_unread_dot'  => false,
                'from_city'       => $fromCity,
                'to_city'         => $toCity,
                'price_kwd'       => $price,
                'formatted_price' => 'KWD ' . number_format($price, 0),
                'loading_date'    => $deliveredAt ? date('d M, H:i', strtotime($deliveredAt)) : 'Delivered',
                'total_weight'    => 'Up to ' . $weight,
                'truck_type'      => $truckType,
                'note'            => $pod ? ('Received by: ' . $receiverName) : 'Completed delivery',
                'cargo_type'      => $cargoType,
                'distance'        => $dist,
                'category'        => 'completed',
                'state'           => 'completed',
                'receiver_name'   => $pod ? $receiverName : null,
                'has_pod'         => (bool) $pod,
            ];
        }

        // 2. Legacy delivered orders directly assigned to driver without invitation row
        $orders = ScheduledTripOrder::with([
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'pickupCity',
            'dropoffCity',
            'truckType',
            'shipmentType',
            'proofOfDelivery',
        ])
            ->where('driver_id', $driverId)
            ->when(!empty($seenOrderIds), fn($q) => $q->whereNotIn('id', $seenOrderIds))
            ->where(function ($q) {
                $q->where('status', 'delivered')
                  ->orWhereHas('proofOfDelivery');
            })
            ->latest('updated_at')
            ->get();

        foreach ($orders as $order) {
            $seenOrderIds[] = $order->id;
            $trip = $order->scheduledTrip;
            if ($trip) $seenTripIds[] = $trip->id;

            $fromCity = 'Farwaniya';
            $toCity = 'Dubai';
            if ($order->pickupCity) {
                $fromCity = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->originCity) {
                $fromCity = $trip->route->originCity->name_en;
            }

            if ($order->dropoffCity) {
                $toCity = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->destinationCity) {
                $toCity = $trip->route->destinationCity->name_en;
            }

            $pricing = $this->resolveDriverTripPricing($trip, $order);
            $price = (float) $pricing['driver_price'];
            $pod = $order->proofOfDelivery;
            $deliveredAt = ($pod && $pod->delivered_at) ? $pod->delivered_at : $order->updated_at;
            $receiverName = ($pod && $pod->receiver_name) ? $pod->receiver_name : ($order->arrival_contact_name ?: 'Received on-site');

            $weight = $order->weight ? number_format(floatval($order->weight), 0) . ' Kg' : ($trip && $trip->total_weight_ton ? number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : '100,000 Kg');
            $truckType = ($order->truckType) ? $order->truckType->name_en : ($trip && $trip->truckType ? $trip->truckType->name_en : 'Tanker');
            $cargoType = ($order->shipmentType) ? $order->shipmentType->name_en : ($order->shipment_name ?: 'General Cargo');
            $dist = ($trip && $trip->route && $trip->route->estimated_distance) ? number_format($trip->route->estimated_distance, 0) . ' km' : '100 km';

            $list[] = [
                'id'              => 'TRP-' . ($trip ? $trip->id : $order->id),
                'trip_id'         => $trip ? $trip->id : null,
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'status_tag'      => 'Completed',
                'order_status'    => 'delivered',
                'posted_ago'      => $deliveredAt ? 'Delivered ' . $deliveredAt->diffForHumans() : 'Delivered recently',
                'has_unread_dot'  => false,
                'from_city'       => $fromCity,
                'to_city'         => $toCity,
                'price_kwd'       => $price,
                'formatted_price' => 'KWD ' . number_format($price, 0),
                'loading_date'    => $deliveredAt ? date('d M, H:i', strtotime($deliveredAt)) : 'Delivered',
                'total_weight'    => 'Up to ' . $weight,
                'truck_type'      => $truckType,
                'note'            => 'Received by: ' . $receiverName,
                'cargo_type'      => $cargoType,
                'distance'        => $dist,
                'category'        => 'completed',
                'state'           => 'completed',
                'receiver_name'   => $receiverName,
                'has_pod'         => $pod ? true : false,
            ];
        }

        // 2. Completed invitations without duplicate orders
        $invitations = ScheduledTripDriverInvitation::with([
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTripOrder',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->get();

        foreach ($invitations as $inv) {
            if ($inv->scheduled_trip_order_id && in_array($inv->scheduled_trip_order_id, $seenOrderIds)) {
                continue;
            }
            if (!$inv->scheduled_trip_order_id && in_array($inv->scheduled_trip_id, $tripIdsWithoutOrderInv)) {
                continue;
            }

            $trip = $inv->scheduledTrip;
            if (!$trip) continue;
            $seenTripIds[] = $trip->id;

            $fromCity = ($trip->route && $trip->route->originCity) ? $trip->route->originCity->name_en : 'Kuwait City';
            $toCity = ($trip->route && $trip->route->destinationCity) ? $trip->route->destinationCity->name_en : 'Dammam';
            $price = floatval($inv->offered_price ?: $trip->price);
            $dist = ($trip->route && $trip->route->estimated_distance) ? number_format($trip->route->estimated_distance, 0) . ' km' : '472 km';

            $list[] = [
                'id'              => 'TRP-' . $trip->id,
                'trip_id'         => $trip->id,
                'order_id'        => null,
                'order_number'    => null,
                'status_tag'      => 'Completed',
                'order_status'    => 'delivered',
                'posted_ago'      => $inv->updated_at ? 'Delivered ' . $inv->updated_at->diffForHumans() : 'Delivered',
                'has_unread_dot'  => false,
                'from_city'       => $fromCity,
                'to_city'         => $toCity,
                'price_kwd'       => $price,
                'formatted_price' => 'KWD ' . number_format($price, 0),
                'loading_date'    => $inv->updated_at ? date('d M, H:i', strtotime($inv->updated_at)) : 'Delivered',
                'total_weight'    => 'Up to 20,000 Kg',
                'truck_type'      => $trip->truckType ? $trip->truckType->name_en : 'Flatbed',
                'note'            => 'Completed delivery',
                'cargo_type'      => 'General cargo',
                'distance'        => $dist,
                'category'        => 'completed',
                'state'           => 'completed',
                'receiver_name'   => null,
                'has_pod'         => false,
            ];
        }

        // 3. Direct completed scheduled trips assigned to driver without duplicate orders/invitations
        $directTrips = ScheduledTrip::with([
            'route.originCity',
            'route.destinationCity',
            'truckType',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'completed')
            ->when(!empty($seenTripIds), function ($q) use ($seenTripIds) {
                $q->whereNotIn('id', $seenTripIds);
            })
            ->latest('updated_at')
            ->get();

        foreach ($directTrips as $trip) {
            $fromCity = ($trip->route && $trip->route->originCity) ? $trip->route->originCity->name_en : 'Kuwait City';
            $toCity = ($trip->route && $trip->route->destinationCity) ? $trip->route->destinationCity->name_en : 'Dammam';
            $price = floatval($trip->price);
            $dist = ($trip->route && $trip->route->estimated_distance) ? number_format($trip->route->estimated_distance, 0) . ' km' : '472 km';

            $list[] = [
                'id'              => 'TRP-' . $trip->id,
                'trip_id'         => $trip->id,
                'order_id'        => null,
                'order_number'    => null,
                'status_tag'      => 'Completed',
                'order_status'    => 'delivered',
                'posted_ago'      => $trip->updated_at ? 'Delivered ' . $trip->updated_at->diffForHumans() : 'Delivered',
                'has_unread_dot'  => false,
                'from_city'       => $fromCity,
                'to_city'         => $toCity,
                'price_kwd'       => $price,
                'formatted_price' => 'KWD ' . number_format($price, 0),
                'loading_date'    => $trip->updated_at ? date('d M, H:i', strtotime($trip->updated_at)) : 'Delivered',
                'total_weight'    => $trip->total_weight_ton ? 'Up to ' . number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : 'Up to 20,000 Kg',
                'truck_type'      => $trip->truckType ? $trip->truckType->name_en : 'Flatbed',
                'note'            => 'Completed delivery',
                'cargo_type'      => 'General cargo',
                'distance'        => $dist,
                'category'        => 'completed',
                'state'           => 'completed',
                'receiver_name'   => null,
                'has_pod'         => ProofOfDelivery::where('scheduled_trip_id', $trip->id)->where('driver_id', $driverId)->exists(),
            ];
        }

        return $list;
    }

    /**
     * Get all available trip requests / invitations for the driver.
     */
    public function tripRequests(Request $request)
    {
        $driverId = (int) $this->resolveDriverId($request);
        $countryFilter = $this->resolveDriverCountryFilter($request);
        [$driverTruckTypeId] = $this->resolveDriverTruckFilters($driverId);
        $buckets = $this->getDriverRequestBuckets($driverId, $countryFilter);
        $invitations = $buckets['invitations'];
        $matchingOrders = $buckets['matchingOrders'];
        $matchingTrips = $buckets['matchingTrips'];

        $items = [];
        $seenTripIds = [];

        foreach ($invitations as $inv) {
            $trip = $inv->scheduledTrip;
            $order = $inv->scheduledTripOrder;
            if (!$trip && !$order) continue;

            if ($trip) {
                $seenTripIds[] = $trip->id;
            }

            $originCity = $order && $order->pickupCity 
                ? $order->pickupCity->name_en 
                : ($trip && $trip->route && $trip->route->originCity ? $trip->route->originCity->name_en : 'Kuwait City');

            $destCity   = $order && $order->dropoffCity 
                ? $order->dropoffCity->name_en 
                : ($trip && $trip->route && $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dammam');

            $originCountryName = $order && $order->pickupCountry
                ? $order->pickupCountry->name_en
                : ($trip && $trip->route && $trip->route->originCountry ? $trip->route->originCountry->name_en : 'Kuwait');
            $destCountryName = $order && $order->dropoffCountry
                ? $order->dropoffCountry->name_en
                : ($trip && $trip->route && $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : 'Saudi Arabia');

            // Strictly separate driver per-truck payout ONLY! Never customer total_price.
            $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);
            $fleet = $this->getOrderFleetStats($order, $trip);
            $price = (float) $pricing['driver_price'];

            $weight     = $order && $order->weight 
                ? number_format($order->weight, 0) . ' Kg' 
                : ($trip && $trip->total_weight_ton ? 'Up to ' . number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : 'Up to 20,000 Kg');

            $truckType  = $order && $order->truckType 
                ? $order->truckType->name_en 
                : ($trip && $trip->truckType ? $trip->truckType->name_en : 'Flatbed');

            $cargoType  = $order && $order->shipmentType 
                ? $order->shipmentType->name_en 
                : ($trip && $trip->shipmentType ? $trip->shipmentType->name_en : 'General cargo');

            $distance   = $trip && $trip->route && $trip->route->estimated_distance 
                ? number_format($trip->route->estimated_distance, 0) . ' km' 
                : '472 km';

            $dateStr    = $order && $order->loading_date 
                ? date('d M', strtotime($order->loading_date)) . ($order->loading_time ? ', ' . substr($order->loading_time, 0, 5) : '')
                : ($trip && $trip->trip_date ? date('d M', strtotime($trip->trip_date)) . ($trip->trip_time ? ', ' . substr($trip->trip_time, 0, 5) : '') : '16 Jul, 14:00');

            $note       = $order && $order->notes ? $order->notes : ($trip && $trip->notes ? $trip->notes : 'Quick loading');

            $tripDateTimestamp = $order && $order->loading_date 
                ? strtotime($order->loading_date) 
                : ($trip && $trip->trip_date ? strtotime($trip->trip_date) : time());

            $category = ($tripDateTimestamp > strtotime('today 23:59:59')) ? 'upcoming' : 'today';
            $statusTag = ($category === 'upcoming') ? 'Upcoming' : 'New';

            $items[] = [
                'id'                   => (string) $inv->id,
                'trip_id'              => (string) ($trip ? $trip->id : ($order ? $order->scheduled_trip_id : '')),
                'order_number'         => $order ? $order->order_number : null,
                'status_tag'           => $statusTag,
                'posted_ago'           => 'Posted ' . $inv->created_at->diffForHumans(null, true) . ' ago',
                'has_unread_dot'       => true,
                'from_city'            => $originCity,
                'to_city'              => $destCity,
                'from_country'         => $originCountryName,
                'to_country'           => $destCountryName,
                'origin_country'       => $originCountryName,
                'destination_country'  => $destCountryName,
                'price_kwd'            => $price,
                'loading_date'         => $dateStr,
                'total_weight'         => $weight,
                'truck_type'           => $truckType,
                'note'                 => $note,
                'cargo_type'           => $cargoType,
                'distance'             => $distance,
                'category'             => $category,
                'state'                => 'pending',
                'token'                => $inv->token,
                'requested_trucks'     => 1,
                'fleet_required'       => $fleet['required'],
                'fleet_accepted'       => $fleet['accepted'],
                'fleet_remaining'      => $fleet['remaining'],
                'driver_payment_type'  => $pricing['payment_type'],
                'driver_payout_label'  => $pricing['payout_label'],
            ];
        }

        // Add matching new customer orders that don't yet have a driver invitation
        foreach ($matchingOrders as $ord) {
            $trip = $ord->scheduledTrip;
            if ($trip) {
                $seenTripIds[] = $trip->id;
            }

            $originCity = $ord->pickupCity
                ? ($ord->pickupCity->name_en ?: $ord->pickupCity->name_ar)
                : ($trip && $trip->route && $trip->route->originCity ? $trip->route->originCity->name_en : 'Kuwait City');
            $destCity = $ord->dropoffCity
                ? ($ord->dropoffCity->name_en ?: $ord->dropoffCity->name_ar)
                : ($trip && $trip->route && $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dammam');
            [, $ordOriginCountry] = $this->resolveOrderOriginCountry($ord);
            $originCountryName = $ordOriginCountry ? $ordOriginCountry->name_en : 'Kuwait';
            $destCountryName = $ord->dropoffCountry
                ? $ord->dropoffCountry->name_en
                : ($trip && $trip->route && $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : 'Saudi Arabia');
            $pricing = $this->resolveDriverTripPricing($trip, $ord);
            $fleet = $this->getOrderFleetStats($ord, $trip);
            $price = (float) $pricing['driver_price'];
            $weight = $ord->weight
                ? number_format($ord->weight, 0) . ' Kg'
                : ($trip && $trip->total_weight_ton ? 'Up to ' . number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : 'Up to 20,000 Kg');
            $truckType = $ord->truckType
                ? $ord->truckType->name_en
                : ($trip && $trip->truckType ? $trip->truckType->name_en : 'Truck');
            $cargoType = $ord->shipmentType
                ? $ord->shipmentType->name_en
                : ($trip && $trip->shipmentType ? $trip->shipmentType->name_en : 'General cargo');
            $distance = $trip && $trip->route && $trip->route->estimated_distance
                ? number_format($trip->route->estimated_distance, 0) . ' km'
                : '472 km';
            $dateStr = $ord->loading_date
                ? date('d M', strtotime($ord->loading_date)) . ($ord->loading_time ? ', ' . substr($ord->loading_time, 0, 5) : '')
                : ($trip && $trip->trip_date ? date('d M', strtotime($trip->trip_date)) . ($trip->trip_time ? ', ' . substr($trip->trip_time, 0, 5) : '') : 'Available now');
            $note = $ord->notes ?: ($ord->goods_description ?: ($trip && $trip->notes ? $trip->notes : 'Scheduled cargo order'));
            $tripDateTimestamp = $ord->loading_date
                ? strtotime($ord->loading_date)
                : ($trip && $trip->trip_date ? strtotime($trip->trip_date) : time());
            $category = ($tripDateTimestamp > strtotime('today 23:59:59')) ? 'upcoming' : 'today';
            $statusTag = ($category === 'upcoming') ? 'Upcoming' : 'New';

            $items[] = [
                'id'                   => 'order_' . $ord->id,
                'trip_id'              => (string) ($trip ? $trip->id : $ord->scheduled_trip_id),
                'order_number'         => $ord->order_number,
                'status_tag'           => $fleet['required'] > 1 ? "{$fleet['remaining']} left" : $statusTag,
                'posted_ago'           => $ord->created_at ? 'Posted ' . $ord->created_at->diffForHumans(null, true) . ' ago' : 'Posted recently',
                'has_unread_dot'       => true,
                'from_city'            => $originCity,
                'to_city'              => $destCity,
                'from_country'         => $originCountryName,
                'to_country'           => $destCountryName,
                'origin_country'       => $originCountryName,
                'destination_country'  => $destCountryName,
                'price_kwd'            => $price,
                'loading_date'         => $dateStr,
                'total_weight'         => $weight,
                'truck_type'           => $truckType,
                'note'                 => $note,
                'cargo_type'           => $cargoType,
                'distance'             => $distance,
                'category'             => $category,
                'requested_trucks'     => 1,
                'fleet_required'       => $fleet['required'],
                'fleet_accepted'       => $fleet['accepted'],
                'fleet_remaining'      => $fleet['remaining'],
                'driver_payment_type'  => $pricing['payment_type'],
                'driver_payout_label'  => $pricing['payout_label'],
                'state'                => 'pending',
                'token'                => null,
            ];
        }

        // Add matching scheduled trips that are not yet in items
        foreach ($matchingTrips as $trip) {
            if (in_array($trip->id, $seenTripIds)) {
                continue;
            }
            $seenTripIds[] = $trip->id;

            $originCity = $trip->route && $trip->route->originCity ? $trip->route->originCity->name_en : 'Kuwait City';
            $destCity   = $trip->route && $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dubai';
            $originCountryName = $trip->route && $trip->route->originCountry ? $trip->route->originCountry->name_en : 'Kuwait';
            $destCountryName   = $trip->route && $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : 'UAE';
            $pricing = $this->resolveDriverTripPricing($trip);
            $fleet = $this->getOrderFleetStats(null, $trip);
            $price      = (float) $pricing['driver_price'];
            $weight     = $trip->total_weight_ton ? 'Up to ' . number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : 'Up to 25,000 Kg';
            $truckType  = $trip->truckType ? $trip->truckType->name_en : 'Truck';
            $cargoType  = $trip->shipmentType ? $trip->shipmentType->name_en : 'General cargo';
            $distance   = $trip->route && $trip->route->estimated_distance 
                ? number_format($trip->route->estimated_distance, 0) . ' km' 
                : '100 km';
            $dateStr    = $trip->trip_date 
                ? date('d M', strtotime($trip->trip_date)) . ($trip->trip_time ? ', ' . substr($trip->trip_time, 0, 5) : '') 
                : 'Available now';
            $note       = $trip->notes ?: 'Scheduled trip';
            $tripDateTimestamp = $trip->trip_date ? strtotime($trip->trip_date) : time();
            $category = ($tripDateTimestamp > strtotime('today 23:59:59')) ? 'upcoming' : 'today';
            $statusTag = $fleet['required'] > 1 ? "{$fleet['remaining']} left" : (($category === 'upcoming') ? 'Upcoming' : 'New');

            $items[] = [
                'id'                   => 'trip_' . $trip->id,
                'trip_id'              => (string) $trip->id,
                'order_number'         => null,
                'status_tag'           => $statusTag,
                'posted_ago'           => 'Posted ' . $trip->created_at->diffForHumans(null, true) . ' ago',
                'has_unread_dot'       => true,
                'from_city'            => $originCity,
                'to_city'              => $destCity,
                'from_country'         => $originCountryName,
                'to_country'           => $destCountryName,
                'origin_country'       => $originCountryName,
                'destination_country'  => $destCountryName,
                'price_kwd'            => $price,
                'loading_date'         => $dateStr,
                'total_weight'         => $weight,
                'truck_type'           => $truckType,
                'note'                 => $note,
                'cargo_type'           => $cargoType,
                'distance'             => $distance,
                'category'             => $category,
                'state'                => 'pending',
                'token'                => null,
                'requested_trucks'     => 1,
                'fleet_required'       => $fleet['required'],
                'fleet_accepted'       => $fleet['accepted'],
                'fleet_remaining'      => $fleet['remaining'],
                'driver_payment_type'  => $pricing['payment_type'],
                'driver_payout_label'  => $pricing['payout_label'],
            ];
        }

        // 2. Also fetch shipment invitations directly sent to this driver with status 'pending'
        $shipmentInvs = ShipmentDriverInvitation::with([
            'shipment.pickupCountry',
            'shipment.pickupCity.country',
            'shipment.dropoffCountry',
            'shipment.dropoffCity',
            'shipment.truckType',
            'shipment.shipmentType',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        foreach ($shipmentInvs as $sInv) {
            $sh = $sInv->shipment;
            if (!$sh) continue;
            if ($driverTruckTypeId && $sh->truck_type_id && (int) $sh->truck_type_id !== (int) $driverTruckTypeId) {
                continue;
            }
            [$shCountryId, $shCountry] = $this->resolveShipmentOriginCountry($sh);
            if (!$this->matchesOriginCountry($shCountryId, $shCountry, $countryFilter)) {
                continue;
            }
            $shOriginCountryName = $shCountry ? $shCountry->name_en : 'Kuwait';
            $shDestCountryName = $sh->dropoffCountry ? $sh->dropoffCountry->name_en : 'Saudi Arabia';
            $items[] = [
                'id'                   => 'shipment_inv_' . $sInv->id,
                'trip_id'              => (string) $sh->id,
                'status_tag'           => 'New',
                'posted_ago'           => 'Posted ' . $sInv->created_at->diffForHumans(null, true) . ' ago',
                'has_unread_dot'       => true,
                'from_city'            => $sh->pickupCity ? $sh->pickupCity->name_en : 'Kuwait City',
                'to_city'              => $sh->dropoffCity ? $sh->dropoffCity->name_en : 'Dammam',
                'from_country'         => $shOriginCountryName,
                'to_country'           => $shDestCountryName,
                'origin_country'       => $shOriginCountryName,
                'destination_country'  => $shDestCountryName,
                'price_kwd'            => floatval($sInv->offered_price > 0 ? $sInv->offered_price : $sh->total_cost),
                'loading_date'         => $sh->pickup_date ? date('d M, H:i', strtotime($sh->pickup_date)) : 'Today',
                'total_weight'         => $sh->weight_ton ? 'Up to ' . number_format($sh->weight_ton * 1000, 0) . ' Kg' : 'Up to 20,000 Kg',
                'truck_type'           => $sh->truckType ? $sh->truckType->name_en : 'Flatbed',
                'note'                 => $sh->special_instructions ?: 'Quick loading',
                'cargo_type'           => $sh->shipmentType ? $sh->shipmentType->name_en : 'General cargo',
                'distance'             => '420 km',
                'category'             => 'today',
                'state'                => 'pending',
                'token'                => $sInv->token,
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Trip requests retrieved successfully',
            'data'    => $items
        ], 200);
    }

    /**
     * Get single trip request details.
     */
    public function tripDetails(Request $request, $id)
    {
        $cleanId = trim(preg_replace('/^#?(SL-|TRP-|trip_)/i', '', (string) $id));
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                $driverId = $found->id;
            }
        }
        if (!$driverId) {
            $driverId = 18;
        }

        // Handle direct shipment invitations (shipment_inv_{id})
        if (str_starts_with($cleanId, 'shipment_inv_')) {
            $sInvId = (int) str_replace('shipment_inv_', '', $cleanId);
            $sInv = ShipmentDriverInvitation::with([
                'shipment.originCountry',
                'shipment.originCity',
                'shipment.destinationCountry',
                'shipment.destinationCity',
                'shipment.truckType',
                'shipment.truckSubType',
                'shipment.shipmentType',
                'shipment.shipmentNature',
                'shipment.customer.companyProfile',
            ])->find($sInvId);

            if ($sInv && $sInv->shipment) {
                $sh = $sInv->shipment;
                $sOriginCity = $sh->originCity ? $sh->originCity->name_en : 'Kuwait City';
                $sDestCity = $sh->destinationCity ? $sh->destinationCity->name_en : 'Dammam';
                $sOriginCountry = $sh->originCountry ? $sh->originCountry->name_en : 'Kuwait';
                $sDestCountry = $sh->destinationCountry ? $sh->destinationCountry->name_en : 'Kuwait';
                $sIsIntl = strcasecmp($sOriginCountry, $sDestCountry) !== 0;

                $sPickupTitle = $sOriginCity . ($sOriginCountry ? " ({$sOriginCountry})" : '');
                $sDropoffTitle = $sDestCity . ($sDestCountry ? " ({$sDestCountry})" : '');

                $sCustomer = $sh->customer;
                $sCustomerName = $sCustomer ? trim(($sCustomer->fname ?? '') . ' ' . ($sCustomer->lname ?? '')) : 'Shipper';
                if (empty($sCustomerName)) $sCustomerName = 'Shipper';

                return response()->json([
                    'status'  => true,
                    'message' => 'Trip details retrieved successfully',
                    'data'    => [
                        'request_id'             => '#SH-' . $sh->id,
                        'id'                     => 'shipment_inv_' . $sInv->id,
                        'order_number'           => null,
                        'trip_id'                => (string) $sh->id,
                        'status'                 => 'New request',
                        'posted_ago'             => 'Posted ' . $sInv->created_at->diffForHumans(null, true) . ' ago',
                        'from_city'              => $sOriginCity,
                        'to_city'                => $sDestCity,
                        'origin_country'         => $sOriginCountry,
                        'destination_country'    => $sDestCountry,
                        'is_international'       => $sIsIntl,
                        'route_type'             => $sIsIntl ? 'International Cross-Border' : 'Domestic Route',
                        'trip_value_kwd'         => floatval($sInv->offered_price > 0 ? $sInv->offered_price : ($sh->initial_price ?: 0)),
                        'distance'               => '420 km',
                        'duration'               => '~5 h 30 m',
                        'payout_guarantee'       => 'Guaranteed direct payment processed to your driver wallet upon successful arrival, unloading, and delivery sign-off.',
                        'designated_truck'       => '',
                        'designated_truck_plate' => '',
                        'required_credentials'   => [
                            'Valid Heavy Driving License & Civil ID',
                            'Truck Registration (Mulkiya) & Insurance',
                        ],
                        'pickup_title'           => $sPickupTitle,
                        'pickup_address'         => $sh->pickup_address ?: $sPickupTitle,
                        'pickup_area'            => $sh->pickup_area ?: '',
                        'pickup_lat'             => $sh->pickup_lat !== null ? (float) $sh->pickup_lat : null,
                        'pickup_lng'             => $sh->pickup_lng !== null ? (float) $sh->pickup_lng : null,
                        'pickup_time'            => $sh->scheduled_date ? date('D d M Y', strtotime($sh->scheduled_date)) : 'Today',
                        'pickup_time_slot'       => '',
                        'loading_contact_name'   => $sh->loading_contact_name ?: '',
                        'loading_contact_phone'  => $sh->loading_contact_phone ?: '',
                        'pickup_instructions'    => '',
                        'dropoff_title'          => $sDropoffTitle,
                        'dropoff_address'        => $sh->dropoff_address ?: $sDropoffTitle,
                        'dropoff_area'           => $sh->dropoff_area ?: '',
                        'dropoff_lat'            => $sh->dropoff_lat !== null ? (float) $sh->dropoff_lat : null,
                        'dropoff_lng'            => $sh->dropoff_lng !== null ? (float) $sh->dropoff_lng : null,
                        'dropoff_time'           => 'Scheduled arrival',
                        'dropoff_time_slot'      => '',
                        'arrival_contact_name'   => $sh->arrival_contact_name ?: '',
                        'arrival_contact_phone'  => $sh->arrival_contact_phone ?: '',
                        'delivery_instructions'  => '',
                        'shipment_name'          => $sh->shipment_name ?: ('Shipment #' . $sh->id),
                        'cargo_type'             => $sh->shipmentType ? $sh->shipmentType->name_en : 'General cargo',
                        'cargo_nature'           => $sh->shipmentNature ? $sh->shipmentNature->name_en : 'General',
                        'total_weight'           => $sh->weight ? number_format((float)$sh->weight, 0) . ' Kg' : 'Up to 20,000 Kg',
                        'truck_type'             => $sh->truckType ? $sh->truckType->name_en : 'Truck',
                        'truck_sub_type'         => $sh->truckSubType ? $sh->truckSubType->name_en : '',
                        'number_of_trucks'       => 1,
                        'packaging'              => 'Palletised',
                        'dimensions'             => null,
                        'volume'                 => null,
                        'packages_count'         => (int) ($sh->packages_count ?? 1),
                        'is_fragile'             => (bool) $sh->is_fragile,
                        'goods_description'      => $sh->goods_description ?: ($sh->shipment_description ?: ''),
                        'hs_code'                => $sh->hs_code ?: '',
                        'special_equipment'      => [],
                        'customs_clearance'      => false,
                        'instructions'           => $sh->goods_description ?: '',
                        'documents'              => [],
                        'customer_name'          => $sCustomerName,
                        'customer_company'       => ($sCustomer && $sCustomer->companyProfile) ? $sCustomer->companyProfile->company_name : $sCustomerName,
                        'customer_client_code'   => $sCustomer ? ($sCustomer->client_code ?? '') : '',
                        'customer_initials'      => mb_strtoupper(mb_substr($sCustomerName, 0, 2)),
                        'customer_rating'        => 4.9,
                        'customer_trips_count'   => 1,
                        'customer_phone'         => $sCustomer ? ($sCustomer->phone ?? '') : '',
                        'customer_email'         => $sCustomer ? ($sCustomer->email ?? '') : '',
                    ]
                ], 200);
            }
        }

        $trip = null;
        $invitation = null;
        $order = null;

        $eagerRelations = [
            'scheduledTripOrder.customer.companyProfile',
            'scheduledTripOrder.pickupCountry',
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCountry',
            'scheduledTripOrder.dropoffCity',
            'scheduledTripOrder.truckType',
            'scheduledTripOrder.truckSubType',
            'scheduledTripOrder.shipmentType',
            'scheduledTripOrder.shipmentNature',
            'scheduledTrip.route.originCountry',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCountry',
            'scheduledTrip.route.destinationCity',
            'scheduledTrip.truckType',
            'scheduledTrip.truckSubType',
            'scheduledTrip.shipmentType',
            'scheduledTrip.shipmentNature',
            'driverTruck.truckType',
            'driverTruck.truckBrand',
            'driverTruck.truckModel',
        ];

        // 0. If explicit order_{id} or STO-... order number is passed, resolve the exact ScheduledTripOrder first
        if (preg_match('/^order_(\d+)/i', $cleanId, $om) || str_starts_with(strtoupper($cleanId), 'STO-')) {
            $orderQuery = ScheduledTripOrder::with([
                'customer.companyProfile',
                'pickupCountry',
                'pickupCity.country',
                'dropoffCountry',
                'dropoffCity',
                'truckType',
                'truckSubType',
                'shipmentType',
                'shipmentNature',
                'scheduledTrip.route.originCountry',
                'scheduledTrip.route.originCity',
                'scheduledTrip.route.destinationCountry',
                'scheduledTrip.route.destinationCity',
                'scheduledTrip.truckType',
                'scheduledTrip.truckSubType',
                'scheduledTrip.shipmentType',
                'scheduledTrip.shipmentNature',
            ]);
            $order = isset($om[1])
                ? $orderQuery->find((int) $om[1])
                : $orderQuery->where('order_number', $cleanId)->first();
            if ($order) {
                $trip = $order->scheduledTrip;
                $invitation = ScheduledTripDriverInvitation::with($eagerRelations)
                    ->where('scheduled_trip_order_id', $order->id)
                    ->where('driver_id', $driverId)
                    ->latest()
                    ->first();
            }
        }

        // 1. Try finding by magic link token (if token string)
        if (!$order && (strlen($cleanId) >= 15 || !ctype_digit($cleanId))) {
            $invitation = ScheduledTripDriverInvitation::with($eagerRelations)
                ->where('token', $cleanId)
                ->first();
        }

        // 2. If cleanId is numeric, try finding by invitation ID
        if (!$invitation && ctype_digit($cleanId)) {
            $invitation = ScheduledTripDriverInvitation::with($eagerRelations)->find((int) $cleanId);
        }

        // 3. Try finding by scheduled_trip_id for this driver
        if (!$invitation && ctype_digit($cleanId)) {
            $invitation = ScheduledTripDriverInvitation::with($eagerRelations)
                ->where('scheduled_trip_id', (int) $cleanId)
                ->where('driver_id', $driverId)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$invitation) {
                $invitation = ScheduledTripDriverInvitation::with($eagerRelations)
                    ->where('scheduled_trip_id', (int) $cleanId)
                    ->latest()
                    ->first();
            }
        }

        if ($invitation) {
            $trip = $invitation->scheduledTrip;
            $order = $invitation->scheduledTripOrder;
            if (!$order && $trip) {
                $order = ScheduledTripOrder::with([
                    'customer.companyProfile',
                    'pickupCountry',
                    'pickupCity',
                    'dropoffCountry',
                    'dropoffCity',
                    'truckType',
                    'truckSubType',
                    'shipmentType',
                    'shipmentNature',
                ])->where('scheduled_trip_id', $trip->id)->latest()->first();
            }
        } elseif (!$order) {
            $tripId = ctype_digit($cleanId) ? (int) $cleanId : (int) str_replace('trip_', '', $cleanId);
            $trip = ScheduledTrip::with([
                'route.originCountry',
                'route.originCity',
                'route.destinationCountry',
                'route.destinationCity',
                'truckType',
                'truckSubType',
                'shipmentType',
                'shipmentNature',
            ])->find($tripId);

            if ($trip) {
                $order = ScheduledTripOrder::with([
                    'customer.companyProfile',
                    'pickupCountry',
                    'pickupCity',
                    'dropoffCountry',
                    'dropoffCity',
                    'truckType',
                    'truckSubType',
                    'shipmentType',
                    'shipmentNature',
                ])->where('scheduled_trip_id', $trip->id)->latest()->first();
            }
        }

        if (!$trip && !$order) {
            return response()->json([
                'status'  => false,
                'message' => 'Trip not found'
            ], 404);
        }

        $originCity = $order && $order->pickupCity 
            ? $order->pickupCity->name_en 
            : ($trip && $trip->route && $trip->route->originCity ? $trip->route->originCity->name_en : 'Kuwait City');

        $destCity   = $order && $order->dropoffCity 
            ? $order->dropoffCity->name_en 
            : ($trip && $trip->route && $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dammam');

        $originCountry = $order && $order->pickupCountry 
            ? $order->pickupCountry->name_en 
            : ($trip && $trip->route && $trip->route->originCountry ? $trip->route->originCountry->name_en : 'Kuwait');

        $destCountry   = $order && $order->dropoffCountry 
            ? $order->dropoffCountry->name_en 
            : ($trip && $trip->route && $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : 'UAE');

        $isInternational = ($trip && $trip->route && $trip->route->quote_type === 'international') || ($originCountry && $destCountry && strcasecmp($originCountry, $destCountry) !== 0);
        $routeType = $isInternational ? 'International Cross-Border' : 'Domestic Route';

        $designatedTruckDesc = '';
        $designatedTruckPlate = '';
        if ($invitation && $invitation->driverTruck) {
            $dt = $invitation->driverTruck;
            $tType = $dt->truckType ? $dt->truckType->name_en : 'Truck';
            $tBrand = $dt->truckBrand ? $dt->truckBrand->name_en : '';
            $tModel = $dt->truckModel ? $dt->truckModel->name_en : '';
            $tYear = $dt->manufacturing_year ? " ({$dt->manufacturing_year})" : '';
            $designatedTruckPlate = $dt->plate_number ?? '';
            $plateStr = $designatedTruckPlate ? "Plate: {$designatedTruckPlate}" : '';
            
            $truckName = trim("{$tType} " . trim("{$tBrand} {$tModel}"));
            $designatedTruckDesc = trim("{$truckName}{$tYear}");
            if ($plateStr) {
                $designatedTruckDesc .= " · {$plateStr}";
            }
        }

        if (empty($designatedTruckDesc)) {
            $dt = DriverTruck::with(['truckType', 'truckBrand', 'truckModel'])->where('driver_id', $driverId)->where('is_default', 1)->first()
                ?: DriverTruck::with(['truckType', 'truckBrand', 'truckModel'])->where('driver_id', $driverId)->latest()->first();
            if ($dt) {
                $tType = $dt->truckType ? $dt->truckType->name_en : 'Truck';
                $tBrand = $dt->truckBrand ? $dt->truckBrand->name_en : '';
                $tModel = $dt->truckModel ? $dt->truckModel->name_en : '';
                $tYear = $dt->manufacturing_year ? " ({$dt->manufacturing_year})" : '';
                $designatedTruckPlate = $dt->plate_number ?? '';
                $plateStr = $designatedTruckPlate ? "Plate: {$designatedTruckPlate}" : '';
                
                $truckName = trim("{$tType} " . trim("{$tBrand} {$tModel}"));
                $designatedTruckDesc = trim("{$truckName}{$tYear}");
                if ($plateStr) {
                    $designatedTruckDesc .= " · {$plateStr}";
                }
            }
        }

        $credentials = [
            'Valid Heavy Driving License & Civil ID',
            'Truck Registration (Mulkiya) & Insurance',
        ];
        if ($isInternational) {
            $credentials[] = 'Valid Passport & GCC Cross-Border Transit Visa';
            $credentials[] = 'Customs Clearance & Bayan Manifest (Provided)';
        }

        // Strictly separate driver per-truck payout ONLY! Never customer total_price.
        $pricing = $this->resolveDriverTripPricing($trip, $order, $invitation);
        $fleet = $this->getOrderFleetStats($order, $trip);
        $price = (float) $pricing['driver_price'];

        $distance   = $trip && $trip->route && $trip->route->estimated_distance 
            ? number_format($trip->route->estimated_distance, 0) . ' km' 
            : '472 km';

        $rawDist    = (float) ($trip && $trip->route ? ($trip->route->estimated_distance ?? 420) : 420);
        $durationHours = max(1, round($rawDist / 75));
        $duration   = '~' . $durationHours . ' h 20 m';

        // Coordinate, address & contact details shared between tripDetails and tripStatus
        $loc = $this->resolveTripLocationDetails($order, $trip, $originCity, $originCountry, $destCity, $destCountry);
        $pickupTitle = $loc['pickup_title'];
        $pickupAddress = $loc['pickup_address'];
        $pickupArea = $loc['pickup_area'];
        $pickupLat = $loc['pickup_lat'];
        $pickupLng = $loc['pickup_lng'];
        $pickupTimeSlot = $loc['pickup_time_slot'];
        $loadingContactName = $loc['loading_contact_name'];
        $loadingContactPhone = $loc['loading_contact_phone'];
        $pickupInstructions = $loc['pickup_instructions'];
        $pickupTime = $loc['pickup_time'];
        $dropoffTitle = $loc['dropoff_title'];
        $dropoffAddress = $loc['dropoff_address'];
        $dropoffArea = $loc['dropoff_area'];
        $dropoffLat = $loc['dropoff_lat'];
        $dropoffLng = $loc['dropoff_lng'];
        $dropoffTimeSlot = $loc['dropoff_time_slot'];
        $arrivalContactName = $loc['arrival_contact_name'];
        $arrivalContactPhone = $loc['arrival_contact_phone'];
        $deliveryInstructions = $loc['delivery_instructions'];
        $dropoffTime = $loc['dropoff_time'];

        // Cargo specs
        $shipmentName = $order ? ($order->shipment_name ?: 'Scheduled Cargo #' . ($trip ? $trip->id : $order->id)) : ($trip ? 'Scheduled Cargo #' . $trip->id : 'Cargo');
        $cargoType    = $order && $order->shipmentType ? $order->shipmentType->name_en : ($trip && $trip->shipmentType ? $trip->shipmentType->name_en : 'General cargo');
        $cargoNature  = $order && $order->shipmentNature ? $order->shipmentNature->name_en : ($trip && $trip->shipmentNature ? $trip->shipmentNature->name_en : 'General');
        $totalWeight  = $order && $order->weight ? number_format($order->weight, 0) . ' Kg' : ($trip && $trip->total_weight_ton ? 'Up to ' . number_format($trip->total_weight_ton * 1000, 0) . ' Kg' : 'Up to 20,000 Kg');
        $truckType    = $order && $order->truckType ? $order->truckType->name_en : ($trip && $trip->truckType ? $trip->truckType->name_en : 'Flatbed');
        $truckSubType = $order && $order->truckSubType ? $order->truckSubType->name_en : ($trip && $trip->truckSubType ? $trip->truckSubType->name_en : '');
        $numberOfTrucks = $order ? (int) ($order->number_of_trucks ?: $order->booked_capacity ?: 1) : ($trip ? (int) $trip->number_of_trucks : 1);

        $dimensions = null;
        if ($order && ($order->length || $order->width || $order->height)) {
            $dimensions = number_format((float)$order->length, 2) . 'm × ' . number_format((float)$order->width, 2) . 'm × ' . number_format((float)$order->height, 2) . 'm';
        }
        $volume = null;
        if ($order && $order->length && $order->width && $order->height) {
            $volVal = (float)$order->length * (float)$order->width * (float)$order->height;
            $volume = number_format($volVal, 2) . ' m³';
        }
        $packagesCount = $order ? (int) ($order->packages_count ?? 1) : 1;
        $isFragile = $order ? (bool) $order->is_fragile : false;
        $goodsDescription = $order ? ($order->goods_description ?: ($order->shipment_description ?: '')) : '';
        if (empty($goodsDescription) && $trip) {
            $goodsDescription = $trip->description ?: '';
        }
        $hsCode = $order ? ($order->hs_code ?: '') : '';
        $specialEquipment = $order && is_array($order->special_equipment) ? array_values($order->special_equipment) : [];
        $customsClearance = (bool) ($order ? $order->customs_clearance : ($trip ? $trip->customs_clearance : false));
        // Use customer's entered cargo description for instructions/notes
        $instructions = $goodsDescription ?: ($order && $order->notes ? $order->notes : '');

        // Customer details
        $customer = $order ? $order->customer : null;
        $customerName = $customer ? trim(($customer->fname ?? '') . ' ' . ($customer->lname ?? '')) : 'Al Rashid Logistics';
        if (empty($customerName)) {
            $customerName = 'Shipper';
        }
        $customerCompany = ($customer && $customer->companyProfile) ? $customer->companyProfile->company_name : ($order ? $customerName : 'Al Rashid Logistics LLC');
        $customerClientCode = $customer ? ($customer->client_code ?? '') : 'CU-41776';
        $customerPhone = $customer ? ($customer->phone ?? '') : '+965 9876 5432';
        $customerEmail = $customer ? ($customer->email ?? '') : '';

        // Initials
        $parts = array_filter(preg_split('/\s+/', $customerName));
        $customerInitials = 'CU';
        if (count($parts) >= 2) {
            $first = mb_substr(reset($parts), 0, 1);
            $last = mb_substr(end($parts), 0, 1);
            $customerInitials = mb_strtoupper($first . $last);
        } elseif (count($parts) === 1) {
            $customerInitials = mb_strtoupper(mb_substr(reset($parts), 0, 2));
        }

        $customerTripsCount = $customer ? \App\Models\ScheduledTripOrder::where('customer_id', $customer->id)->count() : 12;

        // Customer uploaded documents
        $documents = [];
        if ($order && is_array($order->documents)) {
            foreach ($order->documents as $doc) {
                if (!is_array($doc)) continue;
                $documents[] = [
                    'title'       => $doc['title'] ?? 'Document',
                    'file_name'   => $doc['file_name'] ?? '',
                    'file_url'    => $doc['file_url'] ?? '',
                    'file_size'   => $doc['file_size'] ?? '',
                    'file_type'   => $doc['file_type'] ?? 'pdf',
                    'is_required' => (bool) ($doc['is_required'] ?? false),
                ];
            }
        }

        $postedAgo = $invitation
            ? 'Posted ' . $invitation->created_at->diffForHumans(null, true) . ' ago'
            : ($order && $order->created_at
                ? 'Posted ' . $order->created_at->diffForHumans(null, true) . ' ago'
                : ($trip ? 'Posted ' . $trip->created_at->diffForHumans(null, true) . ' ago' : 'Posted recently'));

        $payoutGuarantee = $pricing['payment_type'] === 'split_50_50'
            ? 'Split payout: 50% credited to your wallet when starting transit + 50% upon delivery sign-off.'
            : 'Guaranteed direct payment (100%) processed to your driver wallet upon delivery sign-off.';

        return response()->json([
            'status'  => true,
            'message' => 'Trip details retrieved successfully',
            'data'    => [
                'request_id'             => '#SL-' . ($trip ? $trip->id : ($invitation ? $invitation->id : ($order ? $order->id : '1'))),
                'id'                     => (string) ($invitation ? $invitation->id : ($order ? 'order_' . $order->id : ($trip ? 'trip_' . $trip->id : '1'))),
                'order_number'           => $order ? $order->order_number : null,
                'trip_id'                => $trip ? (string) $trip->id : ($order ? (string) $order->scheduled_trip_id : ''),
                'status'                 => 'New request',
                'posted_ago'             => $postedAgo,
                'from_city'              => $originCity,
                'to_city'                => $destCity,
                'origin_country'         => $originCountry,
                'destination_country'    => $destCountry,
                'is_international'       => $isInternational,
                'route_type'             => $routeType,
                'trip_value_kwd'         => $price,
                'driver_payment_type'    => $pricing['payment_type'],
                'driver_payout_label'    => $pricing['payout_label'],
                'distance'               => $distance,
                'duration'               => $duration,
                'payout_guarantee'       => $payoutGuarantee,
                'designated_truck'       => $designatedTruckDesc,
                'designated_truck_plate' => $designatedTruckPlate,
                'required_credentials'   => $credentials,
                'pickup_title'           => $pickupTitle,
                'pickup_address'         => $pickupAddress,
                'pickup_area'            => $pickupArea,
                'pickup_lat'             => $pickupLat,
                'pickup_lng'             => $pickupLng,
                'pickup_time'            => $pickupTime,
                'pickup_time_slot'       => $pickupTimeSlot,
                'loading_contact_name'   => $loadingContactName,
                'loading_contact_phone'  => $loadingContactPhone,
                'pickup_instructions'    => $pickupInstructions,
                'dropoff_title'          => $dropoffTitle,
                'dropoff_address'        => $dropoffAddress,
                'dropoff_area'           => $dropoffArea,
                'dropoff_lat'            => $dropoffLat,
                'dropoff_lng'            => $dropoffLng,
                'dropoff_time'           => $dropoffTime,
                'dropoff_time_slot'      => $dropoffTimeSlot,
                'arrival_contact_name'   => $arrivalContactName,
                'arrival_contact_phone'  => $arrivalContactPhone,
                'delivery_instructions'  => $deliveryInstructions,
                'shipment_name'          => $shipmentName,
                'cargo_type'             => $cargoType,
                'cargo_nature'           => $cargoNature,
                'total_weight'           => $totalWeight,
                'truck_type'             => $truckType,
                'truck_sub_type'         => $truckSubType,
                'number_of_trucks'       => 1,
                'fleet_required'         => $fleet['required'],
                'fleet_accepted'         => $fleet['accepted'],
                'fleet_remaining'        => $fleet['remaining'],
                'fleet_ready'            => $fleet['fleet_ready'],
                'packaging'              => 'Palletised',
                'dimensions'             => $dimensions,
                'volume'                 => $volume,
                'packages_count'         => $packagesCount,
                'is_fragile'             => $isFragile,
                'goods_description'      => $goodsDescription,
                'hs_code'                => $hsCode,
                'special_equipment'      => $specialEquipment,
                'customs_clearance'      => $customsClearance,
                'instructions'           => $instructions,
                'documents'              => $documents,
                'customer_name'          => $customerName,
                'customer_company'       => $customerCompany,
                'customer_client_code'   => $customerClientCode,
                'customer_initials'      => $customerInitials,
                'customer_rating'        => 4.9,
                'customer_trips_count'   => $customerTripsCount,
                'customer_phone'         => $customerPhone,
                'customer_email'         => $customerEmail,
            ]
        ], 200);
    }

    /**
     * Respond (Accept or Reject) to a trip request from mobile app.
     */
    public function respondTripRequest(Request $request, $id)
    {
        $id = preg_replace('/^#?SL-?/i', '', (string) $id);
        $action = $request->input('action', 'accept'); // 'accept' or 'reject'
        $user = $request->user();
        if (!$user && $request->filled('client_code')) {
            $user = User::where('client_code', $request->client_code)->first();
        }
        $driverId = $user ? $user->id : ($request->input('driver_id') ?: 18);

        $targetOrder = null;
        $targetTrip = null;
        $invitation = null;

        if (preg_match('/^order_(\d+)/i', $id, $om) || str_starts_with(strtoupper($id), 'STO-')) {
            $targetOrder = isset($om[1])
                ? ScheduledTripOrder::with('scheduledTrip')->find((int) $om[1])
                : ScheduledTripOrder::with('scheduledTrip')->where('order_number', $id)->first();
            $targetTrip = $targetOrder ? $targetOrder->scheduledTrip : null;
        } elseif (str_starts_with($id, 'trip_')) {
            $tripId = (int) str_replace('trip_', '', $id);
            $targetTrip = ScheduledTrip::find($tripId);
        } else {
            $invId = (int) $id;
            $invitation = ScheduledTripDriverInvitation::find($invId);
            if (!$invitation) {
                $invitation = ScheduledTripDriverInvitation::where(function ($q) use ($invId) {
                    $q->where('scheduled_trip_id', $invId)
                      ->orWhere('scheduled_trip_order_id', $invId);
                })
                    ->where('driver_id', $driverId)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();
            }
            if ($invitation) {
                $targetOrder = $invitation->scheduledTripOrder;
                $targetTrip = $invitation->scheduledTrip;
            } else {
                $targetTrip = ScheduledTrip::find($invId);
            }
        }

        if (!$targetOrder && !$targetTrip && !$invitation) {
            return response()->json([
                'status'  => false,
                'message' => 'Trip request not found.',
            ], 404);
        }

        $slotError = null;
        $fleetStats = null;

        DB::transaction(function () use (
            $action,
            $driverId,
            &$targetOrder,
            &$targetTrip,
            &$invitation,
            &$slotError,
            &$fleetStats
        ) {
            if ($targetOrder) {
                $targetOrder = ScheduledTripOrder::where('id', $targetOrder->id)->lockForUpdate()->first();
                if ($targetOrder && !$targetTrip) {
                    $targetTrip = $targetOrder->scheduledTrip;
                }
            }
            if ($targetTrip) {
                $targetTrip = ScheduledTrip::where('id', $targetTrip->id)->lockForUpdate()->first();
            }

            $pricing = $this->resolveDriverTripPricing($targetTrip, $targetOrder, $invitation);
            $offeredPrice = (float) $pricing['driver_price'];

            if ($action === 'accept') {
                if ($targetOrder) {
                    $required = max(1, (int) ($targetOrder->number_of_trucks ?: $targetOrder->booked_capacity ?: 1));
                    $alreadyClaimedOther = ScheduledTripDriverInvitation::where('scheduled_trip_order_id', $targetOrder->id)
                        ->whereIn('status', ['accepted', 'completed'])
                        ->where('driver_id', '!=', $driverId)
                        ->count();
                    if ($alreadyClaimedOther >= $required) {
                        $slotError = "All {$required} truck slot(s) for this order have already been claimed by other drivers.";
                        return;
                    }
                } elseif ($targetTrip) {
                    $required = max(1, (int) ($targetTrip->number_of_trucks ?: 1));
                    $alreadyClaimedOther = ScheduledTripDriverInvitation::where('scheduled_trip_id', $targetTrip->id)
                        ->whereNull('scheduled_trip_order_id')
                        ->whereIn('status', ['accepted', 'completed'])
                        ->where('driver_id', '!=', $driverId)
                        ->count();
                    if ($alreadyClaimedOther >= $required) {
                        $slotError = "All {$required} truck slot(s) for this trip have already been claimed.";
                        return;
                    }
                }
            }

            if ($invitation) {
                $invitation->status = $action === 'accept' ? 'accepted' : 'rejected';
                $invitation->offered_price = $offeredPrice;
                if ($action === 'accept') {
                    $invitation->driver_stage = $invitation->driver_stage ?: 'confirmed';
                    $invitation->accepted_at = $invitation->accepted_at ?: now();
                }
                $invitation->save();
            } elseif ($targetOrder) {
                $invitation = ScheduledTripDriverInvitation::updateOrCreate(
                    [
                        'scheduled_trip_order_id' => $targetOrder->id,
                        'driver_id'               => $driverId,
                    ],
                    [
                        'scheduled_trip_id' => $targetOrder->scheduled_trip_id,
                        'offered_price'     => $offeredPrice,
                        'channel'           => 'in_app',
                        'status'            => $action === 'accept' ? 'accepted' : 'rejected',
                        'driver_stage'      => $action === 'accept' ? 'confirmed' : null,
                        'accepted_at'       => $action === 'accept' ? now() : null,
                    ]
                );
            } elseif ($targetTrip) {
                $invitation = ScheduledTripDriverInvitation::updateOrCreate(
                    [
                        'scheduled_trip_id'       => $targetTrip->id,
                        'driver_id'               => $driverId,
                        'scheduled_trip_order_id' => null,
                    ],
                    [
                        'offered_price' => $offeredPrice,
                        'channel'       => 'in_app',
                        'status'        => $action === 'accept' ? 'accepted' : 'rejected',
                        'driver_stage'  => $action === 'accept' ? 'confirmed' : null,
                        'accepted_at'   => $action === 'accept' ? now() : null,
                    ]
                );
            }

            if ($action === 'accept') {
                if ($targetOrder) {
                    if (empty($targetOrder->driver_id)) {
                        $targetOrder->driver_id = $driverId;
                    }
                    if (in_array(strtolower((string) $targetOrder->status), ['new', 'pending', ''])) {
                        $targetOrder->status = 'confirmed';
                    }
                    $targetOrder->save();
                }

                if ($targetTrip) {
                    if (empty($targetTrip->driver_id)) {
                        $targetTrip->driver_id = $driverId;
                    }
                    if (in_array(strtolower((string) $targetTrip->status), ['completed', 'cancelled', 'canceled'])) {
                        $targetTrip->status = 'boarding';
                    }
                    $targetTrip->save();
                }
            }

            $fleetStats = $this->getOrderFleetStats($targetOrder, $targetTrip);
        });

        if ($slotError !== null) {
            return response()->json([
                'status'  => false,
                'message' => $slotError,
            ], 422);
        }

        if ($action === 'accept' && $targetOrder && $targetOrder->customer_id && $fleetStats) {
            try {
                if ($custUser = User::find($targetOrder->customer_id)) {
                    $driverUser = $user ?: User::find($driverId);
                    $driverName = $driverUser ? trim(($driverUser->fname ?? '') . ' ' . ($driverUser->lname ?? '')) : 'A driver';
                    $rem = (int) ($fleetStats['remaining'] ?? 0);
                    $custUser->notify(new \App\Notifications\CustomerActivityNotification([
                        'title'        => 'Driver Accepted Your Order #' . $targetOrder->order_number,
                        'body'         => "{$driverName} accepted truck {$fleetStats['accepted']} of {$fleetStats['required']}. Remaining: {$rem} truck(s).",
                        'type'         => 'order',
                        'reference_id' => $targetOrder->order_number,
                        'action_route' => '/orders_screen',
                        'metadata'     => [
                            'order_id'       => $targetOrder->id,
                            'order_number'   => $targetOrder->order_number,
                            'fleet_accepted' => $fleetStats['accepted'],
                            'fleet_required' => $fleetStats['required'],
                            'fleet_remaining'=> $rem,
                        ],
                    ]));
                }
            } catch (\Throwable $ignoreNotif) {
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Trip request ' . ($action === 'accept' ? 'accepted' : 'rejected') . ' successfully.',
            'data'    => [
                'fleet' => $fleetStats,
            ],
        ], 200);
    }

    /**
     * Resolve pickup/dropoff addresses, coordinates, schedule, and contacts for trip details & status.
     */
    private function resolveTripLocationDetails(
        ?ScheduledTripOrder $order,
        ?ScheduledTrip $trip,
        string $originCity,
        string $originCountry,
        string $destCity,
        string $destCountry
    ): array {
        $pickupTitle = $originCity . ($originCountry ? " ({$originCountry})" : '');
        $pickupAddress = $order && $order->pickup_address ? $order->pickup_address : $pickupTitle;
        $pickupArea = $order ? ($order->pickup_area ?: '') : '';
        $pickupLat = ($order && $order->pickup_lat !== null && $order->pickup_lat !== '') ? (float) $order->pickup_lat : null;
        $pickupLng = ($order && $order->pickup_lng !== null && $order->pickup_lng !== '') ? (float) $order->pickup_lng : null;
        $pickupTimeSlot = $order ? ($order->loading_time_slot ?: '') : '';
        $loadingContactName = $order ? ($order->loading_contact_name ?: '') : '';
        $loadingContactPhone = $order ? ($order->loading_contact_phone ?: '') : '';
        $pickupInstructions = $order ? ($order->pickup_instructions ?: '') : '';

        $pickupTime = $order && $order->loading_date
            ? date('D d M Y', strtotime($order->loading_date)) . ($order->loading_time ? ' · ' . substr($order->loading_time, 0, 5) : '')
            : ($trip && $trip->trip_date ? date('D d M Y', strtotime($trip->trip_date)) . ($trip->trip_time ? ' · ' . substr($trip->trip_time, 0, 5) : '') : 'Thu 16 Jul 2026 · 14:00');

        $dropoffTitle = $destCity . ($destCountry ? " ({$destCountry})" : '');
        $dropoffAddress = $order && $order->dropoff_address ? $order->dropoff_address : $dropoffTitle;
        $dropoffArea = $order ? ($order->dropoff_area ?: '') : '';
        $dropoffLat = ($order && $order->dropoff_lat !== null && $order->dropoff_lat !== '') ? (float) $order->dropoff_lat : null;
        $dropoffLng = ($order && $order->dropoff_lng !== null && $order->dropoff_lng !== '') ? (float) $order->dropoff_lng : null;
        $dropoffTimeSlot = $order ? ($order->receiving_time_slot ?: '') : '';
        $arrivalContactName = $order ? ($order->arrival_contact_name ?: '') : '';
        $arrivalContactPhone = $order ? ($order->arrival_contact_phone ?: '') : '';
        $deliveryInstructions = $order ? ($order->delivery_instructions ?: '') : '';

        if ($trip && ($pickupLat === null || $pickupLng === null)) {
            $coordOrder = ScheduledTripOrder::where('scheduled_trip_id', $trip->id)
                ->whereNotNull('pickup_lat')
                ->whereNotNull('pickup_lng')
                ->latest()
                ->first();
            if ($coordOrder) {
                $pickupLat = (float) $coordOrder->pickup_lat;
                $pickupLng = (float) $coordOrder->pickup_lng;
            } else {
                $coordShipment = Shipment::where('scheduled_trip_id', $trip->id)
                    ->whereNotNull('pickup_lat')
                    ->whereNotNull('pickup_lng')
                    ->latest()
                    ->first();
                if ($coordShipment) {
                    $pickupLat = (float) $coordShipment->pickup_lat;
                    $pickupLng = (float) $coordShipment->pickup_lng;
                }
            }
        }
        if ($trip && ($dropoffLat === null || $dropoffLng === null)) {
            $coordDropOrder = ScheduledTripOrder::where('scheduled_trip_id', $trip->id)
                ->whereNotNull('dropoff_lat')
                ->whereNotNull('dropoff_lng')
                ->latest()
                ->first();
            if ($coordDropOrder) {
                $dropoffLat = (float) $coordDropOrder->dropoff_lat;
                $dropoffLng = (float) $coordDropOrder->dropoff_lng;
            } else {
                $coordDropShipment = Shipment::where('scheduled_trip_id', $trip->id)
                    ->whereNotNull('dropoff_lat')
                    ->whereNotNull('dropoff_lng')
                    ->latest()
                    ->first();
                if ($coordDropShipment) {
                    $dropoffLat = (float) $coordDropShipment->dropoff_lat;
                    $dropoffLng = (float) $coordDropShipment->dropoff_lng;
                }
            }
        }

        $dropoffTime = $order && $order->receiving_date
            ? date('D d M Y', strtotime($order->receiving_date)) . ($order->receiving_time ? ' · ' . substr($order->receiving_time, 0, 5) : '')
            : ($trip && $trip->trip_date ? date('D d M Y', strtotime($trip->trip_date . ' +1 day')) . ' · 09:00' : 'Fri 17 Jul 2026 · 09:00');

        return [
            'pickup_title'          => $pickupTitle,
            'pickup_address'        => $pickupAddress,
            'pickup_area'           => $pickupArea,
            'pickup_lat'            => $pickupLat,
            'pickup_lng'            => $pickupLng,
            'pickup_time'           => $pickupTime,
            'pickup_time_slot'      => $pickupTimeSlot,
            'loading_contact_name'  => $loadingContactName,
            'loading_contact_phone' => $loadingContactPhone,
            'pickup_instructions'   => $pickupInstructions,
            'dropoff_title'         => $dropoffTitle,
            'dropoff_address'       => $dropoffAddress,
            'dropoff_area'          => $dropoffArea,
            'dropoff_lat'           => $dropoffLat,
            'dropoff_lng'           => $dropoffLng,
            'dropoff_time'          => $dropoffTime,
            'dropoff_time_slot'     => $dropoffTimeSlot,
            'arrival_contact_name'  => $arrivalContactName,
            'arrival_contact_phone' => $arrivalContactPhone,
            'delivery_instructions' => $deliveryInstructions,
        ];
    }

    /**
     * Get live trip status, stages, and checklist connected to scheduled-trip-orders.
     */
    public function tripStatus(Request $request, $id)
    {
        $driverId = (int) $this->resolveDriverId($request);

        // Normalize ID (TRP-14, SL-14, STO-20261103-9F58, order_13, 14, trip_14, etc.)
        $resolved = $this->resolveTripEntities($id, $driverId);
        $order = $resolved['order'];
        $trip = $resolved['trip'];
        $inv = $resolved['invitation'];

        if ($order) {
            $order->loadMissing([
                'scheduledTrip.route.originCountry',
                'scheduledTrip.route.originCity',
                'scheduledTrip.route.destinationCountry',
                'scheduledTrip.route.destinationCity',
                'scheduledTrip.orders',
                'pickupCountry',
                'pickupCity.country',
                'dropoffCountry',
                'dropoffCity',
                'driver',
            ]);
            if (!$trip) {
                $trip = $order->scheduledTrip;
            }
        }
        if ($trip) {
            $trip->loadMissing([
                'route.originCountry',
                'route.originCity',
                'route.destinationCountry',
                'route.destinationCity',
                'orders.pickupCity',
                'orders.dropoffCity',
                'driverInvitations',
            ]);
        }

        // Fallback to active trip if not found
        if (!$trip && !$order) {
            $activeInv = ScheduledTripDriverInvitation::with([
                'scheduledTrip.route.originCity',
                'scheduledTrip.route.destinationCity',
                'scheduledTrip.orders'
            ])
            ->where('driver_id', $driverId)
            ->where('status', 'accepted')
            ->latest()
            ->first();

            if ($activeInv) {
                $inv = $activeInv;
                $trip = $activeInv->scheduledTrip;
                $order = $activeInv->scheduledTripOrder ?: ($trip ? $trip->orders()->latest()->first() : null);
            }
        }

        $fleet = $this->getOrderFleetStats($order, $trip);
        $pricing = $this->resolveDriverTripPricing($trip, $order, $inv);

        // Determine THIS driver's current stage first (supports multi-driver independent progress)
        $orderStatus = 'new';
        if ($inv && !empty($inv->driver_stage)) {
            $orderStatus = strtolower(trim($inv->driver_stage));
        } elseif ($inv && $inv->status === 'completed') {
            $orderStatus = 'delivered';
        } elseif ($order && !empty($order->status)) {
            $orderStatus = strtolower(trim($order->status));
        } elseif ($trip && in_array($trip->status, ['completed'])) {
            $orderStatus = 'delivered';
        }

        // Determine route text
        $originName = 'Farwaniya';
        $destName = 'Dubai';
        if ($order) {
            if ($order->pickupCity) {
                $originName = $order->pickupCity->name_en ?: $order->pickupCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->originCity) {
                $originName = $trip->route->originCity->name_en;
            }
            if ($order->dropoffCity) {
                $destName = $order->dropoffCity->name_en ?: $order->dropoffCity->name_ar;
            } elseif ($trip && $trip->route && $trip->route->destinationCity) {
                $destName = $trip->route->destinationCity->name_en;
            }
        } elseif ($trip && $trip->route) {
            $originName = $trip->route->originCity ? $trip->route->originCity->name_en : 'Farwaniya';
            $destName = $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dubai';
        }

        $tripCode = $trip ? 'TRP-' . $trip->id : ($order ? 'TRP-' . $order->scheduled_trip_id : 'TRP-14');
        $orderNumber = $order ? $order->order_number : 'STO-20261103-9F58';
        $routeText = $originName . ' → ' . $destName;

        // Calculate the 6 stages based on $orderStatus
        $loadingTime = $order && $order->loading_date ? date('d M', strtotime($order->loading_date)) : '16 Jul';

        switch ($orderStatus) {
            case 'new':
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => 'Pending acceptance',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'confirmed':
                // 1 of 6 done: Trip accepted is completed, next step is to arrive at pickup
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · ' . date('H:i', strtotime($order && $order->created_at ? $order->created_at : 'now')),
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => 'Heading to pickup location',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => 'I have arrived at pickup',
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'arrived_pickup':
                // 2 of 6 done: Arrived at pickup completed, next step is confirming loading
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · ' . date('H:i', strtotime($order && $order->created_at ? $order->created_at : 'now')),
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => $loadingTime . ' · 13:40',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => 'In progress',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => 'Confirm loading',
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'loaded':
                // 3 of 6 done: Loading confirmed, next step is starting transit
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · 09:12',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => $loadingTime . ' · 13:40',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => $loadingTime . ' · 14:25',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Ready to depart',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => 'Start transit',
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'in_transit':
                // 4 of 6 done: In transit completed, next step is arriving at drop-off
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · 09:12',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => $loadingTime . ' · 13:40',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => $loadingTime . ' · 14:25',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Started ' . date('H:i'),
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Approaching drop-off location',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => 'I have arrived at drop-off',
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Pending',
                        'is_completed'       => false,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'arrived_dropoff':
                // 5 of 6 done: Arrived at drop-off completed, next step is delivery & signature
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · 09:12',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => $loadingTime . ' · 13:40',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => $loadingTime . ' · 14:25',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Completed',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Just now',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Ready to deliver and sign',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => 'Proceed to delivery',
                    ],
                ];
                break;

            case 'delivered':
                $stages = [
                    [
                        'title'              => 'Trip accepted',
                        'time_subtitle'      => $loadingTime . ' · 09:12',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at pickup',
                        'time_subtitle'      => $loadingTime . ' · 13:40',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Loading confirmed',
                        'time_subtitle'      => $loadingTime . ' · 14:25',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'In transit',
                        'time_subtitle'      => 'Completed',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Arrived at drop-off',
                        'time_subtitle'      => 'Completed',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                    [
                        'title'              => 'Delivered & signed',
                        'time_subtitle'      => 'Delivered & confirmed',
                        'is_completed'       => true,
                        'is_current'         => false,
                        'action_button_text' => null,
                    ],
                ];
                break;

            case 'canceled':
            default:
                $stages = [
                    [
                        'title'              => 'Trip canceled',
                        'time_subtitle'      => 'Canceled in admin panel',
                        'is_completed'       => false,
                        'is_current'         => true,
                        'action_button_text' => null,
                    ],
                ];
                break;
        }

        // Checklist
        $isLoadedOrAfter = in_array($orderStatus, ['loaded', 'in_transit', 'arrived_dropoff', 'delivered']);
        $checklist = [
            [
                'id'         => '1',
                'title'      => 'Cargo secured with straps',
                'is_checked' => $isLoadedOrAfter,
            ],
            [
                'id'         => '2',
                'title'      => 'Weight verified at weighbridge',
                'is_checked' => $isLoadedOrAfter,
            ],
            [
                'id'         => '3',
                'title'      => 'Documents received from shipper',
                'is_checked' => $isLoadedOrAfter,
            ],
            [
                'id'         => '4',
                'title'      => 'Photos of loaded cargo uploaded',
                'is_checked' => in_array($orderStatus, ['in_transit', 'arrived_dropoff', 'delivered']),
            ],
        ];

        $pickupCountryName = $order && $order->pickupCountry ? $order->pickupCountry->name_en : ($trip && $trip->route && $trip->route->originCountry ? $trip->route->originCountry->name_en : 'Kuwait');
        $dropoffCountryName = $order && $order->dropoffCountry ? $order->dropoffCountry->name_en : ($trip && $trip->route && $trip->route->destinationCountry ? $trip->route->destinationCountry->name_en : 'UAE');
        $loc = $this->resolveTripLocationDetails($order, $trip, $originName, $pickupCountryName, $destName, $dropoffCountryName);
        $rawDist = (float) ($trip && $trip->route ? ($trip->route->estimated_distance ?? 420) : 420);
        $distance = $trip && $trip->route && $trip->route->estimated_distance
            ? number_format($trip->route->estimated_distance, 0) . ' km'
            : '472 km';
        $duration = '~' . max(1, round($rawDist / 75)) . ' h 20 m';

        // Fleet readiness gate: prevent movement actions until all required trucks are accepted
        $fleetGateMessage = null;
        if (!$fleet['fleet_ready'] && $orderStatus !== 'delivered' && $orderStatus !== 'canceled') {
            $fleetGateMessage = "Waiting for all trucks to accept ({$fleet['accepted']}/{$fleet['required']} accepted · {$fleet['remaining']} remaining). Fleet movement unlocks once all drivers confirm.";
            $stages = array_map(function ($stage) {
                if (!empty($stage['is_current'])) {
                    $stage['action_button_text'] = null;
                }
                return $stage;
            }, $stages);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Trip status fetched successfully',
            'data'    => array_merge([
                'trip_id'                => $trip ? $trip->id : 14,
                'trip_code'              => $tripCode,
                'order_id'               => $order ? $order->id : 5,
                'order_number'           => $orderNumber,
                'order_status'           => $orderStatus,
                'route_text'             => $routeText,
                'origin'                 => $originName,
                'destination'            => $destName,
                'from_city'              => $originName,
                'to_city'                => $destName,
                'origin_country'         => $pickupCountryName,
                'destination_country'    => $dropoffCountryName,
                'distance'               => $distance,
                'duration'               => $duration,
                'fleet_required'         => $fleet['required'],
                'fleet_accepted'         => $fleet['accepted'],
                'fleet_remaining'        => $fleet['remaining'],
                'fleet_ready'            => $fleet['fleet_ready'],
                'fleet_override'         => $fleet['fleet_override'],
                'fleet_gate_message'     => $fleetGateMessage,
                'driver_price'           => (float) $pricing['driver_price'],
                'driver_payment_type'    => $pricing['payment_type'],
                'driver_payout_label'    => $pricing['payout_label'],
                'stages'                 => $stages,
                'checklist'              => $checklist,
            ], $loc),
        ], 200);
    }

    /**
     * Advance or update trip stage from driver app, syncing order status to admin dashboard.
     */
    public function updateTripStage(Request $request, $id)
    {
        $driverId = (int) $this->resolveDriverId($request);
        $action = $request->input('action');
        $targetStatus = $request->input('status');

        $resolved = $this->resolveTripEntities($id, $driverId);
        $order = $resolved['order'];
        $trip = $resolved['trip'];
        $inv = $resolved['invitation'];
        $inv = $resolved['invitation'];

        if (!$order && $trip) {
            $order = $trip->orders()->latest()->first();
        }

        $newStatus = $targetStatus;
        if (!$newStatus) {
            switch ($action) {
                case 'arrived_pickup':
                    $newStatus = 'arrived_pickup';
                    break;
                case 'confirm_loading':
                    $newStatus = 'loaded';
                    break;
                case 'start_transit':
                    $newStatus = 'in_transit';
                    break;
                case 'arrived_dropoff':
                    $newStatus = 'arrived_dropoff';
                    break;
                case 'proceed_delivery':
                case 'delivered':
                    $newStatus = 'delivered';
                    break;
                default:
                    $newStatus = 'in_transit';
                    break;
            }
        }

        $fleet = $this->getOrderFleetStats($order, $trip);
        if (!$fleet['fleet_ready'] && in_array($newStatus, ['arrived_pickup', 'loaded', 'in_transit', 'arrived_dropoff', 'delivered'])) {
            return response()->json([
                'status'  => false,
                'message' => "Cannot start fleet movement yet. Waiting for all required trucks ({$fleet['accepted']}/{$fleet['required']} accepted, {$fleet['remaining']} remaining).",
                'data'    => ['fleet' => $fleet],
            ], 422);
        }

        if (!$inv && ($order || $trip)) {
            $inv = ScheduledTripDriverInvitation::firstOrCreate(
                [
                    'scheduled_trip_id'       => $trip ? $trip->id : ($order ? $order->scheduled_trip_id : null),
                    'driver_id'               => $driverId,
                    'scheduled_trip_order_id' => $order ? $order->id : null,
                ],
                [
                    'offered_price' => $this->resolveDriverTripPricing($trip, $order)['driver_price'],
                    'channel'       => 'in_app',
                    'status'        => 'accepted',
                    'driver_stage'  => 'confirmed',
                    'accepted_at'   => now(),
                ]
            );
        }

        if ($inv && $newStatus) {
            $inv->driver_stage = $newStatus;
            if ($newStatus === 'in_transit' && !$inv->started_at) {
                $inv->started_at = now();
            }
            if ($newStatus === 'delivered') {
                $inv->status = 'completed';
                $inv->completed_at = $inv->completed_at ?: now();
            }
            $inv->save();
        }

        // Credit 50% upfront when entering 'in_transit' on split_50_50 payout trips
        if ($newStatus === 'in_transit') {
            $this->creditDriverInvitationPayout($driverId, $inv, $trip, $order, 'upfront');
        }

        if ($order && $newStatus) {
            if ($fleet['required'] > 1) {
                $this->syncMultiTruckOrderStatus($order, $trip);
            } else {
                $order->status = $newStatus;
                $order->save();
                if ($trip && $newStatus === 'in_transit' && !in_array($trip->status, ['completed', 'canceled'])) {
                    $trip->status = 'in_transit';
                    $trip->save();
                } elseif ($trip && $newStatus === 'delivered') {
                    $trip->status = 'completed';
                    $trip->save();
                }
            }
        } elseif ($trip && $newStatus === 'delivered') {
            $updatedFleet = $this->getOrderFleetStats(null, $trip);
            if ($updatedFleet['completed'] >= $updatedFleet['required']) {
                $trip->status = 'completed';
                $trip->save();
            }
        }

        return $this->tripStatus($request, $id);
    }

    /**
     * Get Driver Documents (Registration and Truck credentials)
     */
    public function driverDocuments(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                $driverId = $found->id;
            }
        }
        if (!$driverId) {
            $driverId = 18;
        }

        $user = User::with(['driverProfile.truckType', 'trucks.truckType'])->find($driverId);
        $profile = $user ? $user->driverProfile : null;

        $civilIdPhoto = $profile ? $profile->civil_id_photo : null;
        $licensePhoto = $profile ? $profile->license_photo : null;
        $passportPhoto = $profile ? $profile->passport_photo : null;
        $truckRegPhoto = $profile ? $profile->truck_registration_photo : null;

        $docs = [
            [
                'id' => 'doc_civil_id',
                'type_key' => 'civil_id',
                'title' => 'Civil ID',
                'expiry_text' => 'Valid until 12 Mar 2029',
                'status' => 'valid',
                'category' => 'driver',
                'file_name' => $civilIdPhoto,
                'file_url' => $civilIdPhoto ? asset('upload/driver_docs/' . $civilIdPhoto) : null,
                'document_number' => $user ? ($user->civil_id ?: $user->client_code) : 'DR-95818',
                'is_uploaded' => !empty($civilIdPhoto),
                'updated_at' => $profile && $profile->updated_at ? $profile->updated_at->format('d M Y') : '03 Nov 2026',
            ],
            [
                'id' => 'doc_driving_licence',
                'type_key' => 'license',
                'title' => 'Driving licence',
                'expiry_text' => 'Valid until 04 Sep 2027',
                'status' => 'valid',
                'category' => 'driver',
                'file_name' => $licensePhoto,
                'file_url' => $licensePhoto ? asset('upload/driver_docs/' . $licensePhoto) : null,
                'document_number' => $profile && $profile->license_number ? $profile->license_number : 'LIC-94959092',
                'is_uploaded' => !empty($licensePhoto),
                'updated_at' => $profile && $profile->updated_at ? $profile->updated_at->format('d M Y') : '03 Nov 2026',
            ],
            [
                'id' => 'doc_passport_copy',
                'type_key' => 'passport',
                'title' => 'Passport copy',
                'expiry_text' => 'Valid until 15 Nov 2030',
                'status' => 'valid',
                'category' => 'driver',
                'file_name' => $passportPhoto,
                'file_url' => $passportPhoto ? asset('upload/driver_docs/' . $passportPhoto) : null,
                'document_number' => 'PASS-982144',
                'is_uploaded' => !empty($passportPhoto),
                'updated_at' => $profile && $profile->updated_at ? $profile->updated_at->format('d M Y') : '03 Nov 2026',
            ],
            [
                'id' => 'doc_vehicle_reg',
                'type_key' => 'vehicle_reg',
                'title' => 'Vehicle registration',
                'expiry_text' => 'Valid until 18 Jul 2027',
                'status' => 'valid',
                'category' => 'truck',
                'file_name' => $truckRegPhoto,
                'file_url' => $truckRegPhoto ? asset('upload/driver_docs/' . $truckRegPhoto) : null,
                'document_number' => 'Plate: ' . ($profile && $profile->plate_number ? $profile->plate_number : '1234'),
                'is_uploaded' => !empty($truckRegPhoto),
                'updated_at' => $profile && $profile->updated_at ? $profile->updated_at->format('d M Y') : '03 Nov 2026',
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Driver documents retrieved successfully',
            'data' => $docs,
            'summary' => [
                'total_count' => count($docs),
                'expired_count' => 0,
                'expiring_count' => 0,
                'attention_count' => 0,
            ]
        ]);
    }

    /**
     * Update or upload a specific driver document
     */
    public function updateDriverDocument(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                $driverId = $found->id;
            }
        }
        if (!$driverId) {
            $driverId = 18;
        }

        $user = User::with('driverProfile')->find($driverId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Driver not found'], 404);
        }

        $driver = $user->driverProfile;
        if (!$driver) {
            $driver = \App\Models\DriverProfile::firstOrCreate(['user_id' => $driverId]);
        }

        $file = $request->file('file') ?? ($request->file('document') ?? $request->file('photo'));
        if (!$file) {
            return response()->json(['status' => false, 'message' => 'No file uploaded'], 422);
        }

        $typeKey = $request->input('document_type', $request->input('type_key', 'civil_id'));

        $uploadDir = public_path('upload/driver_docs');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $prefix = preg_replace('/[^a-zA-Z0-9]/', '', $typeKey);
        $fileName = date('YmdHi') . '_' . $prefix . '_' . uniqid() . '.' . $ext;
        $file->move($uploadDir, $fileName);

        $cpanelDir = base_path('../public_html/upload/driver_docs');
        if (is_dir(base_path('../public_html')) && realpath($uploadDir) !== realpath($cpanelDir)) {
            if (!File::exists($cpanelDir)) {
                File::makeDirectory($cpanelDir, 0755, true, true);
            }
            @copy($uploadDir . '/' . $fileName, $cpanelDir . '/' . $fileName);
        }

        // Assign to corresponding column in driver_profiles
        if (in_array($typeKey, ['civil_id', 'doc_civil_id'])) {
            if ($driver->civil_id_photo && File::exists($uploadDir . '/' . $driver->civil_id_photo)) {
                @unlink($uploadDir . '/' . $driver->civil_id_photo);
            }
            $driver->civil_id_photo = $fileName;
        } elseif (in_array($typeKey, ['license', 'doc_driving_licence', 'driving_licence'])) {
            if ($driver->license_photo && File::exists($uploadDir . '/' . $driver->license_photo)) {
                @unlink($uploadDir . '/' . $driver->license_photo);
            }
            $driver->license_photo = $fileName;
        } elseif (in_array($typeKey, ['passport', 'doc_passport_copy', 'passport_copy'])) {
            if ($driver->passport_photo && File::exists($uploadDir . '/' . $driver->passport_photo)) {
                @unlink($uploadDir . '/' . $driver->passport_photo);
            }
            $driver->passport_photo = $fileName;
        } elseif (in_array($typeKey, ['vehicle_reg', 'doc_vehicle_reg', 'truck_registration'])) {
            if ($driver->truck_registration_photo && File::exists($uploadDir . '/' . $driver->truck_registration_photo)) {
                @unlink($uploadDir . '/' . $driver->truck_registration_photo);
            }
            $driver->truck_registration_photo = $fileName;
        }

        $driver->save();

        return response()->json([
            'status' => true,
            'message' => 'Document updated successfully',
            'data' => [
                'type_key' => $typeKey,
                'file_name' => $fileName,
                'file_url' => asset('upload/driver_docs/' . $fileName),
            ]
        ]);
    }

    /**
     * Update driver profile details (Name, Phone, Email, License, Photo).
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $driverId = null;
        if ($user && $user->role === 'driver') {
            $driverId = $user->id;
        } elseif ($request->filled('driver_id')) {
            $driverId = $request->driver_id;
        } elseif ($request->filled('user_id')) {
            $driverId = $request->user_id;
        } elseif ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                $driverId = $found->id;
            }
        }
        if (!$driverId) {
            $driverId = 18;
        }

        $driver = User::with('driverProfile')->find($driverId);
        if (!$driver) {
            return response()->json(['status' => false, 'message' => 'Driver not found'], 404);
        }

        if ($request->filled('full_name') || $request->filled('name')) {
            $fullName = trim($request->input('full_name') ?: $request->input('name'));
            $parts = explode(' ', $fullName);
            $driver->fname = $parts[0] ?: $driver->fname;
            $driver->lname = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $driver->lname;
        }

        if ($request->filled('email')) {
            $email = strtolower(trim($request->input('email')));
            $existing = User::where('email', $email)->where('id', '!=', $driverId)->first();
            if (!$existing) {
                $driver->email = $email;
            }
        }

        if ($request->filled('phone')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', (string)$request->input('phone'));
            if (str_starts_with($cleanPhone, '965') && strlen($cleanPhone) > 8) {
                $cleanPhone = substr($cleanPhone, 3);
            }
            if (!empty($cleanPhone)) {
                $driver->phone = $cleanPhone;
            }
        }

        if ($request->filled('country_code')) {
            $driver->country_code = $request->input('country_code');
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($file->isValid()) {
                $filename = date('YmdHis') . '_driver_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $destPath = public_path('upload/user_images');
                if (!file_exists($destPath)) {
                    mkdir($destPath, 0755, true);
                }
                $file->move($destPath, $filename);
                $driver->photo = $filename;

                $cpanelDir = base_path('../public_html/upload/user_images');
                if (is_dir(base_path('../public_html')) && realpath($destPath) !== realpath($cpanelDir)) {
                    if (!File::exists($cpanelDir)) {
                        File::makeDirectory($cpanelDir, 0755, true, true);
                    }
                    @copy($destPath . '/' . $filename, $cpanelDir . '/' . $filename);
                }
            }
        }

        $driver->save();

        // Update driver profile record (license_number, etc.)
        $driverProfile = $driver->driverProfile;
        if (!$driverProfile) {
            $driverProfile = \App\Models\DriverProfile::firstOrCreate(['user_id' => $driverId]);
        }

        if ($request->filled('license_number')) {
            $driverProfile->license_number = trim($request->input('license_number'));
        }

        $driverProfile->save();

        $name = trim(($driver->fname ?? '') . ' ' . ($driver->lname ?? ''));
        $initials = 'MD';
        $parts = explode(' ', $name);
        if (count($parts) >= 2 && !empty($parts[0]) && !empty($parts[1])) {
            $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
        } elseif (!empty($parts[0])) {
            $initials = strtoupper(mb_substr($parts[0], 0, 1));
        }

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'data'    => [
                'driver_id'       => $driver->client_code ?: ('DR-' . $driver->id),
                'driver_name'     => $name,
                'driver_initials' => $initials,
                'driver_email'    => $driver->email,
                'driver_phone'    => ($driver->country_code ?: '+965') . ' ' . $driver->phone,
                'license_number'  => $driverProfile->license_number,
                'photo'           => $driver->photo ? url('upload/user_images/' . $driver->photo) : null,
            ]
        ], 200);
    }

    /**
     * Get in-app notifications for the driver.
     * Notifications strictly arrive for the driver in case of new trip requests.
     */
    public function notifications(Request $request)
    {
        $driverId = $this->resolveDriverId($request);

        // 1. Sync pending trip requests (ScheduledTripDriverInvitation & ShipmentDriverInvitation) into notifications table
        $pendingScheduled = ScheduledTripDriverInvitation::with([
            'scheduledTripOrder.pickupCity',
            'scheduledTripOrder.dropoffCity',
            'scheduledTrip.route.originCity',
            'scheduledTrip.route.destinationCity',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingScheduled as $inv) {
            $trip = $inv->scheduledTrip;
            $order = $inv->scheduledTripOrder;
            $orderNumber = $order ? $order->order_number : ($trip ? 'TRP-' . $trip->id : 'INV-' . $inv->id);
            $origin = $order && $order->pickupCity ? $order->pickupCity->name_en : ($trip && $trip->route && $trip->route->originCity ? $trip->route->originCity->name_en : 'Kuwait City');
            $dest = $order && $order->dropoffCity ? $order->dropoffCity->name_en : ($trip && $trip->route && $trip->route->destinationCity ? $trip->route->destinationCity->name_en : 'Dammam');
            $price = floatval($inv->offered_price > 0 ? $inv->offered_price : ($trip ? $trip->price : 0));

            $existing = DB::table('notifications')
                ->where('notifiable_id', $driverId)
                ->where('data->metadata->invitation_id', $inv->id)
                ->first();

            if (!$existing) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $driverId,
                    'type'            => 'App\Notifications\NewTripRequestNotification',
                    'data'            => json_encode([
                        'title'        => 'New Trip Request Available',
                        'body'         => "New trip request #{$orderNumber} from {$origin} to {$dest} (Offered: {$price} KWD). Tap to view and accept.",
                        'type'         => 'order',
                        'reference_id' => $orderNumber,
                        'action_route' => '/driver_trip_requests_screen',
                        'metadata'     => [
                            'invitation_id' => $inv->id,
                            'trip_id'       => $trip ? $trip->id : null,
                            'order_number'  => $orderNumber,
                            'from_city'     => $origin,
                            'to_city'       => $dest,
                            'price_kwd'     => $price,
                        ],
                    ]),
                    'read_at'         => null,
                    'created_at'      => $inv->created_at ?: now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        $pendingShipments = ShipmentDriverInvitation::with([
            'shipment.originCity',
            'shipment.destinationCity',
        ])
            ->where('driver_id', $driverId)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingShipments as $sinv) {
            $shipment = $sinv->shipment;
            $shipmentCode = $shipment ? ($shipment->tracking_number ?: 'SHP-' . $shipment->id) : 'SHP-' . $sinv->id;
            $origin = $shipment && $shipment->originCity ? $shipment->originCity->name_en : 'Kuwait City';
            $dest = $shipment && $shipment->destinationCity ? $shipment->destinationCity->name_en : 'Riyadh';
            $price = floatval($sinv->offered_price > 0 ? $sinv->offered_price : ($shipment ? $shipment->driver_cost : 0));

            $existing = DB::table('notifications')
                ->where('notifiable_id', $driverId)
                ->where('data->metadata->shipment_invitation_id', $sinv->id)
                ->first();

            if (!$existing) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $driverId,
                    'type'            => 'App\Notifications\NewTripRequestNotification',
                    'data'            => json_encode([
                        'title'        => 'New Shipment Request Available',
                        'body'         => "New shipment request #{$shipmentCode} from {$origin} to {$dest} (Offered: {$price} KWD). Tap to view and accept.",
                        'type'         => 'order',
                        'reference_id' => $shipmentCode,
                        'action_route' => '/driver_trip_requests_screen',
                        'metadata'     => [
                            'shipment_invitation_id' => $sinv->id,
                            'shipment_id'            => $shipment ? $shipment->id : null,
                            'from_city'              => $origin,
                            'to_city'                => $dest,
                            'price_kwd'              => $price,
                        ],
                    ]),
                    'read_at'         => null,
                    'created_at'      => $sinv->created_at ?: now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // 2. Query driver notifications strictly filtering for trip requests / orders
        $rawNotifications = DB::table('notifications')
            ->where('notifiable_id', $driverId)
            ->where(function ($q) {
                $q->where('type', 'App\Notifications\NewTripRequestNotification')
                  ->orWhere('data->type', 'order')
                  ->orWhere('data->action_route', '/driver_trip_requests_screen');
            })
            ->latest('created_at')
            ->take(50)
            ->get();

        $unreadCount = $rawNotifications->whereNull('read_at')->count();

        $formatted = $rawNotifications->map(function ($n) {
            $data = is_array($n->data) ? $n->data : json_decode($n->data, true);
            return [
                'id'           => (string) $n->id,
                'title'        => $data['title'] ?? 'New Trip Request Available',
                'body'         => $data['body'] ?? '',
                'type'         => 'order',
                'reference_id' => $data['reference_id'] ?? null,
                'action_route' => $data['action_route'] ?? '/driver_trip_requests_screen',
                'is_read'      => !is_null($n->read_at),
                'read_at'      => $n->read_at,
                'created_at'   => $n->created_at,
                'metadata'     => $data['metadata'] ?? null,
            ];
        })->values();

        return response()->json([
            'status'       => true,
            'message'      => 'Driver notifications retrieved successfully',
            'unread_count' => $unreadCount,
            'data'         => $formatted,
        ], 200);
    }

    /**
     * Mark a single driver notification as read.
     */
    public function markNotificationRead($id, Request $request)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->update([
                'read_at'    => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'Notification marked as read',
        ], 200);
    }

    /**
     * Mark all driver notifications as read.
     */
    public function markAllNotificationsRead(Request $request)
    {
        $driverId = $this->resolveDriverId($request);

        DB::table('notifications')
            ->where('notifiable_id', $driverId)
            ->where(function ($q) {
                $q->where('type', 'App\Notifications\NewTripRequestNotification')
                  ->orWhere('data->type', 'order')
                  ->orWhere('data->action_route', '/driver_trip_requests_screen');
            })
            ->whereNull('read_at')
            ->update([
                'read_at'    => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'status'  => true,
            'message' => 'All driver notifications marked as read',
        ], 200);
    }

    /**
     * Delete a single driver notification.
     */
    public function deleteNotification($id, Request $request)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Notification deleted successfully',
        ], 200);
    }

    /**
     * Clear all driver notifications.
     */
    public function clearAllNotifications(Request $request)
    {
        $driverId = $this->resolveDriverId($request);

        DB::table('notifications')
            ->where('notifiable_id', $driverId)
            ->where(function ($q) {
                $q->where('type', 'App\Notifications\NewTripRequestNotification')
                  ->orWhere('data->type', 'order')
                  ->orWhere('data->action_route', '/driver_trip_requests_screen');
            })
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'All driver notifications cleared successfully',
        ], 200);
    }

    /**
     * Get the current driver's truck details.
     */
    public function getDriverTruck(Request $request)
    {
        $driverId = $this->resolveDriverId($request);
        $driver = User::find($driverId);

        $truck = DriverTruck::with(['truckType', 'truckSubType', 'truckBrand', 'truckModel'])
            ->where('driver_id', $driverId)
            ->where('is_default', 1)
            ->first();

        if (!$truck) {
            $truck = DriverTruck::with(['truckType', 'truckSubType', 'truckBrand', 'truckModel'])
                ->where('driver_id', $driverId)
                ->latest()
                ->first();
        }

        $driverProfile = $driver ? $driver->driverProfile : null;
        if (!$driverProfile && $driver) {
            $driverProfile = \App\Models\DriverProfile::where('user_id', $driverId)->first();
        }

        $truckTypeId = $truck ? $truck->truck_type_id : ($driverProfile && $driverProfile->truck_type_id ? $driverProfile->truck_type_id : null);
        $truckType = $truck && $truck->truckType ? $truck->truckType : ($truckTypeId ? TruckType::find($truckTypeId) : null);
        $truckTypeName = $truckType ? ($truckType->name_en ?: $truckType->name_ar) : '';
        $truckTypeNameAr = $truckType ? ($truckType->name_ar ?: $truckType->name_en) : '';

        $truckSubType = $truck ? $truck->truckSubType : null;
        $truckSubTypeName = $truckSubType ? ($truckSubType->name_en ?: $truckSubType->name_ar) : null;
        $truckSubTypeNameAr = $truckSubType ? ($truckSubType->name_ar ?: $truckSubType->name_en) : null;

        $plateNumber = $truck && $truck->plate_number ? $truck->plate_number : ($driverProfile && $driverProfile->plate_number ? $driverProfile->plate_number : '');
        $modelYear = $truck && $truck->manufacturing_year ? (string)$truck->manufacturing_year : ($driverProfile && $driverProfile->model_year ? (string)$driverProfile->model_year : '');

        // Real capacity from database
        $maxWeightNum = null;
        if ($truckSubType && $truckSubType->max_payload) {
            $maxWeightNum = (float)$truckSubType->max_payload * 1000;
        } elseif ($truckType && $truckType->max_weight) {
            $maxWeightNum = (float)$truckType->max_weight * 1000;
        }
        $maxCapacity = $maxWeightNum !== null ? number_format($maxWeightNum, 0) . ' Kg' : null;

        // Subtitle: Real brand & model if available in database, or real sub-type name if available, otherwise General (No Sub-type)
        $brandName = $truck && $truck->truckBrand ? ($truck->truckBrand->name_en ?: $truck->truckBrand->name_ar) : null;
        $modelName = $truck && $truck->truckModel ? ($truck->truckModel->name_en ?: $truck->truckModel->name_ar) : null;

        $fullModelTitle = null;
        if ($brandName && $modelName) {
            $fullModelTitle = $brandName . ' ' . $modelName;
        } elseif ($truckSubTypeName) {
            $fullModelTitle = $truckSubTypeName;
        } else {
            $fullModelTitle = 'General (No Sub-type)';
        }

        $isDefault = $truck ? (bool)$truck->is_default : true;
        $isVerified = $truck ? (bool)$truck->is_verified : false;
        $axlesCount = $truck && $truck->axles_count ? (string)$truck->axles_count : null;

        $title = trim($truckTypeName . ($modelYear ? ' · ' . $modelYear : ''));

        return response()->json([
            'status'  => true,
            'message' => 'Driver truck retrieved successfully',
            'data'    => [
                'id'                  => $truck ? $truck->id : 0,
                'driver_id'           => $driverId,
                'truck_type_id'       => $truckTypeId,
                'truck_type_name'     => $truckTypeName,
                'truck_type_name_ar'  => $truckTypeNameAr,
                'truck_sub_type_id'   => $truck ? $truck->truck_sub_type_id : null,
                'truck_sub_type_name' => $truckSubTypeName,
                'truck_sub_type_name_ar' => $truckSubTypeNameAr,
                'title'               => $title,
                'model_name'          => $fullModelTitle,
                'plate_number'        => $plateNumber,
                'model_year'          => $modelYear,
                'max_capacity'        => $maxCapacity,
                'axles'               => $axlesCount,
                'is_default'          => $isDefault,
                'is_verified'         => $isVerified,
                'status'              => $isDefault ? 'Default Truck' : 'Secondary',
                'verification_status' => $isVerified ? 'Verified' : 'Unverified',
                'photo_url'           => $truckType && $truckType->photo ? url('upload/truck_images/' . $truckType->photo) : null,
            ]
        ], 200);
    }

    /**
     * Update the driver's truck type, truck sub-type, and specifications.
     */
    public function updateDriverTruck(Request $request)
    {
        $driverId = $this->resolveDriverId($request);
        $driver = User::find($driverId);

        $validator = Validator::make($request->all(), [
            'truck_type_id'     => 'required|exists:truck_types,id',
            'truck_sub_type_id' => 'nullable|exists:truck_sub_types,id',
            'plate_number'      => 'nullable|string|max:50',
            'model_year'        => 'nullable|string|max:10',
            'axles_count'       => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $truckTypeId = $request->input('truck_type_id');
        $truckSubTypeId = $request->input('truck_sub_type_id');

        // 1. Update or create default DriverTruck
        $truck = DriverTruck::where('driver_id', $driverId)->where('is_default', 1)->first();
        if (!$truck) {
            $truck = DriverTruck::where('driver_id', $driverId)->latest()->first();
        }
        if (!$truck) {
            $truck = new DriverTruck();
            $truck->driver_id = $driverId;
            $truck->is_default = 1;
        }

        $truck->truck_type_id = $truckTypeId;
        $truck->truck_sub_type_id = $truckSubTypeId ?: null;

        if ($request->filled('plate_number')) {
            $truck->plate_number = trim($request->input('plate_number'));
        }
        if ($request->filled('model_year')) {
            $truck->manufacturing_year = trim($request->input('model_year'));
        }
        if ($request->filled('axles_count')) {
            $truck->axles_count = (int)$request->input('axles_count');
        }
        $truck->save();

        // 2. Sync to DriverProfile
        $driverProfile = $driver ? $driver->driverProfile : null;
        if (!$driverProfile && $driver) {
            $driverProfile = \App\Models\DriverProfile::firstOrCreate(['user_id' => $driverId]);
        }
        if ($driverProfile) {
            $driverProfile->truck_type_id = $truckTypeId;
            if ($request->filled('plate_number')) {
                $driverProfile->plate_number = trim($request->input('plate_number'));
            }
            if ($request->filled('model_year')) {
                $driverProfile->model_year = trim($request->input('model_year'));
            }
            $driverProfile->save();
        }

        // Return updated truck details using getDriverTruck
        return $this->getDriverTruck($request);
    }

    /**
     * Helper to resolve driver ID consistently.
     */
    private function resolveDriverId(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === 'driver') {
            return $user->id;
        }
        if ($request->filled('driver_id')) {
            return $request->driver_id;
        }
        if ($request->filled('client_code')) {
            $found = User::where('client_code', $request->client_code)->first();
            if ($found) {
                return $found->id;
            }
        }
        return 18;
    }
}
