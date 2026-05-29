@extends('layouts.admin.app')

@section('title', \App\CentralLogics\Helpers::get_business_settings('business_name') ?? translate('messages.dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="d-flex flex-wrap gap-2 justify-content-between py-2">
                <div class="d-flex align-items-center flex-grow-1">
                    <img src="{{asset('/public/assets/admin/img/new-img/users.svg')}}" alt="img">
                    <div class="w-0 flex-grow pl-3">
                        <h1 class="page-header-title mb-1">{{translate('Dispatch Overview')}}</h1>
                        <p class="page-header-text text-dark m-0">
                            {{translate('Monitor your')}}
                            <span class="font-semibold">{{translate('Dispatch Management')}}</span>
                            {{translate('statistics by zone')}}
                        </p>
                    </div>
                </div>
                <div class="alert bg--10 font-bold fs-14" role="alert">
                    {{ translate('This_section_only_contains_Order_Data') }}
                </div>
            </div>
        </div>

        <div class="row g-1">
            <div class="col-lg-8">
                <div class="row gap__10 __customer-statistics-card-wrap-2">
                    <div class="col-sm-6">
                        <div class="__customer-statistics-card h-100">
                            <div class="title">
                                <img src="{{asset('public/assets/admin/img/new-img/deliveryman/active.svg')}}"
                                    alt="new-img">
                                <h4>{{$active_deliveryman}}</h4>
                            </div>
                            <h4 class="subtitle text-capitalize mt-2">{{translate('messages.active_delivery_man')}}</h4>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="__customer-statistics-card h-100 d-flex gap-3" style="--clr:#FF5A54">
                            <div>
                                <img width="48" height="48"
                                    src="{{asset('public/assets/admin/img/new-img/deliveryman/newly.svg')}}" alt="new-img">
                            </div>
                            <div class="d-flex justify-content-around gap-3 flex-grow-1">
                                <div>
                                    <h4 class="title">{{ $inactive_deliveryman }}</h4>
                                    <h4 class="subtitle text-capitalize">{{translate('messages.in_Active')}}</h4>
                                </div>
                                <div>
                                    <h4 class="title">{{ $suspend_deliveryman }}</h4>
                                    <h4 class="subtitle text-capitalize">{{ translate('suspended')}}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="__customer-statistics-card h-100">
                            <div class="title">
                                <img src="{{asset('public/assets/admin/img/new-img/deliveryman/active.svg')}}"
                                    alt="new-img">
                                <h4>{{ $unavailable_deliveryman }}</h4>
                            </div>
                            <h4 class="subtitle text-capitalize mt-2">{{ translate('Fully Booked Delivery Man')}}</h4>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="__customer-statistics-card h-100" style="--clr:#FF5A54">
                            <div class="title">
                                <img src="{{asset('public/assets/admin/img/new-img/deliveryman/in-active.svg')}}"
                                    alt="new-img">
                                <h4>{{$available_deliveryman}}</h4>
                            </div>
                            <h4 class="subtitle text-capitalize mt-2">{{translate('Available to assign more order')}}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="shadow--order-card">
                    <div class="row m-0">
                        <div class="col-12 p-0">
                            <a class="order--card h-100" href="#">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                        <img src="{{asset('public/assets/admin/img/dashboard/food/unassigned.svg')}}"
                                            alt="dashboard" class="oder--card-icon">
                                        <span>{{translate('messages.unassigned_orders')}}</span>
                                    </h6>
                                    <span class="card-title text-00A3FF">
                                        {{$data['searching_for_dm']}}
                                    </span>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 p-0">
                            <a class="order--card h-100" href="#">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                        <img src="{{asset('public/assets/admin/img/dashboard/food/accepted.svg')}}"
                                            alt="dashboard" class="oder--card-icon">
                                        <span>{{translate('Accepted by Delivery Man')}}</span>
                                    </h6>
                                    <span class="card-title text-success">
                                        {{$data['accepted_by_dm']}}
                                    </span>
                                </div>
                            </a>
                        </div>
                        <div class="col-12 p-0">
                            <a class="order--card h-100" href="#">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                                        <img src="{{asset('public/assets/admin/img/dashboard/food/out-for.svg')}}"
                                            alt="dashboard" class="oder--card-icon">
                                        <span>{{translate('Out for Delivery')}}</span>
                                    </h6>
                                    <span class="card-title text-success">
                                        {{$data['picked_up']}}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="__map-wrapper-2 mt-3">
                    <div class="map-pop-deliveryman">
                        <form action="javascript:" id="search-form" class="map-pop-deliveryman-inner">
                            <label>{{ translate('Currently Active Delivery Men') }} </label>
                            <div class="position-relative mx-auto">
                                <i class="tio-search"></i>
                                <input type="text" name="search" class="form-control"
                                    placeholder="{{translate('Search Delivery Man ...')}}">
                            </div>
                            <a href="{{ route('admin.users.delivery-man.list') }}"
                                class="link font-semibold">{{ translate('View All Delivery Men') }}</a>
                        </form>
                    </div>
                    <div class="map-warper map-wrapper-2 rounded">
                        <div id="map-canvas" width="900px" class="rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    "use strict";
    let map;
    let dmMarkers = {};

    function initialize() {
        @php($default_location = \App\CentralLogics\Helpers::get_business_settings('default_location'))
        var lat = {{ $default_location ? $default_location['lat'] : '23.757989' }};
        var lng = {{ $default_location ? $default_location['lng'] : '90.360587' }};
        var deliveryMan = <?php echo json_encode($deliveryMen); ?>;

        map = L.map('map-canvas').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var bounds = L.latLngBounds();
        deliveryMan.forEach(dm => {
            if (dm.lat) {
                var icon = L.icon({
                    iconUrl: "{{ asset('public/assets/admin/img/delivery_boy_active.png') }}",
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });
                var m = L.marker([dm.lat, dm.lng], {icon: icon}).addTo(map);
                m.bindPopup(`
                    <div style='display:flex;align-items:center;gap:10px;'>
                        <img style='max-height:40px;width:auto;' src='${dm.image_link}'>
                        <div>
                            <b>${dm.name}</b><br/>
                            ${dm.location}<br/>
                            Assigned Order: ${dm.assigned_order_count}
                        </div>
                    </div>`);
                bounds.extend([dm.lat, dm.lng]);
                dmMarkers[dm.id] = m;
            }
        });
        if (bounds.isValid()) {
            map.fitBounds(bounds, {padding: [30, 30]});
        }
    }

    $(document).ready(function() {
        initialize();
    });

    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        let formData = new FormData(this);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post({
            url: '{{ route('admin.users.delivery-man.active-search') }}',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                let itemCount = 0;
                if (data.dm) {
                    Object.keys(dmMarkers).forEach(id => {
                        var item = dmMarkers[id];
                        var isDMActive = data.dm.some(ddm => ddm.id == id);
                        var iconUrl = isDMActive ?
                            "{{ asset('public/assets/admin/img/delivery_boy_active.png') }}" :
                            "{{ asset('public/assets/admin/img/delivery_boy_map_inactive.png') }}";
                        item.setIcon(L.icon({
                            iconUrl: iconUrl,
                            iconSize: [32, 32],
                            iconAnchor: [16, 32],
                            popupAnchor: [0, -32]
                        }));
                        if (isDMActive) itemCount++;
                        if (isDMActive && itemCount == 1) {
                            map.setView(item.getLatLng(), 18);
                            item.openPopup();
                        }
                    });
                } else {
                    toastr.error('Delivery Man not found', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            },
        });
    });
</script>

@endpush
