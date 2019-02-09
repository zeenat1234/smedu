<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\MonthAccountService;

class SmeduCalculateMonthTotalsCommand extends Command
{
    protected static $defaultName = 'smedu:calculate-month-totals';
    protected $monthAccountService;

    public function __construct(MonthAccountService $monthAccountService)
    {
        parent::__construct(self::$defaultName);
        $this->monthAccountService = $monthAccountService;
    }

    protected function configure()
    {
        $this->setDescription('Calculate total price for each month account');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        
        $message = $this->monthAccountService->calculateMonthTotal();

        $io->block($message);
        $io->success('Success! The totals have been recalculated!');
    }
}
