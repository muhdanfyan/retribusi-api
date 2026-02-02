<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | SIPANDA Docs</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .prose img { border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .mermaid { margin-bottom: 2rem; background: #f8fafc; padding: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #074764; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-[#074764] text-white sticky top-0 z-50 shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <span class="font-bold text-xl tracking-tight">SIPANDA System Docs</span>
                    </div>
                    <nav class="flex items-center gap-6 text-sm font-medium">
                        <a href="{{ url('/docs/README') }}" class="{{ $current_page === 'README' ? 'text-white underline underline-offset-8' : 'text-slate-300 hover:text-white' }} transition-colors">Utama</a>
                        <a href="{{ url('/docs/system_documentation') }}" class="{{ $current_page === 'system_documentation' ? 'text-white underline underline-offset-8' : 'text-slate-300 hover:text-white' }} transition-colors">Arsitektur</a>
                        <a href="{{ url('/docs/retribusi_system_diagrams') }}" class="{{ $current_page === 'retribusi_system_diagrams' ? 'text-white underline underline-offset-8' : 'text-slate-300 hover:text-white' }} transition-colors">Diagram</a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 overflow-hidden">
                <article id="markdown-content" class="prose prose-slate prose-lg max-w-none prose-headings:text-[#074764] prose-a:text-[#074764] prose-strong:text-slate-900">
                    <!-- Markdown content will be rendered here -->
                    <div class="flex items-center justify-center py-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-[#074764]"></div>
                    </div>
                </article>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-6">
            <div class="max-w-7xl mx-auto px-4 text-center text-slate-500 text-sm">
                &copy; {{ date('Y') }} SIPANDA Retribusi System. Seluruh hak cipta dilindungi.
            </div>
        </footer>
    </div>

    <script id="raw-markdown" type="text/markdown">{!! $content !!}</script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rawContent = document.getElementById('raw-markdown').textContent;
            const article = document.getElementById('markdown-content');
            
            try {
                // Configure marked to handle relative image paths
                const renderer = new marked.Renderer();
                const originalImage = renderer.image.bind(renderer);
                
                renderer.image = function(hrefOrObj, title, text) {
                    let href = typeof hrefOrObj === 'object' ? hrefOrObj.href : hrefOrObj;
                    let imgTitle = typeof hrefOrObj === 'object' ? hrefOrObj.title : title;
                    let imgText = typeof hrefOrObj === 'object' ? hrefOrObj.text : text;

                    // If it's a relative path in our docs folder, route it through our asset controller
                    if (href && !href.startsWith('http') && !href.startsWith('/') && !href.startsWith('file')) {
                        href = "{{ url('/docs/assets') }}/" + href;
                    }
                    
                    return `<img src="${href}" alt="${imgText || ''}" title="${imgTitle || ''}">`;
                };

                marked.use({ renderer });
                const htmlContent = marked.parse(rawContent);
                
                article.innerHTML = htmlContent;

                // Initialize Mermaid
                mermaid.initialize({ 
                    startOnLoad: true,
                    theme: 'neutral',
                    securityLevel: 'loose'
                });

                // Re-render Mermaid diagrams if any were parsed from markdown
                const mermaidBlocks = article.querySelectorAll('.language-mermaid');
                mermaidBlocks.forEach((block, i) => {
                    const content = block.textContent;
                    const container = document.createElement('div');
                    container.className = 'mermaid';
                    container.innerHTML = content;
                    block.parentNode.replaceChild(container, block);
                });
                
                // Explicitly run mermaid to ensure rendering
                if (mermaidBlocks.length > 0) {
                    mermaid.init(undefined, article.querySelectorAll('.mermaid'));
                }

            } catch (error) {
                console.error('Failed to render documentation:', error);
                article.innerHTML = `
                    <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl">
                        <h3 class="font-bold text-lg mb-2">Gagal memuat dokumentasi</h3>
                        <p class="text-sm">Terjadi kesalahan saat memproses konten dokumentasi. Silakan periksa format file markdown.</p>
                        <pre class="mt-4 p-3 bg-red-100/50 rounded text-xs overflow-auto">${error.message}</pre>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
