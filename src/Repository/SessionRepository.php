<?php
namespace App\Repository;

use App\Entity\Centre;
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

    public function countActive(Centre $centre): int {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.centre = :centre')
            ->andWhere('s.dateDebut <= :now')
            ->andWhere('s.dateFin   >= :now')
            ->setParameter('centre', $centre)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFinishedThisMonth(Centre $centre): int {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.centre = :centre')
            ->andWhere('s.dateFin BETWEEN :start AND :end')
            ->setParameter('centre', $centre)
            ->setParameter('start', (new \DateTime('first day of this month'))->setTime(0, 0))
            ->setParameter('end', (new \DateTime('last day of this month'))->setTime(23, 59, 59))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
