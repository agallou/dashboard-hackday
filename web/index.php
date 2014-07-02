<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

$yaml = new Parser();
$issuesIds = $yaml->parse(file_get_contents(__DIR__ . '/../config/issues.yml'));


$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$githubConfigFilepath = __DIR__ . '/../config/github.yml';
if (is_file($githubConfigFilepath)) {
    $githubConfig = $yaml->parse(file_get_contents($githubConfigFilepath));
    $githubToken = $githubConfig['token'];
} else {
    $githubToken = getenv('GITHUB_TOKEN');
}

$guzzleConfig = array(
    'base_url' => 'https://api.github.com',
);

if (strlen($githubToken) > 0) {
    $guzzleConfig['defaults'] = array('auth' => array(null, $githubToken));
}

$client = new GuzzleHttp\Client($guzzleConfig);

$app->get('/', function() use($app, $client, $issuesIds) {

    $issues = array();

    foreach ($issuesIds as $issuePath) {
        $issues[$issuePath] = $client->get('repos/' . $issuePath)->json();
    }

    $sortByClosedAt = function($issueA, $issueB) {
        return $issueA['closed_at'] < $issueB['closed_at'];
    };

    $sortByCreatedAt = function($issueA, $issueB) {
        return $issueA['created_at'] < $issueB['created_at'];
    };

    $closedIssues = array();

    foreach ($issues as $issueId => $issue) {

        if ($issue['state'] != 'closed') {
            continue;
        }
        $closedIssues[$issueId] =  $issue;
    }

    uasort($issues, $sortByCreatedAt);
    uasort($closedIssues, $sortByClosedAt);

    return $app['twig']->render('index.twig', array(
      'issues' => $issues,
      'closed_issues' => $closedIssues,
      'last_issues'        => array_slice($issues, 0, 5),
      'last_closed_issues' => array_slice($closedIssues, 0, 5),
    ));
});

$app->run();
