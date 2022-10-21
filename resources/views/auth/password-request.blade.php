@extends('monet::layouts.auth')

@section('title')
    Forgot password
@endsection

@section('content')
    <div>
        @livewire('monet::auth.password-request')
    </div>
@endsection
