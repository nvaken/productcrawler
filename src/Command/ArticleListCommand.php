<?php

namespace App\Command;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArticleListCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'article:list';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct(self::$defaultName);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $articleRepository = $this->em->getRepository(Article::class);
        $articles = $articleRepository->findAll();

        if (!$articles) {
            $io->error('No articles found in database.');
            return Command::FAILURE;
        }

        $io->title('Found the following articles');
        $rows = [];

        /** @var Article $article */
        foreach ($articles as $article) {
            $priceHigh = $article->getPriceHigh();
            $priceLow = $article->getPriceLow();
            $priceCurrent = $article->getArticlePriceEntries()->last();

            $rows[] = [
                $article->getName(),
                $article->getUrl(),
                $priceCurrent->getPrice() . ' ' . $priceCurrent->getPriceCurrency() . ' (' . $priceCurrent->getCreated()->format('m-d-Y') . ')',
                $priceHigh->getPrice() . ' ' . $priceHigh->getPriceCurrency() . ' (' . $priceHigh->getCreated()->format('m-d-Y') . ')',
                $priceLow->getPrice() . ' ' . $priceLow->getPriceCurrency() . ' (' . $priceLow->getCreated()->format('m-d-Y') . ')',
            ];
            $rows[] = [
                '',
                '',
                '',
                ''
            ];
//            $rows[] = [' ', ' ', ' ', ' '];

        }
        $io->table(['Article', 'URL', 'Latest', 'High', 'Low'], $rows);

        return Command::SUCCESS;
    }

}

