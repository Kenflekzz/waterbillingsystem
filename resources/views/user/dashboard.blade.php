@extends('layouts.user')

@section('content')
    <h1>Welcome, {{ $user->first_name }} {{ $user->last_name }}</h1>
    <p>Meter Number: {{ $user->meter_number }}</p>
    <p>Email: {{ $user->email }}</p>
@endsection
