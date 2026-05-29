@extends('layouts.admin.app')
@section('title', translate('messages.Global Pricing Settings'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/settings.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Global Pricing Settings')}}</span>
            </h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Available Business Models')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.hybrid-pricing.update-global-settings')}}" method="POST">
                    @csrf
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="commission_business_model" id="commission_global" value="1" {{$commissionModel ? 'checked' : ''}}>
                        <label class="form-check-label" for="commission_global">
                            <strong>{{translate('messages.Commission Model')}}</strong>
                            <br><small class="text-muted">{{translate('messages.Allow stores to use commission-based pricing')}}</small>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="subscription_business_model" id="subscription_global" value="1" {{$subscriptionModel ? 'checked' : ''}}>
                        <label class="form-check-label" for="subscription_global">
                            <strong>{{translate('messages.Subscription Model')}}</strong>
                            <br><small class="text-muted">{{translate('messages.Allow stores to use subscription-based pricing')}}</small>
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="fixed_fee_business_model" id="fixed_global" value="1" {{$fixedFeeModel ? 'checked' : ''}}>
                        <label class="form-check-label" for="fixed_global">
                            <strong>{{translate('messages.Fixed Fee Model')}}</strong>
                            <br><small class="text-muted">{{translate('messages.Allow stores to pay fixed fee per delivery')}}</small>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">{{translate('messages.Save Settings')}}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
