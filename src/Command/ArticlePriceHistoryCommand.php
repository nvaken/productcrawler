<?php

namespace App\Command;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArticlePriceHistoryCommand extends Command
{
    use ArticleChoiceQuestionTrait;

    protected static $defaultName = 'article:price-history';
    protected static $defaultDescription = 'Show price history for defined product.';
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('article-id', InputArgument::OPTIONAL, 'The article ID to show, leave empty for interactive selection.');
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $articleId = $input->getArgument('article-id');
        if (!$articleId) {
            $question = $this->getArticleChoiceQuestion($this->em);
            $helper = $this->getHelper('question');
            $articleId = $helper->ask($input, $output, $question);
        }

        /** @var Article $article */
        $article = $this->em->find(Article::class, $articleId);

        $prices = $article->getArticlePriceEntries();
        $rows = [];

        foreach ($prices as $price) {
            $rows[] = [$price->getCreated()->format('d M Y - H:i:s'), $price->getPrice(), $price->getPriceCurrency()];
        }

        $io->table(['Date', 'Price', 'Currency'], $rows);

        return Command::SUCCESS;
    }
}
