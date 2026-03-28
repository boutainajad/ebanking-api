<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use App\Models\Transfer;

class TransferController extends Controller
{
    protected $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
        $this->middleware('auth:api');
    }

    public function store(TransferRequest $request)
    {
        try {
            $transfer = $this->transferService->initiateTransfer($request->validated(), auth()->user());
            return response()->json($transfer, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $transfer = Transfer::with(['fromAccount', 'toAccount', 'initiator'])->findOrFail($id);
        $user = auth()->user();

        if (!$transfer->fromAccount->owners->contains($user) && !$transfer->toAccount->owners->contains($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($transfer);
    }
}