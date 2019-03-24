<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\SchoolYear;

class CalculatePenaltyCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:calculate-penalty';

    private $em;

    public function __construct(?string $name = null, EntityManagerInterface $em) {
      parent::__construct($name);

      $this->em = $em;
    }

    protected function configure()
    {
      $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Calculates Penalties.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command looks through all invoices and updates penalties.')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $output->writeln([
        'Penalty Calculator',
        '============',
        '',
      ]);

      $currentSchoolYear = $this->em->getRepository
      (SchoolYear::class)->findCurrentYear();

      foreach ($currentSchoolYear->getSchoolunits() as $unit ) {
        foreach ($unit->getEnrollments() as $enrollment) {
          if ($enrollment->getIsActive() && $enrollment->getDaysToPay() > 0) {
            $student = $enrollment->getStudent();
            foreach ($student->getMonthAccounts() as $monthAccount) {
              //the following is a special request for one of our clients
              if ($monthAccount->getAccYearMonth() > (new \DateTime('2018/12/15'))) {
                foreach ($monthAccount->getAccountInvoices() as $invoice) {
                  $hasServiceTax = false;
                  $serviceTaxSum = 0;
                  foreach ($invoice->getPaymentItems() as $payItem) {
                    if (!empty($payItem->getItemService()) && $invoice->getIsLocked()) {
                      $hasServiceTax = true;
                      $serviceTaxSum = $serviceTaxSum + $payItem->getItemPrice() * $payItem->getItemCount();
                    }
                  }
                  if ($hasServiceTax && ($serviceTaxSum > 0)) {
                    $now = new \DateTime('now');
                    if ($invoice->getIsPaid() == false) {
                      if ($invoice->getInvoiceDate()->diff($now)->format("%a") > $enrollment->getDaysToPay()) {
                        //verify if we have any pending payments
                        $hasPendingPay = false;
                        foreach ($invoice->getPayments() as $payment) {
                          if ($payment->getIsPending() == true) {
                            $hasPendingPay = true;
                          }
                        }
                        if (!$hasPendingPay) {
                          // give another grace period if a partial payment was made
                          $penaltyDays = 0;
                          $penaltySum = 0;
                          $partialPenaltyDays = 0;
                          $partialPenaltySum = 0;

                          if (!empty($invoice->getInvoicePaidDate())) {
                            if ($invoice->getInvoicePaid() < $serviceTaxSum) {
                              if ($invoice->getInvoicePaidDate()->diff($now)->format("%a") > $enrollment->getDaysToPay()) {
                                $partialPenaltyDays = $invoice->getInvoicePaidDate()->diff($now)->format("%a") - $enrollment->getDaysToPay();
                                $partialPenaltySum = 0.01 * ($serviceTaxSum - $invoice->getInvoicePaid());
                                $invoice->setPartialPenaltyDays($partialPenaltyDays);
                                $invoice->setPartialPenaltySum($partialPenaltySum);
                              }
                            }
                          } else {
                            $penaltyDays = $invoice->getInvoiceDate()->diff($now)->format("%a") - $enrollment->getDaysToPay();
                            $penaltySum = 0.01 * $serviceTaxSum;
                            $invoice->setPenaltyDays($penaltyDays);
                            $invoice->setPenaltySum($penaltySum);
                          }
                          $entityManager = $this->em;
                          $entityManager->persist($invoice);
                          $entityManager->flush();

                          // $this->get('session')->getFlashBag()->add(
                          //   'notice',
                          //   "SUMAR: \n".$student->getUser()->getRoName()." --> Zile: ".$invoice->getPenaltyDays()." (".$invoice->getPartialPenaltyDays().")\n".
                          //   "--------> "."Suma pe zi: ".$invoice->getPenaltySum()." (".$invoice->getPartialPenaltySum().")"
                          // );

                          $output->writeln([
                            "SUMAR: ".$student->getUser()->getRoName()." --> Zile Penalități: ".$invoice->getPenaltyDays()." (".$invoice->getPartialPenaltyDays().")",
                            "--------> "."Suma pe zi: ".$invoice->getPenaltySum()." (".$invoice->getPartialPenaltySum().")",
                          ]);

                        }
                      }
                    }
                  } //end if has service tax
                } //end foreach $invoice
              }
            }
          }
        }
      }

    }
}
