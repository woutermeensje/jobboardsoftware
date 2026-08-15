<?php

namespace App\Filament\Workspace\Pages;

use App\Models\BillingPlan;
use App\Support\AdminActionNotifier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use UnitEnum;

class Billing extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Account';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Billing';

    protected static ?string $slug = 'billing';

    protected string $view = 'filament.workspace.pages.billing';

    /**
     * @return Collection<int, BillingPlan>
     */
    public function plans(): Collection
    {
        return BillingPlan::query()
            ->where('is_active', true)
            ->orderBy('monthly_price_cents')
            ->get();
    }

    public function selectPlanAction(): Action
    {
        return Action::make('selectPlan')
            ->label('Select')
            ->requiresConfirmation()
            ->action(function (array $arguments): ?RedirectResponse {
                $plan = BillingPlan::query()->where('key', $arguments['plan'])->firstOrFail();
                $user = Filament::auth()->user();

                if ($plan->stripe_price_id && config('cashier.key') && config('cashier.secret')) {
                    app(AdminActionNotifier::class)->notify('Stripe checkout gestart', [
                        'pakket' => $plan->name,
                        'pakket_key' => $plan->key,
                        'stripe_price_id' => $plan->stripe_price_id,
                    ], $user);

                    return $user
                        ->newSubscription('default', $plan->stripe_price_id)
                        ->trialDays(14)
                        ->checkout([
                            'success_url' => route('billing.success').'?session_id={CHECKOUT_SESSION_ID}',
                            'cancel_url' => route('billing.index'),
                        ])
                        ->redirect();
                }

                $user->forceFill([
                    'billing_plan_id' => $plan->id,
                    'billing_status' => 'trial',
                    'onboarding_step' => $user->ownedTenants()->exists() ? 'jobs' : 'environment',
                ])->save();

                $user->ownedTenants()->update([
                    'plan' => $plan->key,
                    'billing_status' => 'trial',
                ]);

                app(AdminActionNotifier::class)->notify('Pakket gekozen', [
                    'pakket' => $plan->name,
                    'pakket_key' => $plan->key,
                    'bedrag_per_maand_cent' => $plan->monthly_price_cents,
                    'billing_status' => $user->billing_status,
                    'onboarding_step' => $user->onboarding_step,
                ], $user);

                Notification::make()
                    ->title('Your package has been saved.')
                    ->body('Stripe checkout will be used once price IDs and Stripe keys are configured.')
                    ->success()
                    ->send();

                return null;
            });
    }
}
