<?php

use Illuminate\Support\Facades\Storage;
use Monet\Framework\Setting\Drivers\SettingsFileDriver;
use Monet\Framework\Setting\Facades\Settings;

it('can load from an existing file', function () {
    Storage::disk('local')->put('settings.json', json_encode(['::test-key::' => '::test-value::']));

    expect(Settings::get('::test-key::'))->toEqual('::test-value::');
});

it('returns the default value if the key does not exist', function () {
    expect(Settings::get('::new-test-key::', '::default-value::'))->toEqual('::default-value::');
});

it('can read dot notation keys', function () {
    Storage::disk('local')->put('settings.json', json_encode([
        '::test-key::' => [
            '::next-test-key::' => '::child-test-value::',
        ],
    ]));

    expect(Settings::get('::test-key::.::next-test-key::'))->toEqual('::child-test-value::');
});

it('deletes the data after pulling', function () {
    Settings::put('::test-key::', '::test-value::');

    expect(Settings::pull('::test-key::'))->toEqual('::test-value::');
    expect(Settings::get('::test-key::'))->toBeNull();
});

it('saves the settings file after a request', function () {
    $this->app->instance(
        SettingsFileDriver::class,
        Mockery::mock(SettingsFileDriver::class, [
            $this->app['filesystem'],
            'local',
            'settings.json',
        ], function (Mockery\MockInterface $mock) {
            $mock->makePartial()->shouldReceive('save')->once()->passthru();
        })
    );

    Settings::put('::test-key::', '::test-value::');

    $this->get('/');

    expect(
        json_decode(Storage::disk('local')->get('settings.json'), true, 512, JSON_THROW_ON_ERROR)
    )->toEqual(['::test-key::' => '::test-value::']);
});

it('does not save settings that have been pulled or deleted', function () {
    $this->app->instance(
        SettingsFileDriver::class,
        Mockery::mock(SettingsFileDriver::class, [
            $this->app['filesystem'],
            'local',
            'settings.json',
        ], function (Mockery\MockInterface $mock) {
            $mock->makePartial()->shouldReceive('save')->once()->passthru();
        })
    );

    Storage::disk('local')->put('settings.json', json_encode([
        '::test-key::' => '::test-value::',
        '::second-test-key::' => '::second-test-value::',
        '::third-test-key::' => '::third-test-value::',
    ]));

    expect(Settings::pull('::test-key::'))->toEqual('::test-value::');
    expect(Settings::get('::test-key::'))->toBeNull();

    expect(Settings::get('::second-test-key::'))->toEqual('::second-test-value::');

    Settings::forget('::second-test-key::');

    expect(
        json_decode(Storage::disk('local')->get('settings.json'), true, 512, JSON_THROW_ON_ERROR)
    )->toEqual([
        '::test-key::' => '::test-value::',
        '::second-test-key::' => '::second-test-value::',
        '::third-test-key::' => '::third-test-value::',
    ]);

    $this->get('/');

    expect(
        json_decode(Storage::disk('local')->get('settings.json'), true, 512, JSON_THROW_ON_ERROR)
    )->toEqual(['::third-test-key::' => '::third-test-value::']);
});
