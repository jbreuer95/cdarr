<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SettingsRadarrController extends Controller
{
    public function index()
    {
        return Inertia::render('SettingsRadarrPage', [
            'settings' => config('radarr'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
        ]);

        $setting = Setting::where('key', 'radarr')->first();

        $value = $setting->value;
        $value['url'] = str($validated['url'])->rtrim('/');
        $value['token'] = $validated['token'];
        $setting->value = $value;

        $setting->save();

        return redirect()->back();
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'max:50'],
            'url' => ['required', 'max:50'],
        ]);

        $ping_url = str($validated['url'])->rtrim('/') . '/ping';
        $api_url = str($validated['url'])->rtrim('/') . '/api';

        $connected = false;
        $authenticated = false;
        try {
            $response = Http::connectTimeout(3)->timeout(3)->get($ping_url);
            if ($response->ok() && !empty($response->object()->status) && $response->object()->status === 'OK') {
                $connected = true;
            }
        } catch (ConnectionException $e) {}

        if (!$connected) {
            throw ValidationException::withMessages([
                'url' => 'Cannot connect to this url, are you sure this docker instance can connect to this url?'
            ]);
        }

        try {
            $response = Http::timeout(3)->withHeaders([
                'X-Api-Key' => $validated['token']
            ])->get($api_url);
            if ($response->ok() && !empty($response->object()->current) && $response->object()->current === 'v3') {
                $authenticated = true;
            }
        } catch (ConnectionException $e) {}

        if (!$authenticated) {
            throw ValidationException::withMessages([
                'token' => 'Invalid token, double check?'
            ]);
        }

        // $setting = Setting::where('key', 'radarr')->first();

        // $value = $setting->value;
        // $value['url'] = str($validated['url'])->rtrim('/');
        // $value['token'] = $validated['token'];
        // $setting->value = $value;

        // $setting->save();

        return redirect()->back();
    }
}
