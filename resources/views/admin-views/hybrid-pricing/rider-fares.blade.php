@extends('layouts.admin.app')
@section('title', translate('messages.Rider Fares'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/taxi.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Rider Fares')}}</span>
            </h1>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Vehicle Fare Configuration')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.Vehicle')}}</th>
                            <th>{{translate('messages.Base Fare')}}</th>
                            <th>{{translate('messages.Per KM Fare')}}</th>
                            <th>{{translate('messages.Fixed Fee')}}</th>
                            <th>{{translate('messages.Minimum Fare')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $vehicle)
                        <tr>
                            <td>{{$vehicle->type}}</td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($vehicle->rider_base_fare)}}</td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($vehicle->rider_per_km_fare)}}</td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($vehicle->rider_fixed_fee)}}</td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($vehicle->rider_minimum_fare)}}</td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#fareModal{{$vehicle->id}}">{{translate('messages.Edit')}}</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $vehicles->links() !!}
            </div>
        </div>
    </div>

    @foreach($vehicles as $vehicle)
    <div class="modal fade" id="fareModal{{$vehicle->id}}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.hybrid-pricing.update-rider-fare', $vehicle->id)}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('messages.Edit Fare')}} - {{$vehicle->type}}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{translate('messages.Base Fare')}}</label>
                            <input type="number" step="0.01" name="rider_base_fare" class="form-control" value="{{$vehicle->rider_base_fare}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Per KM Fare')}}</label>
                            <input type="number" step="0.01" name="rider_per_km_fare" class="form-control" value="{{$vehicle->rider_per_km_fare}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Fixed Fee (Platform)')}}</label>
                            <input type="number" step="0.01" name="rider_fixed_fee" class="form-control" value="{{$vehicle->rider_fixed_fee}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Minimum Fare')}}</label>
                            <input type="number" step="0.01" name="rider_minimum_fare" class="form-control" value="{{$vehicle->rider_minimum_fare}}">
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
