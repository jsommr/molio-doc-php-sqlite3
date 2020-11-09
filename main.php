<?php

// Run from console
//   php main.php > output.db.gz
//
// Run from dev server
//   `php -S localhost:8000` then go to http://localhost:8000/main.php

require 'vendor/autoload.php';
require 'MolioDoc.php';

use Ramsey\Uuid\Uuid;

// SQLite3 throws warning on constraints: Imitate strict error handling like in Laravel
set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Create a temporary file that's deleted upon script end
$dbFile = tmpfile();
$dbPath = stream_get_meta_data($dbFile)['uri'];
$db = new SQLite3($dbPath);

// Load template into db
$db->exec(file_get_contents(__DIR__ . '/template.sql'));

$doc = new MolioDoc($db);

$b = new Bygningsdelsbeskrivelse();
$b->name = "hej";
$b->bygningsdelsbeskrivelse_guid = Uuid::uuid4();
$b->basisbeskrivelse_version_guid = Uuid::uuid4();
$bId = $doc->addBygningsdelsbeskrivelse($b);

$bs1 = new BygningsdelsbeskrivelseSection($bId, 1, 'OMFANG');
$bs1Id = $doc->addBygningsdelsbeskrivelseSection($bs1);

$bs2 = new BygningsdelsbeskrivelseSection($bId, 2, 'ALMENE SPECIFIKATIONER');
$bs2Id = $doc->addBygningsdelsbeskrivelseSection($bs2);

$bs25 = new BygningsdelsbeskrivelseSection($bId, 5, 'Generelt');
$bs25->text = 'Lorem ipsum 123';
$bs25->parentId = $bs2Id;
$bs25Id = $doc->addBygningsdelsbeskrivelseSection($bs25);

$pdf = new Attachment('sample.pdf', 'application/pdf', file_get_contents(__DIR__ . '/sample.pdf'));
$pdfId = $doc->addAttachment($pdf);

$doc->addBygningsdelsbeskrivelseSectionAttachment($bs25Id, $pdfId);

$db->close();

header('Content-Type: application/x-molio-doc-todo');
header('Content-Disposition: attachment; filename="molio.db.gz"');

// Uncomment to have the browser gunzip the database
// header('Content-Encoding: gzip');

$output = fopen('php://output', 'w');
fwrite($output, gzencode(file_get_contents($dbPath), 9));
fclose($output);