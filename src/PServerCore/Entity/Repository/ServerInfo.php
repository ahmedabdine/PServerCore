<?php

namespace PServerCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ServerInfo extends EntityRepository
{

    /**
     * @return null|\PServerCore\Entity\ServerInfo[]
     */
    public function getActiveInfoList()
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.active = :active')
            ->setParameter('active', '1')
            ->orderBy('p.sortKey', 'asc')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @return null|\PServerCore\Entity\ServerInfo[]
     */
    public function getInfoList()
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $id
     *
     * @return null|\PServerCore\Entity\ServerInfo
     */
    public function getServerInfo4Id($id)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->select('p');
    }
} 