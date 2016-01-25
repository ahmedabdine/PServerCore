<?php

namespace PServerCore\Controller;

use PServerCore\Entity\UserCodes;
use PServerCore\Helper\HelperOptions;
use PServerCore\Helper\HelperService;
use PServerCore\Helper\HelperServiceLocator;

class AuthController extends \SmallUser\Controller\AuthController
{
    use HelperServiceLocator, HelperService, HelperOptions;

    /**
     * @return array|\Zend\Http\Response
     */
    public function registerAction()
    {

        //if already login, redirect to success page
        if ($this->getUserService()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getLoggedInRoute());
        }

        $form = $this->getUserService()->getRegisterForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = $this->getUserService()->register($this->params()->fromPost());
            if ($user) {
                return $this->redirect()->toRoute('small-user-auth', ['action' => 'register-done']);
            }
        }

        return [
            'registerForm' => $form
        ];
    }

    /**
     * @return array
     */
    public function registerDoneAction()
    {
        return [
            'mail_confirmation' => $this->getUserService()->isRegisterMailConfirmationOption()
        ];
    }

    /**
     * @return array|mixed|\Zend\Http\Response
     */
    public function registerConfirmAction()
    {
        $codeRoute = $this->params()->fromRoute('code');

        $userCode = $this->getCode4Data($codeRoute, UserCodes::TYPE_REGISTER);
        if (!$userCode) {
            return $this->forward()->dispatch('PServerCore\Controller\Auth', ['action' => 'wrong-code']);
        }

        $user = $this->getUserService()->registerGameWithSamePassword($userCode);

        $form = $this->getUserService()->getPasswordForm();
        $request = $this->getRequest();
        if ($request->isPost() || $user) {
            if (!$user) {
                $user = $this->getUserService()->registerGameWithOtherPw($this->params()->fromPost(), $userCode);
            }
            if ($user) {
                //$this->getUserService()->doAuthentication($user);
                return $this->redirect()->toRoute($this->getLoggedInRoute());
            }
        }

        return [
            'registerForm' => $form
        ];
    }

    public function ipConfirmAction()
    {
        $code = $this->params()->fromRoute('code');

        $oCode = $this->getCode4Data($code, UserCodes::TYPE_CONFIRM_COUNTRY);
        if (!$oCode) {
            return $this->forward()->dispatch('PServerCore\Controller\Auth', ['action' => 'wrong-code']);
        }

        $user = $this->getUserService()->countryConfirm($oCode);
        if ($user) {
            return $this->redirect()->toRoute('small-user-auth', ['action' => 'ip-confirm-done']);
        }

        return [];
    }

    public function ipConfirmDoneAction()
    {
        return [];
    }

    public function pwLostAction()
    {

        $form = $this->getUserService()->getPasswordLostForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = $this->getUserService()->lostPw($this->params()->fromPost());
            if ($user) {
                return $this->redirect()->toRoute('small-user-auth', ['action' => 'pw-lost-done']);
            }
        }

        return [
            'pwLostForm' => $form
        ];
    }

    public function pwLostDoneAction()
    {
        return [];
    }

    public function pwLostConfirmAction()
    {
        $code = $this->params()->fromRoute('code');

        $codeEntity = $this->getCode4Data($code, UserCodes::TYPE_LOST_PASSWORD);
        if (!$codeEntity) {
            return $this->forward()->dispatch('PServerCore\Controller\Auth', ['action' => 'wrong-code']);
        }
        $form = $this->getUserService()->getPasswordForm();
        $form->addSecretQuestion($codeEntity->getUser());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = $this->getUserService()->lostPwConfirm($this->params()->fromPost(), $codeEntity);
            if ($user) {
                return $this->redirect()->toRoute('small-user-auth', ['action' => 'pw-lost-confirm-done']);
            }
        }

        return [
            'pwLostForm' => $form
        ];
    }

    public function secretLoginAction()
    {
        $code = $this->params()->fromRoute('code');

        $codeEntity = $this->getCode4Data($code, UserCodes::TYPE_SECRET_LOGIN);
        if (!$codeEntity) {
            return $this->forward()->dispatch('PServerCore\Controller\Auth', ['action' => 'wrong-code']);
        }
        $this->getUserService()->doAuthentication($codeEntity->getUser());
        $this->getUserCodesService()->deleteCode($codeEntity);

        return $this->redirect()->toRoute($this->getLoggedInRoute());
    }

    public function pwLostConfirmDoneAction()
    {
        return [];
    }

    public function wrongCodeAction()
    {
        return [];
    }

    public function addEmailAction()
    {
        $code = $this->params()->fromRoute('code');

        $codeEntity = $this->getCode4Data($code, UserCodes::TYPE_ADD_EMAIL);
        if (!$codeEntity) {
            return $this->forward()->dispatch('PServerCore\Controller\Auth', ['action' => 'wrong-code']);
        }
        $user = $this->getAddEmailService()->changeMail($codeEntity->getUser());
        $this->getUserCodesService()->deleteCode($codeEntity);

        $this->getUserService()->doAuthentication($user);
    }

    protected function getCode4Data($code, $type)
    {
        $entityManager = $this->getEntityManager();
        /** @var $repositoryCode \PServerCore\Entity\Repository\UserCodes */
        $repositoryCode = $entityManager->getRepository($this->getEntityOptions()->getUserCodes());
        $codeEntity = $repositoryCode->getData4CodeType($code, $type);

        return $codeEntity;
    }

}
