<?php
namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 *
 * @method Session|null find($id, $lockMode = null, $lockVersion = null)
 * @method Session|null findOneBy(array $criteria, array $orderBy = null)
 * @method Session[]    findAll()
 * @method Session[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Session::class);
    }

    public function save(Session $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Session $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les sessions actives
     */
    public function findActive(): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.is_active = :active')
            ->setParameter('active', true)
            ->orderBy('s.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sessions par centre
     */
    public function findByCentre(int $centreId): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.centre = :centreId')
            ->setParameter('centreId', $centreId)
            ->orderBy('s.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sessions par formation
     */
    public function findByFormation(int $formationId): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.formation = :formationId')
            ->setParameter('formationId', $formationId)
            ->orderBy('s.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les sessions par formateur
     */
    public function findByFormateur(int $formateurId): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.formateur_responsable = :formateurId')
            ->setParameter('formateurId', $formateurId)
            ->orderBy('s.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Session[] Returns an array of Session objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Session
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
