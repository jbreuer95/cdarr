<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RadarSettingsController extends Controller
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
        $request->validate([
            'token' => ['required', 'max:50'],
            'url' => ['required', 'max:50'],
        ]);

        throw ValidationException::withMessages(['test']);

        return redirect()->back();
    }
}
