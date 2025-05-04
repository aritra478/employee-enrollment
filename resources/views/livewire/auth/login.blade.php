<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Tymon\JWTAuth\Facades\JWTAuth;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
    public string $token = '';

    public function login(): void
    {
        $this->validate();
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $token = JWTAuth::fromUser(Auth::user());
        $this->token = $token;
        session(['jwt_token' => $token]);

        $this->dispatch('jwt-generated', token: $token);
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
};
?>
<div class="flex flex-col gap-6" x-data @jwt-generated.window="localStorage.setItem('token', $event.detail.token)">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <x-form.field>
            <div class="flex items-center gap-1">
                <x-form.label name="email" label="Email Address" />
                <span class="font-bold text-red-700">*</span>
            </div>
            <input type="email" wire:model="email" name="email" id="email" required autocomplete="email"
                placeholder="email@example.com"
                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <x-form.error name="email" />
        </x-form.field>
        <x-form.field>
            <div x-data="{ show: true }">
                <div class="flex items-center gap-1">
                    <x-form.label name="password" label="Password" />
                    <span class="font-bold text-red-700">*</span>
                </div>
                <div class="relative">
                    <input :type="show ? 'password' : 'text'" wire:model="password" placeholder="{{ __('Password') }}"
                        name="password" id="password" required autocomplete="off"
                        class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:outline-none focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <div class="absolute inset-y-0 inline-block items-center pr-3 text-sm leading-5"
                        style="left: 91%; top: 25%;">
                        <svg class="h-5 text-gray-700 cursor-pointer" fill="none" @click="show = !show"
                            :class="{ 'hidden': !show, 'block': show }" xmlns="http://www.w3.org/2000/svg"
                            viewbox="0 0 576 512">
                            <path fill="currentColor"
                                d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z">
                            </path>
                        </svg>

                        <svg class="h-5 text-gray-700 cursor-pointer" fill="none" @click="show = !show"
                            :class="{ 'block': !show, 'hidden': show }" xmlns="http://www.w3.org/2000/svg"
                            viewbox="0 0 640 512">
                            <path fill="currentColor"
                                d="M320 400c-75.85 0-137.25-58.71-142.9-133.11L72.2 185.82c-13.79 17.3-26.48 35.59-36.72 55.59a32.35 32.35 0 0 0 0 29.19C89.71 376.41 197.07 448 320 448c26.91 0 52.87-4 77.89-10.46L346 397.39a144.13 144.13 0 0 1-26 2.61zm313.82 58.1l-110.55-85.44a331.25 331.25 0 0 0 81.25-102.07 32.35 32.35 0 0 0 0-29.19C550.29 135.59 442.93 64 320 64a308.15 308.15 0 0 0-147.32 37.7L45.46 3.37A16 16 0 0 0 23 6.18L3.37 31.45A16 16 0 0 0 6.18 53.9l588.36 454.73a16 16 0 0 0 22.46-2.81l19.64-25.27a16 16 0 0 0-2.82-22.45zm-183.72-142l-39.3-30.38A94.75 94.75 0 0 0 416 256a94.76 94.76 0 0 0-121.31-92.21A47.65 47.65 0 0 1 304 192a46.64 46.64 0 0 1-1.54 10l-73.61-56.89A142.31 142.31 0 0 1 320 112a143.92 143.92 0 0 1 144 144c0 21.63-5.29 41.79-13.9 60.11z">
                            </path>
                        </svg>
                    </div>
                </div>
                <x-form.error name="password_confirmation" />
            </div>
        </x-form.field>
        <x-password-policy />
        @if (Route::has('password.request'))
            <div class="text-sm text-right" style="margin-left: 60%">
                <flux:link :href="route('password.request')" wire:navigate class="text-indigo-600 hover:underline">
                    {{ __('Forgot your password?') }}
                </flux:link>
            </div>
        @endif
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Don\'t have an account?') }}
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>
