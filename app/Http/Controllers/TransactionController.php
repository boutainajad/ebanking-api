<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request, $accountId)
    {
        $account = Account::findOrFail($accountId);
        $this->authorize('view', $account);

        $query = $account->transactions();

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = Transaction::with('account')->findOrFail($id);
        $user = auth()->user();

        if (!$transaction->account->owners->contains($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($transaction);
    }
}