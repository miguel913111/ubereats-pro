@extends('layouts.blank')

@section('title', "Nexo Food Software Activation")

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="card mt-3">
                <div class="card-body">
                    <div class="mar-ver pad-btm text-center mb-4">
                        <h1 class="h3">{{ "Nexo Food Software Activation" }}</h1>
                        <p class="text-muted">Your software is ready to use.</p>
                    </div>

                    <form method="POST" action="{{ route('system.activation-check') }}">
                        @csrf
                        <div class="text-center">
                            <button type="submit" class="btn btn-dark px-sm-5">{{ "Continue" }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
