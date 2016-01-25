<?php

namespace PServerCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class SecretQuestion extends EntityRepository
{

    /**
     * @return \PServerCore\Entity\SecretQuestion[]|null
     */
    public function getQuestions()
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.sortKey', 'asc')
            ->getQuery();


        return $query->getResult();
    }

    /**
     * @param $id
     *
     * @return \PServerCore\Entity\SecretQuestion[]|null
     */
    public function getQuestion4Id($id)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery();


        return $query->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQuestionQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->select('p');
    }
} 