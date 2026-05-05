<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortfolioController extends Controller
{
    public function index()
    {
        $projects = Project::with('category')->get();
        $certificates = DB::table('certificates')->orderBy('sort_order')->get();
        $experiences = Experience::orderBy('order')->get();
        
        // Load settings from the single record
        $settings = \App\Models\SiteSetting::first() ?? (object)[];

        return view('welcome', compact('projects', 'certificates', 'settings', 'experiences'));
    }
}
