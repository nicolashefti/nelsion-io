<?php

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

define('APP_ROOT', __DIR__ . '/../../');

$loader = require_once APP_ROOT . 'vendor/autoload.php';

$app = new Application();


$app['debug'] = true;

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_sqlite',
        'path' => APP_ROOT . 'data/app.db',
    )
));

$app->register(new MonologServiceProvider, array(
    'monolog.logfile' => APP_ROOT . 'data/app.log',
));

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->after(function (Request $request, Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
});

// Routing
$app->get('/', function () use ($app) {
    return $app->json([
        'message' => 'Welcome to API root, here are listed all available roots.',
    ]);
});

$app->get('/nelsons', function () use ($app) {
    $sql = "SELECT * FROM nelson";
    $beers = $app['db']->fetchAll($sql);

    return $app->json($beers);

})->value('id', null);

$app->get('/nelson/{id}', function ($id) use ($app) {

    $sql = "SELECT * FROM nelson WHERE nelson.id = ?";
    $nelson = $app['db']->fetchAssoc($sql, [$id]);

    if (!$nelson) {
        return $app->json([
            'message' => 'Nelson not found'
        ], 404);
    }

    return $app->json([
        'id' => $nelson['id'],
        'position' => $nelson['position'],
    ], 200);

});

$app->get('/nelson/{id}/position', function ($id) use ($app) {

    $sql = "SELECT * FROM nelson WHERE nelson.id = ?";
    $nelson = $app['db']->fetchAssoc($sql, [$id]);

    if (!$nelson) {
        return $app->json([
            'message' => 'Nelson not found'
        ], 404);
    }

    return $nelson['position'];
});

$app->post('/nelson/{id}', function (Request $request, $id) use ($app) {

    $sql = "UPDATE nelson SET position = ? WHERE id = ?";
    $app['db']->executeUpdate($sql,
        [
            (int)$request->get('position'),
            (int)$request->get('id')
        ]
    );

    return new Response('Beer updated', 200);
});

$app->options('/nelson/{id}', function () use ($app) {
    return $app->json('Ok');
});

/**
 * Insert
 */
$app->post('/nelson', function (Request $request) use ($app) {
    // get data
    if (!$data = $request->get('beer'))
        return new Response('Missing parameters', 400);
    try {
        $app['db']->insert('beer', [
            'id' => null,
            'name' => $data['name'],
            'style_id' => (int)$data['style_id'],
        ]);
    } catch (\Exception $e) {
        return new Response(json_encode($e->getMessage()), 404);
    }

    // redirect to new beer
    return $app->redirect('/beers/' . $data['name'], 201);
});


/**
 * Delete (delete)
 */
$app->delete('/nelson/{id}', function (Request $request, $id) use ($app) {
    // get data
    try {
        $sql = "SELECT * FROM nelson WHERE name = ?";
        $beer = $app['db']->fetchAssoc($sql, array($id));

        if (!$beer)
            return new Response(json_encode('Beer not found'), 404);

        $app['db']->delete('beer', array(
                'id' => $beer['id'],
            )
        );
    } catch (\Exception $e) {
        return new Response(json_encode($e->getMessage()), 404);
    }

    // redirect to new beer
    return new Response('Beer Deleted', 200);
});

$app->run();
