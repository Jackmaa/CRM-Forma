<?php
namespace App\Repository;

use App\Entity\Centre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Centre>
 *
 * @method Centre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Centre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Centre[]    findAll()
 * @method Centre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CentreRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Centre::class);
    }

    public function save(Centre $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Centre $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve un centre par son SIRET
     */
    public function findBySiret(string $siret): ?Centre {
        return $this->findOneBy(['siret' => $siret]);
    }

    public function findByCentre(Centre $centre): array {
        return $this->createQueryBuilder('s')
            ->join('s.formation', 'f')->addSelect('f')
            ->andWhere('f.centre = :c')->setParameter('c', $centre)
            ->orderBy('s.dateDebut', 'DESC')
            ->getQuery()->getResult();

    }
}