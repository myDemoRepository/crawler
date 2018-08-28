<?php

namespace App\Console\Commands;

use App\Interfaces\ICrawler;
use App\Repositories\CrawlerRepository;
use Illuminate\Console\Command;


class Crawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:get {domain} {--depth=} {--max_pages=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Super Crawler for image tags counting';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $params = [
            'domain' => $this->argument('domain'),
            'depth' => $this->option('depth'),
            'max_pages' => $this->option('max_pages'),
        ];

        /** @var ICrawler $crawlerRepository */
        $crawlerRepository = app(CrawlerRepository::class);
        $crawlerRepository->setup($params);
        $crawlerRepository->run();
    }
}
