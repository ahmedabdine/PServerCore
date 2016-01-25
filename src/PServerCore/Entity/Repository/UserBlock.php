<?php

namespace PServerCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use PServerCore\Entity\UserInterface;

/**
 * IPBlock
 */
class UserBlock extends EntityRepository
{
    /**
     * @param UserInterface $user
     * @param null $expireTime
     * @return null|\PServerCore\Entity\UserBlock
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isUserAllowed(UserInterface $user, $expireTime = null)
    {
        if (!$expireTime) {
            $expireTime = new \DateTime();
        }

        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.expire >= :expireTime')
            ->setParameter('expireTime', $expireTime)
            ->orderBy('p.expire', 'desc')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param UserInterface $user
     * @param \DateTime|null $dateTime
     * @return mixed
     */
    public function removeBlock(UserInterface $user, $dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = new \DateTime();
        }

        $query = $this->createQueryBuilder('p')
            ->delete('PServerCore\Entity\UserBlock', 'p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.expire >= :dateTime')
            ->setParameter('dateTime', $dateTime)
            ->getQuery();

        return $query->execute();
    }
}