<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ScheduledTrip;

class WalletController extends Controller
{
    /**
     * Display all user wallets, current balances, and transaction history.
     */
    public function AllWallets(Request $request)
    {
        $usersQuery = User::whereIn('role', ['user', 'customer', 'company_customer', 'driver'])->orderBy('fname', 'asc');

        if ($request->filled('role')) {
            $usersQuery->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $usersQuery->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->get();

        // Calculate balances for each user
        foreach ($users as $user) {
            $lastTxn = WalletTransaction::where('user_id', $user->id)->latest('id')->first();
            $user->current_balance = $lastTxn ? (float) $lastTxn->balance_after : 0.00;
            $user->txns_count       = WalletTransaction::where('user_id', $user->id)->count();
            $user->last_activity    = $lastTxn ? $lastTxn->created_at->format('Y-m-d H:i') : '—';
        }

        // Global Statistics
        $totalDeposits = WalletTransaction::whereIn('type', ['deposit', 'trip_earnings', 'refund'])->sum('amount');
        $totalDeductions = WalletTransaction::whereIn('type', ['withdrawal', 'commission_deduction'])->sum('amount');

        $stats = [
            'total_wallets'    => $users->count(),
            'total_balance'    => $users->sum('current_balance'),
            'total_deposits'   => $totalDeposits,
            'total_deductions' => $totalDeductions,
        ];

        // All recent transactions for tab
        $recentTransactions = WalletTransaction::with(['user', 'shipment', 'scheduledTrip'])->latest()->limit(100)->get();

        return view('admin.backend.wallet.all_wallets', compact('users', 'stats', 'recentTransactions'));
    }

    /**
     * Show form to record a deposit or withdrawal transaction for a user.
     */
    public function AddTransaction(Request $request)
    {
        $users = User::whereIn('role', ['user', 'customer', 'company_customer', 'driver'])->orderBy('fname', 'asc')->get();
        $selectedUserId = $request->user_id ?: null;

        $shipments = Shipment::orderBy('id', 'desc')->limit(100)->get(['id', 'shipment_name']);
        $trips     = ScheduledTrip::with(['route.originCity', 'route.destinationCity'])->orderBy('id', 'desc')->limit(100)->get();

        return view('admin.backend.wallet.add_transaction', compact('users', 'selectedUserId', 'shipments', 'trips'));
    }

    /**
     * Store new transaction and recalculate balance_after.
     */
    public function StoreTransaction(Request $request)
    {
        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:deposit,withdrawal,trip_earnings,commission_deduction,refund',
            'amount'            => 'required|numeric|min:0.01',
            'description'       => 'nullable|string|max:255',
            'shipment_id'       => 'nullable|exists:shipments,id',
            'scheduled_trip_id' => 'nullable|exists:scheduled_trips,id',
        ]);

        $userId = $request->user_id;
        $amount = (float) $request->amount;
        $type   = $request->type;

        // Fetch last balance
        $lastTxn = WalletTransaction::where('user_id', $userId)->latest('id')->first();
        $currentBalance = $lastTxn ? (float) $lastTxn->balance_after : 0.00;

        if (in_array($type, ['deposit', 'trip_earnings', 'refund'])) {
            $newBalance = $currentBalance + $amount;
        } else {
            $newBalance = $currentBalance - $amount;
        }

        $txn = WalletTransaction::create([
            'user_id'           => $userId,
            'amount'            => $amount,
            'type'              => $type,
            'balance_after'     => $newBalance,
            'description'       => $request->description ?: (ucfirst(str_replace('_', ' ', $type)) . ' transaction'),
            'shipment_id'       => $request->shipment_id ?: null,
            'scheduled_trip_id' => $request->scheduled_trip_id ?: null,
        ]);

        $notification = [
            'message'    => 'Wallet Transaction Recorded! New Balance: KWD ' . number_format($newBalance, 2),
            'alert-type' => 'success'
        ];

