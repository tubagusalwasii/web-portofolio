<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PortfolioController extends Controller
{
    public function index()
    {
        $projects = Project::with('category')->orderBy('id', 'desc')->get();
        $certificates = DB::table('certificates')->orderBy('sort_order')->get();
        $experiences = Experience::orderBy('order')->get();
        $skills = Skill::orderBy('sort_order')->get();

        // Cache settings untuk 1 jam — data ini jarang berubah
        $settings = Cache::remember('site_settings', 3600, fn () => SiteSetting::first() ?? (object)[]);

        return view('welcome', compact('projects', 'certificates', 'settings', 'experiences', 'skills'));
    }
}
