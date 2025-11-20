<?php

namespace App\Command;

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Dumper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:book-client',
    description: 'Testing book client command.',
)]
final class BookClientCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $bookClient,
        #[Autowire(service: 'psr18.book.client')]
        private readonly ClientInterface $psr18BookClient,
    )
    {
        parent::__construct();
    }

    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Argument(suggestedValues: ['/new', '/books/9781484206485'])] ?string $path = null,
        #[Option] ?bool $psr18 = null,
    ): int
    {
        $path ??= '/new';
        $psr18 ??= false;

        $io = new SymfonyStyle($input, $output);
        $dump = new Dumper($output);

        if ($psr18) {
            $response = $this->psr18BookClient->sendRequest(new Request('GET', '/1.0'.$path));
            $io->write($dump($response->getBody()->getContents()));

            return Command::SUCCESS;
        }

        $response = $this->bookClient->request('GET', '/1.0'.$path);
        $io->write($dump($response->toArray()));

        return Command::SUCCESS;
    }
}
