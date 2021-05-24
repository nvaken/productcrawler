<?php

namespace App\Command;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ArticleRemoveCommand extends Command
{
    use ArticleChoiceQuestionTrait;

    protected static $defaultName = 'article:remove';
    protected static $defaultDescription = 'Remove the article from the watchlist.';
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('article-id', InputArgument::OPTIONAL, 'The article ID.');
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

        $this->em->remove($article);
        $this->em->flush();

        $io->info('Article removed');

        return Command::SUCCESS;
    }

}
