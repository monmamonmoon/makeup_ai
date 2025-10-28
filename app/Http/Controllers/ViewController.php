<?php

namespace App\Http\Controllers; // Make sure namespace is correct

// Remove Request import if not used
// use Illuminate\Http\Request;
use Illuminate\View\View; // Import the View class

class ViewController extends Controller
{
    // This function tells Laravel to load the 'welcome.blade.php' file
    // from the 'pages' folder inside 'resources/views'
    public function welcome(): View
    {
        return view('pages.welcome');
    }

    // Add placeholder functions for other pages
    public function analysis(): View
    {
        return view('pages.analysis');
    }

    public function savedLooks(): View
    {
        return view('pages.saved-looks');
    }

    public function tutorials(): View
    {
        return view('pages.tutorials');
    }

    // We don't need a login() method here anymore
    // public function login(): View { return view('pages.login'); }
}