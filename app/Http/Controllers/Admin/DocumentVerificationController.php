<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class DocumentVerificationController extends Controller
{
    public function index(Request $request)
    {
        if (!Helpers::module_permission_check('document_verification')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $status = $request->get('status', 'pending');
        $documents = DocumentVerification::with('verifiable')
            ->where('status', $status)
            ->latest()
            ->paginate(25);

        return view('admin-views.document-verification.index', compact('documents', 'status'));
    }

    public function show($id)
    {
        if (!Helpers::module_permission_check('document_verification')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $document = DocumentVerification::with('verifiable')->findOrFail($id);
        return view('admin-views.document-verification.show', compact('document'));
    }

    public function approve(Request $request, $id)
    {
        if (!Helpers::module_permission_check('document_verification')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $document = DocumentVerification::findOrFail($id);
        $document->status = 'approved';
        $document->verified_by = auth('admin')->user()->id;
        $document->verified_at = now();
        $document->save();

        Toastr::success(translate('messages.Document approved successfully'));
        return redirect()->back();
    }

    public function reject(Request $request, $id)
    {
        if (!Helpers::module_permission_check('document_verification')) {
            Toastr::error(translate('messages.access_denied'));
            return back();
        }
        $document = DocumentVerification::findOrFail($id);
        $document->status = 'rejected';
        $document->rejection_reason = $request->rejection_reason;
        $document->verified_by = auth('admin')->user()->id;
        $document->verified_at = now();
        $document->save();

        Toastr::success(translate('messages.Document rejected successfully'));
        return redirect()->back();
    }
}
