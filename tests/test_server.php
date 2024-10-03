<?php

$headers = getallheaders();
if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer secret_token') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit(0);
}

header('Content-Type: application/json');
$routes = [
    'GET' => [
        '/api/data' => function () {
            echo json_encode(['message' => 'GET request received']);
            exit(0);
        },
        '/api/search' => function () {
            $name = $_GET['name'] ?? 'Guest';
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

            echo json_encode([
                'message' => "GET request received",
                'name' => $name,
                'page' => $page,
                'limit' => $limit
            ]);
            exit(0);
        }
    ],
    'POST' => [
        '/api/post/data' => function () {
            if ('application/json' !== $_SERVER['CONTENT_TYPE']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid content type']);
                exit(0);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON']);
                exit(0);
            }

            echo json_encode($input);
            exit(0);
        },
        '/api/post/data/form' => function () {
            if ('application/x-www-form-urlencoded' !== $_SERVER['CONTENT_TYPE']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid content type']);
                exit(0);
            }
            if (empty($_POST)) {
                http_response_code(400);
                echo json_encode(['error' => 'No data provided']);
                exit(0);
            }
            echo json_encode($_POST);
            exit(0);
        }
    ]
];
if (array_key_exists($_SERVER['REQUEST_METHOD'], $routes)) {

    foreach ($routes[$_SERVER['REQUEST_METHOD']] as $route => $callback) {
        if ($route == strtok($_SERVER['REQUEST_URI'], '?')) {
            $callback();
            break;
        }
    }
}
http_response_code(404);
echo json_encode(['error' => 'Not found', 'route' => $_SERVER['REQUEST_URI']]);
exit(0);
