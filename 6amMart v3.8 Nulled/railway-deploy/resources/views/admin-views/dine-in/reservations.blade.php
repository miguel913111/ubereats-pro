@extends('layouts.admin.app')
@section('title', translate('messages.Table Reservations'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-calendar nav-icon"></i>
                </span>
                <span>{{translate('messages.Table Reservations')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Reservation List')}}</h5>
                <div>
                    <a href="{{route('admin.dine-in.reservations', ['status' => 'all'])}}" class="btn btn-sm btn-{{$status == 'all' ? 'primary' : 'outline-primary'}}">{{translate('messages.All')}}</a>
                    <a href="{{route('admin.dine-in.reservations', ['status' => 'pending'])}}" class="btn btn-sm btn-{{$status == 'pending' ? 'warning' : 'outline-warning'}}">{{translate('messages.Pending')}}</a>
                    <a href="{{route('admin.dine-in.reservations', ['status' => 'confirmed'])}}" class="btn btn-sm btn-{{$status == 'confirmed' ? 'success' : 'outline-success'}}">{{translate('messages.Confirmed')}}</a>
                    <a href="{{route('admin.dine-in.reservations', ['status' => 'completed'])}}" class="btn btn-sm btn-{{$status == 'completed' ? 'info' : 'outline-info'}}">{{translate('messages.Completed')}}</a>
                    <a href="{{route('admin.dine-in.reservations', ['status' => 'cancelled'])}}" class="btn btn-sm btn-{{$status == 'cancelled' ? 'danger' : 'outline-danger'}}">{{translate('messages.Cancelled')}}</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{translate('messages.Store')}}</th>
                                <th>{{translate('messages.Customer')}}</th>
                                <th>{{translate('messages.Table')}}</th>
                                <th>{{translate('messages.Date & Time')}}</th>
                                <th>{{translate('messages.Guests')}}</th>
                                <th>{{translate('messages.Status')}}</th>
                                <th>{{translate('messages.Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $key => $res)
                            <tr>
                                <td>{{$reservations->firstItem() + $key}}</td>
                                <td>{{$res->store?->name ?? '-'}}</td>
                                <td>{{$res->user?->f_name ?? ''}} {{$res->user?->l_name ?? ''}}<br><small>{{$res->user?->phone ?? '-'}}</small></td>
                                <td>{{$res->storeTable?->table_number ?? '-'}}</td>
                                <td>{{$res->reservation_date}}<br><small>{{$res->reservation_time}}</small></td>
                                <td>{{$res->number_of_guests}}</td>
                                <td>
                                    @if($res->status == 'pending')
                                        <span class="badge badge-warning">{{translate('messages.Pending')}}</span>
                                    @elseif($res->status == 'confirmed')
                                        <span class="badge badge-success">{{translate('messages.Confirmed')}}</span>
                                    @elseif($res->status == 'completed')
                                        <span class="badge badge-info">{{translate('messages.Completed')}}</span>
                                    @elseif($res->status == 'cancelled')
                                        <span class="badge badge-danger">{{translate('messages.Cancelled')}}</span>
                                    @else
                                        <span class="badge badge-secondary">{{$res->status}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($res->status == 'pending')
                                        <a href="{{route('admin.dine-in.reservations.confirm', $res->id)}}" class="btn btn-success btn-sm" onclick="return confirm('{{translate('messages.Confirm this reservation?')}}')">{{translate('messages.Confirm')}}</a>
                                    @endif
                                    @if($res->status == 'confirmed')
                                        <a href="{{route('admin.dine-in.reservations.complete', $res->id)}}" class="btn btn-info btn-sm" onclick="return confirm('{{translate('messages.Complete this reservation?')}}')">{{translate('messages.Complete')}}</a>
                                    @endif
                                    @if(in_array($res->status, ['pending', 'confirmed']))
                                        <a href="{{route('admin.dine-in.reservations.cancel', $res->id)}}" class="btn btn-danger btn-sm" onclick="return confirm('{{translate('messages.Cancel this reservation?')}}')">{{translate('messages.Cancel')}}</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">{{translate('messages.No reservations found')}}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($reservations->hasPages())
                <div class="card-footer">
                    {!! $reservations->links() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
