<?php

namespace A5sys\AclDoctrineFilterBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class AclAnnotation
{
    const ACL_ANNOTATION = 'A5sys\AclDoctrineFilterBundle\Annotation\AclAnnotation';

    const USER_ID = '##USERID##';
    const TABLEALIAS = '##TABLEALIAS##';

    public $aclSql;
}
