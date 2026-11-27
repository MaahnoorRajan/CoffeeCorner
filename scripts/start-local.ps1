# Start Coffee Corner on loopback. Start the configured database before running this script.
[CmdletBinding()]
param(
    [string]$Php = 'php',
    [ValidateRange(1024,65535)][int]$Port = 8000
)
$ErrorActionPreference = 'Stop'
$project = Split-Path $PSScriptRoot -Parent
$public = Join-Path $project 'public'
if (!(Test-Path -LiteralPath (Join-Path $project 'config/database.local.php'))) {
    throw 'Set up config/database.local.php first. See README.md > Run locally.'
}
$phpExe = (Get-Command $Php -CommandType Application -ErrorAction Stop).Source
$settings = @()
$modules = & $phpExe -m
if ($modules -notcontains 'pdo_mysql') {
    $extensions = Join-Path (Split-Path $phpExe -Parent) 'ext'
    if (!(Test-Path -LiteralPath (Join-Path $extensions 'php_pdo_mysql.dll'))) {
        throw 'Enable the PDO MySQL extension for this PHP installation.'
    }
    $settings = @('-d', "extension_dir=$extensions", '-d', 'extension=pdo_mysql')
}
Write-Host "Coffee Corner: http://127.0.0.1:$Port/   (Ctrl+C to stop)"
& $phpExe @settings -S "127.0.0.1:$Port" -t $public
exit $LASTEXITCODE
