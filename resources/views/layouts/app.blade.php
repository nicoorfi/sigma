@extends('layout')

@section('content')
<div class="h-screen flex overflow-hidden bg-gray-100">

    <sidebar ref="sidebar"> </sidebar>

    <div class="flex flex-col w-0 flex-1 overflow-hidden">

        <spinner ref="spinner"></spinner>

        <navbar v-cloak avatar-url="{{ $user->avatar_url }}"></navbar>

        <main id="main" class="flex-1 relative z-0 overflow-y-auto py-6 focus:outline-none" tabindex="0">
            <bar ref="bar"></bar>

            {{-- <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                <h1 class="text-2xl font-semibold text-gray-900">Lorem ipsum</h1>
                <button @click="animate(1)">Foo animate</button>
            </div> --}}

            <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

                <router-view>
                </router-view>

                @yield('app.content')
            </div>
        </main>
    </div>
</div>

@include('common.logout')

@endsection
