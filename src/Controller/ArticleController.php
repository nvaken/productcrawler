<?php

namespace App\Controller;

use App\Entity\Article;
use App\Scrapers\ArticleScraper;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ArticleController extends AbstractController
{

  /**
   * @var \Doctrine\ORM\EntityManagerInterface
   */
  private $em;

  public function __construct(EntityManagerInterface $entityManager) {
    $this->em = $entityManager;
  }

  /**
   * @Route("/", name="article_list")
   */
  public function articleList() {
    $articleRepository = $this->em->getRepository(Article::class);

    /** @var Article $article */
    $articles = $articleRepository->findAll();

    $rows = [];
    foreach ($articles as $article) {
      $priceHigh = $article->getPriceHigh();
      $priceLow = $article->getPriceLow();
      $priceCurrent = $article->getArticlePriceEntries()->last();

      $rows[] = [
        'id' => $article->getId(),
        'name' => $article->getName(),
        'shop_url' => $article->getUrl(),
        'price_current' => $priceCurrent->getPrice() . ' ' . $priceCurrent->getPriceCurrency() . ' (' . $priceCurrent->getCreated()->format('m-d-Y') . ')',
        'price_high' => $priceHigh->getPrice() . ' ' . $priceHigh->getPriceCurrency() . ' (' . $priceHigh->getCreated()->format('m-d-Y') . ')',
        'price_low' => $priceLow->getPrice() . ' ' . $priceLow->getPriceCurrency() . ' (' . $priceLow->getCreated()->format('m-d-Y') . ')',
      ];
    }

    return $this->render('articles/list.html.twig', [
      'rows' => $rows,
    ]);
  }

  /**
   * @Route("/article/{id}", name="article", requirements={"id": "\d+"})
   */
  public function articleDetail(ChartBuilderInterface $chartBuilder, int $id): Response
  {

    $articleRepository = $this->em->getRepository(Article::class);

    /** @var Article $article */
    $article = $articleRepository->find($id);

    $currentDate = new DateTime();

    $yearInterval = DateInterval::createFromDateString('1 year');
    $date = clone $currentDate;
    $date->sub($yearInterval);
    $dateLabels = [];
    while ($date < $currentDate) {
      $date->add(DateInterval::createFromDateString('1 day'));
      $dateLabels[$date->format('Y-m-d')] = NULL;
    }

    $data = [
      'labels' => array_keys($dateLabels),
      'datasets' => [
        [
          'label' => $article->getName(),
          'data' => $dateLabels,
        ]
      ],
    ];

    foreach ($article->getArticlePriceEntries() as $articlePriceEntry) {
      $date = $articlePriceEntry->getCreated()->format('Y-m-d');
      if (!in_array($date, array_keys($dateLabels))) {
        continue;
      }
      $data['datasets'][0]['data'][$date] = $articlePriceEntry->getPrice();
    }

    $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
    $chart->setData($data);
    $chart->setOptions([
      'spanGaps' => TRUE,
      'scales' => [
        'y' => [
          'beginAtZero' => TRUE,
        ],
      ],
      'cubicInterpolationMode' => 'monotone',
      'borderColor' => 'blue',
    ]);
    $articleChart = $chart;

    return $this->render('articles/detail.html.twig', [
      'article' => $article,
      'chart' => $articleChart,
    ]);
  }

  /**
   * @Route("/article/new", name="article_new")
   */
  public function articleAdd(Request $request, ArticleScraper $articleScraper) {
    $article = new Article();

    $form = $this->createFormBuilder($article)
      ->add('url', UrlType::class)
      ->add('save', SubmitType::class, ['label' => 'Save'])
      ->getForm();

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      /** @var Article $article */
      $article = $form->getData();

      $url = $article->getUrl();
      $articleScraper->setUrl($url);
      $article = $articleScraper->fetchArticle();

      $this->em->persist($article);
      $this->em->flush();

      $this->addFlash('success', 'Successfully added article');
      return $this->redirectToRoute('article_list');
    }

    return $this->render(
      'articles/form.html.twig',
      [
        'form' => $form->createView(),
      ]
    );
  }

  /**
   * @Route("/article/{id}/delete", name="article_delete", requirements={"id": "\d+"})
   */
  public function articleDelete(int $id) {
    $repository = $this->em->getRepository(Article::class);
    $article = $repository->find($id);

    if (!$article) {
      $this->addFlash('warning', 'Article not found?');
      return $this->redirectToRoute('article_list');
    }


    $this->em->remove($article);
    $this->em->flush();

    $this->addFlash('success', 'Successfully deleted article.');
    return $this->redirectToRoute('article_list');
  }

}
