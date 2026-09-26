# Project-local runtime selector. Does not alter the machine PATH.
$ErrorActionPreference = 'Stop'
$projectPhp = $env:PROJECT_PHP
if (-not $projectPhp) {
    if (Test-Path 'C:\Program Files\php-8.4.10\php.exe') { $projectPhp = 'C:\Program Files\php-8.4.10\php.exe' }
    else { $projectPhp = (Get-Command php -ErrorAction Stop).Source }
}
$projectVersion = & $projectPhp -r 'echo PHP_VERSION_ID;'
if ([int]$projectVersion -lt 80400) { throw 'This project requires PHP 8.4+. Set PROJECT_PHP to a compatible executable.' }
$projectModules = & $projectPhp -m
$projectFlags = @()
if ($projectModules -notcontains 'pdo_sqlite') { $projectFlags += @('-d','extension=pdo_sqlite') }
if ($projectModules -notcontains 'sqlite3') { $projectFlags += @('-d','extension=sqlite3') }
& $projectPhp @projectFlags @args
exit $LASTEXITCODE
