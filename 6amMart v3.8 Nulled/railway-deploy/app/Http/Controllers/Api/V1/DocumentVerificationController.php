<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentVerificationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|max:191',
            'document_number' => 'nullable|string|max:191',
            'document_images' => 'required|array',
            'document_images.*' => 'string', // base64 or url
            'notes' => 'nullable|string',
            'verifiable_type' => 'required|string|in:App\Models\User,App\Models\Store,App\Models\DeliveryMan',
            'verifiable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $existing = DocumentVerification::where('verifiable_type', $request->verifiable_type)
            ->where('verifiable_id', $request->verifiable_id)
            ->where('document_type', $request->document_type)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json(['errors' => [['code' => 'pending_exists', 'message' => translate('messages.You already have a pending document for this type')]]], 403);
        }

        $document = DocumentVerification::create([
            'verifiable_type' => $request->verifiable_type,
            'verifiable_id' => $request->verifiable_id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'document_images' => $request->document_images,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return response()->json([
            'document' => $document,
            'message' => translate('messages.Document submitted successfully')
        ], 200);
    }

    public function myDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verifiable_type' => 'required|string',
            'verifiable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $documents = DocumentVerification::where('verifiable_type', $request->verifiable_type)
            ->where('verifiable_id', $request->verifiable_id)
            ->latest()
            ->get();

        return response()->json($documents, 200);
    }

    public function checkStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verifiable_type' => 'required|string',
            'verifiable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $pending = DocumentVerification::where('verifiable_type', $request->verifiable_type)
            ->where('verifiable_id', $request->verifiable_id)
            ->where('status', 'pending')
            ->count();

        $approved = DocumentVerification::where('verifiable_type', $request->verifiable_type)
            ->where('verifiable_id', $request->verifiable_id)
            ->where('status', 'approved')
            ->count();

        $rejected = DocumentVerification::where('verifiable_type', $request->verifiable_type)
            ->where('verifiable_id', $request->verifiable_id)
            ->where('status', 'rejected')
            ->count();

        return response()->json([
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'is_verified' => ($pending == 0 && $approved > 0),
        ], 200);
    }
}
