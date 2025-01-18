# Clear the console
Clear-Host

# Cool Boot-Up Banner
Write-Host "
╔════════════════════════════════════════════════════════════════╗
║                    Starting Docker Services                    ║
║                     Development Environment                    ║
╚════════════════════════════════════════════════════════════════╝
" -ForegroundColor Cyan

# Function to ensure Docker Desktop and the Docker daemon are running
function Wait-ForDocker {
    Write-Host "[INFO] Checking if Docker Desktop is running..." -ForegroundColor Yellow

    # Check if Docker Desktop process is running
    $dockerdRunning = Get-Process -Name "Docker Desktop" -ErrorAction SilentlyContinue

    if (-not $dockerdRunning) {
        Write-Host "[INFO] Docker Desktop is not running. Launching Docker Desktop..." -ForegroundColor Yellow

        # Find Docker Desktop executable dynamically, fall back to default paths
        $dockerDesktopPath = (Get-Command "Docker Desktop" -ErrorAction SilentlyContinue)?.Source

        if (-not $dockerDesktopPath) {
            $defaultPaths = @(
                "C:\\Program Files\\Docker\\Docker\\Docker Desktop.exe",
                "C:\\Program Files (x86)\\Docker\\Docker\\Docker Desktop.exe"
            )

            foreach ($path in $defaultPaths) {
                if (Test-Path $path) {
                    $dockerDesktopPath = $path
                    break
                }
            }
        }

        if (-not $dockerDesktopPath) {
            Write-Host "[ERROR] Docker Desktop executable not found. Please ensure Docker is installed and available in PATH or default locations." -ForegroundColor Red
            exit 1
        }

        Start-Process -FilePath $dockerDesktopPath -NoNewWindow
        Write-Host "[INFO] Waiting for Docker Desktop to start..." -ForegroundColor Yellow
        Start-Sleep -Seconds 15 # Adjust if Docker Desktop takes longer to start
    } else {
        Write-Host "[SUCCESS] Docker Desktop is already running." -ForegroundColor Green
    }

    Write-Host "[INFO] Ensuring Docker daemon is ready..." -ForegroundColor Yellow
    $dockerReady = $false
    while (-not $dockerReady) {
        try {
            docker version > $null 2>&1
            $dockerReady = $true
        } catch {
            Write-Host "[WARNING] Docker daemon not ready, retrying..." -ForegroundColor Red
            Start-Sleep -Seconds 5
        }
    }
    Write-Host "[SUCCESS] Docker is ready to use." -ForegroundColor Green
}

# Start Docker Desktop and ensure the daemon is ready
Wait-ForDocker

# Start Docker Compose
Write-Host "[INFO] Starting Docker Compose services..." -ForegroundColor Yellow
try {
    docker-compose -f "${PWD}\docker-compose.yml" up -d --build
    Write-Host "[SUCCESS] Docker Compose services started successfully!" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to start Docker Compose services: $_" -ForegroundColor Red
    exit 1
}

Write-Host "
╔════════════════════════════════════════════════════════════════╗
║                     Docker Services Status                     ║
╚════════════════════════════════════════════════════════════════╝
" -ForegroundColor Cyan

        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"        

        Write-Host "
╔════════════════════════════════════════════════════════════════╗
║ Frontend is running at: http://localhost:8080                  ║
║ Backend API is accessible at: http://localhost:5000            ║
╚════════════════════════════════════════════════════════════════╝
" -ForegroundColor Blue

Write-Host "[INFO] Open the above links in your browser to access the application." -ForegroundColor Yellow
Write-Host "[INFO] Press Ctrl+C in console or shift+F5 in ide to stop." -ForegroundColor Yellow
while ($true) {
    Start-Sleep -Seconds 60
}
