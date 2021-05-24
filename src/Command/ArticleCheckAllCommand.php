<?php

namespace App\Command;

use App\Entity\Article;
use App\Scrapers\ArticleScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArticleCheckAllCommand extends Command
{
    protected static $defaultName = 'article:check-all';
    protected static $defaultDescription = 'Will check all articles for changes in price.';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ArticleScraper
     */
    private $articleScraper;

    public function __construct(EntityManagerInterface $em, ArticleScraper $articleScraper) {
        $this->em = $em;
        parent::__construct();
        $this->articleScraper = $articleScraper;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Article[] $articles */
        $articles = $this->em->getRepository(Article::class)->findAll();

        foreach($articles as $article) {
            $this->articleScraper->setUrl($article->getUrl());
            $articlePriceEntry = $this->articleScraper->fetchArticlePrice();
            if ($articlePriceEntry === NULL) {
                continue;
            }
            $article->addArticlePriceEntry($articlePriceEntry);
            $this->em->persist($article);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
