<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\AdminBundle\Controller;

use Sylius\Bundle\CoreBundle\CatalogPromotion\Processor\CatalogPromotionArchivalProcessorInterface;
use Sylius\Component\Promotion\Exception\CatalogPromotionAlreadyArchivedException;
use Sylius\Component\Promotion\Exception\CatalogPromotionNotFoundException;
use Sylius\Component\Promotion\Exception\InvalidCatalogPromotionStateException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ArchiveCatalogPromotionAction
{
    public function __construct(private CatalogPromotionArchivalProcessorInterface $catalogPromotionArchivalProcessor)
    {
    }

    public function __invoke(Request $request): Response
    {
        /** @var Session $session */
        $session = $request->getSession();

        try {
            $this->catalogPromotionArchivalProcessor->archiveCatalogPromotion($request->attributes->get('code'));
            $session->getFlashBag()->add('success', 'sylius.catalog_promotion.archive');

            return new RedirectResponse($request->headers->get('referer'));
        } catch (CatalogPromotionAlreadyArchivedException $e) {
            $this->catalogPromotionArchivalProcessor->restoreCatalogPromotion($request->attributes->get('code'));
            $session->getFlashBag()->add('success', 'sylius.catalog_promotion.restore');

            return new RedirectResponse($request->headers->get('referer'));
        } catch (CatalogPromotionNotFoundException) {
            throw new NotFoundHttpException('The catalog promotion has not been found');
        } catch (InvalidCatalogPromotionStateException $exception) {
            throw new BadRequestException($exception->getMessage());
        }
    }
}