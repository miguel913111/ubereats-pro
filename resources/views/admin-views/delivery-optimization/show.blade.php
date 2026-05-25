@extends('layouts.admin.app')
@section('title', translate('messages.Batch Details'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/route.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Batch')}} #{{$batch->id}}</span>
            </h1>
            <a href="{{route('admin.delivery-optimization.index')}}" class="btn btn-secondary btn-sm">{{translate('messages.Back')}}</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('messages.Batch Info')}}</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>{{translate('messages.Delivery Man')}}:</strong> {{$batch->deliveryMan?->f_name ?? '-'}}</p>
                        <p><strong>{{translate('messages.Zone')}}:</strong> {{$batch->zone?->name ?? '-'}}</p>
                        <p><strong>{{translate('messages.Status')}}:</strong> <span class="badge badge-{{$batch->status == 'completed' ? 'success' : ($batch->status == 'active' ? 'info' : 'secondary')}}">{{$batch->status}}</span></p>
                        <p><strong>{{translate('messages.Total Orders')}}:</strong> {{$batch->total_orders}}</p>
                        <p><strong>{{translate('messages.Total Distance')}}:</strong> {{$batch->total_distance_km}} km</p>
                        <p><strong>{{translate('messages.Estimated Duration')}}:</strong> {{$batch->estimated_duration_min}} min</p>
                        <p><strong>{{translate('messages.Started At')}}:</strong> {{$batch->started_at ?? '-'}}</p>
                        <p><strong>{{translate('messages.Completed At')}}:</strong> {{$batch->completed_at ?? '-'}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('messages.Orders')}}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{translate('messages.Sequence')}}</th>
                                    <th>{{translate('messages.Order ID')}}</th>
                                    <th>{{translate('messages.Customer')}}</th>
                                    <th>{{translate('messages.Address')}}</th>
                                    <th>{{translate('messages.Distance')}}</th>
                                    <th>{{translate('messages.Time')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->batchOrders as $bo)
                                <tr>
                                    <td>{{$bo->delivery_sequence}}</td>
                                    <td>#{{$bo->order_id}}</td>
                                    <td>{{$bo->order?->customer?->f_name ?? '-'}}</td>
                                    <td>{{Str::limit($bo->order?->delivery_address ?? '-', 50)}}</td>
                                    <td>{{$bo->distance_from_prev_km}} km</td>
                                    <td>{{$bo->estimated_time_min}} min</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Route Segments')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.Sequence')}}</th>
                            <th>{{translate('messages.From')}}</th>
                            <th>{{translate('messages.To')}}</th>
                            <th>{{translate('messages.Distance')}}</th>
                            <th>{{translate('messages.Est. Minutes')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batch->routeSegments as $seg)
                        <tr>
                            <td>{{$seg->sequence}}</td>
                            <td>{{$seg->from_type}} ({{$seg->from_lat}}, {{$seg->from_lng}})</td>
                            <td>{{$seg->to_type}} ({{$seg->to_lat}}, {{$seg->to_lng}})</td>
                            <td>{{$seg->distance_km}} km</td>
                            <td>{{$seg->estimated_minutes}} min</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
