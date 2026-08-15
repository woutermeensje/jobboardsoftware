<x-filament-panels::page>
    @php
        $user = filament()->auth()->user();
        $plan = $user?->billingPlan;
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Current plan
        </x-slot>

        <div class="grid gap-6 md:grid-cols-3">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                <p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                    {{ $plan?->name ?? ucfirst((string) ($user?->billing_status ?? 'Trial')) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Billing status</p>
                <p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                    {{ ucfirst((string) ($user?->billing_status ?? 'trial')) }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Monthly price</p>
                <p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                    {{ $plan?->formattedMonthlyPrice() ?? 'Not configured' }}
                </p>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Included
        </x-slot>

        @if ($plan)
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-sm font-medium text-gray-950 dark:text-white">Features</p>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        @forelse (($plan->features ?? []) as $feature)
                            <li>{{ $feature }}</li>
                        @empty
                            <li>No features configured yet.</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-950 dark:text-white">Limits</p>
                    <dl class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                        @forelse (($plan->limits ?? []) as $label => $value)
                            <div class="flex justify-between gap-4">
                                <dt>{{ str($label)->headline() }}</dt>
                                <dd class="font-medium text-gray-950 dark:text-white">{{ $value }}</dd>
                            </div>
                        @empty
                            <div>No limits configured yet.</div>
                        @endforelse
                    </dl>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-300">
                No billing plan is attached to this account yet.
            </p>
        @endif
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">
            Available plans
        </x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($this->plans() as $availablePlan)
                @php
                    $isCurrentPlan = $plan?->is($availablePlan) ?? false;
                @endphp

                <div class="flex flex-col justify-between gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div>
                        <p class="text-base font-semibold text-gray-950 dark:text-white">
                            {{ $availablePlan->name }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $availablePlan->formattedMonthlyPrice() }}
                        </p>
                        @if ($availablePlan->description)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                {{ $availablePlan->description }}
                            </p>
                        @endif
                    </div>

                    @if ($isCurrentPlan)
                        <x-filament::badge color="success">
                            Current plan
                        </x-filament::badge>
                    @else
                        <x-filament::button
                            wire:click="mountAction('selectPlan', { plan: '{{ $availablePlan->key }}' })"
                            color="primary"
                        >
                            Select
                        </x-filament::button>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>
