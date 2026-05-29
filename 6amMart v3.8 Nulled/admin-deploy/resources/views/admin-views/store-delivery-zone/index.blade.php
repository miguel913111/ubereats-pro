@extends('layouts.admin.app')
@section('title', translate('messages.Store Delivery Zones'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-map nav-icon"></i>
                </span>
                <span>{{translate('messages.Store Delivery Zones')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Zone List')}}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{translate('messages.Store')}}</th>
                                <th>{{translate('messages.Name')}}</th>
                                <th>{{translate('messages.Delivery Charge')}}</th>
                                <th>{{translate('messages.Status')}}</th>
                                <th>{{translate('messages.Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zones as $key => $zone)
                            <tr>
                                <td>{{$zones->firstItem() + $key}}</td>
                                <td>{{$zone->store?->name ?? '-'}}</td>
                                <td>{{$zone->name}}</td>
                                <td>{{$zone->delivery_charge}}</td>
                                <td>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status-toggle"
                                            data-url="{{route('admin.store-delivery-zone.status')}}"
                                            data-id="{{$zone->id}}"
                                            {{$zone->status == 1 ? 'checked' : ''}}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-danger delete-data" href="javascript:"
                                       data-id="delete-{{$zone->id}}"
                                       data-message="{{translate('messages.Want to delete this zone')}}?">
                                        {{translate('messages.delete')}}
                                    </a>
                                    <form action="{{route('admin.store-delivery-zone.delete', $zone->id)}}"
                                          method="post" id="delete-{{$zone->id}}">
                                        @csrf @method('delete')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">{{translate('messages.no_data_found')}}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="page-area">
                    {!! $zones->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script>
        $(document).on('change', '.status-toggle', function () {
            let url = $(this).data('url');
            let id = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            $.post(url, { _token: '{{csrf_token()}}', id: id, status: status }, function (data) {
                toastr.success('{{translate('messages.Status updated successfully')}}');
            });
        });
    </script>
@endpush
