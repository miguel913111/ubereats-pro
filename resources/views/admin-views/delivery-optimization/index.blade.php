@extends('layouts.admin.app')
@section('title', translate('messages.Delivery Optimization'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/route.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Delivery Optimization')}}</span>
            </h1>
            <div>
                <a href="{{route('admin.delivery-optimization.settings')}}" class="btn btn-info btn-sm">{{translate('messages.Settings')}}</a>
                <a href="{{route('admin.delivery-optimization.time-windows')}}" class="btn btn-warning btn-sm">{{translate('messages.Time Windows')}}</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Auto Batch Generation')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.delivery-optimization.auto-batch')}}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>{{translate('messages.Select Zone')}}</label>
                        <select name="zone_id" class="form-control" required>
                            @foreach($zones as $zone)
                                <option value="{{$zone->id}}">{{$zone->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">{{translate('messages.Generate Batches')}}</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Delivery Batches')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.ID')}}</th>
                            <th>{{translate('messages.Delivery Man')}}</th>
                            <th>{{translate('messages.Zone')}}</th>
                            <th>{{translate('messages.Status')}}</th>
                            <th>{{translate('messages.Orders')}}</th>
                            <th>{{translate('messages.Distance')}}</th>
                            <th>{{translate('messages.Duration')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                        <tr>
                            <td>#{{$batch->id}}</td>
                            <td>{{$batch->deliveryMan?->f_name ?? '-'}}</td>
                            <td>{{$batch->zone?->name ?? '-'}}</td>
                            <td>
                                <span class="badge badge-{{$batch->status == 'completed' ? 'success' : ($batch->status == 'active' ? 'info' : 'secondary')}}">
                                    {{$batch->status}}
                                </span>
                            </td>
                            <td>{{$batch->total_orders}}</td>
                            <td>{{$batch->total_distance_km}} km</td>
                            <td>{{$batch->estimated_duration_min}} min</td>
                            <td>
                                <a href="{{route('admin.delivery-optimization.show', $batch->id)}}" class="btn btn-primary btn-sm">{{translate('messages.View')}}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $batches->links() !!}
            </div>
        </div>
    </div>
@endsection
