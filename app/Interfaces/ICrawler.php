<?php

namespace App\Interfaces;

interface ICrawler
{
    /**
     * @param array $params
     */
    public function setup(array $params);

    /**
     * Run crawler -example- php artisan crawler:get http://gadget-it.ru --depth=2 --max_pages=5
     */
    public function run();
}