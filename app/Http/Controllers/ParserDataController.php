<?php

namespace App\Http\Controllers;

use App\Repositories\ParserRepository;

class ParserDataController extends Controller
{
    /** @var ParserRepository $parserRepository */
    protected $parserRepository;

    /**
     * ParserDataController constructor.
     */
    public function __construct()
    {
        $this->parserRepository = app(ParserRepository::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parserData = $this->parserRepository->getData();

        return view('welcome', ['parserData' => $parserData]);
    }
}
