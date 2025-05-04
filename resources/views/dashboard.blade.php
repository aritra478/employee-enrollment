<x-layouts.app :title="__('Dashboard')">
    <div class="px-6 py-4" style="margin-left: 12%; bottom:30%">
        <h1 class="text-2xl font-bold mb-6">Welcome, {{ auth()->user()->FULL_name }}</h1>

        {{-- Profile Overview --}}
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {{-- User Details Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow">
                <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">Profile Details</h2>
                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <li><strong>Email:</strong> {{ auth()->user()->email }}</li>
                    <li><strong>Mobile:</strong> {{ auth()->user()->mobile ?? 'N/A' }}</li>
                    <li><strong>Gender:</strong> {{ ucfirst(auth()->user()->gender) }}</li>
                    <li><strong>Category:</strong> {{ ucfirst(auth()->user()->category) }}</li>
                    <li><strong>Position:</strong> {{ ucfirst(auth()->user()->position) }}</li>
                </ul>
            </div>

            {{-- Application Submission Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow">
                <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">Application Status</h2>
                @php
                    $approval = \App\Models\Approval::where('user_id', auth()->id())
                        ->latest()
                        ->first();
                    $payment_datails = \App\Models\PaymentDetail::where('user_id', auth()->id())
                        ->latest()
                        ->first();
                @endphp
                @if ($approval)
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <li>Status: <strong class="capitalize">{{ $approval->status }}</strong></li>
                        <li>Acknowledgement No: <strong class="font-mono">{{ auth()->user()->ack_no }}</strong>
                        </li>
                        <li>Registration No: <strong
                                class="font-mono">{{ $approval->registration_no ?? 'Pending' }}</strong></li>
                    </ul>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No application submitted yet.</p>
                @endif
            </div>

            {{-- Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow">
                <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">Quick Action</h2>
                <div class="mt-2">
                    <a href="{{ auth()->user()->enrollment_status === 'enrolled' ? '#' : route('application.form') }}"
                        class="nav-item-active block px-4 py-2 rounded-lg 
                          {{ auth()->user()->enrollment_status === 'enrolled' ? 'text-gray-400 cursor-not-allowed' : 'text-blue-600 hover:text-blue-700' }}"
                        {{ auth()->user()->enrollment_status === 'enrolled' ? 'aria-disabled=true' : '' }}>
                        {{ auth()->user()->enrollment_status === 'enrolled' ? 'Enrollment Complete' : 'Start / Continue Registration' }}
                    </a>
                    @if ($approval && $payment_datails->receipt_path)
                        <a href="{{ route('applicant.acknowledgement', auth()->user()->id) }}" target="_blank"
                            class="mt-3 d-block text-center btn btn-success fw-semibold py-2 px-4 shadow">
                            Download Acknowledgement
                        </a>
                        <img src="{{ asset('storage/' . $payment_datails->receipt_path) }}" alt="Receipt">
                    @endif
                </div>
            </div>
        </div>

        {{-- Info Banner --}}
        <div class="mt-8 p-4 bg-yellow-100 text-yellow-800 rounded-md shadow-md">
            <p class="text-sm">If you've already submitted your application, please wait for admin approval. You'll
                receive an email with your Registration Number.</p>
        </div>
    </div>
</x-layouts.app>
