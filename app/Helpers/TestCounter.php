<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class TestCounter
{
    public static function count(): int
    {
        return Cache::remember('test_count', 3600, function () {
            $testsPath = base_path('tests');
            $count = 0;

            if (! is_dir($testsPath)) {
                return 0;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($testsPath)
            );
            $phpFiles = new RegexIterator($iterator, '/Test\.php$/');

            foreach ($phpFiles as $file) {
                $content = file_get_contents($file->getPathname());
                preg_match_all('/public function test_/', $content, $matches);
                $count += count($matches[0]);
            }

            return $count;
        });
    }
}
