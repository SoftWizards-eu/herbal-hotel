<?php
namespace w3des\AdminBundle\Service;

use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use w3des\AdminBundle\Model\ValueDefinition;
use w3des\AdminBundle\Model\ValueInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use w3des\AdminBundle\Model\ValuesMap;

class Values
{
    const MODEL_DEFAULT_LOCALE = 'xx';

    private $uploadDir;
    private $publicDir;
    private $uploadPath;
    private $cacheManager;

    private $defaultLocales = [self::MODEL_DEFAULT_LOCALE];
    private $emptyArray = [];

    public function __construct(CacheManager $manager, $publicDir, $uploadPath)
    {
        $this->publicDir = $publicDir;
        $this->uploadPath = $uploadPath;
        $this->uploadDir = $this->publicDir . '/' . $uploadPath;
        $this->cacheManager = $manager;
    }

    public function removeFile($file)
    {
        $file = (string) $file;
        if (\file_exists($this->publicDir . '/' .$file)) {
            @\unlink($this->publicDir . '/' .$file);
        }
        try {
            $this->cacheManager->remove($file);
        }catch(\Exception $e) {
            // ignore problems
            // XXX Log it
        }
    }

    public function fileFromPath($path)
    {
        $f = new \w3des\AdminBundle\Entity\File();
        $f->setPath($path);

        $file = new File($this->publicDir . '/' . $path);
        $f->setSize($file->getSize());
        $f->setMime($file->getMimeType());

        try {
            $info = \getimagesize($file->getPathname());
            $f->setWidth($info[0]);
            $f->setHeight($info[1]);
        } catch(\Exception $e) {
            $f->setWidth(0);
            $f->setHeight(0);
        }

        return $f;
    }

    public function getPublicDir()
    {
        return $this->publicDir;
    }

    public function saveFile($file, $dir = 'settings')
    {
        if ($file instanceof File) {
            $tmp = new Slugify();
            $source = $file->getBasename();
            $extension = $file->getExtension();
            $mime = $file->getMimeType();
            if ($file instanceof UploadedFile) {
                $source = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mime = $mime ?: $file->getClientMimeType();
            }
            if ($extension) {
                $source = substr($source, 0, strlen($source) - 1 - strlen($extension));
            }

            $dir = $dir ?: 'settings';
            $path = '/' . $dir. '/' . date('Y') . '/' . date('m') . '/' . date('d');
            if (! \file_exists($this->uploadDir . $path)) {
                \mkdir($this->uploadDir . $path, 0755, true);
            }
            $name = \uniqid('', true) . '_' . $tmp->slugify($source) . '.' . \strtolower($extension);
            $f = new \w3des\AdminBundle\Entity\File();
            $f->setPath($this->uploadPath . $path . '/' . $name);
            $f->setSize($file->getSize());

            $file->move($this->uploadDir . $path, $name);

            $tmpF = new File($this->uploadDir . $path . '/' . $name);
            $f->setMime($tmpF->getMimeType() ?: $mime);


            try {
                $info = \getimagesize($tmpF->getPathname());
                $f->setWidth($info[0]);
                $f->setHeight($info[1]);
            } catch(\Exception $e) {
                $f->setWidth(0);
                $f->setHeight(0);
            }

            return $f;
        }
    }

    public function createMap(\Traversable $collection, array $configuration, $defaultLocale)
    {
        return new ValuesMap($configuration, $this->dataView($collection, $configuration, [$defaultLocale]), $defaultLocale);
    }

    /**
     * @param ValueInterface[] $collection
     * @param ValueDefinition[] $configuration
     */
    public function dataView(\Traversable $collection, array $configuration, array $locales)
    {
        $model = $this->dataViewModel($collection, $configuration, $locales);
        foreach ($model as $name => $localized) {
            foreach ($localized as $lang => $values) {
                $tmp = [];
                foreach ($values as $val) {
                    $tmp[$val->getPos()] = $val->getValue();
                }
                if (!$configuration[$name]['array']) {
                    $model[$name][$lang] = $tmp[0] ?? $configuration[$name]['default'];
                } else {
                    $model[$name][$lang] = $configuration[$name]['default'] && count($tmp) == 0 ? $configuration[$name]['default'] : $tmp;
                }
            }
        }

        return $model;
    }

    private function dataViewModel(\Traversable $collection, array $configuration, array $locales)
    {
        $model = [];
        foreach ($configuration as $field => $cfg) {
            $model[$field] = [];
            foreach ($cfg['locale'] ? $locales : $this->defaultLocales as $loc) {
                $model[$field][$loc] = [];
            }
        }
        foreach ($collection as $data) {
            if (!isset($model[$data->getName()]) || !isset($model[$data->getName()][$data->getLocale()])) {
                continue;
            }
            $model[$data->getName()][$data->getLocale()][$data->getPos()] = $data;
        }
        foreach ($model as &$localized) {
            foreach ($localized as &$values) {
                \ksort($values);
            }
        }

        return $model;
    }

    public function setDataView(\Traversable $collection, array $data, array $configuration, array $locales, callable $create)
    {
        $models = $this->dataViewModel($collection, $configuration, $locales);
        foreach ($data as $name => $localized) {
            $definition = $configuration[$name];
            foreach ($localized as $locale => $value) {
                if (!$configuration[$name]['array']) {
                    $value = [$value];
                }
                $used = [];
                $pos = 0;
                foreach ($value as $position => $val) {
                    if ($val === null) {
                        $models[$name][$locale][$position] = null;
                        continue;
                    } elseif (!isset($models[$name][$locale][$position])) {
                        /** @var \w3des\AdminBundle\Model\ValueInterface $tmp */
                        $tmp = $create();
                        $models[$name][$locale][$position] = $tmp;
                        $collection[] = $tmp;
                    }
                    $this->setDataViewValue($definition, $position, $models[$name][$locale][$position], $val, $locale);
                    $models[$name][$locale][$position]->setNewPos($pos++);
                    $used[] = $position;
                }
                foreach ($models[$name][$locale] as $position => $v) {
                    if ($v && !\in_array($position, $used)) {
                        unset($models[$name][$locale][$position]);
                    }
                }
            }
        }
        // remove unsued
        foreach ($collection as $key => $item) {
            $loc = $item->getLocale();

            if (empty($models[$item->getName()][$loc][$item->getPos()])) {
                unset($collection[$key]);
            }
        }

    }

    private function setDataViewValue(array $cfg, int $pos, ValueInterface $model, $value, $locale)
    {
        $model->setLocale($locale);
        $model->setPos($pos);
        $model->setType($cfg['storeType']);
        $model->setName($cfg['name']);
        $model->setValue($value);
    }

    public function setValue(\Traversable $collection, array $configuration, $create, $field, $value, $locale)
    {
        $map = $this->dataView($collection, $configuration, [$locale]);
        $map[$field][$configuration[$field]['locale'] ? $locale : self::MODEL_DEFAULT_LOCALE] = $value;
        $this->setDataView($collection, $map, $configuration, [$locale], $create);
    }
}

