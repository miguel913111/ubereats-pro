@extends('layouts.admin.app')
@section('title', translate('messages.Document Verifications'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{asset('public/assets/admin/img/document.png')}}" class="w--26" alt="">
                </span>
                <span>{{translate('messages.Document Verifications')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Document List')}}</h5>
                <div>
                    <a href="{{route('admin.document-verification.list', ['status' => 'pending'])}}" class="btn btn-warning btn-sm">{{translate('messages.Pending')}}</a>
                    <a href="{{route('admin.document-verification.list', ['status' => 'approved'])}}" class="btn btn-success btn-sm">{{translate('messages.Approved')}}</a>
                    <a href="{{route('admin.document-verification.list', ['status' => 'rejected'])}}" class="btn btn-danger btn-sm">{{translate('messages.Rejected')}}</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{translate('messages.ID')}}</th>
                            <th>{{translate('messages.Type')}}</th>
                            <th>{{translate('messages.Document Type')}}</th>
                            <th>{{translate('messages.Document Number')}}</th>
                            <th>{{translate('messages.Status')}}</th>
                            <th>{{translate('messages.Submitted At')}}</th>
                            <th>{{translate('messages.Action')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                        <tr>
                            <td>{{$document->id}}</td>
                            <td>{{class_basename($document->verifiable_type)}} #{{$document->verifiable_id}}</td>
                            <td>{{$document->document_type}}</td>
                            <td>{{$document->document_number}}</td>
                            <td>
                                <span class="badge badge-{{$document->status == 'approved' ? 'success' : ($document->status == 'rejected' ? 'danger' : 'warning')}}">
                                    {{translate('messages.' . ucfirst($document->status))}}
                                </span>
                            </td>
                            <td>{{$document->created_at}}</td>
                            <td>
                                <a href="{{route('admin.document-verification.view', $document->id)}}" class="btn btn-info btn-sm">{{translate('messages.View')}}</a>
                                @if($document->status == 'pending')
                                <form action="{{route('admin.document-verification.approve', $document->id)}}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">{{translate('messages.Approve')}}</button>
                                </form>
                                <form action="{{route('admin.document-verification.reject', $document->id)}}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">{{translate('messages.Reject')}}</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $documents->links() !!}
            </div>
        </div>
    </div>
@endsection
