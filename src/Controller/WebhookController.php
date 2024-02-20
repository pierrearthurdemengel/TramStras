<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\Plan;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook/stripe', name: 'app_webhook_stripe')]
    public function index(LoggerInterface $logger, ManagerRegistry $doctrine): Response
    {
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_sk'));
		$event = null;

		// Check request
		$endpoint_secret = $this->getParameter('stripe_webhook_secret');
        // Récupère la requête / comptenu du post
		$payload = @file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
		
		try {
		    $event = \Stripe\Webhook::constructEvent(
		        $payload, $sig_header, $endpoint_secret
		    );
		} catch(\UnexpectedValueException $e) {
			// Invalid payload
			$logger->info('Webhook Stripe Invalid payload');
		    http_response_code(400);
		    exit();
		} catch(\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			$logger->info('Webhook Stripe Invalid signature');
		    http_response_code(403);
		    exit();
		}

		// Handle the event
		switch ($event->type) {
			case 'checkout.session.completed':
				$logger->info('Webhook Stripe connect checkout.session.completed');
				$session = $event->data->object;
                // récupère l'id de l'abonnement
				$subscriptionId = $session->subscription;

				$stripe = new \Stripe\StripeClient($this->getParameter('stripe_sk'));
				$subscriptionStripe = $stripe->subscriptions->retrieve($subscriptionId, array());
                // récupère l'id du plan
				$planId = $subscriptionStripe->plan->id;

				// Get user / récupère l'email de l'user
				$customerEmail = $session->customer_details->email;
				$user = $doctrine->getRepository(User::class)->findOneByEmail($customerEmail);
				if (!$user) {
                    // vérifie que l'utilisateur qui arrive sur la page de paiement est inscit et qu'on récupère bien son mail
					$logger->info('Webhook Stripe user not found');
                    //sinon 404
					http_response_code(404);
					exit();
				}

				// Disable old subscription
                dump($user->getId());
                //  on regarde si il est déjà abonné
				$activeSub = $doctrine->getRepository(Subscription::class)->findActiveSub($user->getId());
				if ($activeSub) {
                    // si il est déjà abonné, on arrête son abonnement précédent dans Stripe
					\Stripe\Subscription::update(
						$activeSub->getStripeId(), [
							'cancel_at_period_end' => false,
						]
					);
					
					$activeSub->setIsActive(false);
                    //on arrête son abonnement précédent dans la base de donnée

					$doctrine->getManager()->persist($activeSub);
				}

				// Get plan
				$plan = $doctrine->getRepository(Plan::class)->findOneBy(['stripeId' => $planId]);
                // On regarde si plan existe bien
				if (!$plan) {
                    // sinon 404
					$logger->info('Webhook Stripe plan not found');
					http_response_code(404);
					exit();
				}

                // Si tout s'est bien passé
                // on crée une nouvelle subscription
				$subscription = new Subscription();
                // avec le plan qu'on a récupéré
				$subscription->setPlan($plan);
                // avec le StripeId
				$subscription->setStripeId($subscriptionStripe->id);
                // avec la date en timestamp -> format date Symfony
				$subscription->setCurrentPeriodStart(new \Datetime(date('c', $subscriptionStripe->current_period_start)));
				$subscription->setCurrentPeriodEnd(new \Datetime(date('c', $subscriptionStripe->current_period_end)));
                // on met son user 
				$subscription->setUser($user);
                // on met active en true
				$subscription->setIsActive(true);
                // change StripeId
				$user->setStripeId($session->customer);

                // persiste la subscription
				$doctrine->getManager()->persist($subscription);
				$doctrine->getManager()->flush();
				break;

                // si invoice est paid on vérifie qu'il y a bien une subscription
			case 'invoice.paid':
				$subscriptionId = $event->data->object->subscription;
				if (!$subscriptionId) {
					$logger->info('No subscription');
					break;
				}

                // stripe envoie l'invoice.paid avant qu'on ait crée la subscription donc on dors 20s
				$subscription = null;
				for ($i = 0; $i <= 4 && $subscription === null; $i++) {
					$subscription = $doctrine->getRepository(Subscription::class)->findOneByStripeId($subscriptionId);
					if ($subscription) {
						break;
					}
					sleep(5);
				}

				$invoice = new Invoice();
				$invoice->setStripeId($event->data->object->id);
				$invoice->setSubscription($subscription);
				$invoice->setNumber($event->data->object->number);
				$invoice->setAmountPaid($event->data->object->amount_paid);
				// Hosted invoice url is now generated by formator
				$invoice->setHostedInvoiceUrl($event->data->object->hosted_invoice_url);
				
				$doctrine->getManager()->persist($invoice);
				$doctrine->getManager()->flush();

				break;
		    default:
		        // Unexpected event type
		        http_response_code(400);
		        exit();
		}

		http_response_code(200);

		$response = new Response('success');
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}