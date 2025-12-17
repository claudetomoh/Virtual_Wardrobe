<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
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
            background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
            color: #333;
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
            color: #ff6b6b;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin: 0;
            color: #fff;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.2);
            line-height: 1;
        }
        
        .error-title {
            font-size: 2rem;
            margin: 1rem 0;
            font-weight: 600;
            color: #fff;
        }
        
        .error-message {
            font-size: 1.125rem;
            margin: 1rem 0 2rem;
            color: rgba(255,255,255,0.95);
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
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        .btn:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .btn-primary {
            background: #667eea;
            color: #fff;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Forbidden</h2>
        <p class="error-message">
            You don't have permission to access this resource. 
            Please log in or contact an administrator if you believe this is an error.
        </p>
        
        <div class="error-actions">
            <a href="/Virtual_Wardrobe/src/auth/login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </a>
            <a href="/Virtual_Wardrobe" class="btn">
                <i class="fas fa-home"></i>
                Go Home
            </a>
        </div>
    </div>
</body>
</html>
