<?php


namespace PServerCore\View\Helper;

use Zend\View\Model\ViewModel;

class LoggedInWidget extends InvokerBase
{
    /**
     * @return string
     */
    public function __invoke()
    {
        $template = '';

        if ($this->getAuthService()->hasIdentity()) {
            $user = $this->getAuthService()->getIdentity();
            $viewModel = new ViewModel([
                'user' => $user,
                'coins' => $this->getGameBackendService()->getCoins($user),
                'loggedIn' => $this->getConfig()['pserver']['logged_in']
            ]);
            $viewModel->setTemplate('helper/sidebarLoggedInWidget');
            $template = $this->getView()->render($viewModel);
        }

        return $template;
    }
}