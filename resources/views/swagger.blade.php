<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
        .swagger-ui .topbar {
            background-color: #1f2937;
        }
        .swagger-ui .topbar .download-url-wrapper {
            display: none;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '/api/swagger.json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null,
                docExpansion: "list",
                filter: true,
                showRequestHeaders: true,
                showCommonExtensions: true,
                tryItOutEnabled: true,
                requestInterceptor: (req) => {
                    const token = localStorage.getItem('auth_token');
                    if (token) {
                        req.headers.Authorization = `Bearer ${token}`;
                    }
                    return req;
                }
            });
            
            // Добавляем кнопку для сохранения токена
            const authButton = document.createElement('button');
            authButton.innerHTML = '💾 Сохранить токен';
            authButton.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999; padding: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;';
            authButton.onclick = () => {
                const token = prompt('Введите токен авторизации:');
                if (token) {
                    localStorage.setItem('auth_token', token);
                    alert('Токен сохранен! Теперь все запросы будут использовать этот токен.');
                }
            };
            document.body.appendChild(authButton);
        };
    </script>
</body>
</html>