        return redirect()->route('all.wallets')->with($notification);
    }

    /**
     * Show form to edit transaction.
     */
    public function EditTransaction($id)
    {
        $transaction = WalletTransaction::with(['user', 'shipment', 'scheduledTrip'])->findOrFail($id);
        $users       = User::whereIn('role', ['user', 'customer', 'company_customer', 'driver'])->orderBy('fname', 'asc')->get();
        $shipments   = Shipment::orderBy('id', 'desc')->limit(100)->get(['id', 'shipment_name']);
        $trips       = ScheduledTrip::with(['route.originCity', 'route.destinationCity'])->orderBy('id', 'desc')->limit(100)->get();

        return view('admin.backend.wallet.edit_transaction', compact('transaction', 'users', 'shipments', 'trips'));
    }

    /**
     * Update transaction and recalculate balance history for user.
     */
    public function UpdateTransaction(Request $request)
    {
        $id  = $request->id;
        $txn = WalletTransaction::findOrFail($id);

        $request->validate([
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:deposit,withdrawal,trip_earnings,commission_deduction,refund',
            'amount'            => 'required|numeric|min:0.01',
            'description'       => 'nullable|string|max:255',
            'shipment_id'       => 'nullable|exists:shipments,id',
            'scheduled_trip_id' => 'nullable|exists:scheduled_trips,id',
        ]);

        $oldUserId = $txn->user_id;

        $txn->update([
            'user_id'           => $request->user_id,
            'type'              => $request->type,
            'amount'            => $request->amount,
            'description'       => $request->description,
            'shipment_id'       => $request->shipment_id ?: null,
            'scheduled_trip_id' => $request->scheduled_trip_id ?: null,
        ]);

        // Recalculate ledger for user(s)
        $this->recalculateUserLedger($oldUserId);
        if ($oldUserId != $request->user_id) {
            $this->recalculateUserLedger($request->user_id);
        }

        $notification = [
            'message'    => 'Wallet Transaction #' . $id . ' Updated & Ledger Recalculated!',
            'alert-type' => 'success'
        ];

        return redirect()->route('all.wallets')->with($notification);
    }

    /**
     * Delete transaction and recalculate balance history.
     */
    public function DeleteTransaction($id)
    {
        $txn = WalletTransaction::findOrFail($id);
        $userId = $txn->user_id;
        $txn->delete();

        $this->recalculateUserLedger($userId);

        $notification = [
            'message'    => 'Wallet Transaction Deleted & Balance Recalculated!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * AJAX: Fetch complete transaction ledger for a user for Modal Drawer.
     */
    public function GetUserWalletLogAjax($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        $txns = WalletTransaction::with(['shipment', 'scheduledTrip'])
                                 ->where('user_id', $user_id)
                                 ->orderBy('id', 'desc')
                                 ->get();

        $lastTxn = $txns->first();
        $currentBalance = $lastTxn ? (float) $lastTxn->balance_after : 0.00;

        $list = [];
        foreach ($txns as $t) {
            $list[] = [
                'id'            => $t->id,
                'type'          => $t->type,
                'type_label'    => ucfirst(str_replace('_', ' ', $t->type)),
                'amount'        => number_format($t->amount, 2),
                'balance_after' => number_format($t->balance_after, 2),
                'description'   => $t->description ?: '—',
                'shipment'      => $t->shipment ? ('Shipment #' . $t->shipment->id) : null,
                'scheduled_trip'=> $t->scheduledTrip ? ('Trip #' . $t->scheduledTrip->id) : null,
                'date'          => $t->created_at ? $t->created_at->format('Y-m-d H:i') : '—',
            ];
        }

        return response()->json([
            'status' => 'success',
            'user'   => [
                'id'              => $user->id,
                'name'            => $user->fname . ' ' . ($user->lname ?? ''),
                'email'           => $user->email,
                'role'            => ucfirst(str_replace('_', ' ', $user->role)),
                'current_balance' => number_format($currentBalance, 2),
            ],
            'transactions' => $list
        ]);
    }

    /**
     * Helper: Recalculate all balance_after values for a user in chronological order.
     */
    private function recalculateUserLedger($userId)
    {
        $txns = WalletTransaction::where('user_id', $userId)->orderBy('id', 'asc')->get();
        $runningBalance = 0.00;

        foreach ($txns as $t) {
            $amt = (float) $t->amount;
            if (in_array($t->type, ['deposit', 'trip_earnings', 'refund'])) {
                $runningBalance += $amt;
            } else {
                $runningBalance -= $amt;
            }
            $t->balance_after = $runningBalance;
            $t->save();
        }
    }
}
