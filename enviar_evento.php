<?php
// --- CONFIGURACIÓN ---
$access_token = 'EAAIBrjFE0hgBOxBV9HYcJdRsV2hmhWr5ZBPbVUJpGmGY38EaHociIGrkxpjOqhKZCtoYoF7su8ZACyCRyoj8rzXbtHN9I6cAm6BZAbMfahq5vfye41Org3883wqQgthUm2CflzEY0GvwRJ26KSAeHOnIuZAzJ3ZCR6k5K7HjcZATNVRkZCLCo7BLbaQTmmVutgZDZD';
$pixel_id = '618119577985096';

// Obtener datos enviados por el frontend
$data_json = file_get_contents("php://input");
$data = json_decode($data_json, true);

// Verificación básica
if (!$data || !isset($data['event_name']) || !isset($data['event_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos necesarios"]);
    exit;
}

// --- CONSTRUCCIÓN DEL EVENTO ---
$event = [
    "event_name" => $data['event_name'],
    "event_time" => $data['event_time'] ?? time(),
    "action_source" => "website",
    "event_source_url" => $data['event_source_url'] ?? '',
    "event_id" => $data['event_id'], // USAR MISMO ID QUE EN EL PIXEL
    "user_data" => [
        "client_user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? '',
        "client_ip_address" => $_SERVER['REMOTE_ADDR'] ?? ''
    ],
    "custom_data" => [
        "value" => $data['value'] ?? 5.00,
        "currency" => $data['currency'] ?? "ARS"
    ]
];

$payload = [
    "data" => [$event]
];

// --- ENVÍO A META CAPI ---
$ch = curl_init("https://graph.facebook.com/v18.0/$pixel_id/events?access_token=$access_token");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- RESPUESTA ---
http_response_code($http_code);
header('Content-Type: application/json');
echo $response;
