<?php

namespace App\Repository\Main;

use App\Entity\Main\User;
use App\Model\User\UserRepositoryCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
// use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        $entityManager = $this->getEntityManager();

        // Check if the identifier is an email address
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->findOneBy(['email' => $identifier]);
        }
        if (Uuid::isValid($identifier)) {
            return $this->findOneBy(['uuid' => Uuid::fromString($identifier)]);
        }
        return null;
    }

    public function findByCriteria(UserRepositoryCriteria $criteria, PaginatorInterface $pager): array
    {

        $em = $this->getEntityManager();

        $result = $em->createQueryBuilder();

        $dql = $result->select('user')
            ->from('Main:User', 'user')
            ->orderBy('user.firstName', 'ASC');

        if ($criteria->searchText !== null) {
            $dql->andWhere('user.firstName like :userName')
                ->orwhere('user.lastName like :userName')
                ->setParameter('userName', '%' . $criteria->searchText  . '%');
        }

        $paginatedData = $pager->paginate(
            $dql->getQuery(),
            $criteria->page,
            $criteria->itemsPerPage
        );

        return [
            'items' => $paginatedData->getItems(),
            'totalCount' => $paginatedData->getTotalItemCount()
        ];
    }
}
