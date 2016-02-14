<?php

namespace PServerCore\Service;

use PServerCore\Mapper\HydratorPageInfo;
use PServerCore\Keys\Caching;

class PageInfo extends InvokableBase
{
    /**
     * @param $type
     *
     * @return \PServerCore\Entity\PageInfo|null
     */
    public function getPage4Type($type)
    {
        $cachingKey = Caching::PAGE_INFO . '_' . $type;

        $pageInfo = $this->getCachingHelperService()->getItem($cachingKey, function () use ($type) {
            /** @var \PServerCore\Entity\Repository\PageInfo $repository */
            $repository = $this->getEntityManager()->getRepository($this->getEntityOptions()->getPageInfo());
            return $repository->getPageData4Type($type);
        });

        return $pageInfo;
    }

    /**
     * @param array $data
     * @param string $type
     *
     * @return bool|\PServerCore\Entity\PageInfo
     */
    public function pageInfo(array $data, $type)
    {
        $form = $this->getAdminPageInfoForm();
        $form->setHydrator(new HydratorPageInfo());
        $class = $this->getEntityOptions()->getPageInfo();
        $form->bind(new $class());
        $form->setData($data);
        if (!$form->isValid()) {
            return false;
        }

        /** @var \PServerCore\Entity\PageInfo $pageInfo */
        $pageInfo = $form->getData();
        $pageInfo->setType($type);

        $entity = $this->getEntityManager();
        $entity->persist($pageInfo);
        $entity->flush();

        return $pageInfo;
    }

    /**
     * @return array
     */
    public function getPossiblePageInfoTypes()
    {
        return $this->getConfigService()->get('pserver.pageinfotype', []);
    }

} 