<?php
namespace w3des\AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

class SettingsList extends ArrayCollection
{
    private $removed = [];

    public function remove($key) {
        $removed = parent::remove($key);
        if ($removed) {
            $this->removed[] = $removed;
        }
        return $removed;
    }

    public function getRemoved()
    {
        return $this->removed;
    }
}

