<?php

namespace App\Repositories;

use App\CentralLogics\Helpers;
use App\Contracts\Repositories\ModuleRepositoryInterface;
use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function __construct(protected Module $module)
    {
    }

    public function add(array $data): string|object
    {
        $module = $this->module->newInstance();
        foreach ($data as $key => $column) {
            $module[$key] = $column;
        }
        $module->save();
        return $module;
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->module->where($params)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->module->get();
    }

    public function getListWhere(string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection|LengthAwarePaginator
    {
        $key = explode(' ', $searchValue);

        return $this->module->withCount($relations)->where($filters)
            ->when(isset($key) , function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('module_name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->paginate($dataLimit);
    }

    public function update(string $id, array $data): bool|string|object
    {
        $module = $this->module->find($id);
        foreach ($data as $key => $column) {
            $module[$key] = $column;
        }
        $module->save();
        return $module;
    }

    public function delete(string $id): bool
    {
        $module = $this->module->find($id);

        if ($module) {
            // Desativar foreign key checks temporariamente
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Obter IDs relacionados
            $storeIds = DB::table('stores')->where('module_id', $id)->pluck('id');
            $itemIds = DB::table('items')->where('module_id', $id)->pluck('id');
            $orderIds = DB::table('orders')->where('module_id', $id)->pluck('id');
            $categoryIds = DB::table('categories')->where('module_id', $id)->pluck('id');
            $campaignIds = DB::table('campaigns')->where('module_id', $id)->pluck('id');

            // ========== TABELAS RELACIONADAS A ORDERS ==========
            if ($orderIds->isNotEmpty()) {
                $tablesByOrderId = [
                    'cash_back_histories',
                    'd_m_reviews',
                    'delivery_histories',
                    'expenses',
                    'messages',
                    'offline_payments',
                    'order_delivery_histories',
                    'order_details',
                    'order_payments',
                    'order_references',
                    'order_taxes',
                    'order_transactions',
                    'parcel_cancellations',
                    'parcel_penalty_fees',
                    'parcel_return_fees',
                    'refunds',
                    'track_deliverymen',
                ];
                foreach ($tablesByOrderId as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->whereIn('order_id', $orderIds)->delete();
                    }
                }
            }

            // ========== TABELAS RELACIONADAS A ITEMS ==========
            if ($itemIds->isNotEmpty()) {
                $tablesByItemId = [
                    'allergy_item',
                    'carts',
                    'ecommerce_item_details',
                    'flash_sale_items',
                    'item_generic_names',
                    'item_nutrition',
                    'item_seo_data',
                    'item_tag',
                    'pharmacy_item_details',
                    'reviews',
                    'temp_products',
                    'wishlists',
                ];
                foreach ($tablesByItemId as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->whereIn('item_id', $itemIds)->delete();
                    }
                }
                if (Schema::hasTable('item_translations')) {
                    DB::table('item_translations')->whereIn('item_id', $itemIds)->delete();
                }
                if (Schema::hasTable('item_details')) {
                    DB::table('item_details')->whereIn('item_id', $itemIds)->delete();
                }
            }

            // ========== TABELAS RELACIONADAS A STORES ==========
            if ($storeIds->isNotEmpty()) {
                $tablesByStoreId = [
                    'add_ons',
                    'advertisements',
                    'campaign_store',
                    'coupons',
                    'delivery_men',
                    'disbursement_details',
                    'disbursement_withdrawal_methods',
                    'discounts',
                    'employee_roles',
                    'expenses',
                    'item_campaigns',
                    'items',
                    'order_taxes',
                    'orders',
                    'reviews',
                    'store_configs',
                    'store_details',
                    'store_notification_settings',
                    'store_schedule',
                    'store_subscriptions',
                    'subscription_billing_and_refund_histories',
                    'subscription_transactions',
                    'temp_products',
                    'vendor_employees',
                    'wishlists',
                ];
                foreach ($tablesByStoreId as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->whereIn('store_id', $storeIds)->delete();
                    }
                }
                if (Schema::hasTable('store_translations')) {
                    DB::table('store_translations')->whereIn('store_id', $storeIds)->delete();
                }
            }

            // ========== TABELAS RELACIONADAS A CATEGORIES ==========
            if ($categoryIds->isNotEmpty()) {
                if (Schema::hasTable('category_details')) {
                    DB::table('category_details')->whereIn('category_id', $categoryIds)->delete();
                }
                if (Schema::hasTable('category_translations')) {
                    DB::table('category_translations')->whereIn('category_id', $categoryIds)->delete();
                }
            }

            // ========== TABELAS RELACIONADAS A CAMPAIGNS ==========
            if ($campaignIds->isNotEmpty()) {
                if (Schema::hasTable('campaign_details')) {
                    DB::table('campaign_details')->whereIn('campaign_id', $campaignIds)->delete();
                }
                if (Schema::hasTable('campaign_store')) {
                    DB::table('campaign_store')->whereIn('campaign_id', $campaignIds)->delete();
                }
            }

            // ========== TABELAS COM MODULE_ID DIRETO ==========
            $tablesByModuleId = [
                'addon_categories',
                'advertisements',
                'banners',
                'brands',
                'campaigns',
                'categories',
                'coupons',
                'carts',
                'flash_sales',
                'item_campaigns',
                'items',
                'module_wise_banners',
                'module_wise_why_chooses',
                'module_zone',
                'order_transactions',
                'orders',
                'parcel_categories',
                'recent_searches',
                'reviews',
                'stores',
                'surge_price_dates',
                'temp_products',
            ];
            foreach ($tablesByModuleId as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('module_id', $id)->delete();
                }
            }

            // Reativar foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // Apagar thumbnail do módulo
            if ($module->thumbnail) {
                Helpers::check_and_delete('module/' , $module['thumbnail']);
            }

            // Apagar translations do módulo
            $module->translations()->delete();

            // Apagar o módulo
            $module->delete();
        }

        return true;
    }

    public function getExportList(Request $request): Collection
    {
        $key = explode(' ', $request['search']);
        return $this->module->withCount('stores')->
        when(isset($key) , function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('module_name', 'like', "%{$value}%");
                }
            });
        })
        ->get();
    }

    public function getFirstWithoutGlobalScopeWhere(array $params, array $relations = []): ?Model
    {
        return $this->module->withoutGlobalScope('translate')->where($params)->first();
    }

    public function getSearchListWhere(string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, int $offset = null): Collection
    {
        $key = explode(' ', $searchValue);

        return $this->module->when(isset($key) , function($q) use($key){
                $q->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('module_name', 'like', "%{$value}%");
                    }
                });
            })
            ->latest()->get($dataLimit);
    }
}
