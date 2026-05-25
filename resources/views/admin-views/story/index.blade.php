@extends('layouts.admin.app')
@section('title', translate('messages.Stories'))
@push('css_or_js')
@endpush
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-photo-gallery nav-icon"></i>
                </span>
                <span>{{translate('messages.Stories')}}</span>
            </h1>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">{{translate('messages.Story List')}}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>{{translate('messages.Store')}}</th>
                                <th>{{translate('messages.Title')}}</th>
                                <th>{{translate('messages.Type')}}</th>
                                <th>{{translate('messages.Duration')}}</th>
                                <th>{{translate('messages.Expires At')}}</th>
                                <th>{{translate('messages.Status')}}</th>
                                <th>{{translate('messages.Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stories as $key => $story)
                            <tr>
                                <td>{{$stories->firstItem() + $key}}</td>
                                <td>{{$story->store?->name ?? '-'}}</td>
                                <td>{{$story->title ?? '-'}}</td>
                                <td>
                                    @if($story->type == 'image')
                                        <span class="badge badge-info">{{translate('messages.Image')}}</span>
                                    @else
                                        <span class="badge badge-primary">{{translate('messages.Video')}}</span>
                                    @endif
                                </td>
                                <td>{{$story->duration}}s</td>
                                <td>{{$story->expires_at?->format('Y-m-d H:i') ?? '-'}}</td>
                                <td>
                                    <a href="{{route('admin.story.status', [$story->id, $story->status ? 0 : 1])}}" class="badge badge-{{$story->status ? 'success' : 'danger'}}">
                                        {{$story->status ? translate('messages.Active') : translate('messages.Inactive')}}
                                    </a>
                                </td>
                                <td>
                                    <a href="{{route('admin.story.delete', $story->id)}}" class="btn btn-danger btn-sm" onclick="return confirm('{{translate('messages.Are you sure?')}}')">{{translate('messages.Delete')}}</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">{{translate('messages.No stories found')}}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($stories->hasPages())
                <div class="card-footer">
                    {!! $stories->links() !!}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
