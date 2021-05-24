<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $seller;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\OneToMany(targetEntity=ArticlePriceEntry::class, mappedBy="article", orphanRemoval=true, cascade={"persist"})
     */
    private $articlePriceEntries;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateDates (): void {
        $this->setUpdated(new DateTime('now'));
        if (!$this->getCreated()) {
            $this->setCreated(new DateTime('now'));
        }
    }

    public function __construct()
    {
        $this->articlePriceEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCodeType(): ?string
    {
        return $this->codeType;
    }

    public function setCodeType(?string $codeType): self
    {
        $this->codeType = $codeType;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getSeller(): ?string
    {
        return $this->seller;
    }

    public function setSeller(?string $seller): self
    {
        $this->seller = $seller;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|ArticlePriceEntry[]
     */
    public function getArticlePriceEntries(): Collection
    {
        return $this->articlePriceEntries;
    }

    public function addArticlePriceEntry(ArticlePriceEntry $articlePriceEntry): self
    {
        if (!$this->articlePriceEntries->contains($articlePriceEntry)) {
            $this->articlePriceEntries[] = $articlePriceEntry;
            $articlePriceEntry->setArticle($this);
        }

        return $this;
    }

    public function removeArticlePriceEntry(ArticlePriceEntry $articlePriceEntry): self
    {
        if ($this->articlePriceEntries->removeElement($articlePriceEntry)) {
            // set the owning side to null (unless already changed)
            if ($articlePriceEntry->getArticle() === $this) {
                $articlePriceEntry->setArticle(null);
            }
        }

        return $this;
    }

    public const ARTICLE_CODE_GTIN = 'gtin';
    public const ARTICLE_CODE_GTIN12 = 'gtin12';
    public const ARTICLE_CODE_GTIN13 = 'gtin13';
    public const ARTICLE_CODE_GTIN14 = 'gtin14';
    public const ARTICLE_CODE_GTIN8 = 'gtin8';

    public static function getCodeTypes (): array {
        return [
            self::ARTICLE_CODE_GTIN,
            self::ARTICLE_CODE_GTIN12,
            self::ARTICLE_CODE_GTIN13,
            self::ARTICLE_CODE_GTIN14,
            self::ARTICLE_CODE_GTIN8,
        ];
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function toTabular () {
        $values = get_object_vars($this);
        $output = [];
        foreach ($values as $key => $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $output[$key] = $value;
        }
        return $output;
    }

    public function getPriceHigh() {
        $entries = $this->getArticlePriceEntries();

        /** @var ArticlePriceEntry $highEntry */
        $highEntry = $entries->current();
        foreach ($entries as $entry) {
            if ($highEntry->getPrice() < $entry->getPrice()) {
                $highEntry = $entry;
            }
        }

        return $highEntry;
    }

    public function getPriceLow() {
        $entries = $this->getArticlePriceEntries();

        /** @var ArticlePriceEntry $lowEntry */
        $lowEntry = $entries->current();
        foreach ($entries as $entry) {
            if ($lowEntry->getPrice() > $entry->getPrice()) {
                $lowEntry = $entry;
            }
        }

        return $lowEntry;
    }
}
