<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use App\Services\Collector;
use App\Services\Processor;
use League\CommonMark\Exception\CommonMarkException;

class GenerateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate:blog {--source=} {--target=} {--base_url=} {--image_path=} {--template_path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generates a blog from markdown files.';

    /**
     * @var array|string[]
     */
    private array $requiredParams = [
        'source',
        'target',
        'base_url',
        'image_path',
        'template_path'
    ];

    /**
     * @var array
     */
    private array $disks = [];

    /**
     * @var array
     */
    private array $postUrls = [];

    /**
     * @var array
     */
    private array $postSummaryData = [];

    public function __construct(private readonly Collector $collector, private readonly Processor $processor)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws CommonMarkException
     */
    public function handle(): void
    {
        if (!$this->hasValidParams()) {
            return;
        }

        $disks = $this->collector->getDisks($this->options());

        foreach ($disks['source']->files() as $file) {
            $basename = basename($file, '.md');
            $this->postUrls[] = sprintf('%s.html', $basename);
            $html = $this->processor->getHtmlFromMd($disks['source']->get($file));
            $this->processor->generatePostImage($disks['images'], $basename);

            $this->postSummaryData[$basename] = [
                'title' => ucfirst(str_replace('_', ' ', $basename)),
                'image' => sprintf(
                    '%s/%s/%s.jpg',
                    $this->option('base_url'),
                    basename($this->option('image_path')),
                    $basename
                ),
                'url' => sprintf('%s/%s.html', $this->option('base_url'), $basename)
            ];

            $this->processor->generateBlogPost(
                $disks,
                $basename,
                $this->postSummaryData[$basename],
                $html
            );
        }
    }

    /**
     * @return bool
     */
    private function hasValidParams(): bool
    {
        $missingParams = array_diff($this->requiredParams, array_keys(array_filter($this->options())));
        // get required fields that aren't filled and error
        if (!empty($missingParams)) {
            $this->error(
                sprintf('Missing required params: %s. Please run `generate:blog -h`.', implode(', ', $missingParams))
            );
            return false;
        }

        return true;
    }
}
