<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Dumper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:book-client',
    description: 'Testing book client command.',
)]
final class BookClientCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $bookClient
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(name: 'path', mode: InputArgument::OPTIONAL, description: 'Path', default: '/new')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dump = new Dumper($output);

        $response = $this->bookClient->request('GET', '/1.0'.$input->getArgument('path'));

        $io->write($dump($response->toArray()));

        return Command::SUCCESS;
    }
}
