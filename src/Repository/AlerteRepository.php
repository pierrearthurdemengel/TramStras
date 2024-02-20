<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Alerte;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;



/**
 * @extends ServiceEntityRepository<Alerte>
 *
 * @method Alerte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Alerte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Alerte[]    findAll()
 * @method Alerte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlerteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alerte::class);
    }

    public function save(Alerte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Alerte $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLatestAlert(): ?Alerte
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.alerteDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    // src/Repository/AlerteRepository.php

public function findLatestAlertId(): ?array
{
    $entityManager = $this->getEntityManager();

    $query = $entityManager->createQuery(
        'SELECT a.id FROM App\Entity\Alerte a ORDER BY a.alerteDate DESC'
    )->setMaxResults(1);

    return $query->getOneOrNullResult();
}

public function findUnreadByUser($user)
{
    $this->logger->info('Executing findUnreadByUser query for user: ' . $user->getPseudo());

    return $this->createQueryBuilder('a')
        ->where('a.user = :user')
        ->andWhere('a.isRead = false') 
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}

public function getAlerteStatistics()
{
    $rsm = new ResultSetMapping();
    $rsm->addScalarResult('ligne', 'ligne');
    $rsm->addScalarResult('heure', 'heure');
    $rsm->addScalarResult('nbAlertes', 'nbAlertes');

    $sql = "SELECT a.ligne, DATE_FORMAT(a.alerte_date, '%H') as heure, COUNT(a.id) as nbAlertes 
            FROM alerte a 
            GROUP BY a.ligne, heure 
            ORDER BY nbAlertes DESC";

    $query = $this->_em->createNativeQuery($sql, $rsm);

    return $query->getResult();
}

public function getAllDistinctLignes()
{
    $queryBuilder = $this->createQueryBuilder('a')
        ->select('DISTINCT a.ligne');
    
    $results = $queryBuilder->getQuery()->getResult();
    
    // Transformez les résultats en un tableau simple si nécessaire
    $lignes = array_map(function($row) {
        return $row['ligne'];
    }, $results);
    
    return $lignes;
}



}
