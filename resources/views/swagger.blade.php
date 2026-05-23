<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aimeos REST API Reference and Developer Documentation. Explore and test available API endpoints.">
    <title>Aimeos REST API Documentation</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Swagger UI Assets from CDN -->
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    
    <style>
        /* Base Styling */
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #020617; /* Super dark slate background */
            color: #cbd5e1;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Glowing background effects */
        .glowing-bg {
            position: fixed;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 400px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, rgba(99, 102, 241, 0.05) 50%, rgba(2, 6, 23, 0) 100%);
            z-index: 0;
            pointer-events: none;
            filter: blur(80px);
        }

        /* Header Navigation */
        header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(51, 65, 85, 0.5);
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
        }
        .logo-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .logo-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: #ffffff;
            font-size: 1.2rem;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }
        .logo-text h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }
        .badge {
            background: rgba(139, 92, 246, 0.1);
            border: 1px solid rgba(139, 92, 246, 0.3);
            color: #c084fc;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Documentation Container */
        main {
            position: relative;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            z-index: 1;
        }

        /* Swagger UI Overrides to Slate/Indigo Dark Theme */
        .swagger-ui {
            color: #cbd5e1 !important;
            font-family: 'Outfit', sans-serif !important;
        }
        .swagger-ui .info {
            margin: 30px 0 20px 0 !important;
        }
        .swagger-ui .info .title {
            color: #f8fafc !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
            letter-spacing: -0.03em !important;
        }
        .swagger-ui .info p, 
        .swagger-ui .info li, 
        .swagger-ui .info td, 
        .swagger-ui .info a {
            color: #94a3b8 !important;
        }
        .swagger-ui .info a {
            color: #a78bfa !important;
            text-decoration: none;
            transition: color 0.2s;
        }
        .swagger-ui .info a:hover {
            color: #c084fc !important;
            text-decoration: underline;
        }
        .swagger-ui .scheme-container {
            background-color: #0f172a !important;
            border: 1px solid #1e293b !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
            padding: 1.5rem !important;
            margin-bottom: 2rem !important;
        }
        .swagger-ui .opblock-tag-section {
            background: transparent !important;
        }
        .swagger-ui .opblock-tag {
            color: #f8fafc !important;
            border-bottom: 1px solid #1e293b !important;
            font-family: 'Outfit', sans-serif !important;
            padding: 15px 0 !important;
            font-size: 1.3rem !important;
        }
        .swagger-ui .opblock {
            background-color: #0f172a !important;
            border: 1px solid #1e293b !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15) !important;
            margin-bottom: 1rem !important;
        }
        .swagger-ui .opblock .opblock-summary {
            padding: 12px 20px !important;
        }
        .swagger-ui .opblock .opblock-summary-method {
            border-radius: 6px !important;
            font-weight: 700 !important;
            padding: 6px 12px !important;
            text-transform: uppercase !important;
            font-size: 0.8rem !important;
        }
        .swagger-ui .opblock-post {
            border-color: rgba(16, 185, 129, 0.3) !important;
            background: rgba(16, 185, 129, 0.03) !important;
        }
        .swagger-ui .opblock-post .opblock-summary-method {
            background-color: #10b981 !important;
        }
        .swagger-ui .opblock-post .opblock-summary {
            border-color: rgba(16, 185, 129, 0.2) !important;
        }
        .swagger-ui .opblock-get {
            border-color: rgba(59, 130, 246, 0.3) !important;
            background: rgba(59, 130, 246, 0.03) !important;
        }
        .swagger-ui .opblock-get .opblock-summary-method {
            background-color: #3b82f6 !important;
        }
        .swagger-ui .opblock-get .opblock-summary {
            border-color: rgba(59, 130, 246, 0.2) !important;
        }
        .swagger-ui .opblock-put {
            border-color: rgba(245, 158, 11, 0.3) !important;
            background: rgba(245, 158, 11, 0.03) !important;
        }
        .swagger-ui .opblock-put .opblock-summary-method {
            background-color: #f59e0b !important;
        }
        .swagger-ui .opblock-put .opblock-summary {
            border-color: rgba(245, 158, 11, 0.2) !important;
        }
        .swagger-ui .opblock-delete {
            border-color: rgba(239, 68, 68, 0.3) !important;
            background: rgba(239, 68, 68, 0.03) !important;
        }
        .swagger-ui .opblock-delete .opblock-summary-method {
            background-color: #ef4444 !important;
        }
        .swagger-ui .opblock-delete .opblock-summary {
            border-color: rgba(239, 68, 68, 0.2) !important;
        }
        .swagger-ui .opblock-summary-path {
            color: #f8fafc !important;
            font-weight: 500 !important;
            font-size: 0.95rem !important;
        }
        .swagger-ui .opblock-summary-description {
            color: #94a3b8 !important;
        }
        .swagger-ui .opblock .opblock-section-header {
            background-color: #1e293b !important;
            border-bottom: 1px solid #334155 !important;
            padding: 10px 20px !important;
        }
        .swagger-ui .opblock .opblock-section-header h4 {
            color: #f8fafc !important;
            font-weight: 600 !important;
        }
        .swagger-ui .opblock-body {
            background-color: #0f172a !important;
        }
        .swagger-ui .tabli button {
            color: #cbd5e1 !important;
            font-weight: 600 !important;
        }
        .swagger-ui .tabli.active button {
            color: #a78bfa !important;
        }
        .swagger-ui .tabli.active button::after {
            background-color: #a78bfa !important;
        }
        .swagger-ui label {
            color: #cbd5e1 !important;
        }
        .swagger-ui input[type=text], 
        .swagger-ui textarea {
            background: #020617 !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            border-radius: 6px !important;
            padding: 8px 12px !important;
        }
        .swagger-ui input[type=text]:focus, 
        .swagger-ui textarea:focus {
            border-color: #8b5cf6 !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.2) !important;
        }
        .swagger-ui select {
            background: #020617 !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
        }
        .swagger-ui select:focus {
            outline: none !important;
            border-color: #8b5cf6 !important;
        }
        .swagger-ui .btn {
            background: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            border-radius: 6px !important;
            padding: 6px 16px !important;
            transition: all 0.2s !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }
        .swagger-ui .btn:hover {
            background: #334155 !important;
            border-color: #475569 !important;
        }
        .swagger-ui .btn.execute {
            background-color: #8b5cf6 !important;
            color: #ffffff !important;
            border-color: #8b5cf6 !important;
        }
        .swagger-ui .btn.execute:hover {
            background-color: #7c3aed !important;
        }
        .swagger-ui .btn.cancel {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: white !important;
        }
        .swagger-ui .btn.cancel:hover {
            background-color: #dc2626 !important;
        }
        .swagger-ui .response-col_status {
            color: #f8fafc !important;
            font-weight: 700 !important;
        }
        .swagger-ui .response-col_links {
            color: #cbd5e1 !important;
        }
        .swagger-ui table thead tr td, 
        .swagger-ui table thead tr th {
            color: #94a3b8 !important;
            border-bottom: 1px solid #334155 !important;
            font-weight: 600 !important;
        }
        .swagger-ui table tbody tr td {
            padding: 12px 10px !important;
        }
        .swagger-ui .dialog-ux .modal-ux {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
        }
        .swagger-ui .dialog-ux .modal-ux-header {
            border-bottom: 1px solid #1e293b !important;
            padding: 15px 20px !important;
        }
        .swagger-ui .dialog-ux .modal-ux-header h3 {
            color: #f8fafc !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
        }
        .swagger-ui .dialog-ux .modal-ux-content {
            padding: 20px !important;
            color: #cbd5e1 !important;
        }
        .swagger-ui .dialog-ux .modal-ux-content h4 {
            color: #f8fafc !important;
        }
        .swagger-ui section.models {
            border: 1px solid #1e293b !important;
            border-radius: 12px !important;
            background-color: #0f172a !important;
            margin-top: 30px !important;
        }
        .swagger-ui section.models h4 {
            color: #f8fafc !important;
            border-bottom: 1px solid #1e293b !important;
            font-family: 'Outfit', sans-serif !important;
            padding: 15px 20px !important;
        }
        .swagger-ui .model-box {
            background-color: #020617 !important;
            border-radius: 8px !important;
            padding: 15px !important;
            border: 1px solid #1e293b !important;
        }
        .swagger-ui .model-title {
            color: #f8fafc !important;
            font-weight: 600 !important;
        }
        .swagger-ui .model {
            color: #cbd5e1 !important;
        }
        .swagger-ui .prop-type {
            color: #c084fc !important;
        }
        .swagger-ui .prop-format {
            color: #64748b !important;
        }
        .swagger-ui .markdown p, 
        .swagger-ui .markdown code {
            color: #94a3b8 !important;
        }
        .swagger-ui .renderedMarkdown code {
            background: rgba(30, 41, 59, 0.8) !important;
            color: #f8fafc !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
        }
        .swagger-ui pre {
            background: #020617 !important;
            border: 1px solid #1e293b !important;
            border-radius: 8px !important;
            padding: 15px !important;
        }
        .swagger-ui code {
            color: #e2e8f0 !important;
        }
        .swagger-ui .model-toggle::after {
            filter: invert(1) !important;
        }
        .swagger-ui .copy-to-clipboard {
            background-color: #1e293b !important;
            border-radius: 4px !important;
            border: 1px solid #334155 !important;
        }
        .swagger-ui .copy-to-clipboard button {
            filter: invert(1) !important;
        }
        
        /* Footer */
        footer {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            text-align: center;
            color: #475569;
            font-size: 0.85rem;
            border-top: 1px solid #1e293b;
        }
    </style>
</head>
<body>
    <div class="glowing-bg"></div>

    <header>
        <div class="header-container">
            <div class="logo-group">
                <div class="logo-icon">A</div>
                <div class="logo-text">
                    <h1>Aimeos API Docs</h1>
                </div>
            </div>
            <div class="badge">v2.0.0</div>
        </div>
    </header>

    <main>
        <div id="swagger-ui"></div>
    </main>

    <footer>
        &copy; {{ date('Y') }} Aimeos Store. Built with Laravel and Swagger.
    </footer>

    <!-- Swagger UI JS Bundle & Presets -->
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            // Build the Swagger UI pointed to our static openapi.json
            const ui = SwaggerUIBundle({
                url: "/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                persistAuthorization: true
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
