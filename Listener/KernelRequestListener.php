<?php

namespace A5sys\AclDoctrineFilterBundle\Listener;

use A5sys\AclDoctrineFilterBundle\Filter\AclFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class KernelRequestListener
{
    private $em;
    private $tokenStorage;
    private $roles;

    /**
     *
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface  $tokenStorage
     * @param string[]               $roles
     */
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, array $roles)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->roles = $roles;
    }

    /**
     *
     * @param ViewEvent $event
     */
    public function onKernelRequest(ViewEvent $event)
    {
        $disableFilters = !$this->isAclEnabled();

        $filters = $this->em->getFilters();
        foreach ($filters->getEnabledFilters() as $filterName => $filter) {
            if ($filter instanceof AclFilter) {
                if ($disableFilters) {
                    $this->em->getFilters()->disable($filterName);
                } else {
                    $this->setFilterParameters($filter);
                }
            }
        }
    }

    /**
     *
     * @param AclFilter $filter
     */
    protected function setFilterParameters(AclFilter $filter)
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();

            if ($filter instanceof AclFilter) {
                $filter->setUser($user);
            }
        }
    }

    /**
     * @return boolean
     */
    protected function isAclEnabled()
    {
        $token = $this->tokenStorage->getToken();
        if ($token) {
            /* @var $token UserInterface */
            $user = $token->getUser();
            if (!$user instanceof UserInterface) {
                return false;
            }

            $roles = $user->getRoles();

            if (count(array_intersect($this->roles, $roles)) > 0) {
                return false;
            }
        }

        return true;
    }
}
