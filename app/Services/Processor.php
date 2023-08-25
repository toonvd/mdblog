<?php

namespace App\Services;

use DateTime;
use Icamys\SitemapGenerator\SitemapGenerator;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

class Processor
{
    public function __construct(private readonly CommonMarkConverter $mdConverter)
    {
    }

    /**
     * @throws CommonMarkException
     */
    public function getHtmlFromMd(string $markdown): string
    {
        return $this->mdConverter->convert($markdown)->getContent();
    }

    public function generatePostImage(FilesystemAdapter $disk, string $basename): void
    {
        $fileName = sprintf('%s.jpg', $basename);
        if (! $disk->exists($fileName)) {
            $imageBaseUrl = 'https://source.unsplash.com/random/185x185/?code,programming';
            $imageHeaders = @get_headers($imageBaseUrl, 1);
            $image = file_get_contents($imageHeaders['Location']);
            $disk->put($fileName, $image);
        }
    }

    public function generateBlogPost(
        array $disks,
        array $post
    ): void {
        $fileName = sprintf('%s.html', $post['basename']);
        $post = $this->wrapKeysForTemplateVars($post);
        $skeleton = $disks['templates']->read('blogPost.html');
        $disks['target']->put(
            $fileName,
            str_replace(array_keys($post), array_values($post), $skeleton)
        );
    }

    public function generateIndex(array $disks, array $posts, bool $shouldEncode = true): void
    {
        $indexSkeleton = $disks['templates']->read('index.html');
        $blogListSkeleton = $disks['templates']->read('indexBlogPost.html');
        $blogListHtml = [];
        foreach ($posts as $post) {
            $post['summary'] = $this->generateSummaryFromHtml($post['html']);
            $post = $this->wrapKeysForTemplateVars($post);
            $postHtml = str_replace(array_keys($post), array_values($post), $blogListSkeleton);
            $blogListHtml[] = $postHtml;
        }

        $blogListHtml = match ($shouldEncode) {
            true => addslashes(json_encode($blogListHtml)),
            false => implode('\n', $blogListHtml)
        };

        $disks['target']->put(
            'index.html',
            str_replace('{{blogList}}', $blogListHtml, $indexSkeleton)
        );
    }

    public function generateSummaryFromHtml(string $html): string
    {
        preg_match('/^(.*?<\/p>)/ms', $html, $matches);

        return $matches[1];
    }

    public function generateSitemap(FilesystemAdapter $disk, array $posts): void
    {
        $sitemapGenerator = new SitemapGenerator('', $disk->path(''));
        foreach ($posts as $post) {
            $sitemapGenerator->addURL($post['url'], new DateTime(), 'always', 0.5);
        }
        $sitemapGenerator->flush();
        $sitemapGenerator->finalize();
        $sitemapGenerator->updateRobots();
        $sitemapGenerator->submitSitemap();
    }

    private function wrapKeysForTemplateVars(array $vars): array
    {
        return Arr::mapWithKeys($vars, function (string $value, string $key) {
            return [sprintf('{{%s}}', $key) => $value];
        });
    }
}
