<?php
$apiKey = '';  // Ваш API ключ
$webhookUrl = 'https://api.wazzup24.com/v3/chats/sendText';  // URL для отправки сообщений
$checkMessagesUrl = 'https://api.wazzup24.com/v3/chats/getMessages'; // URL для проверки сообщений

// Функция для отправки ответов
function sendMessage($chatId, $text) {
    global $webhookUrl, $apiKey;
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $apiKey]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['chatId' => $chatId, 'text' => $text]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Функция для обработки сообщений
function checkMessages() {
    global $checkMessagesUrl, $apiKey;
    $ch = curl_init($checkMessagesUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $messages = json_decode($response, true);
    foreach ($messages as $message) {
        processMessage($message);
    }
}

// Функция для обработки каждого сообщения
function processMessage($message) {
    $userId = $message['from'];
    $userFile = __DIR__ . "/users/$userId.json";

    if (!file_exists($userFile)) {
        $responseText = "Приветствую. Как я могу к вам обращаться? (ваше ФИО)";
        file_put_contents($userFile, json_encode(['step' => 'ask_name']));
    } else {
        $userData = json_decode(file_get_contents($userFile), true);
        $userResponse = $message['body'];

        switch ($userData['step']) {
            case 'ask_name':
                $userData['name'] = $userResponse;
                $userData['step'] = 'ask_phone';
                $responseText = "Спасибо, {$userData['name']}. Теперь введите ваш номер телефона.";
                break;
            case 'ask_phone':
                $userData['phone'] = $userResponse;
                $userData['step'] = 'ask_email';
                $responseText = "Теперь введите ваш email.";
                break;
            case 'ask_email':
                $userData['email'] = $userResponse;
                $responseText = "Спасибо, данные сохранены!";
                unset($userData['step']);  // Завершение диалога
                break;
        }

        file_put_contents($userFile, json_encode($userData));
    }

    sendMessage($userId, $responseText);
}

// Запуск проверки сообщений каждый раз при вызове скрипта
checkMessages();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Bot</title>
</head>
<body>
    <h1><? echo "Bot is working" ?></h1>
</body>
</html>