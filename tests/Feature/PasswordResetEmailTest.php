<?php

use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['mail.password_reset_mailer' => 'smtp', 'mail.from.address' => 'farm@example.org']);
});

it('emails a usable code and completes password recovery', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'customer@example.org', 'status' => 'active']);
    $this->from(route('password.forgot'))->post(route('password.send'), ['email' => $user->email])
        ->assertSessionHasNoErrors()->assertSessionHas('ok');
    $code = null;
    Mail::assertSent(PasswordResetCodeMail::class, function ($mail) use ($user, &$code) {
        $code = $mail->code;
        return $mail->hasTo($user->email) && preg_match('/^[0-9]{6}$/', $code);
    });
    expect(Hash::check($code, PasswordOtp::first()->code))->toBeTrue();
    $this->post(route('password.verify'), ['email' => $user->email, 'otp' => $code])->assertRedirect(route('password.reset'));
    $this->post(route('password.update'), ['password' => 'a-new-password-123', 'password_confirmation' => 'a-new-password-123'])
        ->assertRedirect(route('home'));
    expect(Hash::check('a-new-password-123', $user->fresh()->password))->toBeTrue();
});

it('reports mail delivery errors and keeps an earlier valid code', function () {
    $user = User::factory()->create(['email' => 'customer@example.org', 'status' => 'active']);
    $prior = PasswordOtp::create(['email' => $user->email, 'code' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);
    $sender = Mockery::mock();
    $sender->shouldReceive('to')->with($user->email)->andReturnSelf();
    $sender->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP unavailable'));
    Mail::shouldReceive('mailer')->with('smtp')->andReturn($sender);
    $this->from(route('password.forgot'))->post(route('password.send'), ['email' => $user->email])
        ->assertSessionHasErrors('email')->assertSessionMissing('ok');
    expect(PasswordOtp::count())->toBe(1)->and(PasswordOtp::first()->id)->toBe($prior->id);
});

it('does not claim delivery when email is configured to log only', function () {
    config(['mail.password_reset_mailer' => 'log']);
    Mail::fake();
    $user = User::factory()->create(['status' => 'active']);
    $this->from(route('password.forgot'))->post(route('password.send'), ['email' => $user->email])
        ->assertSessionHasErrors('email')->assertSessionMissing('ok');
    Mail::assertNothingSent();
    expect(PasswordOtp::count())->toBe(0);
});

it('does not send a code for an unknown or inactive account', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'inactive@example.org', 'status' => 'inactive']);
    foreach ([$user->email, 'missing@example.org'] as $email) {
        $this->post(route('password.send'), ['email' => $email])->assertSessionHasNoErrors()->assertSessionHas('ok');
    }
    Mail::assertNothingSent();
    expect(PasswordOtp::count())->toBe(0);
});
