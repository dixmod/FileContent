<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Sort extends Command
{
    /** @var InputInterface */
    private $input;

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName('sort')
            ->setDescription('This command sort content file')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Who do you want to sort?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $inputPathFile = $this->input->getArgument('path');
        try {
            // Open source file
            $countsChars = $this->getCountsCharsInFile($inputPathFile);

            // Sorting result
            ksort($countsChars);

            // Write result file
            $this->writeSortingFile($inputPathFile . '.sort', $countsChars);

            $this->output->writeln('<info>Done</info>');
        } catch (\Exception $exception) {
            $this->output->writeln('<error>' . $exception->getMessage() . '</error>');
        }
    }

    /**
     * @param string $inputPathFile
     * @return bool|resource
     */
    private function openFileForRead(string $inputPathFile)
    {
        if (!is_file($inputPathFile)) {
            return false;
        }

        return fopen($inputPathFile, 'rb');
    }

    /**
     * @param string $inputPathFile
     * @return bool|resource
     */
    private function openFileForWrite(string $inputPathFile)
    {
        return fopen($inputPathFile, 'wb');
    }

    /**
     * @param string $inputPathFile
     * @return array|bool
     * @throws \Exception
     */
    private function getCountsCharsInFile(string $inputPathFile)
    {
        if (false === ($fileHandler = $this->openFileForRead($inputPathFile))) {
            throw new \Exception('Invalid open file: ' . $inputPathFile);
        }

        $countsChars = [];
        while (false !== ($char = fgetc($fileHandler))) {
            $charCode = \mb_ord($char, 'UTF-8');
            if (isset($countsChars[$charCode])) {
                ++$countsChars[$charCode];
            } else {
                $countsChars[$charCode] = 1;
            }
        }

        fclose($fileHandler);

        return $countsChars;
    }


    /**
     * @param string $outputPathFile
     * @param array $countsChars
     * @return bool
     * @throws \Exception
     */
    private function writeSortingFile(string $outputPathFile, array $countsChars): bool
    {
        if (false === ($fileHandler = $this->openFileForWrite($outputPathFile))) {
            throw new \Exception('Invalid write result file');
        }

        foreach ($countsChars as $charCode => $count) {
            fwrite($fileHandler, str_repeat(\chr($charCode), $count));
        }

        fclose($fileHandler);

        return true;
    }
}
