@extends('monet::layouts.auth')

@section('title')
    Login
@endsection

@section('content')
    <div>
        @livewire('monet::auth.login')
    </div>
@endsection
