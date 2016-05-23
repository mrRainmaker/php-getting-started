<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register the Twig templating engine
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));

// Register the Postgres database add-on
$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Herrera\Pdo\PdoServiceProvider(),
  array(
    'pdo.dsn' => 'pgsql:dbname='.ltrim($dbopts["dacqc1kplgok5f"],'/').';host='.$dbopts["ec2-54-243-202-113.compute-1.amazonaws.com"],
    'pdo.port' => $dbopts["5432"],
    'pdo.username' => $dbopts["kuzrdpmzrwfkum"],
    'pdo.password' => $dbopts["hyyrZHM7CA5zpGvpJ5PkBg2aNl"]
  )
);

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return 'Hello';
});

$app->get('/db/', function() use($app) {
  $st = $app['pdo']->prepare('SELECT * FROM votes');
  $st->execute();

  $names = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $app['monolog']->addDebug('Row ' . $row['name']);
    $names[] = $row;
  }

  return $app['twig']->render('database.twig', array(
    'names' => $names
  ));
});

$app->get('/twig/{name}', function($name) use($app) {
  return $app['twig']->render('index.twig', array(
    'name' => $name,
  ));
});


$app->run();

?>
