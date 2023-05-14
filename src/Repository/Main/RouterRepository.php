<?php

namespace App\Repository\Main;

use ApiPlatform\State\Pagination\PaginatorInterface;
use App\Entity\Main\Router;
use App\Model\Router\RouterRepositoryCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface as PagerPaginatorInterface;

/**
 * @extends ServiceEntityRepository<Router>
 *
 * @method Router|null find($id, $lockMode = null, $lockVersion = null)
 * @method Router|null findOneBy(array $criteria, array $orderBy = null)
 * @method Router[]    findAll()
 * @method Router[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RouterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Router::class);
    }

    public function save(Router $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Router $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByCriteria(RouterRepositoryCriteria $criteria, PagerPaginatorInterface $pager): array
    {

        $em = $this->getEntityManager();

        $result = $em->createQueryBuilder();

        $dql = $result->select('router')
            ->from('Main:Router', 'router')
            ->orderBy('router.identity', 'ASC');

        if ($criteria->searchText !== null) {
            $dql->andWhere('router.identity like :routerIdentity')
                ->setParameter('routerIdentity', '%' . $criteria->searchText  . '%');
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
