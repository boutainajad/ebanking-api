<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\AddCoOwnerRequest;
use App\Services\AccountService;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
        $this->middleware('auth:api');
    }

    public function index()
    {
        $accounts = auth()->user()->accounts;
        return response()->json($accounts);
    }

    public function store(CreateAccountRequest $request)
    {
        try {
            $account = $this->accountService->createAccount($request->validated(), auth()->user());
            return response()->json($account, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $account = Account::with('owners')->findOrFail($id);
        $this->authorize('view', $account);
        return response()->json($account);
    }

    public function addCoOwner(AddCoOwnerRequest $request, $id)
    {
        $account = Account::findOrFail($id);
        $this->authorize('update', $account);
        $newOwner = User::findOrFail($request->user_id);

        try {
            $account = $this->accountService->addCoOwner($account, $newOwner, auth()->user());
            return response()->json($account);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

}