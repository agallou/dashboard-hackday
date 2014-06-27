<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$issuesIds = array(
  'symfony/symfony/issues/5',
);

$app['debug'] = true;


$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$client = new GuzzleHttp\Client(array('base_url' => 'https://api.github.com'));

$issues = array();

foreach ($issuesIds as $issue) {
  $issues[$issue] = $client->get('repos/' . $issue)->json();
}

$app->get('/', function() use($app, $issues) {
  return $app['twig']->render('index.twig', array(
    'issues' => $issues,
  ));
//  $issue = $res->json();
//  return $issue['title'];
});

$app->run();
