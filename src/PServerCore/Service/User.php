<?php

namespace PServerCore\Service;


use GameBackend\Helper\Service;
use PServerCore\Entity\Repository\AvailableCountries as RepositoryAvailableCountries;
use PServerCore\Entity\Repository\CountryList;
use PServerCore\Entity\User as Entity;
use PServerCore\Entity\UserCodes;
use PServerCore\Entity\UserInterface;
use PServerCore\Helper\DateTimer;
use PServerCore\Helper\HelperBasic;
use PServerCore\Helper\HelperForm;
use PServerCore\Helper\HelperOptions;
use PServerCore\Helper\HelperService;
use PServerCore\Validator\AbstractRecord;
use SmallUser\Entity\UserInterface as SmallUserInterface;
use SmallUser\Mapper\HydratorUser;
use Zend\Crypt\Password\Bcrypt;
use Zend\Validator\EmailAddress;

/**
 * Class User
 * @package PServerCore\Service
 * @TODO refactoring
 */
class User extends \SmallUser\Service\User
{
    use HelperService, HelperOptions, HelperForm, HelperBasic, Service;

    /**
     * @param array $data
     * @return bool
     */
    public function login(array $data)
    {
        $result = parent::login($data);
        if (!$result) {
            $form = $this->getLoginForm();
            $error = $form->getMessages('username');
            if ($error && isset($error[AbstractRecord::ERROR_NOT_ACTIVE])) {
                $this->getFlashMessenger()->setNamespace(self::ErrorNameSpace)->addMessage($error[AbstractRecord::ERROR_NOT_ACTIVE]);
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return UserInterface|bool
     */
    public function register(array $data)
    {
        $form = $this->getRegisterForm();
        $form->setHydrator(new HydratorUser());
        $form->bind(new Entity());
        $form->setData($data);
        if (!$form->isValid()) {
            return false;
        }

        $entityManager = $this->getEntityManager();
        /** @var Entity $userEntity */
        $userEntity = $form->getData();
        $userEntity->setCreateIp($this->getIpService()->getIp());
        $plainPassword = $userEntity->getPassword();
        $userEntity->setPassword($this->bCrypt($plainPassword));

        $entityManager->persist($userEntity);
        $entityManager->flush();

        if ($this->isRegisterMailConfirmationOption()) {
            $code = $this->getUserCodesService()->setCode4User($userEntity, UserCodes::TYPE_REGISTER);

            $this->getMailService()->register($userEntity, $code);
        } else {
            $userEntity = $this->registerGame($userEntity, $plainPassword);
            $this->setAvailableCountries4User($userEntity, $this->getIpService()->getIp());
            //valid identity after register with no mail
            $this->doAuthentication($userEntity);
        }

        if ($this->isSecretQuestionOption()) {
            $this->getSecretQuestionService()->setSecretAnswer($userEntity, $data['question'], $data['answer']);
        }

        return $userEntity;
    }

    /**
     * @param UserCodes $userCode
     * @return UserInterface|null
     */
    public function registerGameWithSamePassword(UserCodes $userCode)
    {
        $user = null;
        // config is different pw-system
        if ($this->isSamePasswordOption()) {
            $user = $this->registerGameForm($userCode);
        }

        return $user;
    }

    /**
     * @param array $data
     * @param UserCodes $userCode
     * @return UserInterface|bool
     */
    public function registerGameWithOtherPw(array $data, UserCodes $userCode)
    {
        $form = $this->getPasswordForm();

        $form->setData($data);
        if (!$form->isValid()) {
            return false;
        }

        $data = $form->getData();
        $plainPassword = $data['password'];
        $user = $this->registerGameForm($userCode, $plainPassword);

        return $user;
    }

    /**
     * @param UserCodes $userCode
     * @param null $plainPassword
     * @return UserInterface
     */
    public function registerGameForm(UserCodes $userCode, $plainPassword = null)
    {
        $user = $this->registerGame($userCode->getUser(), $plainPassword);
        $this->setAvailableCountries4User($user, $this->getIpService()->getIp());

        if ($user) {
            $this->getUserCodesService()->deleteCode($userCode);
            //user logged-in after confirmation
            $this->doAuthentication($user);
        }

        return $user;
    }

    /**
     * @param array $data
     * @param UserInterface $user
     * @return bool|null|UserInterface
     */
    public function changeWebPwd(array $data, UserInterface $user)
    {
        $user = $this->getUser4Id($user->getId());

        // check if we use different pw system
        if ($this->isSamePasswordOption()) {
            return false;
        }

        if (!$this->isPwdChangeAllowed($data, $user, 'Web')) {
            return false;
        }

        $user = $this->setNewPasswordAtUser($user, $data['password']);

        return $user;
    }

    /**
     * @param array $data
     * @param UserInterface $user
     * @return bool
     */
    public function changeInGamePwd(array $data, UserInterface $user)
    {
        $user = $this->getUser4Id($user->getId());
        if (!$this->isPwdChangeAllowed($data, $user, 'InGame')) {
            return false;
        }

        // check if we have to change it at web too
        if ($this->isSamePasswordOption()) {
            $user = $this->setNewPasswordAtUser($user, $data['password']);
        }

        $gameBackend = $this->getGameBackendService();
        $gameBackend->setUser($user, $data['password']);

        return $user;
    }

    /**
     * @param array $data
     * @return bool|null|UserInterface
     */
    public function lostPw(array $data)
    {
        $form = $this->getPasswordLostForm();
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $data = $form->getData();

        /** @var \PServerCore\Entity\Repository\User $userRepository */
        $userRepository = $this->getEntityManager()->getRepository($this->getEntityOptions()->getUser());
        $user = $userRepository->getUser4UserName($data['username']);

        $code = $this->getUserCodesService()->setCode4User($user, UserCodes::TYPE_LOST_PASSWORD);

        $this->getMailService()->lostPw($user, $code);

        return $user;
    }

    /**
     * @param array $data
     * @param UserCodes $userCode
     * @return bool|User
     */
    public function lostPwConfirm(array $data, UserCodes $userCode)
    {
        $form = $this->getPasswordForm();
        /** @var \PServerCore\Form\PasswordFilter $filter */
        $filter = $form->getInputFilter();
        if ($this->getEntityManagerAnswer()->getAnswer4UserId($userCode->getUser()->getId())) {
            $filter->addAnswerValidation($userCode->getUser());
        }
        $form->setData($data);
        if (!$form->isValid()) {
            return false;
        }

        $data = $form->getData();
        $plainPassword = $data['password'];
        $userEntity = $userCode->getUser();

        $this->setNewPasswordAtUser($userEntity, $plainPassword);

        $this->getUserCodesService()->deleteCode($userCode);

        if ($this->isSamePasswordOption()) {
            $gameBackend = $this->getGameBackendService();
            $gameBackend->setUser($userEntity, $plainPassword);
        }

        return $userEntity;
    }

    /**
     * @param UserCodes $userCodes
     * @return UserInterface
     */
    public function countryConfirm(UserCodes $userCodes)
    {
        $entityManager = $this->getEntityManager();

        /** @var UserInterface $userEntity */
        $userEntity = $userCodes->getUser();
        $this->setAvailableCountries4User($userEntity, $this->getIpService()->getIp());

        $entityManager->remove($userCodes);
        $entityManager->flush();

        return $userEntity;
    }

    /**
     * @param $userId
     * @return null|UserInterface
     */
    public function getUser4Id($userId)
    {
        /** @var \PServerCore\Entity\Repository\User $userRepository */
        $userRepository = $this->getEntityManager()->getRepository($this->getEntityOptions()->getUser());

        return $userRepository->getUser4Id($userId);
    }

    /**
     * @param UserInterface $user
     * @param string $plainPassword
     * @return UserInterface
     */
    protected function registerGame(UserInterface $user, $plainPassword = '')
    {
        $gameBackend = $this->getGameBackendService();

        $backendId = $gameBackend->setUser($user, $plainPassword);
        $user->setBackendId($backendId);

        $entityManager = $this->getEntityManager();
        /** user have already a backendId, so better to set it there */
        $entityManager->persist($user);
        $entityManager->flush();

        $user = $this->addDefaultRole($user);

        return $user;
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     */
    protected function addDefaultRole(UserInterface $user)
    {
        $entityManager = $this->getEntityManager();
        /** @var \PServerCore\Entity\Repository\UserRole $repositoryRole */
        $repositoryRole = $entityManager->getRepository($this->getEntityOptions()->getUserRole());
        $role = $this->getConfigService()->get('pserver.register.role', 'user');
        /** @var \PServerCore\Entity\UserRoleInterface $roleEntity */
        $roleEntity = $repositoryRole->getRole4Name($role);

        // add the ROLE + Remove the Key
        $user->addUserRole($roleEntity);
        $roleEntity->addUser($user);
        $entityManager->persist($user);
        $entityManager->persist($roleEntity);
        $entityManager->flush();

        return $user;
    }

    /**
     * @param UserInterface $user
     * @param string $ip
     */
    protected function setAvailableCountries4User(UserInterface $user, $ip)
    {
        // skip if the config say no check, so we don´t have to save the country in list
        if (!$this->getLoginOptions()->isCountryCheck()) {
            return;
        }

        $entityManager = $this->getEntityManager();
        /** @var CountryList $countryList */
        $countryList = $entityManager->getRepository($this->getEntityOptions()->getCountryList());
        $class = $this->getEntityOptions()->getAvailableCountries();
        /** @var \PServerCore\Entity\AvailableCountries $availableCountries */
        $availableCountries = new $class;
        $availableCountries->setUser($user);
        $availableCountries->setCntry($countryList->getCountryCode4Ip($this->getIpService()->getIp2Decimal($ip)));
        $entityManager->persist($availableCountries);
        $entityManager->flush();
    }

    /**
     * @param SmallUserInterface|UserInterface $user
     * @return bool
     */
    protected function isValidLogin(SmallUserInterface $user)
    {
        $result = true;

        if ($this->getLoginOptions()->isCountryCheck() && !$this->isCountryAllowed($user)) {
            $result = false;
        }

        if ($result && $this->isUserBlocked($user)) {
            $result = false;
        }

        if ($result && $this->isSecretLogin($user)) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    protected function isCountryAllowed(UserInterface $user)
    {
        $result = true;
        $entityManager = $this->getEntityManager();

        /** @var CountryList $countryList */
        $countryList = $entityManager->getRepository($this->getEntityOptions()->getCountryList());
        $country = $countryList->getCountryCode4Ip($this->getIpService()->getIp2Decimal());
        /** @var RepositoryAvailableCountries $availableCountries */
        $availableCountries = $entityManager->getRepository($this->getEntityOptions()->getAvailableCountries());

        if (!$availableCountries->isCountryAllowedForUser($user->getId(), $country)) {
            $code = $this->getUserCodesService()->setCode4User($user, UserCodes::TYPE_CONFIRM_COUNTRY);
            $this->getMailService()->confirmCountry($user, $code);
            $this->getFlashMessenger()->setNamespace(self::ErrorNameSpace)->addMessage('Please confirm your new ip with your email');
            $result = false;
        }

        return $result;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    protected function isSecretLogin(UserInterface $user)
    {
        $result = false;
        $secretLoginRoleList = $this->getLoginOptions()->getSecretLoginRoleList();

        if ($secretLoginRoleList && $userRoles = $user->getRoles()) {
            $secretLoginRoleList = array_map('strtolower', $secretLoginRoleList);
            foreach ($userRoles as $userRole) {

                if (in_array(strtolower($userRole->getRoleId()), $secretLoginRoleList)) {

                    $code = $this->getUserCodesService()->setCode4User($user, UserCodes::TYPE_SECRET_LOGIN);
                    $this->getMailService()->secretLogin($user, $code);

                    $this->getFlashMessenger()
                        ->setNamespace(self::ErrorNameSpace)
                        ->addMessage('Please confirm your secret-login with your email');

                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    protected function isUserBlocked(UserInterface $user)
    {
        $userBlocked = $this->getUserBlockService()->isUserBlocked($user);
        $result = false;

        if ($userBlocked) {
            $message = sprintf(
                'You are blocked because %s!, try it again @ %s',
                $userBlocked->getReason(),
                $userBlocked->getExpire()->format(
                    $this->getDateTimeFormatTime()
                )
            );
            $this->getFlashMessenger()->setNamespace(self::ErrorNameSpace)->addMessage($message);
            $result = true;
        }

        return $result;
    }

    /**
     * @param SmallUserInterface $user
     */
    protected function doLogin(SmallUserInterface $user)
    {
        parent::doLogin($user);
        $entityManager = $this->getEntityManager();
        /**
         * Set LoginHistory
         */
        $class = $this->getEntityOptions()->getLoginHistory();
        /** @var \PServerCore\Entity\LoginHistory $loginHistory */
        $loginHistory = new $class();
        $loginHistory->setUser($user);
        $loginHistory->setIp($this->getIpService()->getIp());
        $entityManager->persist($loginHistory);
        $entityManager->flush();
    }

    /**
     * @param SmallUserInterface $user
     * @return bool
     */
    protected function handleInvalidLogin(SmallUserInterface $user)
    {
        $maxTries = $this->getLoginOptions()->getExploit()['try'];

        if (!$maxTries) {
            return false;
        }

        $entityManager = $this->getEntityManager();
        /**
         * Set LoginHistory
         */
        $class = $this->getEntityOptions()->getLoginFailed();
        /** @var \PServerCore\Entity\LoginFailed $loginFailed */
        $loginFailed = new $class();
        $loginFailed->setUsername($user->getUsername());
        $loginFailed->setIp($this->getIpService()->getIp());
        $entityManager->persist($loginFailed);
        $entityManager->flush();

        $time = $this->getLoginOptions()->getExploit()['time'];

        /** @var \PServerCore\Entity\Repository\LoginFailed $repositoryLoginFailed */
        $repositoryLoginFailed = $entityManager->getRepository($class);

        if ($repositoryLoginFailed->getNumberOfFailLogin4Ip($this->getIpService()->getIp(), $time) >= $maxTries) {
            $class = $this->getEntityOptions()->getIpBlock();
            /** @var \PServerCore\Entity\IpBlock $ipBlock */
            $ipBlock = new $class();
            $ipBlock->setExpire(DateTimer::getDateTime4TimeStamp(time() + $time));
            $ipBlock->setIp($this->getIpService()->getIp());
            $entityManager->persist($ipBlock);
            $entityManager->flush();
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isIpAllowed()
    {
        $entityManager = $this->getEntityManager();
        /** @var \PServerCore\Entity\Repository\IPBlock $repositoryIPBlock */
        $repositoryIPBlock = $entityManager->getRepository($this->getEntityOptions()->getIpBlock());
        $ipAllowed = $repositoryIPBlock->isIPAllowed($this->getIpService()->getIp());
        $result = true;

        if ($ipAllowed) {
            $message = sprintf('Your IP is blocked!, try it again @ %s', $ipAllowed->getExpire()->format('H:i:s'));
            $this->getFlashMessenger()->setNamespace(self::ErrorNameSpace)->addMessage($message);
            $result = false;
        }

        return $result;
    }

    /**
     * Login with a User
     *
     * @param UserInterface $user
     */
    public function doAuthentication(UserInterface $user)
    {
        /** @var \PServerCore\Entity\Repository\User $repository */
        $repository = $this->getEntityManager()->getRepository($this->getUserEntityClassName());

        // fix if we have a proxy we don´t have a valid entity, so we have to clear before we can create a new select
        $username = $user->getUsername();
        $repository->clear();

        $userNew = $repository->getUser4UserName($username);

        $authService = $this->getAuthService();

        $authService->getStorage()->write($userNew);
    }

    /**
     * read from the config if system works for different pws @ web and in-game or with same
     * @return boolean
     */
    public function isSamePasswordOption()
    {
        return !(bool)$this->getPasswordOptions()->isDifferentPasswords();
    }

    /**
     * @return boolean
     */
    public function isRegisterDynamicImport()
    {
        return (bool)$this->getConfigService()->get('pserver.register.dynamic-import');
    }

    /**
     * read from the config if system works for secret question
     * @return boolean
     */
    public function isSecretQuestionOption()
    {
        return $this->getPasswordOptions()->isSecretQuestion();
    }

    /**
     * read from the config if system works with mail confirmation
     * @return boolean
     */
    public function isRegisterMailConfirmationOption()
    {
        return (bool)$this->getConfigService()->get('pserver.register.mail_confirmation');
    }

    /**
     * @return string
     */
    public function getDateTimeFormatTime()
    {
        return $this->getConfigService()->get('pserver.general.datetime.format.time');
    }

    /**
     * We want to crypt a password =)
     *
     * @param $password
     *
     * @return string
     */
    protected function bCrypt($password)
    {
        if ($this->isSamePasswordOption()) {
            $result = $this->getGameBackendService()->hashPassword($password);
        } else {
            $bCrypt = new Bcrypt();
            $result = $bCrypt->create($password);
        }

        return $result;
    }

    /**
     * @TODO better error handling
     *
     * @param array $data
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function isPwdChangeAllowed(array $data, UserInterface $user, $errorExtension)
    {
        $form = $this->getChangePwdForm();
        $form->setData($data);
        if (!$form->isValid()) {
            $this->getFlashMessenger()
                ->setNamespace(\PServerCore\Controller\AccountController::ERROR_NAME_SPACE . $errorExtension)
                ->addMessage('Form not valid.');
            return false;
        }

        $data = $form->getData();

        if (!$user->hashPassword($user, $data['currentPassword'])) {
            $this->getFlashMessenger()
                ->setNamespace(\PServerCore\Controller\AccountController::ERROR_NAME_SPACE . $errorExtension)
                ->addMessage('Wrong Password.');
            return false;
        }

        return true;
    }

    /**
     * @param UserInterface $user
     * @param $password
     * @return UserInterface
     */
    protected function setNewPasswordAtUser(UserInterface $user, $password)
    {
        $entityManager = $this->getEntityManager();
        $user->setPassword($this->bCrypt($password));

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * @param UserInterface|SmallUserInterface $user
     * @return boolean
     */
    protected function handleAuth4UserLogin(SmallUserInterface $user)
    {
        if ($this->isRegisterDynamicImport()) {
            /** @var \PServerCore\Entity\Repository\User $userRepository */
            $userRepository = $this->getEntityManager()->getRepository($this->getEntityOptions()->getUser());
            if (!$userRepository->getUser4UserName($user->getUsername())) {
                /** @var UserInterface $backendUser */
                if ($backendUser = $this->getGameBackendService()->getUser4Login($user)) {

                    if (!$backendUser->getCreateIp()) {
                        $backendUser->setCreateIp($this->getIpService()->getIp());
                    }

                    // we only save valid names
                    try {
                        if (!(new EmailAddress)->isValid($backendUser->getEmail())) {
                            $backendUser->setEmail('');
                        }
                    } catch (\Exception $e) {
                        $backendUser->setEmail('');
                    }

                    $backendUser->setPassword($this->bCrypt($user->getPassword()));
                    $entityManager = $this->getEntityManager();
                    $entityManager->persist($backendUser);
                    $entityManager->flush();

                    $this->setAvailableCountries4User($backendUser, $this->getIpService()->getIp());
                    $this->addDefaultRole($backendUser);

                    $this->doAuthentication($backendUser);

                    return true;
                }
            }
        }

        return parent::handleAuth4UserLogin($user);
    }

    /**
     * @return null|\PServerCore\Entity\Repository\SecretAnswer
     */
    protected function getEntityManagerAnswer()
    {
        return $this->getEntityManager()->getRepository($this->getEntityOptions()->getSecretAnswer());
    }

    /**
     * @param UserInterface $entity
     * @param string $plaintext
     * @return bool
     */
    public function hashPassword(UserInterface $entity, $plaintext)
    {
        if ($this->isSamePasswordOption()) {
            return $this->getGameBackendService()->isPasswordSame($entity->getPassword(), $plaintext);
        }

        $bcrypt = new Bcrypt();

        return $bcrypt->verify($plaintext, $entity->getPassword());
    }
}