@php use Illuminate\Support\Facades\Route; @endphp
@if(Route::has('register'))
    <div class="w-full flex items-center justify-center text-sm font-medium">
        <span>
            Don't have an account? <a
                href="{{route('register')}}"
                class="text-primary-600 transition-colors hover:text-primary-500 focus:text-primary-700"
            >
                Register here
            </a>
        </span>
    </div>
@endif
