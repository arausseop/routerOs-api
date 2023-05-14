<?php

namespace App\Repository\Main;

use App\Entity\Main\Module;
use App\Model\Module\ModuleRepositoryCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Module>
 *
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    public function save(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Module $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCriteria(ModuleRepositoryCriteria $criteria, PaginatorInterface $pager): array
    {

        $em = $this->getEntityManager();

        $result = $em->createQueryBuilder();

        $dql = $result->select('module')
            ->from('Main:Module', 'module')
            ->orderBy('module.name', 'ASC');

        if ($criteria->searchText !== null) {
            $dql->andwhere('module.name like :moduleName')
            ->setParameter('moduleName', '%' . $criteria->searchText . '%');
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
