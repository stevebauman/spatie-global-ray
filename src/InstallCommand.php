<?php

namespace Stevebauman\SpatieGlobalRay;

use TitasGailius\Terminal\Terminal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Install Spatie Ray globally.');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Before installing, we will make sure to clear the
        // option inside of the ini so we can safely
        // replace the phar without PHP crashing.
        $this->updateAutoPrependFileOptionInPhpIni(null);

        if (! file_exists($this->getPharPath())) {
            $this->generateSpatieRayPhar($output);
        }

        $this->updateAutoPrependFileOptionInPhpIni(
            $this->getPharPath()
        );
        
        return static::SUCCESS;
    }

    /**
     * Generate the Spatie Ray phar.
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function generateSpatieRayPhar(OutputInterface $output)
    {
        $result = Terminal::builder()
            ->output($output)
            ->in('generator')
            ->run('composer install && composer build');

        if (! $result->successful()) {
            throw new \Exception($result->output());
        }
    }
    
    /**
     * Update the auto prepend file option in the PHP ini.
     *
     * @param string|null $value
     *
     * @return void
     */
    protected function updateAutoPrependFileOptionInPhpIni($value = null)
    {
        $iniPath = get_cfg_var('cfg_file_path');

        $contents = file_get_contents($iniPath);

        $option = "auto_prepend_file = {$value}\n";

        if ($line = $this->findOptionInPhpIni($iniPath, 'auto_prepend_file')) {
            $contents = str_replace($line, $option, $contents);
        } else {
            $contents = $option . $contents;
        }
    
        file_put_contents($iniPath, $contents);
    }

    /**
     * Get the generated ray phar path.
     *
     * @return string
     */
    protected function getPharPath()
    {
        return __DIR__ . '/../generator/phar/ray.phar';
    }

    /**
     * FInd the option in the given ini.
     *
     * @param string $iniPath
     * @param string $option
     *
     * @return string|false
     */
    protected function findOptionInPhpIni($iniPath, $option)
    {
        $lines = file($iniPath);

        foreach ($lines as $line) {
            if (strpos($line, $option) !== false) {
                return $line;
            }
        }

        return false;
    }
}
