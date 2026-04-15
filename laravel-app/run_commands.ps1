$ErrorActionPreference = "Continue"
$dir = "C:\wamp64\www\shop\legacy\Naijabuilders\laravel-app"
Set-Location $dir

# Store results
$results = @()

# Command 1
$cmd1 = "php -l app\Http\Middleware\EnsureLegacyAuth.php"
$output1 = cmd /c $cmd1 2>&1
$exit1 = $LASTEXITCODE
$results += @{
    "Command" = $cmd1
    "Output" = $output1
    "ExitCode" = $exit1
}

# Command 2
$cmd2 = "php -l app\Http\Controllers\AuthController.php"
$output2 = cmd /c $cmd2 2>&1
$exit2 = $LASTEXITCODE
$results += @{
    "Command" = $cmd2
    "Output" = $output2
    "ExitCode" = $exit2
}

# Command 3
$cmd3 = "php artisan route:list --name=login --no-ansi"
$output3 = cmd /c $cmd3 2>&1
$exit3 = $LASTEXITCODE
$results += @{
    "Command" = $cmd3
    "Output" = $output3
    "ExitCode" = $exit3
}

# Display results
foreach ($result in $results) {
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "COMMAND: $($result.Command)" -ForegroundColor Green
    Write-Host "----------------------------------------" -ForegroundColor Cyan
    Write-Host "STDOUT/STDERR:"
    Write-Host $result.Output
    Write-Host "----------------------------------------" -ForegroundColor Cyan
    Write-Host "EXIT CODE: $($result.ExitCode)" -ForegroundColor Yellow
    Write-Host ""
}
