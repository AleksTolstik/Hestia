$devicesOutput = Get-PnpDevice | Out-String

# Використання регулярного виразу для отримання інформації про тачпад
$touchpadRegex = [regex]::new("(?i)(touchpad|trackpad).*?(\{[^}]+\})", [System.Text.RegularExpressions.RegexOptions]::Singleline)
$matches = $touchpadRegex.Matches($devicesOutput)

if ($matches.Count -eq 0) {
    Write-Output "Тачпад не знайдено"
    exit
}

foreach ($match in $matches) {
    $deviceId = $match.Groups[2].Value

    # Отримання стану пристрою
    $deviceStatus = Get-PnpDevice -InstanceId $deviceId

    if ($deviceStatus.Status -eq "OK") {
        # Вимкнення тачпада
        Disable-PnpDevice -InstanceId $deviceId -Confirm:$false
        Write-Output "Тачпад вимкнено"
    } else {
        # Увімкнення тачпада
        Enable-PnpDevice -InstanceId $deviceId -Confirm:$false
        Write-Output "Тачпад увімкнено"
    }
}