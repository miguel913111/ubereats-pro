@extends('layouts.admin.app')
@section('title', translate('messages.Delivery Optimization Settings'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/settings.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Delivery Optimization Settings')}}</span>
            </h1>
            <a href="{{route('admin.delivery-optimization.index')}}" class="btn btn-secondary btn-sm">{{translate('messages.Back')}}</a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Batch Delivery Configuration')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.delivery-optimization.update-settings')}}" method="POST">
                    @csrf
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" name="batch_delivery_enabled" id="batch_enabled" value="1" {{$settings['enabled'] ? 'checked' : ''}}>
                        <label class="form-check-label" for="batch_enabled">
                            <strong>{{translate('messages.Enable Batch Delivery')}}</strong>
                            <br><small class="text-muted">{{translate('messages.Allow grouping multiple orders into a single delivery route')}}</small>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>{{translate('messages.Max Radius (km)')}}</label>
                        <input type="number" step="0.1" name="batch_max_radius_km" class="form-control" value="{{$settings['max_radius_km']}}">
                        <small class="text-muted">{{translate('messages.Maximum distance between customers to group orders')}}</small>
                    </div>
                    <div class="form-group">
                        <label>{{translate('messages.Max Orders Per Batch')}}</label>
                        <input type="number" name="batch_max_orders" class="form-control" value="{{$settings['max_orders']}}">
                    </div>
                    <div class="form-group">
                        <label>{{translate('messages.Min Orders to Group')}}</label>
                        <input type="number" name="batch_min_orders_to_group" class="form-control" value="{{$settings['min_orders']}}">
                    </div>
                    <div class="form-group">
                        <label>{{translate('messages.Time Window (minutes)')}}</label>
                        <input type="number" name="batch_time_window_minutes" class="form-control" value="{{$settings['time_window_min']}}">
                        <small class="text-muted">{{translate('messages.Only group orders placed within this time window')}}</small>
                    </div>
                    <button type="submit" class="btn btn-primary">{{translate('messages.Save Settings')}}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
