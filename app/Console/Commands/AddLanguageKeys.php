<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class AddLanguageKeys extends Command
{
    protected $signature = 'lang:add-keys';
    protected $description = 'Add missing language keys to JSON files';

    public function handle()
    {
        $path = resource_path('views/');
        $langFiles = ['en.json', 'fr.json']; // ajoutez d'autres langues au besoin
        $patterns = ['/{{\s*__\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*}}/'];

        foreach ($langFiles as $file) {
            $langPath = 'lang/' . $file;
            $translations = json_decode(file_get_contents($langPath), true);

            foreach (File::allFiles($path) as $view) {
                $content = file_get_contents($view->getPathname());
                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $key) {
                            if (!array_key_exists($key, $translations)) {
                                $translations[$key] = $key; // ou laissez vide pour remplir plus tard
                            }
                        }
                    }
                }
            }

            file_put_contents($langPath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
