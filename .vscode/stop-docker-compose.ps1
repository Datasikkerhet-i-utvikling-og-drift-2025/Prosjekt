# Clear the console
Clear-Host

# Check if Docker containers are running
$containersRunning = docker-compose -f "${PWD}/docker-compose.yml" ps --services --filter "status=running" | Measure-Object -Line

if ($containersRunning.Count -gt 0) {
    Write-Output "Stopping running Docker containers..."
    docker-compose -f "${PWD}/docker-compose.yml" stop
    Write-Output "Docker containers stopped successfully."
} else {
    Write-Output "No running containers found. Nothing to stop."
}

# Exit script
exit
