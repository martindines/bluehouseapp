<?php

namespace Bluehouseapp\Bundle\CoreBundle\Entity;

use Bluehouseapp\Bundle\CoreBundle\Doctrine\ORM\EntityRepository;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends EntityRepository
{

    public function countPostsByNode($node)
    {


        $qb = parent::getQueryBuilder()
            ->select('COUNT(p)')
            ->innerJoin('p.member', 'm')
            ->where('p.node = :node')
            ->andWhere('p.status = :postStatus')
            ->andWhere('p.enabled = :postEnabled')
            ->andWhere('m.locked = :mLocked')
            ->setParameters(array('node' => $node, 'postStatus' => true,
                'postEnabled' => true,
                'mLocked' => false
            ));
        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function  getPost($postId)
    {
        $post = null;

        $query = parent::getQueryBuilder()
            ->innerJoin('p.member', 'm')
            ->innerJoin('p.node', 'n')
            ->innerJoin('n.category', 'c')
            ->where('p.id = :id')
            ->andWhere('p.status = :status')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('n.status = :status')
            ->andWhere('n.enabled = :enabled')
            ->andWhere('c.status = :status')
            ->andWhere('c.enabled = :enabled')
            ->andWhere('m.locked = :mLocked')
            ->setParameters(array(':id' => $postId,
                'status' => true, 'enabled' => true,
                'mLocked' => false
            ))
            ->getQuery();

        try {
            $post = $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            $post = null;
        }

        return $post;

    }

    public function  getPostByNode($currentNodeId)
    {
        $queryBuilder = parent::getQueryBuilder()
            ->innerJoin('p.node', 'n')
            ->innerJoin('p.member', 'm')
            ->innerJoin('n.category', 'c')
            ->orderBy('p.lastCommentTime', 'desc')
            ->where('n.id = :currentNodeId')
            ->andWhere('p.status = :status')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('n.status = :status')
            ->andWhere('n.enabled = :enabled')
            ->andWhere('c.status = :status')
            ->andWhere('c.enabled = :enabled')
            ->andWhere('m.locked = :mLocked')
            ->setParameters(array('currentNodeId' => $currentNodeId,
                'status' => true, 'enabled' => true,
                'mLocked' => false
            ));
        return parent::getPaginator($queryBuilder);
    }

    public function  getPostByCategory($currentCategory)
    {

        $queryBuilder = parent::getQueryBuilder()
            ->innerJoin('p.node', 'n')
            ->innerJoin('p.member', 'm')
            ->innerJoin('n.category', 'c')
            ->orderBy('p.lastCommentTime', 'desc')
            ->where('c.id = :categoryId')
            ->andWhere('p.status = :status')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('n.status = :status')
            ->andWhere('n.enabled = :enabled')
            ->andWhere('c.status = :status')
            ->andWhere('c.enabled = :enabled')
            ->andWhere('m.locked = :mLocked')
            ->setParameters(array('categoryId' => ($currentCategory == null ? 0 : $currentCategory->getId()),
                'status' => true, 'enabled' => true,
                'mLocked' => false
            ));


        return parent::getPaginator($queryBuilder);
    }

    public function  getPostsByMember($member)
    {
        $posts = null;

        $queryBuilder = parent::getQueryBuilder()
            ->innerJoin('p.member', 'm')
            ->innerJoin('p.node', 'n')
            ->innerJoin('n.category', 'c')
            ->where('p.member = :member')
            ->andWhere('m.locked = :mLocked')
            ->andWhere('p.status = :status')
            ->andWhere('p.enabled = :enabled')
            ->andWhere('n.status = :status')
            ->andWhere('n.enabled = :enabled')
            ->andWhere('c.status = :status')
            ->andWhere('c.enabled = :enabled')
            ->setParameters(array('member' => $member,
                'mLocked' => false,
                'status' => true, 'enabled' => true,
                'mLocked' => false
            ))
            ->orderBy('p.modified', 'desc');

        return $this->getPaginator($queryBuilder);

    }

    protected function getAlias()
    {
        return 'p';
    }
}
