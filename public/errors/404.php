<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            overflow: hidden;
        }
        
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-icon {
            font-size: 6rem;
            margin-bottom: 2rem;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.3);
            line-height: 1;
        }
        
        .error-title {
            font-size: 2rem;
            margin: 1rem 0;
            font-weight: 600;
        }
        
        .error-message {
            font-size: 1.125rem;
            margin: 1rem 0 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: rgba(255,255,255,0.3);
        }
        
        .suggestions {
            margin-top: 3rem;
            text-align: left;
        }
        
        .suggestions h3 {
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .suggestions ul {
            list-style: none;
            padding: 0;
        }
        
        .suggestions li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .suggestions li:before {
            content: "→";
            position: absolute;
            left: 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Page Not Found</h2>
        <p class="error-message">
            Oops! The page you're looking for seems to have wandered off. 
            It might have been removed, renamed, or never existed at all.
        </p>
        
        <div class="error-actions">
            <a href="/Virtual_Wardrobe" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Go Home
            </a>
            <a href="javascript:history.back()" class="btn">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
        </div>
        
        <div class="suggestions">
            <h3>What can you do?</h3>
            <ul>
                <li>Check the URL for typos</li>
                <li>Go back to the previous page</li>
                <li>Visit our homepage</li>
                <li>Browse your wardrobe</li>
            </ul>
        </div>
    </div>
</body>
</html>
