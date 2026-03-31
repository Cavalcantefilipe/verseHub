<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    /**
     * Retorna todas as configurações (backoffice)
     */
    public function index()
    {
        $settings = SiteSetting::all()->pluck('value', 'key');

        return response()->json([
            'data' => $settings,
            'message' => 'Settings retrieved successfully',
        ]);
    }

    /**
     * Atualiza as configurações (backoffice)
     */
    public function update(Request $request)
    {
        $request->validate([
            'gtm_id' => 'nullable|string|max:50',
            'head_scripts' => 'nullable|string|max:10000',
            'body_scripts' => 'nullable|string|max:10000',
        ]);

        if ($request->has('gtm_id')) {
            SiteSetting::set('gtm_id', $request->input('gtm_id'));
        }

        if ($request->has('head_scripts')) {
            SiteSetting::set('head_scripts', $request->input('head_scripts'));
        }

        if ($request->has('body_scripts')) {
            SiteSetting::set('body_scripts', $request->input('body_scripts'));
        }

        return response()->json([
            'data' => SiteSetting::all()->pluck('value', 'key'),
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Endpoint público - retorna scripts para o frontend injetar
     */
    public function scripts()
    {
        $gtmId = SiteSetting::get('gtm_id');
        $headScripts = SiteSetting::get('head_scripts');
        $bodyScripts = SiteSetting::get('body_scripts');

        $scripts = [];

        // GTM Head Script
        if ($gtmId) {
            $scripts['gtm_head'] = "<!-- Google Tag Manager -->\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\nnew Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\nj=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n})(window,document,'script','dataLayer','{$gtmId}');</script>\n<!-- End Google Tag Manager -->";

            $scripts['gtm_body'] = "<!-- Google Tag Manager (noscript) -->\n<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtmId}\"\nheight=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n<!-- End Google Tag Manager (noscript) -->";
        }

        if ($headScripts) {
            $scripts['head_scripts'] = $headScripts;
        }

        if ($bodyScripts) {
            $scripts['body_scripts'] = $bodyScripts;
        }

        return response()->json([
            'data' => $scripts,
            'message' => 'Scripts retrieved successfully',
        ]);
    }
}
