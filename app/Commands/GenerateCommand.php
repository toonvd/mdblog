<?php

namespace App\Commands;

use App\Services\Collector;
use App\Services\Processor;
use LaravelZero\Framework\Commands\Command;
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
        'template_path',
    ];

    private array $disks = [];

    private array $postUrls = [];

    private array $postSummaryData = [];

    public function __construct(private readonly Collector $collector, private readonly Processor $processor)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws CommonMarkException
     */
    public function handle(): void
    {
        if (! $this->hasValidParams()) {
            return;
        }

        $disks = $this->collector->getDisks($this->options());

        $postData = $this->collector->getBlogPosts($disks, $this->options(), $this->processor);

        foreach ($postData as $post) {
            $this->processor->generateBlogPost($disks, $post);
        }

        $this->processor->generateIndex($disks, $postData);
        $this->processor->generateSitemap($disks['target'], $postData);
    }

    private function hasValidParams(): bool
    {
        $missingParams = array_diff($this->requiredParams, array_keys(array_filter($this->options())));
        // get required fields that aren't filled and error
        if (! empty($missingParams)) {
            $this->error(
                sprintf('Missing required params: %s. Please run `generate:blog -h`.', implode(', ', $missingParams))
            );

            return false;
        }

        return true;
    }
}
