@extends('monet::layouts.auth')

@section('title')
    Password confirmation
@endsection

@section('content')
    <div>
        @livewire('monet::auth.password-confirmation')
    </div>
@endsection
