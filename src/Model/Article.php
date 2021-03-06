<?php declare(strict_types=1);

namespace One\Model;

use One\Collection;

/**
 * Article Class
 */
class Article extends Model
{
    public const CATEGORY_NASIONAL = 1;

    public const CATEGORY_INTERNASIONAL = 2;

    public const CATEGORY_BISNIS = 3;

    public const CATEGORY_SEPAK_BOLA = 4;

    public const CATEGORY_OLAHRAGA = 5;

    public const CATEGORY_HIBURAN = 6;

    public const CATEGORY_TEKNOLOGI = 7;

    public const CATEGORY_TRAVEL = 8;

    public const CATEGORY_LIFESTYLE = 9;

    public const CATEGORY_WANITA = 10;

    public const CATEGORY_HIJAB = 11;

    public const CATEGORY_KULINER = 12;

    public const CATEGORY_SEHAT = 13;

    public const CATEGORY_OTOMOTIF = 14;

    public const CATEGORY_INSPIRASI = 15;

    public const CATEGORY_UNIK = 16;

    public const CATEGORY_EVENT = 17;

    public const CATEGORY_KOMUNITAS = 18;

    public const TYPE_TEXT = 1;

    public const TYPE_PHOTO = 2;

    public const TYPE_VIDEO = 3;

    public const ATTACHMENT_FIELD_PHOTO = 'photo';

    public const ATTACHMENT_FIELD_PAGE = 'page';

    public const ATTACHMENT_FIELD_VIDEO = 'video';

    public const ATTACHMENT_FIELD_GALLERY = 'gallery';

    /**
     * identifier
     *
     * @var string
     */
    protected $identifier = null;

    /**
     * attachment property
     *
     * @var array<mixed>
     */
    private $attachment = [];

    /**
     * constructor
     *
     * @param \Psr\Http\Message\UriInterface|string $source
     * @param string $uniqueId
     * @param integer $typeId
     * @param integer $categoryId
     * @param string $reporter
     * @param string $lead
     * @param string $tags
     * @param \DateTimeInterface|string $publishedAt
     */
    public function __construct(
        string $title,
        string $body,
        $source,
        $uniqueId,
        $typeId = self::TYPE_TEXT,
        $categoryId = self::CATEGORY_NASIONAL,
        $reporter = '',
        $lead = '',
        $tags = '',
        $publishedAt = null,
        $identifier = null
    ) {
        $source = $this->filterUriInstance($source);
        $publishedAt = $this->filterDateInstance($publishedAt);

        if (empty($lead)) {
            $lead = $this->createLeadFromBody($body);
        }

        $allowedType = [
            self::TYPE_PHOTO,
            self::TYPE_TEXT,
            self::TYPE_VIDEO,
        ];

        if (! in_array($typeId, $allowedType, true)) {
            throw new \InvalidArgumentException("Invalid typeId : ${typeId}, allowed typeId are " . implode(', ', $allowedType));
        }

        $allowedCategory = [
            self::CATEGORY_NASIONAL,
            self::CATEGORY_INTERNASIONAL,
            self::CATEGORY_BISNIS,
            self::CATEGORY_SEPAK_BOLA,
            self::CATEGORY_OLAHRAGA,
            self::CATEGORY_HIBURAN,
            self::CATEGORY_TEKNOLOGI,
            self::CATEGORY_TRAVEL,
            self::CATEGORY_LIFESTYLE,
            self::CATEGORY_WANITA,
            self::CATEGORY_HIJAB,
            self::CATEGORY_KULINER,
            self::CATEGORY_SEHAT,
            self::CATEGORY_OTOMOTIF,
            self::CATEGORY_INSPIRASI,
            self::CATEGORY_UNIK,
            self::CATEGORY_EVENT,
            self::CATEGORY_KOMUNITAS,
        ];

        if (! in_array($categoryId, $allowedCategory, true)) {
            throw new \InvalidArgumentException("Invalid categoryId : ${categoryId}, allowed category are " . implode(', ', $allowedCategory));
        }

        $title = $this->filterStringInstance($title);
        $reporter = $this->filterStringInstance($reporter);
        $lead = $this->filterStringInstance($lead);
        $body = $this->filterStringInstance($body);
        $tags = $this->filterStringInstance($tags);

        $this->collection = new Collection(
            [
                'title' => $title,
                'reporter' => $reporter,
                'lead' => $lead,
                'body' => $body,
                'source' => $source,
                'uniqueId' => $uniqueId,
                'type_id' => $typeId,
                'category_id' => $categoryId,
                'tags' => $tags,
                'published_at' => $publishedAt,
            ]
        );

        if ($identifier) {
            $this->setId((string) $identifier);
        }
    }

    /**
     * get ALL Possible attachment for an article, return arrays of field name. Used for consistency accross sdk
     * leveraging php version 5.3 cannot use array constant
     *
     * @return string[]
     */
    public static function getPossibleAttachment(): array
    {
        return array_merge(
            self::getDeleteableAttachment(),
            [
                self::ATTACHMENT_FIELD_PHOTO,
            ]
        );
    }

    /**
     * get deleteable attachment for constant usage across sdk
     *
     * @return string[]
     */
    public static function getDeleteableAttachment(): array
    {
        return [
            self::ATTACHMENT_FIELD_GALLERY,
            self::ATTACHMENT_FIELD_PAGE,
            self::ATTACHMENT_FIELD_VIDEO,
        ];
    }

    /**
     * setIdentifier from rest api response
     */
    public function setId(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * getIdentifier set before
     */
    public function getId(): string
    {
        return $this->identifier;
    }

    /**
     * check if this object has attachment assigned to it
     */
    public function hasAttachment(string $field): bool
    {
        return isset($this->attachment[$field]);
    }

    /**
     * getAttachment based on fields
     */
    public function getAttachmentByField(string $field): array
    {
        if (isset($this->attachment[$field])) {
            return $this->attachment[$field];
        }

        return [];
    }

    /**
     * get ALL attachment assigned to this object
     */
    public function getAttachments(): ?array
    {
        return $this->attachment;
    }

    /**
     * add attach an attachment to this model
     */
    public function attach(string $field, Model $item): self
    {
        $this->attachment[$field][] = $item;

        return $this;
    }

    /**
     * Attach Photo Attachment to article
     */
    public function attachPhoto(Photo $photo): self
    {
        return $this->attach(
            self::ATTACHMENT_FIELD_PHOTO,
            $photo
        );
    }

    /**
     * Attach Paging
     */
    public function attachPage(Page $page): self
    {
        return $this->attach(
            self::ATTACHMENT_FIELD_PAGE,
            $this->ensureOrder(
                $page,
                self::ATTACHMENT_FIELD_PAGE
            )
        );
    }

    /**
     * Attach gallery here
     */
    public function attachGallery(Gallery $gallery): self
    {
        return $this->attach(
            self::ATTACHMENT_FIELD_GALLERY,
            $this->ensureOrder(
                $gallery,
                self::ATTACHMENT_FIELD_GALLERY
            )
        );
    }

    /**
     * attach Video
     */
    public function attachVideo(Video $video): self
    {
        return $this->attach(
            self::ATTACHMENT_FIELD_VIDEO,
            $this->ensureOrder(
                $video,
                self::ATTACHMENT_FIELD_VIDEO
            )
        );
    }

    /**
     * ensuring order
     */
    private function ensureOrder(Model $attachment, string $type): Model
    {
        $attachmentOrder = $attachment->get('order');

        if (empty($attachmentOrder)) {
            $attachment->set(
                'order',
                count($this->getAttachmentByField($type)) + 1
            );
        }

        return $attachment;
    }
}
