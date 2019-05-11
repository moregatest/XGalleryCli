<?php

namespace XGallery\Applications\Cli\Commands\Jav;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use ReflectionException;
use Symfony\Component\Console\Helper\ProgressBar;
use XGallery\Applications\Cli\Commands\AbstractCommandJav;
use XGallery\Utilities\SystemHelper;

/**
 * Class Crawler
 * @package XGallery\Applications\Cli\Commands\Jav
 */
class Crawler extends AbstractCommandJav
{
    /**
     * @var array
     */
    private $profileLinks = [];

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->options = [
            'skip_profiles' => ['default' => 0],
            'from' => ['default' => 0],
            'to' => ['default' => 0],
            'max_processes' => ['default', 50],
        ];

        parent::configure();
    }

    /**
     * prepareProfiles
     *
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function prepareProfiles()
    {
        $this->profileLinks = $this->xcity->getProfiles();

        return self::PREPARE_SUCCEED;
    }

    /**
     * processProfiles
     *
     * @return boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function processProfiles()
    {
        if (empty($this->profileLinks) || (int)$this->getOption('skip_profiles') === 1) {
            return self::SKIP_PREPARE;
        }

        $this->output->writeln('');
        $progressBar = new ProgressBar($this->output, count($this->profileLinks));
        $progressBar->setFormat("%profileLink%: %profile%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $progressBar->start();

        foreach ($this->profileLinks as $profileLink) {
            $progressBar->setMessage($profileLink, 'profileLink');
            $profile = $this->xcity->getProfile($profileLink);
            $progressBar->setMessage($profile->name, 'profile');
            $this->model->insertModel($profile);
            $progressBar->advance();
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * processMovies
     */
    protected function processMovies()
    {
        $this->output->writeln('');

        $progressBar = $this->getProgressBar();
        $progressBar->setFormat("%profile%\n%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%");
        $progressBar->setMaxSteps(count($this->profileLinks));

        $maxProcess   = $this->getOption('max_processes', 50);
        $countProcess = 0;

        foreach ($this->profileLinks as $profileIndex => $profileLink) {
            $progressBar->setMessage($profileLink, 'profile');
            $to = (int)$this->getOption('to');

            if ($profileIndex < (int)$this->getOption('from') || ($to > 0 && $profileIndex > (int)$this->getOption('to'))) {
                $progressBar->advance();
                continue;
            }

            $processes[$countProcess] = SystemHelper::getProcess([
                'php',
                XGALLERY_ROOT.'/cli.php',
                'jav:movies',
                '--profileLink='.$profileLink,
            ]);

            $processes[$countProcess]->start();

            $countProcess++;

            if ($countProcess === $maxProcess) {
                foreach ($processes as $process) {
                    $process->wait();
                }

                $countProcess = 0;
            }

            $progressBar->advance();
        }

        return true;
    }
}
