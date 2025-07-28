<?php
// App\Http\Controllers\VersionController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppVersion;

class VersionController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:android,ios',
            'current_version' => 'required|string',
        ]);

        $latest = AppVersion::where('platform', $request->platform)
            ->orderByDesc('id')->first();

        if (!$latest) {
            return response()->json(['update' => false]);
        }

        return response()->json([
            'update' => version_compare($request->current_version, $latest->version, '<'),
            'force_update' =>  (bool)$latest->force_update,
            'latest_version' => $latest->version,
            'message' => $latest->message,
        ]);
    }
}
