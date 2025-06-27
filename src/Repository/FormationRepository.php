<?php
namespace App\Repository;

use App\Entity\Formation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Formation>
 *
 * @method Formation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formation[]    findAll()
 * @method Formation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormationRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Formation::class);
    }

    public function save(Formation $entity, bool $flush = false): void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Formation $entity, bool $flush = false): void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve une formation par son slug
     */
    public function findBySlug(string $slug): ?Formation {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * Trouve les formations publiées
     */
    public function findPublished(): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.published = :published')
            ->setParameter('published', true)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations par centre
     */
    public function findByCentre(int $centreId): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.centre = :centreId')
            ->setParameter('centreId', $centreId)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations par thématique
     */
    public function findByThematique(string $thematique): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.thematique = :thematique')
            ->andWhere('f.published = :published')
            ->setParameter('thematique', $thematique)
            ->setParameter('published', true)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations par niveau
     */
    public function findByNiveau(string $niveau): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.niveau = :niveau')
            ->andWhere('f.published = :published')
            ->setParameter('niveau', $niveau)
            ->setParameter('published', true)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de formations par terme
     */
    public function search(string $term): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.titre LIKE :term OR f.description LIKE :term OR f.thematique LIKE :term')
            ->andWhere('f.published = :published')
            ->setParameter('term', '%' . $term . '%')
            ->setParameter('published', true)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations par responsable
     */
    public function findByResponsable(int $responsableId): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.responsable = :responsableId')
            ->setParameter('responsableId', $responsableId)
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations avec leurs sessions
     */
    public function findWithSessions(): array {
        return $this->createQueryBuilder('f')
            ->leftJoin('f.sessions', 's')
            ->addSelect('s')
            ->orderBy('f.titre', 'ASC')
            ->addOrderBy('s.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les formations populaires (avec le plus de sessions)
     */
    public function findPopular(int $limit = 10): array {
        return $this->createQueryBuilder('f')
            ->select('f', 'COUNT(s.id) as sessionCount')
            ->leftJoin('f.sessions', 's')
            ->andWhere('f.published = :published')
            ->setParameter('published', true)
            ->groupBy('f.id')
            ->orderBy('sessionCount', 'DESC')
            ->addOrderBy('f.titre', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
