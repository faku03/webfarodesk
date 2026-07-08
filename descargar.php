<?php
require __DIR__ . '/vendor/autoload.php';

$configPath = __DIR__ . '/config.php';
$config = is_file($configPath) ? require $configPath : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['sitio_web'] ?? '')) {
        http_response_code(204);
        exit;
    }

    $nombre = trim($_POST['nombre'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha = date('d/m/Y H:i:s');

    $spreadsheetId = $config['spreadsheet_id'] ?? '';
    $sheetRange = $config['sheet_range'] ?? 'datosdescargas';
    $downloadPath = $config['download_path'] ?? '/protected-downloads/FaroDeskSetup.exe';
    $downloadName = $config['download_name'] ?? 'FaroDeskSetup.exe';
    $jsonKeyPath = __DIR__ . '/credentials.json';

    try {
        if ($spreadsheetId === '') {
            throw new RuntimeException('Falta configurar spreadsheet_id.');
        }

        $client = new \Google_Client();
        $client->setApplicationName('FaroDesk Landing Leads');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($jsonKeyPath);

        $service = new \Google_Service_Sheets($client);

        $newRow = [$fecha, $nombre, $ciudad, $pais, $email, $telefono];

        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues([$newRow]);

        $options = ['valueInputOption' => 'USER_ENTERED'];
        $service->spreadsheets_values->append($spreadsheetId, $sheetRange, $valueRange, $options);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('X-Accel-Redirect: ' . $downloadPath);
        exit;
    } catch (Exception $e) {
        error_log('Error en Sheets: ' . $e->getMessage());
        echo 'Ocurrio un problema, intenta de nuevo mas tarde.';
    }
}
