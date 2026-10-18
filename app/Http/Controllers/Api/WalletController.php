<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ScheduledTrip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * List all user wallets and their balances.
     */
    public function index(Request $request)
    {
        $usersQuery = User::whereIn('role', ['user', 'customer', 'individual_customer', 'company_customer', 'driver'])
            ->select('id', 'client_code', 'fname', 'lname', 'email', 'phone', 'photo', 'role')
            ->orderBy('fname', 'asc');

        if ($request->filled('role')) {
            $usersQuery->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $usersQuery->where(function ($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('client_code', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->paginate($request->input('per_page', 20));

        // Attach wallet balance & stats to each user
        foreach ($users as $user) {
            $lastTxn = WalletTransaction::where('user_id', $user->id)->latest('id')->first();
            $user->current_balance   = $lastTxn ? (float)$lastTxn->balance_after : 0.00;
            $user->transactions_count = WalletTransaction::where('user_id', $user->id)->count();
            $user->last_activity      = $lastTxn ? $lastTxn->created_at->toIso8601String() : null;
        }

        return response()->json([
            'status'  => true,
            'message' => 'Wallets retrieved successfully',
            'data'    => $users
        ], 200);
    }

    /**
     * Get wallet details and balance for currently authenticated user.
     */
    public function myWallet(Request $request)
    {
        $user = $request->user();

        // 1. Authoritative credit from user model
        $userCredit = (float) $user->credit;

        // 2. Fetch last transaction
        $lastTxn = WalletTransaction::where('user_id', $user->id)->latest('id')->first();
        $lastBalance = $lastTxn ? (float) $lastTxn->balance_after : null;

        // If user has a credit that isn't reflected in a transaction, auto-record it so Activity shows it!
        if ($lastBalance === null && $userCredit > 0) {
            WalletTransaction::create([
                'user_id'       => $user->id,
                'amount'        => $userCredit,
                'type'          => 'deposit',
                'balance_after' => $userCredit,
                'description'   => 'Balance recharge from Administration (شحن رصيد من الإدارة)',
            ]);
        } elseif ($lastBalance !== null && abs($userCredit - $lastBalance) > 0.001) {
            $diff = $userCredit - $lastBalance;
            WalletTransaction::create([
                'user_id'       => $user->id,
                'amount'        => abs($diff),
                'type'          => $diff > 0 ? 'deposit' : 'withdrawal',
                'balance_after' => $userCredit,
                'description'   => $diff > 0
                    ? 'Balance recharge from Administration (شحن رصيد من الإدارة)'
                    : 'Balance adjustment by Administration (تعديل رصيد من الإدارة)',
            ]);
        }

        $currentBalance = (float) $user->credit;

        $totalDeposits = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['deposit', 'trip_earnings', 'refund'])
            ->sum('amount');

        $totalDeductions = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['withdrawal', 'commission_deduction'])
            ->sum('amount');

        $recentTransactions = WalletTransaction::with(['shipment:id,shipment_name', 'scheduledTrip.route'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(25)
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'My wallet retrieved successfully',
            'data'    => [
                'user'                => [
                    'id'          => $user->id,
                    'name'        => $user->fname . ' ' . ($user->lname ?? ''),
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'role'        => $user->role,
                    'client_code' => $user->client_code,
                ],
                'current_balance'     => $currentBalance,
                'total_deposits'      => (float) $totalDeposits,
                'total_deductions'    => (float) $totalDeductions,
                'recent_transactions' => $recentTransactions,
            ]
        ], 200);
    }

    /**
     * Get overview statistics of all wallets and balances.
     */
    public function statistics()
    {
        $totalDeposits = WalletTransaction::whereIn('type', ['deposit', 'trip_earnings', 'refund'])->sum('amount');
        $totalDeductions = WalletTransaction::whereIn('type', ['withdrawal', 'commission_deduction'])->sum('amount');

        $users = User::whereIn('role', ['user', 'customer', 'individual_customer', 'company_customer', 'driver'])->get();
        $totalBalance = 0.00;
        foreach ($users as $user) {
            $lastTxn = WalletTransaction::where('user_id', $user->id)->latest('id')->first();
            $totalBalance += $lastTxn ? (float) $lastTxn->balance_after : 0.00;
        }

        $stats = [
            'total_wallets'    => $users->count(),
            'total_balance'    => $totalBalance,
            'total_deposits'   => (float) $totalDeposits,
            'total_deductions' => (float) $totalDeductions,
        ];

        return response()->json([
            'status'  => true,
            'message' => 'Wallet statistics retrieved successfully',
            'data'    => $stats
        ], 200);
    }

    /**
     * Show single user's wallet and full transaction ledger.
     */
    public function show($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }

        $lastTxn = WalletTransaction::where('user_id', $userId)->latest('id')->first();
        $currentBalance = $lastTxn ? (float) $lastTxn->balance_after : 0.00;

        $transactions = WalletTransaction::with(['shipment:id,shipment_name', 'scheduledTrip.route'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->paginate(30);

        return response()->json([
            'status'  => true,
            'message' => 'User wallet details retrieved successfully',
            'data'    => [
                'user'            => [
                    'id'          => $user->id,
                    'name'        => $user->fname . ' ' . ($user->lname ?? ''),
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'role'        => $user->role,
                    'client_code' => $user->client_code,
                ],
                'current_balance' => $currentBalance,
                'transactions'    => $transactions,
            ]
        ], 200);
    }

    /**
     * List all wallet transactions with filters.
     */
    public function transactions(Request $request)
    {
        $query = WalletTransaction::with([
            'user:id,fname,lname,email,phone,client_code,role',
            'shipment:id,shipment_name',
            'scheduledTrip.route'
        ])->latest('id');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'Wallet transactions retrieved successfully',
            'data'    => $transactions
        ], 200);
    }

    /**
     * Get transactions of currently authenticated user.
     */
    public function myTransactions(Request $request)
    {
        $user = $request->user();

        $query = WalletTransaction::with([
            'shipment:id,shipment_name',
            'scheduledTrip.route'
        ])->where('user_id', $user->id)->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'status'  => true,
            'message' => 'My wallet transactions retrieved successfully',
            'data'    => $transactions
        ], 200);
    }

    /**
     * Store new deposit or withdrawal transaction for a user.
     */
    public function storeTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:deposit,withdrawal,trip_earnings,commission_deduction,refund',
            'amount'            => 'required|numeric|min:0.01',
            'description'       => 'nullable|string|max:255',
            'shipment_id'       => 'nullable|exists:shipments,id',
            'scheduled_trip_id' => 'nullable|exists:scheduled_trips,id',
        ], [
            'user_id.required' => 'Please specify the user wallet owner.',
            'type.required'    => 'Please specify the transaction type (deposit, withdrawal, trip_earnings, commission_deduction, refund).',
            'amount.required'  => 'Please enter a valid transaction amount.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

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
        $targetUser = User::find($userId);
        if ($targetUser) {
            $targetUser->credit = $newBalance;
            $targetUser->save();
        }


        $txn->load(['user:id,fname,lname,email,phone,client_code']);

        return response()->json([
            'status'  => true,
            'message' => 'Wallet transaction recorded successfully',
            'data'    => $txn
        ], 201);
    }

    /**
     * Update transaction and recalculate user ledger.
     */
    public function updateTransaction(Request $request, $id)
    {
        $txn = WalletTransaction::find($id);
        if (!$txn) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'type'              => 'required|in:deposit,withdrawal,trip_earnings,commission_deduction,refund',
            'amount'            => 'required|numeric|min:0.01',
            'description'       => 'nullable|string|max:255',
            'shipment_id'       => 'nullable|exists:shipments,id',
            'scheduled_trip_id' => 'nullable|exists:scheduled_trips,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

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

        $txn->load(['user:id,fname,lname,email,phone,client_code']);

        return response()->json([
            'status'  => true,
            'message' => 'Transaction updated and ledger recalculated successfully',
            'data'    => $txn
        ], 200);
    }

    /**
     * Delete transaction and recalculate user ledger.
     */
    public function deleteTransaction($id)
    {
        $txn = WalletTransaction::find($id);
        if (!$txn) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        $userId = $txn->user_id;
        $txn->delete();

        $this->recalculateUserLedger($userId);

        return response()->json([
            'status'  => true,
            'message' => 'Transaction deleted and user ledger recalculated successfully'
        ], 200);
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
