<?php

namespace Modules\AI\app\Http\Controllers\Admin\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Brian2694\Toastr\Facades\Toastr;

class AISettingsController extends Controller
{
    public function index(): View
    {
        $aiSettings = BusinessSetting::whereIn('key', [
            'ai_product_auto_fill',
            'ai_image_analysis',
            'ai_api_key',
            'ai_model',
        ])->pluck('value', 'key')->toArray();

        return view('ai::settings.index', compact('aiSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ai_product_auto_fill' => 'nullable|in:0,1',
            'ai_image_analysis' => 'nullable|in:0,1',
            'ai_api_key' => 'nullable|string',
            'ai_model' => 'nullable|string',
        ]);

        foreach ($request->only(['ai_product_auto_fill', 'ai_image_analysis', 'ai_api_key', 'ai_model']) as $key => $value) {
            BusinessSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Toastr::success(translate('messages.AI settings updated successfully'));
        return redirect()->back();
    }
}
