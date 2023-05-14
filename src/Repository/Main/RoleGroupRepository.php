<?php

namespace App\Repository\Main;

use App\Entity\Main\RoleGroup;
use App\Model\RoleGroup\RoleGroupRepositoryCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface as PagerPaginatorInterface;

/**
 * @extends ServiceEntityRepository<RoleGroup>
 *
 * @method RoleGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleGroup[]    findAll()
 * @method RoleGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoleGroup::class);
    }

    public function add(RoleGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(RoleGroup $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCriteria(RoleGroupRepositoryCriteria $criteria, PagerPaginatorInterface $pager): array
    {

        $em = $this->getEntityManager();

        $result = $em->createQueryBuilder();

        $dql = $result->select('roleGroup')
            ->from('Main:RoleGroup', 'roleGroup')
            ->orderBy('roleGroup.name', 'ASC');

        if ($criteria->searchText !== null) {
            $dql->andWhere('roleGroup.name like :roleGroupName')
                ->setParameter('roleGroupName', '%' . $criteria->searchText  . '%');
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
