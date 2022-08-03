<?php

use App\Models\DatabaseModel;
use App\Models\tableModel;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require __DIR__ . '/../vendor/autoload.php';

define('USER', 'appapi');
define('PASS', 'appapi123');

define('SECRET', 'appapisecret');

$app = AppFactory::create();

$validateUser = function (Request $request, RequestHandler $handler) {
    $response = new \Slim\Psr7\Response(401);

    $message = json_encode([ 'message' => 'you dont have authorization for this resourses' ], JSON_PRETTY_PRINT);

    if ($request->hasHeader('Authorization')) {
        $token = '';
        if (substr(strtolower($request->getHeaderLine('Authorization')), 0, 6) === 'bearer') {
            try {
                $token = substr($request->getHeaderLine('Authorization'), 7);
                JWT::decode($token, new Key(SECRET, 'HS256'));
                return $handler->handle($request);
            } catch (Exception $e) {
                $response->getBody()->write($message);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
    }

    $response->getBody()->write($message);

    return $response->withHeader('Content-Type', 'application/json');
};

$jsonBodyParser = function (Request $request, RequestHandler $handler) {
    $contentType = $request->getHeaderLine('Content-Type');

    if (strstr($contentType, 'application/json')) {
        $contents = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $request = $request->withParsedBody($contents);

            return $handler->handle($request);
        }
    }

    $response = new \Slim\Psr7\Response(400);
    $response->getBody()->write(json_encode([ 'message' => 'json not found' ], JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
};

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Sync api work!!");
    return $response;
});

$app->post('/login', function (Request $request, Response $response, $args) {
    $contents = (array)$request->getParsedBody();

    if (!isset($contents['username']) || !isset($contents['password'])) {
        $response->getBody()->write(json_encode([ 'message' => 'data is missing' ], JSON_PRETTY_PRINT));

        return $response
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    $username = trim($contents['username']);
    $password = $contents['password'];

    if ($username !== USER || $password !== PASS) {
        $response->getBody()->write(json_encode([ 'message' => 'username or password invalid' ], JSON_PRETTY_PRINT));

        return $response
            ->withStatus(401)
            ->withHeader('Content-Type', 'application/json');
    }

    $key = SECRET;
    $payload = [
        'iss' => 'http://sync.test',
        'aud' => 'http://sync.test',
        'iat' => 1356999524,
        'nbf' => 1357000000,
        'userName' => USER
    ];

    $jwt = JWT::encode($payload, $key, 'HS256');

    $credentials = [
        'user' => USER,
        'token' => $jwt
    ];

    $response->getBody()->write(json_encode($credentials, JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
})->add($jsonBodyParser);;

$app->get('/database', function (Request $request, Response $response, $args) {
    $database_model = new DatabaseModel();
    $tables = $database_model->findAll();

    $database = [];

    foreach($tables as $table) {
        $table_name = $table['TABLE_NAME'];
        $table_model = new tableModel();
        $dates = $table_model->findName($table_name);

        $database[$table_name] = $dates;
    }

    $response->getBody()->write(json_encode($database, JSON_PRETTY_PRINT));

    return $response->withHeader('Content-Type', 'application/json');
})->add($validateUser);

$app->run();
