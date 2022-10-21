@extends('monet::layouts.auth')

@section('title')
    Reset password
@endsection

@section('content')
    <div>
        @livewire('monet::auth.password-reset')
    </div>
@endsection
