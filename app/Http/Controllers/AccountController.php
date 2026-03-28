<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'admin']);
    }

    public function index()
    {
        $accounts = Account::with('owners')->get();
        return response()->json($accounts);
    }

    public function block(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);

        $account = Account::findOrFail($id);

        if ($account->status === 'CLOSED') {
            return response()->json(['error' => 'Impossible de bloquer un compte clôturé'], 422);
        }

        $account->status = 'BLOCKED';
        $account->block_reason = $request->reason;
        $account->save();

        return response()->json($account);
    }

    public function unblock($id)
    {
        $account = Account::findOrFail($id);

        if ($account->status === 'CLOSED') {
            return response()->json(['error' => 'Impossible de débloquer un compte clôturé'], 422);
        }

        $account->status = 'ACTIVE';
        $account->block_reason = null;
        $account->save();

        return response()->json($account);
    }

    public function close($id)
    {
        $account = Account::findOrFail($id);

        if ($account->balance != 0) {
            return response()->json(['error' => 'Le solde doit être nul pour clôturer'], 422);
        }

        $account->status = 'CLOSED';
        $account->save();

        return response()->json($account);
    }
}