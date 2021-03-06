<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public static function createNonDeletedCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('isDeleted', false))
            ->orderBy(['createdAt' => 'DESC'])
            ;
    }
    /**
     * @param string|null $term
     */
    public function getWithSearchQueryBuilder(?string $term): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->innerJoin('c.article', 'a')
            ->addSelect('a') // With this line, we're telling the QueryBuilder to select all of the comment columns and all of the article columns.
        ;

        /*
            How can we do this? Hmm, the QueryBuilder apparently has an orWhere() method.
            Perfect, right? No! Surprise, I never use this method.
            Why? Imagine a complex query with various levels of AND clauses mixed with OR clauses and parenthesis.

            with $qb->andWhere
            SELECT * FROM comment c
                WHERE
                    c.is_deleted = 0
                    AND (
                        c.author_name LIKE '%foo%'
                        OR
                        c.content LIKE '%foo%'
                    )

            DO NOT with $qb->orWhere - because it will return comment with is_deleted = 0
            SELECT * FROM comment c
                WHERE
                    c.is_deleted = 0
                    AND
                    c.author_name LIKE '%foo%'
                    OR
                    c.content LIKE '%foo%'

            With a complex query like this, you would need to be very careful to use the parenthesis in just the right places.
            One mistake could lead to an OR causing many more results to be returned than you expect!
         */
        if ($term) {
            $qb->andWhere('c.content LIKE :term OR c.authorName LIKE :term OR a.title LIKE :term')
                ->setParameter('term', '%'.$term.'%')
            ;
        }

        return $qb
            ->orderBy('c.createdAt', 'DESC')
            // ->getQuery() Removed for pagination
            // ->getResult()
        ;
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
