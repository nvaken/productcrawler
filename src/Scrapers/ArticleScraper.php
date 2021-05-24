<?php

namespace App\Scrapers;

use App\Entity\Article;
use App\Entity\ArticlePriceEntry;
use App\HttpClient;
use App\Serializer\SchemaProductNormalizer;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Serializer;

class ArticleScraper {

    private $httpClient;
    /**
     * @var string
     */
    private $url;

    /**
     * ArticleScraper constructor.
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient) {
        $this->httpClient = $httpClient;
    }

    public function setUrl(string $url) {
        $this->url = $url;
    }

    public function fetchArticle(): ?Article {
        $result = $this->httpClient->get($this->url);

        $crawler = new Crawler($result->getBody()->getContents());
        $ldJson = $crawler->filter('script[type="application/ld+json"]');
        $canonical = $crawler->filter('link[rel="canonical"]')->attr('href');

        $article = NULL;
        $encoders = [new JsonEncoder()];
        $normalizers = [new SchemaProductNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $ldJson->each(function (Crawler $node) use ($serializer, &$article) {
            try {
                /** @var Article $article */
                $article = $serializer->deserialize($node->text(), Article::class, 'json');
            }
            catch (NotNormalizableValueException $e) {
            }
        });

        if (!$article->getUrl()) {
            $article->setUrl($canonical);
        }

        return $article;
    }

    public function fetchArticlePrice(): ?ArticlePriceEntry {
        $result = $this->httpClient->get($this->url);

        $crawler = new Crawler($result->getBody()->getContents());
        $ldJson = $crawler->filter('script[type="application/ld+json"]');

        $article = NULL;
        $encoders = [new JsonEncoder()];
        $normalizers = [new SchemaProductNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $ldJson->each(function (Crawler $node) use ($serializer, &$article) {
            try {
                /** @var Article $article */
                $article = $serializer->deserialize($node->text(), Article::class, 'json');
            }
            catch (NotNormalizableValueException $e) {
            }
        });
        return $article->getArticlePriceEntries()[0];
    }

}
