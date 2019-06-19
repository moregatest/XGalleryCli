<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler\Books;

use App\Service\HttpClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class SachxuaCrawler
 * @package App\Service
 */
class SachxuaCrawler extends HttpClient
{
    /**
     * HttpClient constructor.
     * @param array $options
     */
    public function __construct($options = [])
    {
        parent::__construct(
            [
                'headers' => [
                    'X-Auth-Token' => 'XVNhO3zub5rfPP6brIPW6NHU0+mNtdx1mdFxF32tWERw+3byApB+TS6sEZsXumblE+aOHZLDNBc6IUQcIjw/wA==',
                ],
            ]
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getBooksByPages()
    {
        $list     = [];
        $index    = 0;
        $pageSize = 12;

        do {
            $data = $this->get(
                'https://api.sachxua.info/api/books/paging/' . $index . '/' . $pageSize,
                ['connect_timeout' => 1, 'read_timeout' => 1, 'timeout' => 1]
            );

            if (!$data) {
                $index++;
                continue;
            }

            foreach ($data as $book) {
                foreach ($book->Authors as $author) {
                    $list['authors'][] = $author;
                }
                $list['books'][$book->Id] = $this->getBook($book->Id);
            }

            $index++;

        } while (!empty($data));


    }

    public function getBook($id)
    {
        return $this->get('https://api.sachxua.info/api/books/' . $id);
    }
}
