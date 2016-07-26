<?php


namespace PServerCore\Form;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcTicketSystem\Form\TicketEntry;

class TicketEntryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return TicketEntry
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $form = new TicketEntry();
        /** @noinspection PhpParamsInspection */
        $form->setInputFilter(new TicketEntryFilter($container->get('zfc-bbcode_parser')));

        return $form;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return TicketEntry
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, TicketEntry::class);
    }

}