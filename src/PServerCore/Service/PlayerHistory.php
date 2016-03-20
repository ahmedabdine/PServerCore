<?php

namespace PServerCore\Service;

use Doctrine\ORM\EntityManager;
use GameBackend\DataService\DataServiceInterface;
use PServerCore\Keys\Caching;
use PServerCore\Options\Collection;

class PlayerHistory
{
    /** @var  CachingHelper */
    protected $cachingHelperService;

    /** @var  EntityManager */
    protected $entityManager;

    /** @var  Collection */
    protected $collectionOptions;

    /** @var  DataServiceInterface */
    protected $gameBackendService;

    /**
     * PlayerHistory constructor.
     * @param CachingHelper $cachingHelperService
     * @param EntityManager $entityManager
     * @param Collection $collectionOptions
     * @param DataServiceInterface $gameBackendService
     */
    public function __construct(
        CachingHelper $cachingHelperService,
        EntityManager $entityManager,
        Collection $collectionOptions,
        DataServiceInterface $gameBackendService
    ) {
        $this->cachingHelperService = $cachingHelperService;
        $this->entityManager = $entityManager;
        $this->collectionOptions = $collectionOptions;
        $this->gameBackendService = $gameBackendService;
    }

    /**
     * @return int
     */
    public function getCurrentPlayer()
    {
        $currentPlayer = $this->cachingHelperService->getItem(Caching::PLAYER_HISTORY, function () {
            /** @var \PServerCore\Entity\Repository\PlayerHistory $repository */
            $repository = $this->entityManager->getRepository($this->collectionOptions->getEntityOptions()->getPlayerHistory());
            return $repository->getCurrentPlayer();
        });

        return $currentPlayer;
    }

    /**
     * read from GameBackend the current player [or] as param and save them in database
     *
     * @param int $extraPlayer
     */
    public function setCurrentPlayer($extraPlayer = 0)
    {
        try {
            $player = $this->gameBackendService->getCurrentPlayerNumber();
        } catch (\Exception $e) {
            $player = 0;
        }

        if ($player > 0) {
            $player += $extraPlayer;
        }

        $class = $this->collectionOptions->getEntityOptions()->getPlayerHistory();
        /** @var \PServerCore\Entity\PlayerHistory $playerHistory */
        $playerHistory = new $class();
        $playerHistory->setPlayer($player);

        $this->entityManager->persist($playerHistory);
        $this->entityManager->flush();
    }

    /**
     * output the player online image
     */
    public function outputCurrentPlayerImage()
    {
        $playerNumber = $this->getCurrentPlayer();

        $image = imagecreate(250, 50);

        //set the background color of the image
        $color = $this->collectionOptions->getGeneralOptions()->getImagePlayer()['background_color'];
        imagecolorallocate($image, $color[0], $color[1], $color[2]);
        //set the color for the text
        $color = $this->collectionOptions->getGeneralOptions()->getImagePlayer()['font_color'];
        $fontColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        //adf the string to the image
        $maxPlayer = $this->collectionOptions->getGeneralOptions()->getMaxPlayer();
        if ($maxPlayer > 0) {
            $text = sprintf('%s/%s Player Online', $playerNumber, $maxPlayer);
        } else {
            $text = sprintf('%s Player Online', $playerNumber);
        }

        $this->imageCenterString($image, 5, $text, $fontColor);

        imagepng($image);

        imagedestroy($image);
    }

    /**
     * @param $img
     * @param $font
     * @param $text
     * @param $color
     */
    protected function imageCenterString(&$img, $font, $text, $color)
    {
        if ($font < 0 || $font > 5) {
            $font = 0;
        }

        $num = [
            [4.6, 6],
            [4.6, 6],
            [5.6, 12],
            [6.5, 12],
            [7.6, 16],
            [8.5, 16]
        ];

        $width = ceil(strlen($text) * $num[$font][0]);
        $x = imagesx($img) - $width - 8;
        $y = imagesy($img) - ($num[$font][1] + 2);
        imagestring($img, $font, $x / 2, $y / 2, $text, $color);
    }
}