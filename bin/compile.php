<?php
namespace ngyuki\Compiler;

use Symfony\Component\Finder\Finder;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(-1);

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

chdir(dirname(__DIR__));

echo "composer into build/vendor\n";
exec('COMPOSER_VENDOR_DIR=build/vendor composer install --no-dev');

$version = trim(`git describe --tags --always`);

if (strlen($version) === 0)
{
    echo "Unable detect version.\n\n\t" . "git describe --tags --always\n";
    exit(1);
}

$phar = 'db-migrate.phar';
$stub = 'bin/compile.stub';

$finders = array();

$finders[] = $finder = new Finder();
$finder->files()->in('src');

$finders[] = $finder = new Finder();
$finder->files()->in('build/vendor')
    ->notName('*.md')
    ->notName('composer.json')
    ->notName('phpunit.xml.dist')
    ->exclude('Tests')
;

if (file_exists($phar))
{
    unlink($phar);
}

$stripWhitespace = function ($source) {

    $output = '';

    foreach (token_get_all($source) as $token)
    {
        if (is_string($token))
        {
            $output .= $token;
        }
        else if (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT)))
        {
            $output .= str_repeat("\n", substr_count($token[1], "\n"));
        }
        elseif (T_WHITESPACE === $token[0])
        {
            // reduce wide spaces
            $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);

            // normalize newlines to \n
            $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);

            // trim leading spaces
            $whitespace = preg_replace('{\n +}', "\n", $whitespace);

            $output .= $whitespace;
        }
        else
        {
            $output .= $token[1];
        }
    }

    return $output;
};

$pharObj = new \Phar($phar, 0);
$pharObj->setSignatureAlgorithm(\Phar::SHA1);
$pharObj->addFromString('version', $version);
$pharObj->setMetadata(array('version' => $version));
$pharObj->startBuffering();

foreach ($finders as $finder)
{
    /** @var $file \SplFileInfo */
    foreach ($finder as $file)
    {
        if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'php')
        {
            $source = file_get_contents($file->getPathname());
            $source = $stripWhitespace($source);
            $pharObj->addFromString($file->getPathname(), $source);
            echo "* " . $file->getPathname() . "\n";
        }
        else
        {
            $pharObj->addFile($file->getPathname());
            echo "  " . $file->getPathname() . "\n";
        }
    }
}

$pharObj->setStub(file_get_contents($stub));
$pharObj->stopBuffering();

unset($pharObj);
chmod($phar, 0777);

$size = filesize($phar);
echo "\nCompiled $phar ... $size Byte\n";
