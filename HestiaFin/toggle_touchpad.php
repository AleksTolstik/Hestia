<?php
// Виконання PowerShell скрипта
$output = shell_exec("powershell.exe -ExecutionPolicy Bypass -File toggle_touchpad.ps1");

// Перевірка виводу
if (strpos($output, 'Тачпад не знайдено') !== false) {
    echo json_encode(['status' => 'error', 'message' => 'Тачпад не знайдено']);
} elseif (strpos($output, 'Тачпад вимкнено') !== false) {
    echo json_encode(['status' => 'success', 'message' => 'Тачпад вимкнено']);
} elseif (strpos($output, 'Тачпад увімкнено') !== false) {
    echo json_encode(['status' => 'success', 'message' => 'Тачпад увімкнено']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Тачпад вимкнено']);
}