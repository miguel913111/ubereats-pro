@extends('layouts.admin.app')
@section('title', translate('messages.Dine-in Tables'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-restaurant nav-icon"></i>
                </span>
                <span>{{translate('messages.Dine-in Tables')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Table List')}}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{translate('messages.Store')}}</th>
                                <th>{{translate('messages.Table Number')}}</th>
                                <th>{{translate('messages.Capacity')}}</th>
                                <th>{{translate('messages.Status')}}</th>
                                <th>{{translate('messages.Description')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tables as $key => $table)
                            <tr>
                                <td>{{$tables->firstItem() + $key}}</td>
                                <td>{{$table->store?->name ?? '-'}}</td>
                                <td><span class="badge badge-info">{{$table->table_number}}</span></td>
                                <td>{{$table->capacity}}</td>
                                <td>
                                    @if($table->status == 'available')
                                        <span class="badge badge-success">{{translate('messages.Available')}}</span>
                                    @elseif($table->status == 'occupied')
                                        <span class="badge badge-danger">{{translate('messages.Occupied')}}</span>
                                    @elseif($table->status == 'reserved')
                                        <span class="badge badge-warning">{{translate('messages.Reserved')}}</span>
                                    @else
                                        <span class="badge badge-secondary">{{translate('messages.Maintenance')}}</span>
                                    @endif
                                </td>
                                <td>{{Str::limit($table->description, 40)}}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">{{translate('messages.No tables found')}}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tables->hasPages())
                <div class="card-footer">
                    {!! $tables->links() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
