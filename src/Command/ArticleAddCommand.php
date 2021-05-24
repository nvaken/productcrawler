<?php

namespace App\Command;

use App\Entity\Article;
use App\Scrapers\ArticleScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArticleAddCommand extends Command {
     protected static $defaultName = 'article:add';

    /**
     * @var ArticleScraper
     */
    private $articleScraper;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ArticleAddCommand constructor.
     * @param ArticleScraper $articleScraper
     * @param EntityManagerInterface $em
     */
    public function __construct(ArticleScraper $articleScraper, EntityManagerInterface $em)
     {
         $this->articleScraper = $articleScraper;
         $this->em = $em;

         parent::__construct(self::$defaultName);
     }

    protected function configure()
     {
         $this->setDescription('Adds a new product to watch.')
            ->addArgument('url', InputArgument::REQUIRED, 'The URL to fetch the product from.');

         parent::configure();
     }

     protected function execute(InputInterface $input, OutputInterface $output): int
     {
         $io = new SymfonyStyle($input, $output);
         $url = $input->getArgument('url');

         $this->articleScraper->setUrl($url);
         $article = $this->articleScraper->fetchArticle();

         if ($article === NULL) {
             $io->error('No article found');
             return Command::FAILURE;
         }

         $articleRepository = $this->em->getRepository(Article::class);
         if ($articleRepository->findBy(['url' => $article->getUrl()])) {
             $io->error('Already in database');
             return Command::FAILURE;
         }

         if (!$article) {
             $io->error('No article found');
             return Command::FAILURE;
         }

         $io->title('Found the following article');
         $rows = [];
         foreach ($article->toTabular() as $key => $value) {
             $rows[] = [$key, $value];
         }
         $io->table(['Property', 'Value'], $rows);

         if (!$io->confirm('Do you want to add the article to the watchlist?')){
             $io->warning('Article not added.');
             return Command::FAILURE;
         }

         $this->em->persist($article);
         $this->em->flush();

         return Command::SUCCESS;
     }
}
