<?php
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

function number_abbr($number)
{
    if (isset($number)) {
        $abbrevs = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];

        foreach ($abbrevs as $exponent => $abbrev) {
            if (abs($number) >= pow(10, $exponent)) {
                $display = $number / pow(10, $exponent);
                $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                $number = number_format($display, $decimals).$abbrev;
                break;
            }
        }
    
        return $number;
    } else {
        return 0;
    }
}

function extractZip(string $filePath, string $tempPath) {
    $zip = new ZipArchive;
    $opened = $zip->open($filePath);
    if ($opened !== TRUE) {
        throw new PptxFileException( 'Could not open zip archive ' . $filePath . '[' . $opened . ']' );
    }
    $zip->extractTo($tempPath);
    $zip->close();
}

function compressZip(string $saveLocation, string $archiveLocation) {
    //Create a pptx file again
    $zip = new ZipArchive;

    $opened = $zip->open($saveLocation, ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE);
    if ($opened !== true) {
        throw new PptxFileException( 'Cannot open zip: ' . $saveLocation . ' [' . $opened . ']' );
    }

    // Create recursive directory iterator
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archiveLocation), RecursiveIteratorIterator::LEAVES_ONLY);

    foreach($files as $name => $file) {
        $filePath = $file->getRealPath();
        if (in_array($file->getFilename(), array('.', '..'))) {
            continue;
        }
        if (!file_exists($filePath)) {
            throw new PptxFileException( 'File does not exists: ' . $file->getPathname() );
        } else {
            if (!is_readable($filePath)) {
                throw new PptxFileException( 'File is not readable: ' . $file->getPathname() );
            } else {
                if (!$zip->addFile($filePath, substr($file->getPathname(), strlen($archiveLocation) + 1))) {
                    throw new PptxFileException( 'Error adding file: ' . $file->getPathname() );
                }
            }
        }
    }
    if (!$zip->close()) {
        throw new PptxFileException( 'Could not create zip file' );
    }
}

function rmdirRecursive($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach($files as $file) {
        (is_dir("$dir/$file")) ? rmdirRecursive("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}