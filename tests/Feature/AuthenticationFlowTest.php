<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_unverified_parent_and_sends_verification_email(): void
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
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'parent@example.test')->firstOrFail();

        $this->assertSame('parent', $user->user_type);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertTrue($user->hasRole('parent'));
        Notification::assertSentTo($user, VerifyEmail::class);
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
