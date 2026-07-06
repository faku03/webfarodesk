<?php
require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = $_POST['nombre'] ?? '';
    $ciudad   = $_POST['ciudad'] ?? '';
    $pais     = $_POST['pais'] ?? '';
    $email    = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha = date('d/m/Y H:i:s');

    $spreadsheetId = 'TU_ID_DE_PLANILLA_AQUÍ';
    $jsonKeyPath = __DIR__ . '/credentials.json'; // Tu archivo JSON de Cuenta de Servicio

    try {
        $client = new \Google_Client();
        $client->setApplicationName('FaroDesk Landing Leads');
        $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($jsonKeyPath);

        $service = new \Google_Service_Sheets($client);

        // Armamos la fila con los datos recibidos
        $newRow = [$fecha, $nombre, $ciudad, $pais, $email, $telefono];
        
        $valueRange = new \Google_Service_Sheets_ValueRange();
        $valueRange->setValues([$newRow]);

        $range = 'Sheet1'; // Detecta automáticamente la última fila libre de la hoja
        $options = ['valueInputOption' => 'USER_ENTERED'];

        // Guardar la fila
        $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);

        // Forzar la descarga inmediata del instalador en el navegador
        header('Location: images/FaroDeskBanner.png'); // Reemplazar por la ruta del instalador .exe
        exit;

    } catch (Exception $e) {
        error_log("Error en Sheets: " . $e->getMessage());
        echo "Ocurrió un problema, intentá de nuevo más tarde.";
    }
}