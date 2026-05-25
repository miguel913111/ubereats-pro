<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreDeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class VendorSelfDeliveryController extends Controller
{
    public function getDeliveryMen(Request $request)
    {
        $deliveryMen = StoreDeliveryMan::where('store_id', $request->user()->id)
            ->get();

        return response()->json($deliveryMen, 200);
    }

    public function storeDeliveryMan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:191',
            'l_name' => 'nullable|string|max:191',
            'phone' => 'required|string|max:20|unique:store_delivery_men',
            'email' => 'nullable|email|max:191',
            'password' => 'required|string|min:6',
            'identity_type' => 'nullable|string|max:50',
            'identity_number' => 'nullable|string|max:191',
            'identity_image' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryMan = StoreDeliveryMan::create([
            'store_id' => $request->user()->id,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'identity_type' => $request->identity_type,
            'identity_number' => $request->identity_number,
            'identity_image' => $request->identity_image,
            'image' => $request->image,
        ]);

        return response()->json([
            'delivery_man' => $deliveryMan,
            'message' => translate('messages.Delivery man created successfully'),
        ], 200);
    }

    public function updateDeliveryMan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:191',
            'l_name' => 'nullable|string|max:191',
            'phone' => 'required|string|max:20|unique:store_delivery_men,phone,' . $id,
            'email' => 'nullable|email|max:191',
            'password' => 'nullable|string|min:6',
            'status' => 'required|in:0,1',
            'active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryMan = StoreDeliveryMan::where('id', $id)
            ->where('store_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->only(['f_name', 'l_name', 'phone', 'email', 'status', 'active']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $deliveryMan->update($data);

        return response()->json([
            'delivery_man' => $deliveryMan,
            'message' => translate('messages.Delivery man updated successfully'),
        ], 200);
    }

    public function deleteDeliveryMan($id)
    {
        $deliveryMan = StoreDeliveryMan::where('id', $id)
            ->where('store_id', auth('api')->user()->id)
            ->firstOrFail();

        $deliveryMan->delete();

        return response()->json([
            'message' => translate('messages.Delivery man deleted successfully'),
        ], 200);
    }

    public function assignOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'store_delivery_man_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where('id', $request->order_id)
            ->where('store_id', $request->user()->id)
            ->firstOrFail();

        $deliveryMan = StoreDeliveryMan::where('id', $request->store_delivery_man_id)
            ->where('store_id', $request->user()->id)
            ->where('status', 1)
            ->where('active', 1)
            ->firstOrFail();

        $order->delivery_man_id = $deliveryMan->id;
        $order->order_status = 'confirmed';
        $order->save();

        return response()->json([
            'message' => translate('messages.Order assigned to delivery man successfully'),
            'order' => $order,
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryMan = StoreDeliveryMan::where('phone', $request->phone)->first();

        if (!$deliveryMan || !Hash::check($request->password, $deliveryMan->password)) {
            return response()->json(['errors' => [['code' => 'invalid_credentials', 'message' => translate('messages.Invalid credentials')]]], 401);
        }

        if ($deliveryMan->status != 1) {
            return response()->json(['errors' => [['code' => 'account_inactive', 'message' => translate('messages.Account is inactive')]]], 403);
        }

        $token = $deliveryMan->createToken('StoreDeliveryManToken')->accessToken;

        return response()->json([
            'token' => $token,
            'delivery_man' => $deliveryMan,
        ], 200);
    }

    public function getAssignedOrders(Request $request)
    {
        $orders = Order::where('delivery_man_id', $request->user()->id)
            ->where('order_type', 'delivery')
            ->latest()
            ->get();

        return response()->json($orders, 200);
    }

    public function updateOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'order_status' => 'required|in:confirmed,picked_up,handover,delivered',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $order = Order::where('id', $request->order_id)
            ->where('delivery_man_id', $request->user()->id)
            ->firstOrFail();

        $order->order_status = $request->order_status;
        $order->save();

        return response()->json([
            'message' => translate('messages.Order status updated successfully'),
            'order' => $order,
        ], 200);
    }
}
