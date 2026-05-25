@extends('layouts.admin.app')
@section('title', translate('messages.Hybrid Pricing Models'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/money.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Hybrid Pricing Models')}}</span>
            </h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Store Pricing Models')}}</h5>
                <div>
                    <a href="{{route('admin.hybrid-pricing.global-settings')}}" class="btn btn-info btn-sm">{{translate('messages.Global Settings')}}</a>
                    <a href="{{route('admin.hybrid-pricing.rider-fares')}}" class="btn btn-warning btn-sm">{{translate('messages.Rider Fares')}}</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.Store')}}</th>
                            <th>{{translate('messages.Commission')}}</th>
                            <th>{{translate('messages.Subscription')}}</th>
                            <th>{{translate('messages.Fixed Fee')}}</th>
                            <th>{{translate('messages.Driver/KM')}}</th>
                            <th>{{translate('messages.Driver Fixed')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stores as $store)
                        <tr>
                            <td>{{$store->name}}</td>
                            <td>
                                @if($store->commission_active)
                                    <span class="badge badge-success">{{$store->comission}}%</span>
                                @else
                                    <span class="badge badge-secondary">{{translate('messages.Off')}}</span>
                                @endif
                            </td>
                            <td>
                                @if($store->subscription_active)
                                    <span class="badge badge-success">{{translate('messages.Active')}}</span>
                                @else
                                    <span class="badge badge-secondary">{{translate('messages.Off')}}</span>
                                @endif
                            </td>
                            <td>
                                @if($store->fixed_delivery_fee_active)
                                    <span class="badge badge-success">{{\App\CentralLogics\Helpers::format_currency($store->fixed_delivery_fee)}}</span>
                                @else
                                    <span class="badge badge-secondary">{{translate('messages.Off')}}</span>
                                @endif
                            </td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($store->driver_per_km_charge)}}</td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($store->driver_fixed_charge)}}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editModal{{$store->id}}">{{translate('messages.Edit')}}</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $stores->links() !!}
            </div>
        </div>
    </div>

    @foreach($stores as $store)
    <div class="modal fade" id="editModal{{$store->id}}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.hybrid-pricing.update-store-models', $store->id)}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('messages.Edit')}} - {{$store->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <h6>{{translate('messages.Pricing Models')}}</h6>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="commission_active" id="commission{{$store->id}}" value="1" {{$store->commission_active ? 'checked' : ''}}>
                            <label class="form-check-label" for="commission{{$store->id}}">{{translate('messages.Commission')}}</label>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Commission %')}}</label>
                            <input type="number" step="0.01" name="comission" class="form-control" value="{{$store->comission}}">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="subscription_active" id="subscription{{$store->id}}" value="1" {{$store->subscription_active ? 'checked' : ''}}>
                            <label class="form-check-label" for="subscription{{$store->id}}">{{translate('messages.Subscription')}}</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="fixed_delivery_fee_active" id="fixed{{$store->id}}" value="1" {{$store->fixed_delivery_fee_active ? 'checked' : ''}}>
                            <label class="form-check-label" for="fixed{{$store->id}}">{{translate('messages.Fixed Fee per Delivery')}}</label>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Fixed Fee Amount')}}</label>
                            <input type="number" step="0.01" name="fixed_delivery_fee" class="form-control" value="{{$store->fixed_delivery_fee}}">
                        </div>
                        <hr>
                        <h6>{{translate('messages.Driver Rates')}}</h6>
                        <div class="form-group">
                            <label>{{translate('messages.Per KM Charge')}}</label>
                            <input type="number" step="0.01" name="driver_per_km_charge" class="form-control" value="{{$store->driver_per_km_charge}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Fixed Charge')}}</label>
                            <input type="number" step="0.01" name="driver_fixed_charge" class="form-control" value="{{$store->driver_fixed_charge}}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('messages.Save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
