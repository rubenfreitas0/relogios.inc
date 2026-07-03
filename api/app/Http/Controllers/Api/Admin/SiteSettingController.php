<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * Obter as definições públicas (hero e grid hero).
     */
    public function publicIndex()
    {
        $keys = [
            'hero_subtitle',
            'hero_title',
            'hero_description',
            'hero_image',
            'hero_link',
            'hero_button_text',
            'grid_hero_title',
            'grid_hero_description',
            'grid_hero_image',
            'grid_hero_link',
        ];

        $settings = SiteSetting::whereIn('key', $keys)->get()->pluck('value', 'key');

        // Formatar as URLs das imagens para o frontend
        foreach (['hero_image', 'grid_hero_image'] as $imageKey) {
            if (isset($settings[$imageKey]) && !str_starts_with($settings[$imageKey], 'http') && !str_starts_with($settings[$imageKey], '/')) {
                $settings[$imageKey] = Storage::url($settings[$imageKey]);
            }
        }

        return response()->json($settings);
    }

    /**
     * Listar todas as definições (admin).
     */
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    /**
     * Atualizar definições em bulk (admin).
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            SiteSetting::setValue($key, $value);
        }

        return response()->json([
            'message' => 'Definições atualizadas com sucesso.',
            'settings' => SiteSetting::all()->pluck('value', 'key')
        ]);
    }

    /**
     * Upload de imagem para as definições (admin).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // Max 2MB
            'key' => 'required|string|in:hero_image,grid_hero_image',
        ]);

        $path = $request->file('image')->store('site', 'public');
        
        // Guardar o caminho relativo no site_settings
        SiteSetting::setValue($request->key, $path);

        return response()->json([
            'message' => 'Imagem carregada com sucesso.',
            'url' => Storage::url($path),
            'path' => $path
        ]);
    }
}
