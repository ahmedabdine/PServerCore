<?php

namespace PServerCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SecretQuestion
 * @ORM\Table(name="secret_question")
 * @ORM\Entity(repositoryClass="PServerCore\Entity\Repository\SecretQuestion")
 */
class SecretQuestion
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="question", type="string", length=255, nullable=false)
     */
    private $question;

    /**
     * @var integer
     * @ORM\Column(name="sort_key", type="smallint", nullable=false)
     */
    private $sortKey = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param string $question
     * @return $this
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortKey()
    {
        return $this->sortKey;
    }

    /**
     * @param int $sortKey
     * @return $this
     */
    public function setSortKey($sortKey)
    {
        $this->sortKey = $sortKey;

        return $this;
    }
} 