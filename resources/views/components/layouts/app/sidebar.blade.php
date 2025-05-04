<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .nav-item-active {
            background-color: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
            border-radius: 0.375rem;
            font-weight: 600;
        }

        .nav-item-hover:hover {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 0.375rem;
            transition: background 0.2s;
        }

        .profile-initials {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            background-color: #475569;
            color: #fff;
            font-weight: bold;
        }

        .profile-details span {
            display: block;
            line-height: 1.2;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transition: transform 0.3s ease;
                z-index: 50;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <!-- Sidebar -->
    <div
        class="sidebar fixed top-0 left-0 w-64 h-full bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <!-- Toggle Button for Mobile -->
        <button class="lg:hidden p-4 text-2xl">
            <i class="fa fa-times"></i>
        </button>

        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="inline-block items-center space-x-2 p-4">
            <x-app-logo />
        </a>

        <!-- Navigation -->
        <div class="px-4 py-6">
            <ul class="space-y-2">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-item-active text-blue-600 hover:text-blue-700 block px-4 py-2 rounded-lg">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.pending') }}"
                        class="nav-item-active text-blue-600 hover:text-blue-700 block px-4 py-2 rounded-lg">
                        Enrollment List
                    </a>
                </li>
                @auth
                    @if (auth()->user()->position !== 'admin')
                        <li>
                            <a href="{{ auth()->user()->enrollment_status === 'enrolled' ? '#' : route('application.form') }}"
                                class="nav-item-active block px-4 py-2 rounded-lg 
                                  {{ auth()->user()->enrollment_status === 'enrolled' ? 'text-gray-400 cursor-not-allowed' : 'text-blue-600 hover:text-blue-700' }}"
                                {{ auth()->user()->enrollment_status === 'enrolled' ? 'aria-disabled=true' : '' }}>
                                Application Form
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
        @auth
            <div x-data="{ open: false }" class="relative text-sm font-semibold text-gray-700 dark:text-gray-300"
                style="margin-top: 100%; padding-left: 10%">
                <div class="flex items-center space-x-2 cursor-pointer" @click="open = !open" style="gap: 30%">
                    <div class="profile-initials">
                        {{ auth()->user()->initials() }}
                    </div>
                    <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{ 'rotate-180': open }"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </div>

                <!-- Dropdown Menu -->
                <div x-show="open" x-transition
                    class="absolute left-0 mt-2 w-64 bg-white dark:bg-zinc-800 shadow-lg rounded-lg z-50">
                    <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <span class="block font-semibold">{{ auth()->user()->name }}</span>
                        <span class="block text-xs text-gray-500">{{ auth()->user()->email }}</span>
                    </div>
                    <ul class="py-2">
                        <li>
                            <a href="{{ route('settings.profile') }}"
                                class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
                                Settings
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
                                    Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @endauth
    </div>

    {{ $slot }}
    </div>

    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
