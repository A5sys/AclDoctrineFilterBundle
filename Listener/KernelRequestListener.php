<?php

namespace A5sys\AclDoctrineFilterBundle\Listener;

use A5sys\AclDoctrineFilterBundle\Filter\AclFilter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 */
class KernelRequestListener
{
    private $em;
    private $tokenStorage;
    private $roles;

    /**
     *
     * @param EntityManager $em
     * @param TokenStorage  $tokenStorage
     * @param []            $roles
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage, array $roles)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->roles = $roles;
    }

    /**
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
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

            $roles = $user->getRoles();

            if (count(array_intersect($this->roles, $roles)) > 0) {
                return false;
            }
        }

        return true;
    }
}
