<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class DocumentationController extends Controller
{
    /**
     * Display the documentation index.
     */
    public function index()
    {
        return $this->show('README');
    }

    /**
     * Display a specific documentation page.
     */
    public function show($page)
    {
        // Remove .md if present in the page parameter to avoid duplication
        $page = str_replace('.md', '', $page);
        
        $path = base_path("docs/sipanda-docs/{$page}.md");

        if (!File::exists($path)) {
            abort(404);
        }

        $content = File::get($path);
        
        // Return a view that renders the markdown client-side
        return view('docs.viewer', [
            'content' => $content,
            'title' => ucfirst(str_replace(['_', '-'], ' ', $page)),
            'current_page' => $page
        ]);
    }

    /**
     * Serve documentation assets (images).
     */
    public function asset($filename)
    {
        $path = base_path("docs/sipanda-docs/{$filename}");

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return response($file, 200)->header("Content-Type", $type);
    }
}
