<?php

namespace A5sys\AclDoctrineFilterBundle\Filter;

use A5sys\AclDoctrineFilterBundle\Annotation\AclAnnotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *
 */
class AclFilter extends SQLFilter
{
    private $user;

    /**
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface the user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param ClassMetadata $targetEntity
     * @param string        $targetTableAlias
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $result = "";
        $reflectionClass = $targetEntity->getReflectionClass();
        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation($reflectionClass, AclAnnotation::ACL_ANNOTATION);

        if ($annotation) {
            if (empty($annotation->aclSql)) {
                $entityName = $targetEntity->getName();
                throw new \LogicException('Please provide the ACL SQL for the entity '.$entityName);
            }

            $rawSql = $annotation->aclSql;
            $user = $this->user;
            $userReplaced = str_replace(AclAnnotation::USER_ID, $user->getId(), $rawSql);
            $result = str_replace(AclAnnotation::TABLEALIAS, $targetTableAlias, $userReplaced);
        }

        return $result;
    }
}
