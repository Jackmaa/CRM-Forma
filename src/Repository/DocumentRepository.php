<?php
namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Document::class);
    }

    public function searchDevis(?string $status, ?int $year, string $q = '', int $page = 1, int $limit = 20): array {
        $qb = $this->createQueryBuilder('d')
            ->andWhere('d.type = :type')->setParameter('type', \App\Entity\Document::TYPE_DEVIS);

        if ($status) {$qb->andWhere('d.status = :s')->setParameter('s', $status);}
        if ($q !== '') {$qb->andWhere('d.number LIKE :q')->setParameter('q', '%' . $q . '%');}
        if ($year) {$qb->andWhere('YEAR(d.createdAt) = :y')->setParameter('y', $year);}

        $count = (int) (clone $qb)->select('COUNT(d.id)')->getQuery()->getSingleScalarResult();
        $items = $qb->orderBy('d.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)->setMaxResults($limit)
            ->getQuery()->getResult();

        return ['items' => $items, 'total' => $count];
    }

}
