<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;


/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }
    public function findAllQuery()
    {
        return $this->createQueryBuilder('a')
            ->getQuery();
    }

    public function findByMultipleFieldsQuery($searchTerm)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $expr = new Expr();

        $fields = [
            'Libelle',
            'is_disponible',
            'price',
            'marque',
            
        ];

        $orX = $expr->orX();
        foreach ($fields as $field) {
            $orX->add($expr->like('a.' . $field, ':searchTerm'));
        }

        
        $queryBuilder->leftJoin('a.categorieId', 'c');
        $orX->add($expr->like('c.nomCategorie', ':searchTerm'));

        $queryBuilder->where($orX)->setParameter('searchTerm', '%' . $searchTerm . '%');

        return $queryBuilder->getQuery();
    }


//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
