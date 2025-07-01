<?php
namespace w3des\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table()
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class File
{
    /**
     * @ORM\Column(type="guid")
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $path;

    /**
     * @ORM\Column(type="string")
     */
    private $repository = 'default';

    /**
     * @ORM\Column(type="string")
     */
    private $mime;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="json")
     */
    private $meta = [];

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->id = Uuid::uuid4()->toString();
    }
    /**
     * @return $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return $width
     */
    public function getWidth()
    {
        return $this->getMetaValue('width');
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->setMetaValue('width', (int)$width);
    }

    /**
     * @return $height
     */
    public function getHeight()
    {
        return $this->getMetaValue('height');
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function setMeta(array $meta)
    {
        $this->meta = $meta;
    }

    public function setMetaValue($k, $v)
    {
        $this->meta[$k] = $v;
    }

    public function getMetaValue($k)
    {
        if (isset($this->meta[$k])) {
            return $this->meta[$k];
        }

        return null;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->setMetaValue('height', (int)$height);
    }

    /**
     * @return $mime
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @param mixed $mime
     */
    public function setMime($mime)
    {
        $this->mime = $mime;
    }

    /**
     * @return $size
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
    /**
     * @return $repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param string $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    public function __toString()
    {
        return $this->getPath() . '';
    }

    public function getFile()
    {
        return $this->file;
    }

    public function isRemove()
    {
        return $this->remove;
    }
    /**
     * @return $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
