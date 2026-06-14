<?php

use App\Models\User;
use App\Support\RispettoQrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates branded svg qr code for menu', function (): void {
    $svg = RispettoQrCode::generate(RispettoQrCode::TYPE_MENU);

    expect($svg)->toContain('<svg')
        ->and(RispettoQrCode::isConfigured(RispettoQrCode::TYPE_MENU))->toBeTrue();
});

test('social qr codes require configured urls', function (): void {
    config()->set('site.social', [
        ['label' => 'Instagram', 'icon' => 'instagram', 'url' => '#'],
        ['label' => 'Facebook', 'icon' => 'facebook', 'url' => '#'],
    ]);

    expect(RispettoQrCode::isConfigured(RispettoQrCode::TYPE_INSTAGRAM))->toBeFalse()
        ->and(RispettoQrCode::isConfigured(RispettoQrCode::TYPE_FACEBOOK))->toBeFalse();
});

test('authenticated admin can download menu qr svg', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('filament.admin.qr-codes.download', ['type' => RispettoQrCode::TYPE_MENU]));

    $response->assertOk()
        ->assertHeader('content-disposition');

    expect($response->streamedContent())->toContain('<svg');
});

test('guest cannot download qr codes', function (): void {
    $this->get(route('filament.admin.qr-codes.download', ['type' => RispettoQrCode::TYPE_MENU]))
        ->assertRedirect();
});
