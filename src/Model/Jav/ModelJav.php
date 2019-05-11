<?php

namespace XGallery\Model\Jav;

use Doctrine\DBAL\DBALException;
use XGallery\Model\BaseModel;
use XGallery\Traits\HasLogger;

/**
 * Class JavModel
 * @package XGallery\Model\Jav
 */
class ModelJav extends BaseModel
{
    use HasLogger;

    /**
     * Insert idol
     *
     * @param $model
     * @return boolean|integer|string
     * @throws \Exception
     */
    public function insertModel($model)
    {
        $id   = $this->getIdFrom('xgallery_jav_idols', ['xid' => $model->xid, 'name' => $model->name]);
        $vars = get_object_vars($model);

        if ($id) {
            $this->logNotice('Found duplicated', $vars);

            return $id;

        }

        unset($vars['favorite']);

        if (!$this->insert('xgallery_jav_idols', $vars)) {
            $this->logWarning('Can not insert model', $vars);

            return false;
        }

        return $this->connection->lastInsertId();
    }

    /**
     * insertFilm
     *
     * @param object  $film
     * @param integer $modelId
     * @param string  $type
     * @return boolean
     * @throws DBALException
     */
    public function insertFilm($film, $modelId, $type = 'xcity')
    {
        $filmId = $this->getIdFrom('xgallery_jav_movies', ['xid' => $film->xid, 'name' => $film->name]);

        if (!$filmId) {
            $this->insert('xgallery_jav_movies', [
                'xid' => $film->xid,
                'name' => $film->name,
                'sales_date' => isset($film->sales_date) ? $film->sales_date : null,
                'release_date' => isset($film->release_date) ? $film->release_date : null,
                'item_number' => $film->item_number,
                'description' => isset($film->description) ? $film->description : null,
                'time' => $film->time,
            ]);

            $filmId = $this->connection->lastInsertId();
        }

        $this->insert('xgallery_jav_movie_casts_xref',
            ['movie_id' => $filmId, 'cast_id' => $modelId, 'source' => $type]
        );

        if (!empty($film->genres)) {
            $this->insertMovieGenres($film->genres, $filmId);
        }

        // Label
        $labelId = $this->getIdFrom('xgallery_jav_movie_label', ['name' => $film->label]);

        if (!$labelId && $film->label !== null && !empty($film->label)) {
            $this->insert('xgallery_jav_movie_label', ['name' => $film->label]);
            $labelId = $this->connection->lastInsertId();
        }

        if ($labelId) {
            $this->insertMovieXref(['movie_id' => $filmId, 'xref_id' => (int)$labelId, 'xref_type' => 'label']);
        }

        // Marker
        $markerId = $this->getIdFrom('xgallery_jav_movie_marker', ['name' => $film->marker]);

        if (!$markerId && $film->marker !== null && !empty($film->marker)) {
            $this->insert('xgallery_jav_movie_marker', ['name' => $film->marker]);
            $markerId = $this->connection->lastInsertId();
        }

        if ($markerId) {
            $this->insertMovieXref(['movie_id' => $filmId, 'xref_id' => (int)$markerId, 'xref_type' => 'maker']);
        }
    }

    private function insertMovieGenres($genres, $filmId)
    {
        foreach ($genres as $genre) {
            $genreId = $this->getIdFrom('xgallery_jav_movie_geners', ['name' => $genre]);

            if (!$genreId) {
                $this->connection->insert('xgallery_jav_movie_geners', ['name' => $genre]);
                $genreId = $this->connection->lastInsertId();
            }

            $this->insertMovieXref(['movie_id' => $filmId, 'xref_id' => (int)$genreId, 'xref_type' => 'genre']);
        }
    }

    /**
     * insertMovieXref
     * @param $data
     * @return bool|int
     * @throws \Exception
     */
    private function insertMovieXref($data)
    {
        return $this->insert('xgallery_jav_movie_xref', $data);
    }
}
