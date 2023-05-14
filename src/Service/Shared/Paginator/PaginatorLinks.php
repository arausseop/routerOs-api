<?php

namespace App\Service\Shared\Paginator;

use Symfony\Component\DependencyInjection\ContainerInterface;

class PaginatorLinks
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function generateUrlRef(
        string $route,
        ?int $page = 1,
        ?int $itemsPerPage = 10,
        ?string $searchText = null,
    ) {

        $routeParams = array();
        return $this->container->get('router')->generate($route, array_merge(
            $routeParams,
            array('page' => $page, "itemsPerPage" => $itemsPerPage, "searchText" => $searchText)
        ));
    }

    public function generateRefPaginatorLinks(
        string $route,
        int $page,
        int $itemsPerPage,
        ?string $searchText = null,
        ?int $totalCount = null
    ) {
        // die($totalCount);
        $totalPage = round($totalCount / $itemsPerPage);
        $lastPage = $totalPage == 0 ?  1 : $totalPage;
        $nextPage = $page + 1 > $totalPage ? $totalPage : $page + 1;
        $prevPage = $page - 1 == 0 ? 1 : $page - 1;

        return [
            "self"  => $this->generateUrlRef($route, $page, $itemsPerPage, $searchText, $totalCount),
            "first" => $this->generateUrlRef($route, 1, $itemsPerPage, $searchText, $totalCount),
            "last"  => $this->generateUrlRef($route, $lastPage, $itemsPerPage, $searchText, $totalCount),
            "next"  => $this->generateUrlRef($route, $nextPage, $itemsPerPage, $searchText, $totalCount),
            "prev"  => $this->generateUrlRef($route, $prevPage, $itemsPerPage, $searchText, $totalCount)
        ];
    }
}
