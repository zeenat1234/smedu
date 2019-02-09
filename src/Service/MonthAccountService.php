<?php

namespace App\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\MonthAccount;
use App\Entity\PaymentItem;

class MonthAccountService
{
	protected $managerRegistry;
	protected $em;

	public function __construct(ManagerRegistry $managerRegistry) {
		$this->managerRegistry = $managerRegistry;
		$this->em = $this->managerRegistry->getManager();
	}

	public function calculateMonthTotal (){

		$monthAccountRepo = $this->em->getRepository(MonthAccount::class);
		$payItemRepo 	  = $this->em->getRepository(PaymentItem::class);
		$monthAccounts 	  = $monthAccountRepo->findAll();
		$changedAccounts  = 0;

		foreach ($monthAccounts as $monthAccount){
			$total = $payItemRepo->getTotalPayItemByMonth($monthAccount);

			if (!$total || $monthAccount->getTotalPrice() == $total) {
				continue;
			}


			$changedAccounts++;
			$monthAccount->setTotalPrice($total);
			$this->em->persist($monthAccount);
		}

		$this->em->flush();
		return 'Updated accounts: '.$changedAccounts;
	}
}