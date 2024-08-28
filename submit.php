<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $price = $_POST['price'] ?? '';
    $timeSpentMoreThan30Seconds = $_POST['timeSpentMoreThan30Seconds'] ?? 0;

    $errors = [];

    // Validate name
    $name = trim($name); // Убираем лишние пробелы
    if (empty($name) || !preg_match('/^[а-яА-ЯёЁ\s]+$/u', $name)) {
        $errors[] = 'Имя должно содержать только буквы и пробелы.';
    }

    // Validate email
    if ($email === false) {
        $errors[] = 'Введите корректный email.';
    }

    // Validate phone
    if (empty($phone) || !preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
        $errors[] = 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.';
    }

    // Validate price
    if ($price === false || $price <= 0) {
        $errors[] = 'Цена должна быть положительным числом.';
    }

    if (!empty($errors)) {
        echo json_encode(['errors' => $errors]);
        http_response_code(400);
        exit;
    }

    // Функция для отправки запросов
    function sendRequest($url, $accessToken, $data = null) {
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // AmoCRM API credentials
    $accessToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImMwNGFkMzY0ZmJjMGNhMWE1ODEwYjliZWMwYmUxZDk2YWQ5YmNjODE1ZjY3YmUyMDdlNzEwYTJkMDQ3MTZiYWRjNTc5YzQ0NmQ2MDc3OTdhIn0.eyJhdWQiOiIxMTU0NWI1ZC1hYTFiLTRlMmYtODk5YS1lYjZjMzY1ZjBkYzciLCJqdGkiOiJjMDRhZDM2NGZiYzBjYTFhNTgxMGI5YmVjMGJlMWQ5NmFkOWJjYzgxNWY2N2JlMjA3ZTcxMGEyZDA0NzE2YmFkYzU3OWM0NDZkNjA3Nzk3YSIsImlhdCI6MTcyNDgzNzcxMSwibmJmIjoxNzI0ODM3NzExLCJleHAiOjE3MjY3MDQwMDAsInN1YiI6IjExNDQzOTE0IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxOTE4MjI2LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMzA2YTZiNTEtNDA2Yy00NTY4LThlNjctYzNhNTBkN2VjNGRlIiwiYXBpX2RvbWFpbiI6ImFwaS1iLmFtb2NybS5ydSJ9.A5NF_X7ZjHviTRYljw2EPmYyuLD8TtXVRQIpWa_zyaGmNjfbvyZaJfI2o6T-ltRfbIAmhFfAn5sdEDLWjjLJa41jmfwqC2afz-Rzyh5WAi6mQ6__XCUF2rXMcJqDlUGG1S-iVrtFd55goO9hH_PVd6vvDWldtyB96hxOQkKu5CMUz7WqfDpDpxy1Usez7dJn9EgzR8oy-PcrYddWeqt2qz7ADeK2I3p6Mb1m1IQn9oD_7F-UsWLYlGWBUJ0eg7bo_26QURkiCJGUGY1JgLacRxhrRU-t3fN3GxKm1VxZiX7f4uNFZwNr3V6uE9evr7eCQhyHdBfX69BljNxeaEcNgw';
    $subdomain = 'andrianowartyom2015'; // Replace with your AmoCRM subdomain

    // Create contact
    $contactUrl = 'https://' . $subdomain . '.amocrm.ru/api/v4/contacts';
    $contactData = [
        [
            'first_name' => $name,
            'custom_fields_values' => [
                [
                    'field_id' => 32533,
                    'values' => [
                        [
                            'value' => $email
                        ]
                    ]
                ],
                [
                    'field_id' => 32531,
                    'values' => [
                        [
                            'value' => $phone
                        ]
                    ]
                ],
                [
                    'field_id' => 32773,
                    'values' => [
                        [
                            'value' => $timeSpentMoreThan30Seconds ? 1 : 0 // Флаги передаются как 1 или 0
                        ]
                    ]
                ]
            ]
        ]
    ];

    // Создание сделки
    $dealUrl = 'https://' . $subdomain . '.amocrm.ru/api/v4/leads/complex';
    $dealData = [
        [
            'name' => 'Заявка от ' . $name,
            'price' => (int)$price,
            '_embedded' => [
                'contacts' => [
                    [
                        'first_name' => $name,
                        'custom_fields_values' => [
                            [
                                'field_id' => 32533,
                                'values' => [
                                    [
                                        'value' => $email
                                    ]
                                ]
                            ],
                            [
                                'field_id' => 32531,
                                'values' => [
                                    [
                                        'value' => $phone
                                    ]
                                ]
                            ],
                            [
                                'field_id' => 32773,
                                'values' => [
                                    [
                                        'value' => $timeSpentMoreThan30Seconds ? true : false // Флаги передаются как 1 или 0
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $dealResponse = sendRequest($dealUrl, $accessToken, $dealData);
    // print_r($dealResponse);

    // Возвращаем JSON-ответ
    echo json_encode([
        'contact' => $contactResponse,
        'deal' => $dealResponse
    ]);
}