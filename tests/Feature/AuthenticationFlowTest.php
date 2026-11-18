<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Livewire\User\ProfileEdit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_active_parent_without_an_email_verification_step(): void
    {
        Notification::fake();
        Role::findOrCreate('parent', 'web');

        Livewire::test(Register::class)
            ->set('name', 'Test Parent')
            ->set('email', 'parent@example.test')
            ->set('password', 'a-secure-password')
            ->set('password_confirmation', 'a-secure-password')
            ->set('account_type', 'parent')
            ->call('register')
            ->assertRedirect(route('user.dashboard'));

        $user = User::where('email', 'parent@example.test')->firstOrFail();

        $this->assertSame('parent', $user->user_type);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('parent'));
        Notification::assertNothingSent();
    }

    public function test_profile_email_can_be_changed_without_a_verification_prompt_or_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'old-address@example.test',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(ProfileEdit::class)
            ->set('name', $user->name)
            ->set('email', 'new-address@example.test')
            ->set('phone', '')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Your profile has been updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'new-address@example.test',
            'email_verified_at' => null,
        ]);

        Notification::assertNothingSent();
    }

    public function test_registration_cannot_assign_the_admin_account_type(): void
    {
        Role::findOrCreate('parent', 'web');

        Livewire::test(Register::class)
            ->set('name', 'Untrusted Admin')
            ->set('email', 'untrusted-admin@example.test')
            ->set('password', 'a-secure-password')
            ->set('password_confirmation', 'a-secure-password')
            ->set('account_type', 'admin')
            ->call('register')
            ->assertHasErrors(['account_type' => 'in']);

        $this->assertDatabaseMissing('users', [
            'email' => 'untrusted-admin@example.test',
        ]);
    }
}
