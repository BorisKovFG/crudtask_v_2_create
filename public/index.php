<?php

use Slim\Factory\AppFactory;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

//init App with requires
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

//for flash messages
session_start();
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

//names for routing
$router = $app->getRouteCollector()->getRouteParser();

//bd for data
$repo = new App\PostRepository();

$app->get("/", function ($request, $response) use ($router) {
    $url = $router->urlFor("posts");
    $response = $response->write("<a href=$url>Posts</a>");
    return $this->get('renderer')->render($response, "index.phtml");
})->setName("main");

$app->get("/posts", function ($request, $response) use ($repo, $router) {
    $postData = $repo->read();
    $flash = $this->get('flash')->getMessages();
    $url = $router->urlFor("post");
    $response = $response->write("<a href=$url>Add new Post</a>");
    $params = [
        'post' => $postData,
        'flash' => $flash
    ];
    return $this->get("renderer")->render($response, "posts/index.phtml", $params);
})->setName("posts");

$app->get("/posts/new", function ($request, $response) use ($router, $repo) {
    $params = [
        'errors' => [],
        'posts' => []
    ];
    return $this->get('renderer')->render($response, "posts/new.phtml", $params);
})->setName("post");

$app->post("/posts", function ($request, $response) use ($router, $repo) {
    $postData = $request->getParsedBodyParam("post");
    $validator = new \App\Validator();
    $errors = $validator->validate($postData);
    if (count($errors) === 0) {
        $repo->save($postData);
        $this->get('flash')->addMessage('success', 'Post has been created');
        $url = $router->urlFor("posts");
        return $response->withRedirect($url);
    }

    $params = [
        'errors' => $errors,
        'post' => $postData
    ];

    return $this->get('renderer')->render($response->withStatus(422), "posts/new.phtml", $params);
});
$app->run();