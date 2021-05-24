<?php
namespace App\Serializer;

use App\Entity\Article;
use App\Entity\ArticlePriceEntry;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class SchemaProductNormalizer implements ContextAwareNormalizerInterface,
    ContextAwareDenormalizerInterface
{

//    /**
//     * @var ObjectNormalizer
//     */
//    private $normalizer;

    public function __construct()
    {
    }

    public function normalize($topic, string $format = null, array $context = [])
    {
//        $data = $this->normalizer->normalize($topic, $format, $context);


        return $topic;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Article;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($data['@type'])
            && $data['@type'] === 'Product'
            && $type === Article::class;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $article = new Article();
        $article->setName($data['name']);

        if (isset($data['brand']['name'])) {
            $article->setBrand($data['brand']['name']);
        }

        $offer = $data['offers'];
        if (isset($offer[0])) {
            $offer = $offer[0];
        }
        $priceEntry = new ArticlePriceEntry();
        $priceEntry->setPriceCurrency($offer['priceCurrency'])
            ->setPrice((float) $offer['price']);
        $article->addArticlePriceEntry($priceEntry);

        $codeTypes = Article::getCodeTypes();
        $detectedCodes = array_intersect($codeTypes, array_keys($data));
        if (!empty($detectedCodes)) {
            $detectedCode = reset($detectedCodes);
            $article->setCode($data[$detectedCode]);
            $article->setCodeType($detectedCode);
        }

        if (!empty($data['url'])) {
            $article->setUrl($data['url']);
        }

        return $article;
    }


}
