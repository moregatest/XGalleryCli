<?php

namespace XGallery\Applications\Cli\Commands\Jav;

use ReflectionException;
use Symfony\Component\Console\Helper\ProgressBar;
use XGallery\Applications\Cli\Commands\AbstractCommandJav;

/**
 * Class Movies
 * @package XGallery\Applications\Cli\Commands\Jav
 */
class Movies extends AbstractCommandJav
{
    /**
     * @var array
     */
    private $filmLinks;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Fetch contact from Flickr');
        $this->options = [
            'profileLink' => [],
        ];

        parent::configure();
    }

    protected function prepareFilms()
    {
        $profileLink = $this->getOption('profileLink');

        if (!$profileLink) {
            return self::PREPARE_FAILED;
        }

        $this->filmLinks = $this->xcity->getProfileFilmLinks($profileLink);

        return self::PREPARE_SUCCEED;
    }

    protected function processMovies()
    {
        $this->output->write("\n");
        $profileLink = $this->getOption('profileLink');

        $progressBar = new ProgressBar($this->output, count($this->filmLinks));
        $progressBar->setFormat("%profile%: %film%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $progressBar->setMessage($profileLink, 'profile');
        $progressBar->setMaxSteps(count($this->filmLinks));

        foreach ($this->filmLinks as $index => $filmLink) {
            $film = $this->xcity->getFilm($filmLink);

            $progressBar->setMessage($filmLink, 'film');

            $modelXId = explode('/', trim($profileLink, '/'));
            $this->model->insertFilm($film, end($modelXId), 'xcity');
            $progressBar->advance();
        }

        return true;
    }
}
