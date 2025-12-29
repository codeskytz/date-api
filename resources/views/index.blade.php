<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>date-api — Documentation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Base Styles */
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #7c3aed;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --border-radius: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border-left: 5px solid var(--primary);
        }

        .header h1 {
            color: var(--dark);
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 i {
            color: var(--primary);
            font-size: 2.2rem;
        }

        .header p {
            color: var(--gray-600);
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        .api-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .info-item i {
            color: var(--primary);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .stat-card h3 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .stat-card p {
            color: var(--gray-600);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Endpoint Cards */
        .endpoint-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
            margin-bottom: 40px;
        }

        .endpoint-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .endpoint-card:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--gray-200);
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-100);
        }

        .method-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .method-get { background: #dbeafe; color: var(--primary); }
        .method-post { background: #dcfce7; color: var(--success); }
        .method-put { background: #fef3c7; color: var(--warning); }
        .method-patch { background: #fef3c7; color: var(--warning); }
        .method-delete { background: #fee2e2; color: var(--danger); }

        .endpoint-path {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 1.1rem;
            color: var(--dark);
            background: var(--gray-100);
            padding: 8px 15px;
            border-radius: var(--border-radius);
            flex-grow: 1;
        }

        .endpoint-description {
            color: var(--gray-600);
            margin-bottom: 25px;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray-700);
            margin: 25px 0 15px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .section-title i {
            color: var(--primary);
        }

        /* Code Blocks */
        pre {
            background: var(--gray-800);
            color: #e2e8f0;
            padding: 20px;
            border-radius: var(--border-radius);
            overflow-x: auto;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            position: relative;
        }

        pre::before {
            content: attr(data-language);
            position: absolute;
            top: 0;
            right: 0;
            background: var(--gray-700);
            color: var(--gray-300);
            padding: 5px 15px;
            font-size: 0.8rem;
            border-bottom-left-radius: var(--border-radius);
            border-top-right-radius: var(--border-radius);
        }

        /* JSON Syntax Highlighting */
        .json-key { color: #7dd3fc; }
        .json-string { color: #86efac; }
        .json-number { color: #fbbf24; }
        .json-boolean { color: #f472b6; }
        .json-null { color: #c084fc; }

        /* No Data State */
        .no-data {
            background: white;
            border-radius: var(--border-radius);
            padding: 60px 40px;
            text-align: center;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .no-data i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 20px;
        }

        .no-data h3 {
            color: var(--gray-600);
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        .no-data p {
            color: var(--gray-500);
            max-width: 600px;
            margin: 0 auto 25px;
        }

        /* Code Instruction */
        .code-instruction {
            background: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid var(--success);
        }

        .code-instruction h4 {
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .code-instruction code {
            background: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-family: monospace;
            color: var(--danger);
            font-size: 0.9rem;
            border: 1px solid var(--gray-200);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .endpoint-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .endpoint-path {
                width: 100%;
                overflow-x: auto;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .endpoint-card {
                padding: 20px;
            }
        }

        /* Utility Classes */
        .text-muted {
            color: var(--gray-600);
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background: #dcfce7;
            color: var(--success);
        }

        .badge-warning {
            background: #fef3c7;
            color: var(--warning);
        }

        .badge-info {
            background: #dbeafe;
            color: var(--primary);
        }

        /* Loading Animation */
        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 30px;
            color: var(--gray-600);
            font-size: 0.9rem;
            border-top: 1px solid var(--gray-200);
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1><i class="fas fa-book"></i> date-api — API Documentation</h1>
            <p>Complete API reference with endpoint samples, request/response examples, and implementation status.</p>
            
            <div class="api-info">
                <div class="info-item">
                    <i class="fas fa-server"></i>
                    <span>Base URL: <code>https://localhost:8000/v1</code></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-code"></i>
                    <span>Format: JSON</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-folder-open"></i>
                    <span>Docs Path: <code>/docs/api/</code></span>
                </div>
            </div>
        </header>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-plug"></i>
                <h3 id="total-endpoints">0</h3>
                <p>Total Endpoints</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3 id="implemented-endpoints">0</h3>
                <p>Implemented</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-file-code"></i>
                <h3 id="sample-files">0</h3>
                <p>Sample Files</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-bolt"></i>
                <h3>v1</h3>
                <p>API Version</p>
            </div>
        </div>

        <!-- Main Content -->
        <main>
            <h2 style="color: var(--dark); margin-bottom: 25px; font-size: 1.8rem;">
                <i class="fas fa-code-branch"></i> API Endpoints
            </h2>
            
            @php
                $samples = [];
                foreach (glob(base_path('docs/api/*.json')) as $f) {
                    $content = json_decode(file_get_contents($f), true);
                    if ($content && isset($content['endpoint'])) {
                        $samples[] = $content;
                    }
                }
                
                // Sort by endpoint name
                usort($samples, function($a, $b) {
                    return strcmp($a['endpoint'] ?? '', $b['endpoint'] ?? '');
                });
            @endphp

            @if(count($samples) === 0)
                <!-- No Data State -->
                <div class="no-data">
                    <i class="fas fa-file-alt"></i>
                    <h3>No endpoint samples found</h3>
                    <p>Place JSON sample files in the <code>/docs/api/</code> directory and ensure the corresponding endpoints are implemented in your routes.</p>
                    
                    <div class="code-instruction">
                        <h4><i class="fas fa-info-circle"></i> Sample File Format:</h4>
                        <pre data-language="json">{
  "endpoint": "/api/v1/users",
  "method": "GET",
  "description": "Retrieve list of users",
  "request": {},
  "response": {
    "data": [],
    "status": "success"
  }
}</pre>
                    </div>
                </div>
            @else
                <!-- Endpoint Grid -->
                <div class="endpoint-grid">
                    @foreach($samples as $doc)
                        <div class="endpoint-card">
                            <div class="endpoint-header">
                                <span class="method-badge method-{{ strtolower($doc['method'] ?? 'get') }}">
                                    {{ $doc['method'] ?? 'GET' }}
                                </span>
                                <code class="endpoint-path">{{ $doc['endpoint'] ?? '/api/v1/endpoint' }}</code>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Implemented
                                </span>
                            </div>
                            
                            <p class="endpoint-description">
                                <i class="fas fa-info-circle text-muted"></i>
                                {{ $doc['description'] ?? 'No description provided' }}
                            </p>
                            
                            <div class="section-title">
                                <i class="fas fa-paper-plane"></i>
                                Request
                            </div>
                            <pre data-language="json" id="request-{{ $loop->index }}">{{ json_encode($doc['request'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                            
                            <div class="section-title">
                                <i class="fas fa-reply"></i>
                                Response
                            </div>
                            <pre data-language="json" id="response-{{ $loop->index }}">{{ json_encode($doc['response'] ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p>Generated by date-api documentation system • Last updated: {{ date('Y-m-d H:i:s') }}</p>
            <p class="text-muted" style="margin-top: 10px; font-size: 0.85rem;">
                <i class="fas fa-code"></i> This documentation is auto-generated from JSON samples in /docs/api/
            </p>
        </footer>
    </div>

    <script>
        // JSON Syntax Highlighting
        function highlightJSON(element) {
            const json = element.textContent;
            let highlighted = json;
            
            // Highlight keys
            highlighted = highlighted.replace(/"([^"]+)":/g, '<span class="json-key">"$1":</span>');
            
            // Highlight strings
            highlighted = highlighted.replace(/: "([^"]+)"/g, ': <span class="json-string">"$1"</span>');
            
            // Highlight numbers
            highlighted = highlighted.replace(/: (\d+)/g, ': <span class="json-number">$1</span>');
            
            // Highlight booleans
            highlighted = highlighted.replace(/: (true|false)/g, ': <span class="json-boolean">$1</span>');
            
            // Highlight null
            highlighted = highlighted.replace(/: null/g, ': <span class="json-null">null</span>');
            
            element.innerHTML = highlighted;
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight all JSON code blocks
            document.querySelectorAll('pre[data-language="json"]').forEach(pre => {
                highlightJSON(pre);
            });
            
            // Update stats
            const endpointCards = document.querySelectorAll('.endpoint-card').length;
            const totalEndpoints = {{ count($samples) }};
            
            document.getElementById('total-endpoints').textContent = totalEndpoints;
            document.getElementById('implemented-endpoints').textContent = totalEndpoints;
            document.getElementById('sample-files').textContent = totalEndpoints;
            
            // Add copy to clipboard functionality
            document.querySelectorAll('pre').forEach(pre => {
                const copyBtn = document.createElement('button');
                copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
                copyBtn.className = 'copy-btn';
                copyBtn.style.cssText = `
                    position: absolute;
                    top: 10px;
                    right: 60px;
                    background: #374151;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    opacity: 0.7;
                    transition: opacity 0.3s;
                `;
                copyBtn.title = 'Copy to clipboard';
                
                copyBtn.addEventListener('mouseenter', () => {
                    copyBtn.style.opacity = '1';
                });
                
                copyBtn.addEventListener('mouseleave', () => {
                    copyBtn.style.opacity = '0.7';
                });
                
                copyBtn.addEventListener('click', () => {
                    const text = pre.textContent;
                    navigator.clipboard.writeText(text).then(() => {
                        const originalHTML = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="fas fa-check"></i>';
                        copyBtn.style.background = '#10b981';
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHTML;
                            copyBtn.style.background = '#374151';
                        }, 2000);
                    });
                });
                
                pre.style.position = 'relative';
                pre.appendChild(copyBtn);
            });
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add search functionality placeholder
            const header = document.querySelector('.header');
            const searchBox = document.createElement('div');
            searchBox.innerHTML = `
                <div style="margin-top: 20px;">
                    <input type="text" id="search-endpoints" placeholder="Search endpoints..." 
                           style="width: 100%; padding: 12px 20px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px;">
                </div>
            `;
            header.appendChild(searchBox);
            
            // Search functionality
            const searchInput = document.getElementById('search-endpoints');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const endpointCards = document.querySelectorAll('.endpoint-card');
                    
                    endpointCards.forEach(card => {
                        const endpoint = card.querySelector('.endpoint-path').textContent.toLowerCase();
                        const description = card.querySelector('.endpoint-description').textContent.toLowerCase();
                        
                        if (endpoint.includes(searchTerm) || description.includes(searchTerm)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>