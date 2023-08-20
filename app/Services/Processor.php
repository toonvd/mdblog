<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;

class Processor
{

    public function __construct(private readonly CommonMarkConverter $mdConverter)
    {
    }

    /**
     * @param string $markdown
     * @return string
     * @throws CommonMarkException
     */
    public function getHtmlFromMd(string $markdown): string
    {
        return $this->mdConverter->convert($markdown)->getContent();
    }

    /**
     * @param FilesystemAdapter $disk
     * @param string $basename
     * @return void
     */
    public function generatePostImage(FilesystemAdapter $disk, string $basename): void
    {
        $fileName = sprintf('%s.jpg', $basename);
        if (!$disk->exists($fileName)) {
            $imageBaseUrl = 'https://source.unsplash.com/random/185x185/?code,programming';
            $imageHeaders = @get_headers($imageBaseUrl, 1);
            $image = file_get_contents($imageHeaders['Location']);
            $disk->put($fileName, $image);
        }
    }

    /**
     * @param array $disks
     * @param string $basename
     * @param array $vars
     * @param string $html
     * @return void
     */
    public function generateBlogPost(
        array $disks,
        string $basename,
        array $vars,
        string $html
    ): void {
        $vars['contents'] = $html;
        $vars = $this->wrapKeysForTemplateVars($vars);
        $skeleton = $disks['templates']->read('blogPost.html');
        $disks['target']->put(
            sprintf('%s.html', $basename),
            str_replace(array_keys($vars), array_values($vars), $skeleton)
        );
    }

    /**
     * @param array $vars
     * @return array
     */
    private function wrapKeysForTemplateVars(array $vars): array
    {
        return Arr::mapWithKeys($vars, function (string $value, string $key) {
            return [sprintf('{{%s}}', $key) => $value];
        });
    }
}
