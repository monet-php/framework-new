@extends('monet::layouts.auth')

@section('title')
    Email verification
@endsection

@section('content')
    <div>
        @livewire('monet::auth.email-verification')
    </div>
@endsection
