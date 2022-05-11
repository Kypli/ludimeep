<?php

namespace App\Repository;

use App\Entity\Message;

use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Message::class);
	}

	/**
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function add(Message $entity, bool $flush = true): void
	{
		$this->_em->persist($entity);
		if ($flush) {
			$this->_em->flush();
		}
	}

	/**
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function remove(Message $entity, bool $flush = true): void
	{
		$this->_em->remove($entity);
		if ($flush) {
			$this->_em->flush();
		}
	}

	/**
	 * @return Renvoie le nombre de message non lu d'un user
	 */
	public function messageNonLue($user)
	{
		return $this->createQueryBuilder('m')
			->where('m.lu = false')
			->andWhere('m.destinataire = :user')
			->setParameter('user', $user)
			->select('COUNT(m.id)')
			->getQuery()
			->getSingleScalarResult()
		;
	}

	/**
	 * @return Renvoie le nombre de message admin non lu
	 */
	public function messageAdminNonLue()
	{
		return $this->createQueryBuilder('m')
			->where('m.lu = false')
			->select('COUNT(m.id)')
			->getQuery()
			->getSingleScalarResult()
		;
	}

	/**
	 * @return Renvoie les discussions d'un user
	 */
	public function mesDiscussions($user)
	{
		return $this->createQueryBuilder('m')
			->leftJoin('m.user', 'u')
			->leftJoin('m.destinataire', 'd')
			->where('m.user = :user')
			->setParameter('user', $user)
			->orWhere('m.destinataire = :user')
			->setParameter('user', $user)
			->groupBy('m.discussion')
			->select([
				'm.id',
				'm.libelle',
				'm.discussion',
				'm.date',
				'm.lu as message_lu',
				'd.id as destinataire_id',
				'u.id as user_id',
			])
			->getQuery()
			->getResult()
		;
	}

	/**
	 * @return Renvoie une discussion d'un user, nécessite d'être dans la discussion si non admin
	 */
	public function maDiscussion($user, $admin, $discussion)
	{
		$q = $this->createQueryBuilder('m');

		if (!$admin){
			$q
				->where('m.user = :user or m.destinataire = :user')
				->setParameter('user', $user->getId())
			;
		}

		$q
			->andWhere('m.discussion = :discussion')
			->setParameter('discussion', $discussion)
			->orderBy('m.date', 'DESC')			
		;

		return $q
			->getQuery()
			->getResult()
		;
	}

	/**
	 * @return Renvoie le nombre de message dans une discussion
	 */
	public function countMessagesInDiscussion($discussion_id)
	{
		return $this->createQueryBuilder('m')
			->where('m.discussion = :discussion')
			->setParameter('discussion', $discussion_id)
			->select('COUNT(m)')
			->getQuery()
			->getSingleScalarResult()
		;
	}

	/**
	 * @return Renvoie le nombre message non lu d'une discussion
	 */
	public function discussionLu($discussion_id, $user_id)
	{
		return $this->createQueryBuilder('m')
			->where('m.discussion = :discussion')
			->setParameter('discussion', $discussion_id)
			->andWhere('m.destinataire = :user_id')
			->setParameter('user_id', $user_id)
			->andWhere('m.lu = false')
			->select('COUNT(m.lu)')
			->getQuery()
			->getSingleScalarResult()
		;
	}

	/**
	 * @return Renvoie le numéro de la dernière discussion
	 */
	public function getLastDiscussion()
	{
		return $this->createQueryBuilder('m')
			->select('m.discussion')
			->orderBy('m.discussion', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getScalarResult()
		;
	}
}
