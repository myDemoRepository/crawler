<?php

namespace App\Repositories;

use App\Models\ParserData;
use Illuminate\Support\Facades\Validator;

class ParserRepository
{
    /**
     * @param array $params
     */
    public function saveData(array $params)
    {
        if ($this->validateParams($params)) {
            $parserData = new ParserData();
            $parserData->url = $params['url'];
            $parserData->img_tag_count = $params['img_tag_count'];
            $parserData->page_processing_time = $params['page_processing_time'];
            $parserData->save();
        }
    }

    /**
     * Get parser info
     *
     * return array
     */
    public function getData(): array
    {
        $parserData = ParserData::all()
            ->sortBy('img_tag_count', SORT_DESC)
            ->transform(function ($item) {
                $data = new \stdClass();
                $data->url = $item->url;
                $data->imgCount = $item->img_tag_count;
                $data->pageProcessTime = $item->page_processing_time;

                return $data;
            })
            ->toArray();

        return $parserData;
    }

    /**
     * Validate params
     *
     * @param array $params
     *
     * @return mixed
     */
    protected function validateParams(array $params): bool
    {
        $result = Validator::make(
            $params,
            [
                'url' => 'required|url',
                'img_tag_count' => 'required|integer',
                'page_processing_time' => 'required|numeric|min:0.01',
            ]
        );

        return $result->passes();
    }
}
