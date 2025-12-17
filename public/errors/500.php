<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
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
        
        .info-box {
            margin-top: 3rem;
            padding: 1.5rem;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            text-align: left;
        }
        
        .info-box h3 {
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        
        .info-box p {
            margin: 0.5rem 0;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="error-code">500</h1>
        <h2 class="error-title">Internal Server Error</h2>
        <p class="error-message">
            Something went wrong on our end. Our team has been notified 
            and we're working to fix it as quickly as possible.
        </p>
        
        <div class="error-actions">
            <a href="/Virtual_Wardrobe" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Go Home
            </a>
            <a href="javascript:location.reload()" class="btn">
                <i class="fas fa-sync"></i>
                Retry
            </a>
        </div>
        
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> What happened?</h3>
            <p>
                An unexpected error occurred while processing your request. 
                This could be temporary. Please try again in a few moments.
            </p>
            <p style="margin-top: 1rem;">
                If the problem persists, please contact support with the 
                details of what you were trying to do.
            </p>
        </div>
    </div>
</body>
</html>
