@extends('layouts.admin.app')
@section('title', translate('messages.Delivery Time Windows'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/clock.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Delivery Time Windows')}}</span>
            </h1>
            <a href="{{route('admin.delivery-optimization.index')}}" class="btn btn-secondary btn-sm">{{translate('messages.Back')}}</a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Add Time Window')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.delivery-optimization.time-windows.store')}}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{translate('messages.Zone')}}</label>
                                <select name="zone_id" class="form-control">
                                    <option value="">{{translate('messages.All Zones')}}</option>
                                    @foreach($zones as $zone)
                                        <option value="{{$zone->id}}">{{$zone->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{translate('messages.Day')}}</label>
                                <select name="day_of_week" class="form-control">
                                    <option value="mon">Monday</option>
                                    <option value="tue">Tuesday</option>
                                    <option value="wed">Wednesday</option>
                                    <option value="thu">Thursday</option>
                                    <option value="fri">Friday</option>
                                    <option value="sat">Saturday</option>
                                    <option value="sun">Sunday</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{translate('messages.Start')}}</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{translate('messages.End')}}</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>{{translate('messages.Extra Charge')}}</label>
                                <input type="number" step="0.01" name="extra_charge" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label>{{translate('messages.Peak')}}</label>
                                <input type="checkbox" name="is_peak" value="1" class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{translate('messages.Add')}}</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Time Windows')}}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.Zone')}}</th>
                            <th>{{translate('messages.Day')}}</th>
                            <th>{{translate('messages.Start')}}</th>
                            <th>{{translate('messages.End')}}</th>
                            <th>{{translate('messages.Extra Charge')}}</th>
                            <th>{{translate('messages.Peak')}}</th>
                            <th>{{translate('messages.Status')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeWindows as $tw)
                        <tr>
                            <td>{{$tw->zone?->name ?? 'All'}}</td>
                            <td>{{$tw->day_of_week}}</td>
                            <td>{{$tw->start_time}}</td>
                            <td>{{$tw->end_time}}</td>
                            <td>{{$tw->extra_charge}}</td>
                            <td>{{$tw->is_peak ? 'Yes' : 'No'}}</td>
                            <td><span class="badge badge-{{$tw->status ? 'success' : 'secondary'}}">{{$tw->status ? 'Active' : 'Inactive'}}</span></td>
                            <td>
                                <form action="{{route('admin.delivery-optimization.time-windows.delete', $tw->id)}}" method="POST" class="d-inline" onsubmit="return confirm('{{translate('messages.Are you sure?')}}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{translate('messages.Delete')}}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $timeWindows->links() !!}
            </div>
        </div>
    </div>
@endsection
