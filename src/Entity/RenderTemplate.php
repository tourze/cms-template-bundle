<?php

namespace Tourze\CmsTemplateBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;

#[ORM\Entity(repositoryClass: RenderTemplateRepository::class)]
#[ORM\Table(name: 'cms_render_template', options: ['comment' => '页面模板表'])]
class RenderTemplate implements \Stringable, Itemable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;
    use IpTraceableAware;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true, options: ['comment' => '路径'])]
    private ?string $path = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ORM\Column(type: Types::STRING, length: 255, options: ['comment' => '标题'])]
    private ?string $title = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '模板内容'])]
    private ?string $content = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: RenderTemplate::class, inversedBy: 'children')]
    private ?RenderTemplate $parent = null;

    /**
     * @var Collection<int, RenderTemplate>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: RenderTemplate::class)]
    private Collection $children;

    #[Assert\Type(type: 'bool')]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId() || '' === $this->getId()) {
            return '';
        }

        return $this->getTitle() ?? '';
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return array{id: string|null, text: string}
     */
    public function toSelectItem(): array
    {
        $text = "{$this->getTitle()}({$this->getPath()})";

        return [
            'id' => $this->getId(),
            'text' => $text,
        ];
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection<int, RenderTemplate>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(RenderTemplate $child): void
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }
    }

    public function removeChild(RenderTemplate $child): void
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getContentPreview(): string
    {
        if (null === $this->content || '' === $this->content) {
            return '无内容';
        }
        $preview = strip_tags($this->content);
        if (mb_strlen($preview) > 50) {
            $preview = mb_substr($preview, 0, 50) . '...';
        }

        return $preview;
    }
}
