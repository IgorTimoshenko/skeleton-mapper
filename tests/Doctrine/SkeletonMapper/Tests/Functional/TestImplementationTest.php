<?php

namespace Doctrine\SkeletonMapper\Tests\Functional;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\User;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserDataRepository;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserHydrator;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserPersister;
use Doctrine\SkeletonMapper\Tests\TestImplementation\User\UserRepository;

class TestImplementationTest extends BaseImplementationTest
{
    protected $objectManager;
    protected $users;
    protected $testClassName = 'Doctrine\SkeletonMapper\Tests\TestImplementation\User\User';

    protected function setUp()
    {
        $this->users = new ArrayCollection(array(
            1 => array(
                '_id' => 1,
                'username' => 'jwage',
                'password' => 'password',
            ),
            2 => array(
                '_id' => 2,
                'username' => 'romanb',
                'password' => 'password',
            ),
        ));

        $classMetadataFactory = new SkeletonMapper\Mapping\ClassMetadataFactory();
        $objectFactory = new SkeletonMapper\ObjectFactory();
        $objectRepositoryFactory = new SkeletonMapper\Repository\ObjectRepositoryFactory();
        $objectPersisterFactory = new SkeletonMapper\Persister\ObjectPersisterFactory();
        $objectIdentityMap = new SkeletonMapper\ObjectIdentityMap(
            $objectRepositoryFactory, $classMetadataFactory
        );

        // user class metadata
        $userClassMetadata = new SkeletonMapper\Mapping\ClassMetadata($this->testClassName);
        $userClassMetadata->identifier = array('_id');
        $userClassMetadata->identifierFieldNames = array('id');
        $userClassMetadata->mapField(array(
            'name' => '_id',
            'fieldName' => 'id',
        ));
        $userClassMetadata->mapField(array(
            'fieldName' => 'username',
        ));
        $userClassMetadata->mapField(array(
            'fieldName' => 'password',
        ));

        $classMetadataFactory->setMetadataFor($this->testClassName, $userClassMetadata);

        // user data repo
        $userDataRepository = new UserDataRepository($this->users);

        // user hydrator
        $userHydrator = new UserHydrator();

        // user repo
        $userRepository = new UserRepository(
            $userDataRepository,
            $objectFactory,
            $userHydrator,
            $objectIdentityMap
        );
        $objectRepositoryFactory->addObjectRepository($this->testClassName, $userRepository);

        // user persister
        $userPersister = new UserPersister($objectIdentityMap, $this->users);
        $objectPersisterFactory->addObjectPersister($this->testClassName, $userPersister);

        $unitOfWork = new SkeletonMapper\UnitOfWork(
            $objectPersisterFactory,
            $objectRepositoryFactory,
            $objectIdentityMap
        );

        $this->objectManager = new SkeletonMapper\ObjectManager(
            $objectRepositoryFactory,
            $objectPersisterFactory,
            $unitOfWork,
            $classMetadataFactory
        );
    }
}