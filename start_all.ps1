Param(
    [switch]$Down,
    [switch]$Seed,
    [switch]$Local,
    [switch]$Test,
    [switch]$StopLocal,
    [int]$Port = 8080
)

# Allow passing PORT via environment variable as well
if ($env:VW_PORT) { $Port = [int]$env:VW_PORT }
# PSScriptAnalyzer disable=All  # suppress analyzer warnings for this convenience script


function Test-Docker {
    try { docker --version | Out-Null; return $true } catch { return $false }
}

if ($Local) { $localMode = $true } elseif (-not (Test-Docker)) { Write-Host "Docker is not available. Using local start mode by default." -ForegroundColor Yellow; $localMode = $true } else { $localMode = $false }

if ($Down) {
    if (-not $localMode) { docker-compose down }
    exit 0
}
if ($StopLocal) {
    Write-Host "Stopping local Node and PHP processes..."
    if (Test-Path 'node-process-id') { $nodeProcessContent = Get-Content 'node-process-id'; Stop-Process -Id $nodeProcessContent -ErrorAction SilentlyContinue; Remove-Item 'node-process-id' -ErrorAction SilentlyContinue }
    if (Test-Path 'php-process-id') { $phpProcessContent = Get-Content 'php-process-id'; Stop-Process -Id $phpProcessContent -ErrorAction SilentlyContinue; Remove-Item 'php-process-id' -ErrorAction SilentlyContinue }
    Write-Host "Stopped local services."
    exit 0
}

if (-not $localMode) {
    docker-compose up -d --build
    Write-Host "Waiting for services to be healthy..." -ForegroundColor Green
    Start-Sleep -Seconds 6
    docker ps --filter "name=vw-" --format "table {{.Names}}\t{{.Status}}"
}
Write-Host "To seed DB: run `C:\xampp\php\php.exe src/admin/seed_sample.php` with DB env variables set."
if (-not $localMode) { Write-Host "Using docker: to seed run: docker exec -it vw-php php src/admin/seed_sample.php" }
Write-Host "Dev note: run .\start_all.ps1 -Local -Port <port> to use a different HTTP port (default: $Port)."
if (-not $localMode -and $Seed) {
    Write-Host "Running DB seed inside php container..." -ForegroundColor Green
    docker exec -it vw-php php src/admin/seed_sample.php
}

if ($localMode) {
    Write-Host "Starting local PHP and Node servers..." -ForegroundColor Green
    # Start PHP dev server using XAMPP php.exe
    $phpExe = 'C:\xampp\php\php.exe'
    if (Test-Path $phpExe) {
        $env:SOCKET_SERVER_URL = 'http://localhost:3000'
        $env:APP_BASE_PATH = '/Virtual_Wardrobe'
        $env:APP_URL = "http://localhost:$Port/Virtual_Wardrobe"
        $startArgs = "-S 0.0.0.0:$Port -t C:\xampp\htdocs"
        $phpProc = Start-Process -FilePath $phpExe -ArgumentList $startArgs -NoNewWindow -RedirectStandardOutput 'php-server.log' -RedirectStandardError 'php-server.err.log' -PassThru
        $phpProc.Id | Out-File -FilePath 'php-process-id' -Encoding ascii
        Write-Host "PHP dev server started (http://localhost:$Port); ProcessID=$($phpProc.Id)"
    } else { Write-Host "XAMPP PHP not found at $phpExe; start PHP server manually." }

    # Start Node socket server
    $nodeExe = (Get-Command node -ErrorAction SilentlyContinue).Source
    if ($nodeExe) {
        Set-Location 'C:\xampp\htdocs\Virtual_Wardrobe\node\socket-server'
        $env:SOCKET_API_KEY = 'dev-secret-key'
        $env:SOCKET_JWT_SECRET = 'socket-secret'
        $env:SOCKET_REDIS_URL = 'redis://127.0.0.1:6379'
        $env:SOCKET_ALLOWED_ORIGINS = "http://localhost,http://localhost:8000,http://localhost:$Port"
        # Ensure Node runs on a known port to match PHP meta (3000)
        $env:PORT = 3000
        $env:SOCKET_SERVER_URL = 'http://localhost:3000'
        Remove-Item -Path 'socket-server.log','socket-server.err.log' -ErrorAction SilentlyContinue
        $nodeProc = Start-Process -FilePath $nodeExe -ArgumentList 'server.js' -NoNewWindow -RedirectStandardOutput 'socket-server.log' -RedirectStandardError 'socket-server.err.log' -PassThru
        $nodeProc.Id | Out-File -FilePath 'node-process-id' -Encoding ascii
        Write-Host "Node socket server started (http://localhost:3000)"
    } else { Write-Host "Node not found on PATH; start Node server manually in node/socket-server." }

    if ($Seed) {
        $phpExe2 = 'C:\xampp\php\php.exe'
        $seedPath = 'C:\xampp\htdocs\Virtual_Wardrobe\src\admin\seed_sample.php'
        if ((Test-Path $phpExe2) -and (Test-Path $seedPath)) { & $phpExe2 $seedPath }
    }
}

if ($StopLocal) {
    Write-Host "Stopping local Node and PHP processes..."
    if (Test-Path 'node-process-id') { Stop-Process -Id (Get-Content 'node-process-id') -ErrorAction SilentlyContinue; Remove-Item 'node-process-id' -ErrorAction SilentlyContinue }
    if (Test-Path 'php-process-id') { Stop-Process -Id (Get-Content 'php-process-id') -ErrorAction SilentlyContinue; Remove-Item 'php-process-id' -ErrorAction SilentlyContinue }
    Write-Host "Stopped local services."
}

if ($Test) {
    # run tests after local stack is up
    Write-Host "Running Playwright tests..."
    Set-Location 'C:\xampp\htdocs\Virtual_Wardrobe\node\socket-server'
    $env:SOCKET_SERVER_URL = 'http://localhost:3000'
    $env:SOCKET_API_KEY = 'dev-secret-key'
    $env:SOCKET_JWT_SECRET = 'socket-secret'
    $env:PORT = 3000
    $env:APP_URL = "http://localhost:$Port/Virtual_Wardrobe"
    npm ci
    npx playwright install --with-deps
    npx playwright test --timeout=300000
}
# PSScriptAnalyzer enable=All
