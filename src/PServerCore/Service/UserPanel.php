<?php


namespace PServerCore\Service;


class UserPanel extends InvokableBase
{
    const ErrorNameSpace = 'p-server-admin-user-panel';

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserListQueryBuilder()
    {
        /** @var \PServerCore\Entity\Repository\User $repository */
        $repository = $this->getEntityManager()->getRepository($this->getEntityOptions()->getUser());

        return $repository->getUserListQueryBuilder();
    }

    /**
     * @param $id
     * @return null|\PServerCore\Entity\UserInterface
     */
    public function getUser4Id($id)
    {
        return parent::getUser4Id($id);
    }

}