<?php
// src/Service/DocumentNumberGenerator.php
namespace App\Service;

use App\Entity\Document;
use Doctrine\ORM\EntityManagerInterface;

final class DocumentNumberGenerator {
    public function __construct(private EntityManagerInterface $em) {}

    public function nextForDevis(): string {
        $year    = (new \DateTimeImmutable())->format('Y');
        $pattern = sprintf('DEV-%s-%%', $year);

        $count = (int) $this->em->createQueryBuilder()
            ->select('COUNT(d.id)')
            ->from(Document::class, 'd')
            ->where('d.type = :t')
            ->andWhere('d.number LIKE :pattern')
            ->setParameter('t', Document::TYPE_DEVIS)
            ->setParameter('pattern', $pattern)
            ->getQuery()
            ->getSingleScalarResult();

        $seq = str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        return sprintf('DEV-%s-%s', $year, $seq);
    }
    public function nextForConvention(): string {
        $year    = (new \DateTimeImmutable())->format('Y');
        $pattern = sprintf('CNV-%s-%%', $year);

        $count = (int) $this->em->createQueryBuilder()
            ->select('COUNT(d.id)')
            ->from(\App\Entity\Document::class, 'd')
            ->where('d.type = :t')->setParameter('t', \App\Entity\Document::TYPE_CONVENTION)
            ->andWhere('d.number LIKE :p')->setParameter('p', $pattern)
            ->getQuery()->getSingleScalarResult();

        $seq = str_pad((string) ($count + 1), 5, '0', STR_PAD_LEFT);
        return sprintf('CNV-%s-%s', $year, $seq);
    }
}
