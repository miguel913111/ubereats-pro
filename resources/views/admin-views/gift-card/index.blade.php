@extends('layouts.admin.app')
@section('title', translate('messages.Gift Cards'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('assets/admin/img/gift.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Gift Cards')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Gift Card List')}}</h5>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addGiftCardModal">{{translate('messages.Add Gift Card')}}</button>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.Title')}}</th>
                            <th>{{translate('messages.Code')}}</th>
                            <th>{{translate('messages.Amount')}}</th>
                            <th>{{translate('messages.Start Date')}}</th>
                            <th>{{translate('messages.Expire Date')}}</th>
                            <th>{{translate('messages.Status')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($giftCards as $giftCard)
                        <tr>
                            <td>{{$giftCard->title}}</td>
                            <td><code>{{$giftCard->code}}</code></td>
                            <td>{{\App\CentralLogics\Helpers::format_currency($giftCard->amount)}}</td>
                            <td>{{$giftCard->start_date}}</td>
                            <td>{{$giftCard->expire_date}}</td>
                            <td>
                                <a href="{{route('admin.gift-card.status', [$giftCard->id, $giftCard->status ? 0 : 1])}}" class="btn btn-{{$giftCard->status ? 'success' : 'danger'}} btn-sm">
                                    {{$giftCard->status ? translate('messages.Active') : translate('messages.Inactive')}}
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editGiftCardModal{{$giftCard->id}}">{{translate('messages.Edit')}}</button>
                                <a href="{{route('admin.gift-card.delete', $giftCard->id)}}" class="btn btn-danger btn-sm" onclick="return confirm('{{translate('messages.Are you sure?')}}')">{{translate('messages.Delete')}}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $giftCards->links() !!}
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addGiftCardModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.gift-card.store')}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('messages.Add Gift Card')}}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{translate('messages.Title')}}</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Description')}}</label>
                            <textarea name="description" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Code')}}</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Amount')}}</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Min Purchase')}}</label>
                            <input type="number" step="0.01" name="min_purchase" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Max Discount')}}</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control" value="0">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Start Date')}}</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Expire Date')}}</label>
                            <input type="date" name="expire_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Usage Limit')}}</label>
                            <input type="number" name="limit" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Status')}}</label>
                            <select name="status" class="form-control">
                                <option value="1">{{translate('messages.Active')}}</option>
                                <option value="0">{{translate('messages.Inactive')}}</option>
                            </select>
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

    @foreach($giftCards as $giftCard)
    <!-- Edit Modal -->
    <div class="modal fade" id="editGiftCardModal{{$giftCard->id}}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('admin.gift-card.update', $giftCard->id)}}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{translate('messages.Edit Gift Card')}}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{translate('messages.Title')}}</label>
                            <input type="text" name="title" class="form-control" value="{{$giftCard->title}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Description')}}</label>
                            <textarea name="description" class="form-control">{{$giftCard->description}}</textarea>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Code')}}</label>
                            <input type="text" name="code" class="form-control" value="{{$giftCard->code}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Amount')}}</label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{$giftCard->amount}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Min Purchase')}}</label>
                            <input type="number" step="0.01" name="min_purchase" class="form-control" value="{{$giftCard->min_purchase}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Max Discount')}}</label>
                            <input type="number" step="0.01" name="max_discount" class="form-control" value="{{$giftCard->max_discount}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Start Date')}}</label>
                            <input type="date" name="start_date" class="form-control" value="{{$giftCard->start_date}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Expire Date')}}</label>
                            <input type="date" name="expire_date" class="form-control" value="{{$giftCard->expire_date}}" required>
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Usage Limit')}}</label>
                            <input type="number" name="limit" class="form-control" value="{{$giftCard->limit}}">
                        </div>
                        <div class="form-group">
                            <label>{{translate('messages.Status')}}</label>
                            <select name="status" class="form-control">
                                <option value="1" {{$giftCard->status ? 'selected' : ''}}>{{translate('messages.Active')}}</option>
                                <option value="0" {{!$giftCard->status ? 'selected' : ''}}>{{translate('messages.Inactive')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('messages.Close')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('messages.Update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
