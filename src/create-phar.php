<?php
$pharFile = 'Tai.phar';

// Clean up
if (file_exists($pharFile)) {
    unlink($pharFile);
}
if (file_exists($pharFile . '.gz')) {
    unlink($pharFile . '.gz');
}

// Create a new Phar file
$phar = new Phar($pharFile);

// Start buffering. Mandatory to modify stub.
$phar->startBuffering();

// Adding files to the Phar archive
$phar->buildFromDirectory(__DIR__, '/\.php$/');
$phar->addFile('config.ini');

// Set the default stub
$defaultStub = $phar->createDefaultStub('index.php');
$stub = "#!/usr/bin/env php\n" . $defaultStub;
$phar->setStub($stub);

// Stop buffering
$phar->stopBuffering();

// Plus - compressing it into gzip
$phar->compressFiles(Phar::GZ);

// Make it executable
chmod($pharFile, 0770);

echo "'$pharFile' successfully created";