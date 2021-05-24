<?php

namespace App\Command;

use App\Console\ChoiceQuestion;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;

trait ArticleChoiceQuestionTrait {

    /**
     * Returns a ChoiceQuestion for selecting Articles.
     *
     * @param EntityManagerInterface $em
     * @return ChoiceQuestion
     */
    protected function getArticleChoiceQuestion (EntityManagerInterface $em):ChoiceQuestion  {

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository(Article::class);
        $articleNames = $articleRepository->findArticleNames();
        $articleOptions = [];
        foreach ($articleNames as $articleName) {
            $articleOptions[$articleName['id']] = $articleName['name'] . ' (' . $articleName['id'] . ')';
        }

        return new ChoiceQuestion('Select article', $articleOptions, TRUE);
    }
}
