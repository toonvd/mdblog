<?php

namespace App\Services;

use Gitonomy\Git\Repository;
use League\CommonMark\Exception\CommonMarkException;
use Storage;

class Collector
{
    public function getDisks(array $options): array
    {
        return [
            'source' => Storage::build([
                'driver' => 'local',
                'root' => $options['source'],
            ]),
            'target' => Storage::build([
                'driver' => 'local',
                'root' => $options['target'],
            ]),
            'images' => Storage::build([
                'driver' => 'local',
                'root' => $options['image_path'],
            ]),
            'templates' => Storage::build([
                'driver' => 'local',
                'root' => $options['template_path'],
            ]),
        ];
    }

    /**
     * @throws CommonMarkException
     */
    public function getBlogPosts(array $disks, array $options, Processor $processor): array
    {
        $repositoryRoot = $disks['target']->path('');
        $repository = new Repository($repositoryRoot);
        $blogPosts = [];
        foreach ($disks['source']->files() as $file) {
            $basename = basename($file, '.md');
            $html = $processor->getHtmlFromMd($disks['source']->get($file));
            $processor->generatePostImage($disks['images'], $basename);
            $blogPosts[$basename] = [
                'title' => ucfirst(str_replace('_', ' ', $basename)),
                'image' => sprintf(
                    '%s/%s/%s.jpg',
                    $options['base_url'],
                    basename($options['image_path']),
                    $basename
                ),
                'url' => sprintf('%s/%s.html', $options['base_url'], $basename),
                'basename' => $basename,
                'updated_at' => trim($repository->run('log', ['-1', '--pretty=%ci', $disks['source']->path($file)])),
                'html' => $html,
            ];
        }

        return $blogPosts;
    }
}
